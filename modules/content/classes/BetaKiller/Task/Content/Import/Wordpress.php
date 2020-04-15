<?php

namespace BetaKiller\Task\Content\Import;

use Arr;
use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Content\Shortcode\AttachmentShortcode;
use BetaKiller\Content\Shortcode\GalleryShortcode;
use BetaKiller\Content\Shortcode\ImageShortcode;
use BetaKiller\Content\Shortcode\YoutubeShortcode;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Exception\ValidationException;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Model\ContentCategory;
use BetaKiller\Model\ContentComment;
use BetaKiller\Model\ContentGallery;
use BetaKiller\Model\ContentGalleryInterface;
use BetaKiller\Model\ContentImageInterface;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\ContentYoutubeRecord;
use BetaKiller\Model\Entity;
use BetaKiller\Model\Quote;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\WordpressAttachmentInterface;
use BetaKiller\Repository\WordpressAttachmentRepositoryInterface;
use BetaKiller\Service\UserService;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use DateTime;
use DateTimeImmutable;
use DiDom\Document;
use Throwable;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface as ThunderShortcodeInterface;
use Valid;
use WP;

class Wordpress extends AbstractTask
{
    private const ATTACH_PARSING_MODE_HTTP  = 'http';
    private const ATTACH_PARSING_MODE_LOCAL = 'local';

    private const WP_OPTION_PARSING_MODE = 'betakiller_parsing_mode';
    private const WP_OPTION_PARSING_PATH = 'betakiller_parsing_path';

    /**
     * @var string
     */
    private $attachParsingMode;

    /**
     * @var string
     */
    private $attachParsingPath;

    /**
     * @var string[]
     */
    private $unknownBbTags = [];

    /**
     * @var \DateTimeImmutable
     */
    private $skipBeforeDate;

    /**
     * @var Entity
     */
    private $contentPostEntity;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Helper\ContentHelper
     * @Inject
     */
    private $contentHelper;

    /**
     * @var \BetaKiller\Factory\EntityFactoryInterface
     * @Inject
     */
    private $entityFactory;

    /**
     * @var \BetaKiller\Repository\ContentPostRepository
     * @Inject
     */
    private $postRepository;

    /**
     * @var \BetaKiller\Repository\ContentCommentRepository
     * @Inject
     */
    private $commentRepository;

    /**
     * @var \BetaKiller\Workflow\ContentCommentWorkflow
     * @Inject
     */
    private $commentWorkflow;

    /**
     * @var \BetaKiller\Repository\ContentCategoryRepository
     * @Inject
     */
    private $categoryRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\ContentGalleryRepository
     */
    private $galleryRepository;

    /**
     * @var \BetaKiller\Repository\ContentYoutubeRecordRepository
     * @Inject
     */
    private $youtubeRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\EntityRepository
     */
    private $entityRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\QuoteRepository
     */
    private $quoteRepository;

    /**
     * @Inject
     * @var \BetaKiller\Repository\UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @Inject
     * @var \BetaKiller\Service\UserService
     */
    private $userService;

    /**
     * @Inject
     * @var \BetaKiller\Content\Shortcode\ShortcodeFacade
     */
    private $shortcodeFacade;

    /**
     * @Inject
     * @var \BetaKiller\Workflow\StatusWorkflowFactory
     */
    private $statusWorkflowFactory;

    /**
     * @Inject
     * @var \BetaKiller\Assets\ContentTypes
     */
    private $contentTypes;

    /**
     * @Inject
     * @var \WP
     */
    private $wp;

    /**
     * @Inject
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $loadedTmpFiles = [];

    public function defineOptions(): array
    {
        return [
            'skip-before' => null,
        ];
    }

    /**
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Workflow\WorkflowException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     * @throws \BetaKiller\Exception\ValidationException
     */
    public function run(): void
    {
        $skipBefore = $this->getOption('skip-before');
        if ($skipBefore) {
            $this->skipBeforeDate = new DateTimeImmutable($skipBefore);
        }

        $this->contentPostEntity = $this->entityRepository->findByModelName('ContentPost');

        $this->configureDialog();

        // Users
        $this->importUsers();

        // Posts
        $this->importPostsAndPages();

        // Categories
        $this->importCategories();

        // Comments to posts and pages
        $this->importComments();

        // Quotes plugin
        $this->importQuotes();

        // Cleanup temp files
        foreach ($this->loadedTmpFiles as $loadedTmpFile) {
            unlink($loadedTmpFile);
        }
    }

    /**
     * @throws \BetaKiller\Task\TaskException
     */
    private function configureDialog(): void
    {
        $parsingMode = $this->wp->getOption(self::WP_OPTION_PARSING_MODE);

        if (!$parsingMode) {
            $parsingMode = $this->read('Select parsing mode', [
                self::ATTACH_PARSING_MODE_HTTP,
                self::ATTACH_PARSING_MODE_LOCAL,
            ]);
        }

        $this->logger->info('Parsing mode is: '.$parsingMode);


        $parsingPath = $this->wp->getOption(self::WP_OPTION_PARSING_PATH);

        if (!$parsingPath) {
            if ($parsingMode === self::ATTACH_PARSING_MODE_HTTP) {
                $parsingPath = $this->read('Input fully qualified project URL');

                $parsingPath = rtrim($parsingPath, '/').'/';

                if (!Valid::url($parsingPath)) {
                    throw new TaskException('Incorrect project URL');
                }
            } elseif ($parsingMode === self::ATTACH_PARSING_MODE_LOCAL) {
                $parsingPath = $this->read('Input absolute project path');

                $parsingPath = '/'.trim($parsingPath, '/');

                if (!is_dir($parsingPath) || !file_exists($parsingPath)) {
                    throw new TaskException('Incorrect project path');
                }
            }
        }

        $this->logger->info('Parsing path is: '.$parsingPath);

        $this->attachParsingMode = $parsingMode;
        $this->attachParsingPath = $parsingPath;

        $this->wp->setOption(self::WP_OPTION_PARSING_MODE, $parsingMode);
        $this->wp->setOption(self::WP_OPTION_PARSING_PATH, $parsingPath);
    }

    /**
     * @param array                                                    $attach
     * @param int                                                      $entityItemID
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface|null $provider
     *
     * @return \BetaKiller\Model\WordpressAttachmentInterface
     * @throws \BetaKiller\Task\TaskException
     */
    private function createWordpressAttachment(
        array $attach,
        int $entityItemID,
        AssetsProviderInterface $provider
    ): WordpressAttachmentInterface {
        $url = $attach['guid'];

        if (!$url) {
            throw new TaskException('Empty attach data');
        }

        $this->logger->debug('Found attach with guid = :url', [':url' => $url]);

        return $this->storeAttachment($provider, $url, $entityItemID);
    }

    /**
     * @param \BetaKiller\Model\WordpressAttachmentInterface $model
     */
    private function importWpAttachmentData(WordpressAttachmentInterface $model): void
    {
        $wpPath = $model->getWpPath();

        $wpData = $this->wp->getAttachmentByPath($wpPath);

        if (!$wpData) {
            $this->logger->warning('No WP data for attachment :path', [':path' => $wpPath]);

            return;
        }

        $wpID = (int)$wpData['ID'];
        $model->setWpId($wpID);

        // Save created_at + updated_at
        $createdAt = new DateTimeImmutable($wpData['post_date']);
        $updatedAt = new DateTimeImmutable($wpData['post_modified']);

        $model->setUploadedAt($createdAt);
        $model->setLastModifiedAt($updatedAt);

        // Set title for images
        if ($model instanceof AssetsModelImageInterface) {

            if (!$model->getTitle()) {
                $title = $wpData['post_excerpt'] ?: $wpData['post_title'];
                $model->setTitle($title);
            }

            if (!$model->getAlt()) {
                $meta = $this->wp->getPostMeta($wpID);
                $model->setAlt($meta['_wp_attachment_image_alt'] ?? '');
            }
        }
    }

    /**
     * @param AssetsProviderInterface $provider
     * @param string                  $url
     * @param int|null                $entityItemID
     *
     * @return \BetaKiller\Model\WordpressAttachmentInterface
     * @throws TaskException
     */
    private function storeAttachment(
        AssetsProviderInterface $provider,
        string $url,
        ?int $entityItemID = null
    ): WordpressAttachmentInterface {
        $repository = $provider->getRepository();

        if (!($repository instanceof WordpressAttachmentRepositoryInterface)) {
            throw new TaskException('Attachment repository [:name] must be instance of :must', [
                ':name' => $repository::getCodename(),
                ':must' => WordpressAttachmentRepositoryInterface::class,
            ]);
        }

        $urlPath          = parse_url($url, PHP_URL_PATH);
        $originalFilename = basename($url);

        /** @var WordpressAttachmentInterface $model */
        $model = $repository->findByWpPath($urlPath);

        if ($model) {
            $this->logger->debug('Attach with WP path = :path already exists', [':path' => $urlPath]);

            // Fetch data from WP by path
            $this->importWpAttachmentData($model);

            return $model;
        }

        $this->logger->debug('Adding attach with WP path = :path', [':path' => $urlPath]);

        // Getting path for local file with attachment content
        $path = $this->getAttachmentPath($urlPath, $provider->getAllowedMimeTypes());

        if (!$path) {
            throw new TaskException('Can not get path for guid = :url', [':url' => $url]);
        }

        /** @var \BetaKiller\Model\WordpressAttachmentInterface $model */
        $model = $provider->store($path, $originalFilename, $this->user);

        if ($model instanceof \BetaKiller\Model\EntityItemRelatedInterface) {
            // Storing entity
            $model->setEntity($this->contentPostEntity);

            // Storing entity item ID
            if ($entityItemID) {
                $model->setEntityItemID($entityItemID);
            }
        }

        // Storing WP path
        $model->setWpPath($urlPath);

        // Fetch data from WP by path
        $this->importWpAttachmentData($model);

        $this->logger->info('Attach with WP path = :path successfully stored', [':path' => $urlPath]);

        return $model;
    }

    /**
     * @param string     $originalUrlPath
     * @param array|bool $expectedMimes
     *
     * @return bool|null|string
     * @throws \BetaKiller\Task\TaskException
     */
    private function getAttachmentPath(string $originalUrlPath, $expectedMimes): ?string
    {
        if ($this->attachParsingMode === self::ATTACH_PARSING_MODE_HTTP) {
            $url = $this->attachParsingPath.ltrim($originalUrlPath, '/');

            $this->logger->debug('Loading attach at url = :url', [':url' => $url]);

            try {
                // TODO Replace with system-wide crawler
                throw new NotImplementedHttpException('Implement http crawler');
                $request  = Request::factory($url);
                $response = $request->execute();
            } catch (\Request_Exception $e) {
                throw TaskException::wrap($e);
            } catch (HttpExceptionInterface $e) {
                throw TaskException::wrap($e);
            }

            if ($response->status() !== 200) {
                throw new TaskException('Got :code status from :url', [
                    ':code' => $response->status(),
                    ':url'  => $url,
                ]);
            }

            $realMime = $response->headers('Content-Type');

            if (\is_array($expectedMimes) && !\in_array($realMime, $expectedMimes, true)) {
                throw new TaskException('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     => $realMime,
                    ':expected' => implode('] or [', $expectedMimes),
                ]);
            }

            $content = $response->body();

            if (!$content) {
                throw new TaskException('Empty content for url [:url]', [':url' => $url,]);
            }

            $path = tempnam(sys_get_temp_dir(), 'wp-attach-');

            file_put_contents($path, $content);

            $this->loadedTmpFiles[] = $path;

            return $path;
        }

        if ($this->attachParsingMode === self::ATTACH_PARSING_MODE_LOCAL) {
            $path = $this->attachParsingPath.'/'.trim($originalUrlPath, '/');

            if (!file_exists($path)) {
                throw new TaskException('No file exists at :path', [':path' => $path]);
            }

            $this->logger->debug('Getting attach at local path = :path', [':path' => $path]);

            $realMime = $this->contentTypes->getFileMimeType($path);

            if (\is_array($expectedMimes) && !\in_array($realMime, $expectedMimes, true)) {
                throw new TaskException('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     => $realMime,
                    ':expected' => implode('] or [', $expectedMimes),
                ]);
            }

            return $path;
        }

        return null;
    }

    /**
     * @throws \BetaKiller\Notification\NotificationException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \BetaKiller\Workflow\WorkflowStateException
     * @throws \BetaKiller\Workflow\WorkflowException
     * @throws \LogicException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     */
    private function importPostsAndPages(): void
    {
        $posts = $this->wp->getPostsAndPages($this->skipBeforeDate);

        $total   = count($posts);
        $current = 1;

        foreach ($posts as $post) {
            $wpID      = $post['ID'];
            $uri       = $post['post_name'];
            $name      = $post['post_title'];
            $type      = $post['post_type'];
            $content   = $post['post_content'];
            $createdAt = new DateTimeImmutable($post['post_date']);
            $updatedAt = new DateTimeImmutable($post['post_modified']);

            $meta        = $this->wp->getPostMeta($wpID);
            $title       = $meta['_aioseop_title'] ?? null;
            $description = $meta['_aioseop_description'] ?? null;

            $this->logger->info('[:current/:total] Processing article :uri', [
                ':uri'     => $uri,
                ':current' => $current,
                ':total'   => $total,
            ]);

            $model = $this->postRepository->findByWpID($wpID);

            if (!$model) {
                /** @var ContentPostInterface $model */
                $model = $this->entityFactory->create(ContentPost::getModelName());
                $model->setWpId($wpID);
            }

            // Detect is this is a new record
            $isNew = !$model->hasID();

            // Detect type
            switch ($type) {
                case WP::POST_TYPE_PAGE:
                    $model->markAsPage();
                    break;

                case WP::POST_TYPE_POST:
                    $model->markAsArticle();
                    break;

                default:
                    throw new TaskException('Unknown post type: :value', [':type' => $type]);
            }

            $model->setUri($uri);
            $model->setCreatedBy($this->user);

            // Saving minimal model and getting its ID for further processing
            $this->postRepository->save($model);

            $model->setLabel($name);

            if ($title) {
                $model->setTitle($title);
            }

            if ($description) {
                $model->setDescription($description);
            }

            // Link thumbnail images to post
            $this->processThumbnails($model, $meta);

            if ($content) {
                // Parsing custom tags next
                $content = $this->processCustomBbTags($content, $model, $uri);

                // Processing YouTube <iframe> embeds
                $content = $this->processContentYoutubeIFrames($content, $model);

                $content = $this->postProcessArticleText($content, $model);

                // Process content and insert it only once after all processing is done
                $model->setContent($content);
            } else {
                $this->logger->warning('Post has no content at :uri', [':uri' => $uri]);
            }

            // Saving original creating and modification dates
            $model->setCreatedAt($createdAt);
            $model->setUpdatedAt($updatedAt);

            // Use current user as an author of revision
            $model->injectNewRevisionAuthor($this->user);

            // Actualize revision with imported data
            $model->setLatestRevisionAsActual();

            // Saving model content
            $this->postRepository->save($model);

            // Auto publishing for new posts (we are importing only published posts)
            if ($isNew) {
                /** @var \BetaKiller\Workflow\ContentPostWorkflow $workflow */
                $workflow = $this->statusWorkflowFactory->createFor($model);

                $workflow->complete($model, $this->user); // Publishing would be done automatically
            }

            // Saving updated workflow status
            $this->postRepository->save($model);

            $current++;
        }

        $this->notifyAboutUnknownBbTags();
    }

    private function notifyAboutUnknownBbTags(): void
    {
        foreach ($this->unknownBbTags as $tag => $url) {
            $this->logger->warning('Found unknown BB tag [:name] at :url', [':name' => $tag, ':url' => $url]);
        }
    }

    /**
     * @param string                                 $content
     * @param \BetaKiller\Model\ContentPostInterface $item
     *
     * @return string
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function postProcessArticleText(string $content, ContentPostInterface $item): string
    {
        $this->logger->debug('Text post processing...');

        $content = $this->wp->autop($content, false);

        $document = new Document();

        $html = '<html><body>'.$content.'</body></html>';

        // Make custom tags self-closing
        $document->loadHtml($html, LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NONET);

        if (!$document->has('body')) {
            $this->logger->alert('Post parsing error for :url', [':url' => $item->getUri()]);

            return $content;
        }

        $body = $document->find('body')[0];

        if ($body->innerHtml() !== $content) {
            $this->logger->debug('HTML parsing had modified the post content');
        }

        // Process attachments first coz they are images inside links
        $this->updateLinksOnAttachments($document, $item->getID());

        // Parsing all other images next to get @alt and @title values
        $this->processImagesInText($document, $item->getID());

        return $body->innerHtml();
    }

    /**
     * @param \DiDom\Document $document
     * @param int             $postID
     *
     * @throws \InvalidArgumentException
     * @throws \LogicException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function updateLinksOnAttachments(Document $document, int $postID): void
    {
        $this->logger->debug('Updating links on attachments...');

        foreach ($document->find('a') as $link) {
            $href = $link->getAttribute('href');

            // Skip non-attachment links
            if (strpos($href, '/wp-content/') === false) {
                continue;
            }

            // Search for image inside
            $img = $link->first('img');

            if ($img && $img->attr('src') === $href) {
                // Clickable image
                // Remove links to content images coz they would be added automatically
                $imageShortcode = $this->processImgTag($img, $postID);

                // Clickable image must be zoomable
                $imageShortcode->enableZoomable();

                // Replace link with image shortcode
                $link->replace($imageShortcode->asDomText());
            } else {
                // Link to another attachment
                $provider = $this->contentHelper->getAttachmentAssetsProvider();

                // Force saving images in AttachmentAssetsProvider
                $attachElement = $this->storeAttachment($provider, $href, $postID);

                $provider->saveModel($attachElement);

                /** @var AttachmentShortcode $attachShortcode */
                $attachShortcode = $this->shortcodeFacade->createFromCodename(AttachmentShortcode::codename());

                $attachShortcode->setID($attachElement->getID());

                if ($img) {
                    // Image as a thumbnail => layout="image" image-id="<id>"
                    $imageShortcode = $this->processImgTag($img, $postID);

                    $attachShortcode->useImageLayout($imageShortcode->getID());
                } else {
                    // Regular text link => layout="text" label="..."
                    $attachShortcode->useTextLayout($link->text());
                }

                $link->replace($attachShortcode->asDomText());
            }

//            $this->logger->debug('Link for :old was changed to :new', [
//                ':old' => $href,
//                ':new' => $this->assetsHelper->getOriginalUrl($model),
//            ]);
        }
    }

    /**
     * @param \BetaKiller\Model\ContentPost $post
     * @param array                         $meta
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function processThumbnails(ContentPost $post, array $meta): void
    {
        $wpID = $post->getWpId();

        $wpImagesIDs = [];

        if ($this->wp->postHasPostFormat($wpID, 'gallery')) {
            $this->logger->debug('Getting thumbnail images from from _format_gallery_images');

            // Getting images from meta._format_gallery_images
            $wpImagesIDs += (array)unserialize($meta['_format_gallery_images'], ['allowed_classes' => false]);
        }

        if (!$wpImagesIDs && isset($meta['_thumbnail_id'])) {
            $this->logger->debug('Getting thumbnail image from from _thumbnail_id');

            // Getting thumbnail image from meta._thumbnail_id
            $wpImagesIDs = [$meta['_thumbnail_id']];
        }

        if (!$wpImagesIDs) {
            if ($post->needsThumbnails()) {
                $this->logger->warning('Article with uri [:uri] needs thumbnail to be set', [
                    ':uri' => $post->getUri(),
                ]);
            }

            return;
        }

        // Getting data for each thumbnail
        $imagesWpData = $this->wp->getAttachments(null, $wpImagesIDs);

        if (!$imagesWpData) {
            $this->logger->warning('Some images can not be found with WP ids :ids', [
                ':ids' => implode(', ', $wpImagesIDs),
            ]);

            return;
        }

        $provider = $this->contentHelper->getPostThumbnailAssetsProvider();

        try {
            foreach ($imagesWpData as $imageData) {
                /** @var \BetaKiller\Model\ContentPostThumbnailInterface $imageModel */
                $imageModel = $this->createWordpressAttachment($imageData, $post->getID(), $provider);

                // Linking image to post
                $imageModel->setPost($post);

                // Fix WP issue when alt was not saved
                if (!$imageModel->getAlt() && $imageModel->getTitle()) {
                    $imageModel->setAlt($imageModel->getTitle());
                }

                $provider->saveModel($imageModel);
            }
        } catch (ValidationException $e) {
            throw new TaskException(':error', [
                ':error' => $e->getFirstItem()->getMessage(),
            ]);
        }
    }

    /**
     * @param string                        $content
     * @param \BetaKiller\Model\ContentPost $item
     * @param string                        $uri
     *
     * @return string
     */
    private function processCustomBbTags(string $content, ContentPost $item, string $uri): string
    {
        $this->logger->debug('Processing custom tags...');

        $handlers = new \Thunder\Shortcode\HandlerContainer\HandlerContainer();

        // [caption id="attachment_571" align="alignnone" width="780"]
        $handlers->add('caption', function (ThunderShortcodeInterface $shortcode) use ($item) {
            return $this->thunderHandlerCaption($shortcode, $item);
        });

        // [gallery ids="253,261.260"]
        $handlers->add('gallery', function (ThunderShortcodeInterface $shortcode) use ($item) {
            return $this->thunderHandlerGallery($shortcode, $item);
        });

        // [wonderplugin_slider id="1"]
        $handlers->add('wonderplugin_slider', function (ThunderShortcodeInterface $shortcode) use ($item) {
            return $this->thunderHandlerWonderplugin($shortcode, $item);
        });

        // All unknown shortcodes
        $handlers->setDefault(function (ThunderShortcodeInterface $s) use ($uri) {
            $serializer = new TextSerializer();
            $name       = $s->getName();

            try {
                $this->shortcodeFacade->createFromTagName($name);
            } catch (\Throwable $e) {
                LoggerHelper::logException($this->logger, $e);

                if (!isset($this->unknownBbTags[$name])) {
                    $this->unknownBbTags[$name] = $uri;
                    $this->logger->debug('Unknown BB-code found [:name], keep it', [':name' => $name]);
                }
            }

            return $serializer->serialize($s);
        });

        $parser    = new RegexParser;
        $processor = new Processor($parser, $handlers);

        return $processor->process($content);
    }

    /**
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @param \BetaKiller\Model\ContentPost                   $post
     *
     * @return null|string
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \InvalidArgumentException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    public function thunderHandlerCaption(ThunderShortcodeInterface $s, ContentPost $post): ?string
    {
        $this->logger->debug('[caption] found');

        $content  = $s->getContent();
        $document = new Document($content);
        $imageTag = $document->first('img');

        if (!$imageTag) {
            throw new TaskException('Can not detect image tag in [caption]');
        }

        // Getting attributes
        $attributes = $imageTag->attributes();

        // Force title to have caption text (without <img /> tag)
        $attributes['title'] = trim(strip_tags($content));

        $image = $this->createImageFromAttributes($attributes, $post->getID());

        /** @var ImageShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(ImageShortcode::codename());

        $shortcode->setID($image->getID());

        $this->importHtmlAttributesIntoImageShortcode($shortcode, $attributes);

        $shortcode->useCaptionLayout();

        return $shortcode->asHtml();
    }

    /**
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @param \BetaKiller\Model\ContentPostInterface          $post
     *
     * @return null|string
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    public function thunderHandlerGallery(ThunderShortcodeInterface $s, ContentPostInterface $post): ?string
    {
        $this->logger->debug('[gallery] found');

        $wpIDsList = $s->getParameter('ids');
        $type      = $s->getParameter('type');
        $columns   = $s->getParameter('columns');

        // Removing spaces
        $wpIDsList = str_replace(' ', '', $wpIDsList);
        $wpIDs     = explode(',', $wpIDsList);

        $gallery = $this->createGalleryFromWpIDs($wpIDs, $post->getID());

        // Can not create gallery => remove [gallery] tag
        if (!$gallery) {
            return null;
        }

        // Link gallery to current post
        $gallery->setEntity($this->contentPostEntity)->setEntityItemID($post->getID());

        // Saving to get ID
        $this->galleryRepository->save($gallery);

        /** @var GalleryShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(GalleryShortcode::codename());

        $shortcode->setID($gallery->getID());

        if (strpos($type, 'slider') !== false) {
            $shortcode->useSliderLayout();
        } elseif (strpos($type, 'masonry') !== false) {
            $shortcode->useMasonryLayout($columns);
        } elseif ($type) {
            throw new TaskException('Unknown gallery type [:value]', [':value' => $type]);
        } else {
            // Use default layout if none provided
            $shortcode->useDefaultLayout();
        }

        return $shortcode->asHtml();
    }

    /**
     * @param array $wpIDs
     * @param int   $entityItemID
     *
     * @return \BetaKiller\Model\ContentGalleryInterface|null
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function createGalleryFromWpIDs(array $wpIDs, int $entityItemID): ?ContentGalleryInterface
    {
        $gallery = $this->galleryRepository->findByImagesWpIDs($wpIDs);

        if ($gallery) {
            $this->logger->debug('Gallery found by WP IDs');

            return $gallery;
        }

        /** @var ContentGalleryInterface $gallery */
        $gallery = $this->entityFactory->create(ContentGallery::getModelName());

        // Link gallery to current post
        $gallery->setEntity($this->contentPostEntity)->setEntityItemID($entityItemID);

        // Save gallery to populate ID (dirty hack for Kohana ORM)
        $this->galleryRepository->save($gallery);

        $wpImages = $this->wp->getAttachments(null, $wpIDs);

        if (!$wpImages) {
            $this->logger->warning('No images found for gallery with WP IDs :ids', [
                ':ids' => implode(', ', $wpIDs),
            ]);

            return null;
        }

        $this->logger->debug('Creating new gallery from WP IDs');

        $provider = $this->contentHelper->getImageAssetsProvider();

        try {
            // Process every image in set
            foreach ($wpImages as $wpImageData) {
                /** @var \BetaKiller\Model\ContentImageInterface $model */
                $model = $this->createWordpressAttachment($wpImageData, $entityItemID, $provider);

                // Fix WP issue when alt was not saved
                if (!$model->getAlt() && $model->getTitle()) {
                    $model->setAlt($model->getTitle());
                }

                // Save model and populate ID for linking to gallery
                $provider->saveModel($model);

                $gallery->addImage($model);
            }
        } catch (ValidationException $e) {
            throw new TaskException(':error', [
                ':error' => $e->getFirstItem()->getMessage(),
            ]);
        }

        return $gallery;
    }

    /**
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @param \BetaKiller\Model\ContentPostInterface          $post
     *
     * @return string
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    public function thunderHandlerWonderplugin(ThunderShortcodeInterface $s, ContentPostInterface $post): string
    {
        $this->logger->debug('[wonderplugin_slider] found');

        $wonderPluginID = $s->getParameter('id');
        $config         = $this->wp->getWonderpluginSliderConfig($wonderPluginID);
        $this->logger->debug('Processing wonderplugin slider :id', [':id' => $wonderPluginID]);

        /** @var array $slides */
        $slides = $config['slides'];

        $provider = $this->contentHelper->getImageAssetsProvider();
        $images   = [];

        foreach ($slides as $slide) {
            $url = $slide['image'];

            /** @var \BetaKiller\Model\ContentImageInterface $image */
            $image = $this->storeAttachment($provider, $url, $post->getID());

            if (!$image->getTitle()) {
                $image->setTitle($slide['title']);
            }

            if (!$image->getAlt()) {
                $image->setAlt($slide['alt']);
            }

            // Fix WP issue when alt was not saved
            if (!$image->getAlt() && $image->getTitle()) {
                $image->setAlt($image->getTitle());
            }

            try {
                // Save model and populate ID for linking to gallery
                $provider->saveModel($image);
            } catch (ValidationException $e) {
                throw new TaskException(':error', [
                    ':error' => $e->getFirstItem()->getMessage(),
                ]);
            }

            $images[] = $image;
        }

        $gallery = $this->createGalleryFromImages($images, $post->getID());

        /** @var GalleryShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(GalleryShortcode::codename());

        // Saving to get ID
        $this->galleryRepository->save($gallery);

        $shortcode->setID($gallery->getID());
        $shortcode->useSliderLayout();

        return $shortcode->asHtml();
    }

    /**
     * @param ContentImageInterface[] $images
     *
     * @param int                     $entityItemID
     *
     * @return \BetaKiller\Model\ContentGalleryInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
     */
    private function createGalleryFromImages(array $images, int $entityItemID): ContentGalleryInterface
    {
        $wpIDs = array_map(function (ContentImageInterface $image) {
            return $image->getWpId();
        }, $images);

        $gallery = $this->galleryRepository->findByImagesWpIDs($wpIDs);

        // Gallery found => images linked already
        if ($gallery) {
            $this->logger->debug('Gallery :id found bu WP ids', [':id' => $gallery->getID()]);

            return $gallery;
        }

        // Nothing found => create new gallery
        /** @var ContentGalleryInterface $gallery */
        $gallery = $this->entityFactory->create(ContentGallery::getModelName());

        // Link gallery to current post
        $gallery->setEntity($this->contentPostEntity)->setEntityItemID($entityItemID);

        // Save gallery to obtain ID (dirty hack for Kohana ORM)
        $this->galleryRepository->save($gallery);

        $this->logger->debug('New gallery created');

        foreach ($images as $image) {
            $this->logger->debug('Adding image :id to gallery', [':id' => $image->getID()]);
            $gallery->addImage($image);
        }

        return $gallery;
    }

    /**
     * @param \DiDom\Document $root
     * @param int             $entityItemID
     *
     * @throws \InvalidArgumentException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \LogicException
     */
    private function processImagesInText(Document $root, int $entityItemID): void
    {
        foreach ($root->find('img') as $image) {
            $shortcode = null;

            try {
                // Creating new [image /] tag as replacement for <img />
                $shortcode = $this->processImgTag($image, $entityItemID);
            } catch (Throwable $e) {
                LoggerHelper::logException($this->logger, $e);
            }

            // Exit if something went wrong
            if (!$shortcode) {
                continue;
            }

            $this->logger->debug('Replacement tag is :tag', [':tag' => $shortcode->asHtml()]);

            $image->replace($shortcode->asDomText());
        }
    }

    /**
     * @param \DiDom\Element $node
     * @param                $entityItemID
     *
     * @return ImageShortcode
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function processImgTag(\DiDom\Element $node, $entityItemID): ImageShortcode
    {
        $this->logger->debug('Found inline image :tag', [':tag' => $node->html()]);

        // Getting attributes
        $attributes = $node->attributes();

        $image = $this->createImageFromAttributes($attributes, $entityItemID);

        /** @var ImageShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(ImageShortcode::codename());

        $shortcode->setID($image->getID());

        $this->importHtmlAttributesIntoImageShortcode($shortcode, $attributes);

        return $shortcode;
    }

    /**
     * @param array $attributes
     * @param       $entityItemID
     *
     * @return \BetaKiller\Assets\Model\AssetsModelImageInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \BetaKiller\Exception\ValidationException
     */
    private function createImageFromAttributes(array $attributes, $entityItemID): AssetsModelImageInterface
    {
        // Original URL
        $originalUrl = trim($attributes['src']);

        $provider = $this->contentHelper->getImageAssetsProvider();

        /** @var \BetaKiller\Model\ContentImageInterface $image */
        $image = $this->storeAttachment($provider, $originalUrl, $entityItemID);

        $alt   = trim($attributes['alt'] ?? null);
        $title = trim($attributes['title'] ?? null);

        // Save alt and title in image model
        if ($alt) {
            $image->setAlt($alt);
        }

        if ($title) {
            $image->setTitle($title);
        }

        // Save model and populate ID
        $provider->saveModel($image);

        return $image;
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ImageShortcode $shortcode
     * @param array                                        $attributes
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Task\TaskException
     */
    private function importHtmlAttributesIntoImageShortcode(ImageShortcode $shortcode, array $attributes): void
    {
        // Convert old full-size images to responsive images
        if (isset($attributes['width']) && (int)$attributes['width'] === 780) { // TODO move 780 to config
            unset($attributes['width']);
            $shortcode->setAlignJustify();
        }

        if (isset($attributes['width'])) {
            $shortcode->setWidth((int)$attributes['width']);
        }

        $class = $attributes['class'] ?? null;

        if ($class) {
            $this->processImageClasses($shortcode, $class);
        }

        if (isset($attributes['style'])) {
            throw new TaskException('Image :id has inline styling, fix it in the source page');
        }
    }

    /**
     * @param \BetaKiller\Content\Shortcode\ImageShortcode $shortcode
     * @param string                                       $classValue
     *
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Task\TaskException
     */
    private function processImageClasses(ImageShortcode $shortcode, string $classValue): void
    {
        $classes = explode(' ', $classValue);

        $translators = [
            'alignleft'   => function () use ($shortcode) {
                $shortcode->setAlignLeft();
            },
            'alignright'  => function () use ($shortcode) {
                $shortcode->setAlignRight();
            },
            'aligncenter' => function () use ($shortcode) {
                $shortcode->setAlignCenter();
            },
            'alignnone'   => function () use ($shortcode) {
                $shortcode->setAlignNone();
            },
            'size-full'   => function () {
                // Nothing to do here
            },
            'size-medium' => function () {
                // Nothing to do here
            },
        ];

        $processed = [];

        foreach ($classes as $class) {
            $translator = $translators[$class] ?? null;

            if ($translator) {
                $translator();
                $processed[] = $class;
            } elseif (preg_match('/wp-image-[\d]+/', $class)) {
                $processed[] = $class;
            }
        }

        $diff = array_diff($classes, $processed);

        if ($diff) {
            throw new TaskException('Image :id has unprocessed classes :classes', [
                ':id'      => $shortcode->getID(),
                ':classes' => implode(', ', $diff),
            ]);
        }
    }

    /**
     * @param string                                 $content
     * @param \BetaKiller\Model\ContentPostInterface $item
     *
     * @return string
     */
    private function processContentYoutubeIFrames(string $content, ContentPostInterface $item): string
    {
        return $this->processYoutubeVideosInText($content, $item->getID());
    }

    /**
     * @param string $text
     * @param int    $entityItemID
     *
     * @return string
     */
    private function processYoutubeVideosInText(string $text, int $entityItemID): string
    {
        $this->logger->debug('Processing Youtube iframe tags...');

        $pattern = '/<iframe[\s]+?src="(http[s]*:\/\/)?www\.youtube\.com\/embed\/([a-zA-Z0-9-_]{11})[^"]*"[^>]*?><\/iframe>/';

        // <iframe width="854" height="480" src="https://www.youtube.com/embed/xfTfeWTOxHk" frameborder="0" allowfullscreen></iframe>

        /** @var array $matches */
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Выходим, если ничего не нашли
        if (!$matches) {
            return $text;
        }

        foreach ($matches as $match) {
            // Изначальный тег
            $originalTag = $match[0];
            $targetTag   = null;

            try {
                // Создаём новый тег <youtube /> на замену <iframe />
                $targetTag = $this->processYoutubeIFrameTag($originalTag, $entityItemID);
            } catch (\Throwable $e) {
                LoggerHelper::logException($this->logger, $e);
            }

            // Если новый тег не сформирован, то просто переходим к следующему
            if (!$targetTag) {
                continue;
            }

            // Производим замену в тексте
            $text = str_replace($originalTag, $targetTag, $text);
        }

        return $text;
    }

    /**
     * @param string $tagString
     * @param int    $entityItemID
     *
     * @return string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     * @throws \BetaKiller\Exception\ValidationException
     */
    private function processYoutubeIFrameTag(string $tagString, int $entityItemID): string
    {
        // Parsing
        $sx = simplexml_load_string($tagString);

        if ($sx === false) {
            throw new TaskException('Youtube iframe tag parsing failed on :string', [':string' => $tagString]);
        }

        // Getting attributes
        $attributes = iterator_to_array($sx->attributes());

        // Original URL
        $embedUrl = trim($attributes['src']);

        $this->logger->debug('Found youtube iframe :tag', [':tag' => $tagString]);

        $youtubeID = $this->youtubeRepository->getYoutubeIdFromEmbedUrl($embedUrl);

        if (!$youtubeID) {
            throw new TaskException('Youtube iframe ID parsing failed on :string', [':string' => $tagString]);
        }

        $video = $this->youtubeRepository->findRecordByYoutubeEmbedUrl($youtubeID);

        if (!$video) {
            /** @var \BetaKiller\Model\ContentYoutubeRecord $video */
            $video = $this->entityFactory->create(ContentYoutubeRecord::getModelName());
            $video->setYoutubeId($youtubeID);
        }

        $width  = trim(Arr::get($attributes, 'width'));
        $height = trim(Arr::get($attributes, 'height'));

        // Save width and height in video model if they not set
        if (!$video->getWidth() && !$video->getHeight()) {
            $video->setWidth($width);
            $video->setHeight($height);
        }

        if ($video->changed()) {
            $this->logger->info('Youtube video :id processed', [':id' => $youtubeID]);
        }

        $video
            ->setUploadedBy($this->user)
            ->setEntity($this->contentPostEntity)
            ->setEntityItemID($entityItemID);

        $this->youtubeRepository->save($video);

        /** @var YoutubeShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(YoutubeShortcode::codename());

        $shortcode->setID($video->getID());

        return $shortcode->asHtml();
    }

    /**
     * Import all categories with WP IDs
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
     */
    private function importCategories(): void
    {
        $categories = $this->wp->getCategoriesWithPosts();

        $total   = \count($categories);
        $current = 1;

        foreach ($categories as $term) {
            $wpID  = $term['term_id'];
            $uri   = $term['slug'];
            $label = $term['name'];

            $this->logger->info('[:current/:total] Processing category :uri', [
                ':uri'     => $uri,
                ':current' => $current,
                ':total'   => $total,
            ]);

            $category = $this->categoryRepository->findByWpID($wpID);

            if (!$category) {
                /** @var \BetaKiller\Model\ContentCategoryInterface $category */
                $category = $this->entityFactory->create(ContentCategory::getModelName());
                $category->setWpId($wpID);
            }

            $category->setLabel($label);
            $category->setUri($uri);

            $this->categoryRepository->save($category);

            // Find articles related to current category
            $postsWpIDs = $this->wp->getPostsIDsLinkedToCategory($wpID);

            // Check for any linked objects
            if ($postsWpIDs) {
                // Get real article IDs
                $articlesIDs = $this->postRepository->findIDsByWpIDs($postsWpIDs);

                // Does articles exist?
                if ($articlesIDs) {
                    // Link articles to category
                    $category->linkPosts($articlesIDs);
                }
            }

            $current++;
        }

        foreach ($categories as $term) {
            $wpID       = (int)$term['term_id'];
            $parentWpID = (int)$term['parent'];

            // Skip categories without parent
            if (!$parentWpID) {
                continue;
            }

            $category = $this->categoryRepository->findByWpID($wpID);
            $parent   = $this->categoryRepository->findByWpID($parentWpID);

            $category->setParent($parent);

            $this->categoryRepository->save($category);
        }
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \Kohana_Exception
     * @throws \BetaKiller\Exception\ValidationException
     */
    private function importQuotes(): void
    {
        $quotesData = $this->wp->getQuotesCollectionQuotes();
        $counter    = 0;

        foreach ($quotesData as $data) {
            $id        = $data['id'];
            $text      = $data['text'];
            $author    = $data['author'];
            $createdAt = new DateTime($data['created_at']);

            $model = $this->quoteRepository->findByWpId($id);

            if (!$model) {
                /** @var \BetaKiller\Model\QuoteInterface $model */
                $model = $this->entityFactory->create(Quote::getModelName());
                $model->setWpId($id);
            }

            $model
                ->setCreatedAt($createdAt)
                ->setAuthor($author)
                ->setText($text);

            $this->quoteRepository->save($model);
            $counter++;
        }

        $this->logger->info(':total quotes processed', [
            ':total' => $counter,
        ]);
    }

    private function importComments(): void
    {
        $commentsData = $this->wp->getComments();

        $this->logger->info('Processing :total comments ', [
            ':total' => \count($commentsData),
        ]);

        foreach ($commentsData as $data) {
            try {
                $this->importSingleComment($data);
            } catch (ValidationException $e) {
                $this->logger->warning('Comment with WP ID = :id is invalid, skipping :errors', [
                    ':id'     => $data['id'],
                    ':errors' => json_encode($e),
                ]);
            } catch (Throwable $e) {
                LoggerHelper::logException($this->logger, $e);
            }
        }
    }

    /**
     * @param array $data
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \BetaKiller\Exception\ValidationException
     */
    private function importSingleComment(array $data): void
    {
        $wpID        = $data['id'];
        $wpParentID  = $data['parent_id'];
        $wpPostID    = $data['post_id'];
        $createdAt   = new DateTime($data['created_at']);
        $authorName  = $data['author_name'];
        $authorEmail = $data['author_email'];
        $authorIP    = $data['author_ip_address'];
        $message     = $data['message'];
        $wpApproved  = $data['approved'];
        $userAgent   = $data['user_agent'];

        $post = $this->postRepository->findByWpID($wpPostID);

        if (!$post) {
            throw new TaskException('Unknown WP post ID [:post] used as reference in WP comment :comment', [
                ':post'    => $wpPostID,
                ':comment' => $wpID,
            ]);
        }

        $model = $this->commentRepository->findByWpID($wpID);

        if (!$model) {
            /** @var \BetaKiller\Model\ContentCommentInterface $model */
            $model = $this->entityFactory->create(ContentComment::getModelName());
            $model->setWpId($wpID);
        }

        $parentModel = $wpParentID ? $this->commentRepository->findByWpID($wpParentID) : null;

        if ($wpParentID && !$parentModel) {
            throw new TaskException('Unknown WP comment parent ID [:parent] used in WP comment [:comment]', [
                ':parent'  => $wpParentID,
                ':comment' => $wpID,
            ]);
        }

        // Skip existing comments coz they may be edited after import
        if ($model->hasID()) {
            return;
        }

        $model->setParent($parentModel);

        $model
            ->setEntity($this->contentPostEntity)
            ->setEntityItemID($post->getID());

        $model
            ->setIpAddress($authorIP)
            ->setUserAgent($userAgent)
            ->setCreatedAt($createdAt);

        // Detecting user by name
        $authorUser = $this->userRepository->searchBy($authorName);

        if ($authorUser) {
            $model->setAuthorUser($authorUser);
        } else {
            $model->setGuestAuthorName($authorName)->setGuestAuthorEmail($authorEmail);
        }

        $isApproved = ((int)$wpApproved === 1);
        $isSpam     = (mb_strtolower($wpApproved) === 'spam');
        $isTrash    = (mb_strtolower($wpApproved) === 'trash');

        if ($isSpam) {
            $this->commentWorkflow->initAsSpam($model);
        } elseif ($isTrash) {
            $this->commentWorkflow->initAsTrash($model);
        } elseif ($isApproved) {
            $this->commentWorkflow->initAsApproved($model);
        } else {
            $this->commentWorkflow->initAsPending($model);
        }

        $model->setMessage($message);

        $this->commentRepository->save($model);
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function importUsers(): void
    {
        $this->logger->info('Importing users...');

        foreach ($this->wp->getUsers() as $wpUser) {
            $wpLogin = $wpUser['login'];
            $wpEmail = $wpUser['email'];

            $userModel = $this->userRepository->searchBy($wpEmail) ?: $this->userRepository->searchBy($wpLogin);

            if (!$userModel) {
                $userModel = $this->userService->createUser(
                    RoleInterface::LOGIN,
                    $wpEmail,
                    UserService::DEFAULT_IP,
                    $wpLogin
                );
                $this->logger->info('User :login successfully imported', [':login' => $userModel->getEmail()]);
            } else {
                $this->logger->info('User :login already exists', [':login' => $userModel->getEmail()]);
            }
        }
    }
}
