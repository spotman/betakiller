<?php

use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Content\Shortcode\AttachmentShortcode;
use BetaKiller\Content\Shortcode\GalleryShortcode;
use BetaKiller\Content\Shortcode\ImageShortcode;
use BetaKiller\Content\Shortcode\YoutubeShortcode;
use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\ContentPost;
use BetaKiller\Model\ContentPostInterface;
use BetaKiller\Model\Entity;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\WordpressAttachmentInterface;
use BetaKiller\Repository\WordpressAttachmentRepositoryInterface;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Task\TaskException;
use DiDom\Document;
use Thunder\Shortcode\Parser\RegexParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Shortcode\ShortcodeInterface as ThunderShortcodeInterface;

class Task_Content_Import_Wordpress extends AbstractTask
{
    private const ATTACH_PARSING_MODE_HTTP  = 'http';
    private const ATTACH_PARSING_MODE_LOCAL = 'local';

    private const WP_OPTION_PARSING_MODE = 'betakiller_parsing_mode';
    private const WP_OPTION_PARSING_PATH = 'betakiller_parsing_path';

    use LoggerHelperTrait;

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
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

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
     * @var \BetaKiller\Repository\ContentImageRepository
     */
    private $imageRepository;

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
     * @var \BetaKiller\Repository\UserRepository
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
     * @var \BetaKiller\Status\StatusWorkflowFactory
     */
    private $postWorkflowFactory;

    /**
     * @Inject
     * @var \WP
     */
    private $wp;

    protected function defineOptions(): array
    {
        return [
            'skip-before' => null,
        ];
    }

    /**
     * @param array $params
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     * @throws \ORM_Validation_Exception
     */
    protected function _execute(array $params): void
    {
        if ($params['skip-before']) {
            $this->skipBeforeDate = new DateTimeImmutable($params['skip-before']);
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
    }

    /**
     * @throws \BetaKiller\Task\TaskException
     */
    private function configureDialog(): void
    {
        $parsingMode = $this->wp->get_option(self::WP_OPTION_PARSING_MODE);

        if (!$parsingMode) {
            $parsingMode = $this->read('Select parsing mode', [
                self::ATTACH_PARSING_MODE_HTTP,
                self::ATTACH_PARSING_MODE_LOCAL,
            ]);
        }

        $this->logger->info('Parsing mode is: '.$parsingMode);


        $parsingPath = $this->wp->get_option(self::WP_OPTION_PARSING_PATH);

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

        $this->wp->set_option(self::WP_OPTION_PARSING_MODE, $parsingMode);
        $this->wp->set_option(self::WP_OPTION_PARSING_PATH, $parsingPath);
    }

    /**
     * @param array                                                    $attach
     * @param int                                                      $entityItemID
     * @param \BetaKiller\Assets\Provider\AssetsProviderInterface|null $provider
     *
     * @return \BetaKiller\Model\WordpressAttachmentInterface
     * @throws \BetaKiller\Task\TaskException
     */
    private function processWordpressAttachment(
        array $attach,
        int $entityItemID,
        AssetsProviderInterface $provider = null
    ): WordpressAttachmentInterface {
        $wpID = $attach['ID'];
        $url  = $attach['guid'];

        if (!$wpID || !$url) {
            throw new TaskException('Empty attach data');
        }

        $this->logger->debug('Found attach with guid = :url', [':url' => $url]);

        if (!$provider) {
            $mime = $attach['post_mime_type'];

            $this->logger->debug('Creating assets provider by MIME-type :mime', [':mime' => $mime]);

            // Detect and instantiate assets provider by file MIME-type
            $provider = $this->contentHelper->createAssetsProviderFromMimeType($mime);
        }

        $model = $this->storeAttachment($provider, $url, $wpID, $entityItemID);

        // Save created_at + updated_at
        $created_at = new DateTime($attach['post_date']);
        $updated_at = new DateTime($attach['post_modified']);

        if ($model instanceof AssetsModelImageInterface) {
            $title = $attach['post_excerpt'];

            if ($title && !$model->getTitle()) {
                $model->setTitle($title);
            }
        }

        $model->setUploadedAt($created_at);
        $model->setLastModifiedAt($updated_at);

        $provider->saveModel($model);

        return $model;
    }

    /**
     * @param AssetsProviderInterface $provider
     * @param string                  $url
     * @param int                     $wpID
     * @param int|null                $entityItemID
     *
     * @return \BetaKiller\Model\WordpressAttachmentInterface
     * @throws TaskException
     */
    private function storeAttachment(
        AssetsProviderInterface $provider,
        string $url,
        int $wpID,
        ?int $entityItemID = null
    ): WordpressAttachmentInterface {
        $repository = $provider->getRepository();

        if (!($repository instanceof WordpressAttachmentRepositoryInterface)) {
            throw new TaskException('Attachment repository [:name] must be instance of :must', [
                ':name' => $repository::getCodename(),
                ':must' => WordpressAttachmentRepositoryInterface::class,
            ]);
        }

        // Search for such file already exists
        $model = $repository->findByWpID($wpID);

        if ($model) {
            $this->logger->debug('Attach with WP ID = :id already exists', [':id' => $wpID,]);

            return $model;
        }

        $this->logger->debug('Adding attach with WP ID = :id', [':id' => $wpID]);

        $urlPath          = parse_url($url, PHP_URL_PATH);
        $originalFilename = basename($url);

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

        // Storing WP path and ID
        $model->setWpPath($urlPath);
        $model->setWpId($wpID);
        $provider->saveModel($model);

        // Cleanup temp files
        if ($this->attachParsingMode === self::ATTACH_PARSING_MODE_HTTP) {
            unlink($path);
        }

        $this->logger->info('Attach with WP ID = :id successfully stored', [':id' => $wpID]);

        return $model;
    }

    /**
     * @param string $originalUrlPath
     * @param        $expectedMimes
     *
     * @return bool|null|string
     * @throws \BetaKiller\Task\TaskException
     * @throws \Request_Exception
     */
    private function getAttachmentPath(string $originalUrlPath, $expectedMimes): ?string
    {
        if ($this->attachParsingMode === self::ATTACH_PARSING_MODE_HTTP) {
            $url = $this->attachParsingPath.ltrim($originalUrlPath, '/');

            $this->logger->debug('Loading attach at url = :url', [':url' => $url]);

            // TODO Replace with system-wide crawler
            $request  = Request::factory($url);
            $response = $request->execute();

            if ($response->status() !== 200) {
                throw new TaskException('Got :code status from :url', [
                    ':code' => $response->status(),
                    ':url'  => $url,
                ]);
            }

            $realMime = $response->headers('Content-Type');

            if (is_array($expectedMimes) && !in_array($realMime, $expectedMimes, true)) {
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

            return $path;
        }

        if ($this->attachParsingMode === self::ATTACH_PARSING_MODE_LOCAL) {
            $path = $this->attachParsingPath.'/'.trim($originalUrlPath, '/');

            if (!file_exists($path)) {
                throw new TaskException('No file exists at :path', [':path' => $path]);
            }

            $this->logger->debug('Getting attach at local path = :path', [':path' => $path]);

            $realMime = File::mime($path);

            if (is_array($expectedMimes) && !in_array($realMime, $expectedMimes, true)) {
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
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     */
    private function importPostsAndPages(): void
    {
        $posts = $this->wp->get_posts_and_pages($this->skipBeforeDate);

        $total   = $posts->count();
        $current = 1;

        foreach ($posts as $post) {
            $wpID      = $post['ID'];
            $uri       = $post['post_name'];
            $name      = $post['post_title'];
            $type      = $post['post_type'];
            $content   = $post['post_content'];
            $createdAt = new DateTime($post['post_date']);
            $updatedAt = new DateTime($post['post_modified']);

            $meta        = $this->wp->get_post_meta($wpID);
            $title       = $meta['_aioseop_title'] ?? null;
            $description = $meta['_aioseop_description'] ?? null;

            $this->logger->info('[:current/:total] Processing article :uri', [
                ':uri'     => $uri,
                ':current' => $current,
                ':total'   => $total,
            ]);

            $model = $this->postRepository->findByWpID($wpID);

            if (!$model) {
                $model = $this->postRepository->create();
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
            $model->injectNewRevisionAuthor($this->user);

            // Saving model and getting its ID for further processing
            $this->postRepository->save($model);

            $model->setLabel($name);
            $model->setContent($content);

            if ($title) {
                $model->setTitle($title);
            }

            if ($description) {
                $model->setDescription($description);
            }

            // Link thumbnail images to post
            $this->processThumbnails($model, $meta);

            if ($model->getContent()) {
                // Parsing custom tags next
                $this->processCustomBbTags($model);

                // Processing YouTube <iframe> embeds
                $this->processContentYoutubeIFrames($model);

                $this->postProcessArticleText($model);
            } else {
                $this->logger->warning('Post has no content at :uri', [':uri' => $uri]);
            }

            // Saving original creating and modification dates
            $model->setCreatedAt($createdAt);
            $model->setUpdatedAt($updatedAt);

            // Auto publishing for new posts (we are importing only published posts)
            if ($isNew) {
                /** @var \BetaKiller\Status\ContentPostWorkflow $workflow */
                $workflow = $this->postWorkflowFactory->create($model);

                $workflow->complete(); // Publishing would be done automatically
            }

            // Actualize revision with imported data
            $model->setLatestRevisionAsActual();

            // Saving model content
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
     * @param \BetaKiller\Model\ContentPost $item
     *
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     */
    private function postProcessArticleText(ContentPost $item): void
    {
        $this->logger->debug('Text post processing...');

        $text = $item->getContent();

        $text = $this->wp->autop($text, false);

        $document = new Document();

        // Make custom tags self-closing
        $document->loadHtml($text, LIBXML_PARSEHUGE | LIBXML_NONET);

        $body = $document->find('body')[0];

        if ($body) {
            // Process attachments first coz they are images inside links
            $this->updateLinksOnAttachments($document, $item->getID());

            // Parsing all other images next to get @alt and @title values
            $this->processImagesInText($document, $item->getID());

            $item->setContent($body->innerHtml());
        } else {
            $this->logger->warning('Post parsing error for :url', [':url' => $item->getUri()]);
        }
    }

    /**
     * @param \DiDom\Document $document
     * @param int             $postID
     *
     * @throws \ORM_Validation_Exception
     * @throws \LogicException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function updateLinksOnAttachments(Document $document, int $postID): void
    {
        $this->logger->debug('Updating links on attachments...');

        $links = $document->find('a');

        foreach ($links as $link) {
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
                $wpAttach = $this->wp->get_attachment_by_path($href);

                if (!$wpAttach) {
                    throw new TaskException('Unknown attachment href :url', [':url' => $href]);
                }

                $provider = $this->contentHelper->getAttachmentAssetsProvider();

                // Force saving images in AttachmentAssetsProvider
                $attachElement = $this->processWordpressAttachment($wpAttach, $postID, $provider);

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
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     */
    private function processThumbnails(ContentPost $post, array $meta): void
    {
        $wpID = $post->getWpId();

        $wpImagesIDs = [];

        if ($this->wp->post_has_post_format($wpID, 'gallery')) {
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
        $imagesWpData = $this->wp->get_attachments(null, $wpImagesIDs);

        if (!$imagesWpData) {
            $this->logger->warning('Some images can not be found with WP ids :ids', [
                ':ids' => implode(', ', $wpImagesIDs),
            ]);

            return;
        }

        $provider = $this->contentHelper->getPostThumbnailAssetsProvider();

        foreach ($imagesWpData as $imageData) {
            /** @var \BetaKiller\Model\ContentPostThumbnailInterface $imageModel */
            $imageModel = $this->processWordpressAttachment($imageData, $post->getID(), $provider);

            // Linking image to post
            $imageModel->setPost($post);

            $provider->saveModel($imageModel);
        }
    }

    /**
     * @param \BetaKiller\Model\ContentPost $item
     *
     * @throws \RuntimeException
     */
    private function processCustomBbTags(ContentPost $item): void
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
        $handlers->setDefault(function (ThunderShortcodeInterface $s) use ($item) {
            $serializer = new TextSerializer();
            $name       = $s->getName();

            try {
                $this->shortcodeFacade->createFromTagName($name);
            } catch (\Throwable $e) {
                $this->logException($this->logger, $e);

                if (!isset($this->unknownBbTags[$name])) {
                    $this->unknownBbTags[$name] = $this->ifaceHelper->getReadEntityUrl($item, IFaceZone::PUBLIC_ZONE);
                    $this->logger->debug('Unknown BB-code found [:name], keep it', [':name' => $name]);
                }
            }

            return $serializer->serialize($s);
        });

        $parser    = new RegexParser;
        $processor = new Processor($parser, $handlers);

        $content = $item->getContent();
        $content = $processor->process($content);
        $item->setContent($content);
    }

    /**
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @param \BetaKiller\Model\ContentPost                   $post
     *
     * @return null|string
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    public function thunderHandlerCaption(ThunderShortcodeInterface $s, ContentPost $post): ?string
    {
        $this->logger->debug('[caption] found');

        $attributes = $s->getParameters();

        $imageWpID = (int)str_replace('attachment_', '', $attributes['id']);

        // Find image in WP and process attachment
        $wpImageData = $this->wp->get_attachment_by_id($imageWpID);

        if (!$wpImageData) {
            $this->logger->warning('No image found by wp_id :id', [':id' => $imageWpID]);

            return null;
        }

        $image = $this->processWordpressAttachment($wpImageData, $post->getID());

        // Removing <img /> tag
        $captionText = trim(strip_tags($s->getContent()));

        // Convert old full-size images to responsive images
        if (isset($attributes['width']) && (int)$attributes['width'] === 780) { // TODO move 780 to config
            unset($attributes['width']);
        }

        // Do not use height, it would be calculated automatically in the widget
        unset($attributes['height']);

        /** @var ImageShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(ImageShortcode::codename(), $attributes);

        $shortcode->useCaptionLayout($captionText);
        $shortcode->setID($image->getID());

        return $shortcode->asHtml();
    }

    /**
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @param \BetaKiller\Model\ContentPostInterface          $post
     *
     * @return null|string
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    public function thunderHandlerGallery(ThunderShortcodeInterface $s, ContentPostInterface $post): ?string
    {
        // TODO Deal with gallery duplicating on multiple task execution
        $this->logger->debug('[gallery] found');

        $gallery = $this->galleryRepository->create();

        // Link gallery to current post
        $gallery->setEntity($this->contentPostEntity)->setEntityItemID($post->getID());

        /** @var GalleryShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(GalleryShortcode::codename());

        $wpIDsList = $s->getParameter('ids');
        $type      = $s->getParameter('type');
        $columns   = $s->getParameter('columns');

        // Removing spaces
        $wpIDsList = str_replace(' ', '', $wpIDsList);
        $wpIDs     = explode(',', $wpIDsList);

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

        // Saving to get ID
        $this->galleryRepository->save($gallery);

        $shortcode->setID($gallery->getID());

        $wpImages = $this->wp->get_attachments(null, $wpIDs);

        if (!$wpImages) {
            $this->logger->warning('No images found for gallery with WP IDs :ids', [
                ':ids' => implode(', ', $wpIDs),
            ]);

            return null;
        }

        // Process every image in set
        foreach ($wpImages as $wpImageData) {
            /** @var \BetaKiller\Model\ContentImageInterface $model */
            $model = $this->processWordpressAttachment($wpImageData, $post->getID());

            $gallery->addImage($model);
        }

        return $shortcode->asHtml();
    }

    /**
     * @param \Thunder\Shortcode\Shortcode\ShortcodeInterface $s
     * @param \BetaKiller\Model\ContentPostInterface          $post
     *
     * @return string
     * @throws \ORM_Validation_Exception
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Task\TaskException
     */
    public function thunderHandlerWonderplugin(ThunderShortcodeInterface $s, ContentPostInterface $post): string
    {
        $this->logger->debug('[wonderplugin_slider] found');

        $gallery = $this->galleryRepository->create();

        // Link gallery to current post
        $gallery->setEntity($this->contentPostEntity)->setEntityItemID($post->getID());

        /** @var GalleryShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(GalleryShortcode::codename());

        // Saving to get ID
        $this->galleryRepository->save($gallery);

        $shortcode->setID($gallery->getID());
        $shortcode->useSliderLayout();

        $wonderPluginID = $s->getParameter('id');
        $config         = $this->wp->get_wonderplugin_slider_config($wonderPluginID);
        $this->logger->debug('Processing wonderplugin slider :id', [':id' => $wonderPluginID]);

        /** @var array $slides */
        $slides = $config['slides'];

        foreach ($slides as $slide) {
            $url = $slide['image'];

            // Searching for image
            $wpImage = $this->wp->get_attachment_by_path($url);

            if (!$wpImage) {
                throw new TaskException('Unknown image in wonderplugin: :url', [':url' => $url]);
            }

            if (!$wpImage['post_excerpt']) {
                $caption = $slide['title'] ?: $slide['alt'];

                // Preset image title
                $wpImage['post_excerpt'] = $caption;
            }

            /** @var \BetaKiller\Model\ContentImageInterface $image */
            $image = $this->processWordpressAttachment($wpImage, $post->getID());

            $this->logger->debug('Adding image :id to wonderplugin slider', [':id' => $image->getID()]);

            $gallery->addImage($image);
        }

        return $shortcode->asHtml();
    }

    /**
     * @param \DiDom\Document $root
     * @param int             $entityItemID
     */
    private function processImagesInText(Document $root, int $entityItemID): void
    {
        $images = $root->find('img');

        foreach ($images as $image) {
            $shortcode = null;

            try {
                // Creating new [image /] tag as replacement for <img />
                $shortcode = $this->processImgTag($image, $entityItemID);
            } catch (Throwable $e) {
                $this->logException($this->logger, $e);
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
     * @param                $entity_item_id
     *
     * @return ImageShortcode
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \ORM_Validation_Exception
     */
    private function processImgTag(\DiDom\Element $node, $entity_item_id): ImageShortcode
    {
        // Getting attributes
        $attributes = $node->attributes();

        // Original URL
        $originalUrl = trim($attributes['src']);

        $this->logger->debug('Found inline image :tag', [':tag' => $node->html()]);

        $wpImage = $this->wp->get_attachment_by_path($originalUrl);

        if (!$wpImage) {
            throw new TaskException('Unknown image with src :value', [':value' => $originalUrl]);
        }

        /** @var \BetaKiller\Model\ContentImageInterface $image */
        $image = $this->processWordpressAttachment($wpImage, $entity_item_id);

        $alt   = trim(Arr::get($attributes, 'alt'));
        $title = trim(Arr::get($attributes, 'title'));

        // Save alt and title in image model
        if ($alt) {
            $image->setAlt($alt);
        }

        if ($title) {
            $image->setTitle($title);
        }

        $this->imageRepository->save($image);

        // Removing unnecessary attributes
        unset(
            $attributes['id'],
            $attributes['src'],
            $attributes['alt'],
            $attributes['title']
        );

        // Convert old full-size images to responsive images
        if (isset($attributes['width']) && (int)$attributes['width'] === 780) { // TODO move 780 to config
            unset($attributes['width']);
        }

        // Height would be calculated automatically from width and scale in widget
        unset($attributes['height']);

        $attributes['id'] = $image->getID();

        /** @var ImageShortcode $shortcode */
        $shortcode = $this->shortcodeFacade->createFromCodename(ImageShortcode::codename(), $attributes);

        return $shortcode;
    }

    /**
     * @param \BetaKiller\Model\ContentPost $item
     */
    private function processContentYoutubeIFrames(ContentPost $item): void
    {
        $text = $this->processYoutubeVideosInText($item->getContent(), $item->getID());

        $item->setContent($text);
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
                $this->logException($this->logger, $e);
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
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     * @throws \Kohana_Exception
     * @throws \ORM_Validation_Exception
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
            $video = $this->youtubeRepository->create();
            $video->setYoutubeId($youtubeID);
        }

        $width  = trim(Arr::get($attributes, 'width'));
        $height = trim(Arr::get($attributes, 'height'));

        // Save width and height in video model if they not set
        if ($width && !$video->getWidth()) {
            $video->setWidth($width);
        }

        if ($height && !$video->getHeight()) {
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

        $attributes = [
            'id'     => $video->getID(),
            'width'  => $width,
            'height' => $height,
        ];

        return $this->shortcodeFacade->createFromCodename(YoutubeShortcode::codename(), $attributes)->asHtml();
    }

    /**
     * Import all categories with WP IDs
     *
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \ORM_Validation_Exception
     */
    private function importCategories(): void
    {
        $categories = $this->wp->get_categories_with_posts();

        $total   = count($categories);
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
                $category = $this->categoryRepository->create();
                $category->setWpId($wpID);
            }

            $category->setLabel($label);
            $category->setUri($uri);

            $this->categoryRepository->save($category);

            // Find articles related to current category
            $postsWpIDs = $this->wp->get_posts_ids_linked_to_category($wpID);

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
     * @throws \ORM_Validation_Exception
     */
    private function importQuotes(): void
    {
        $quotesData = $this->wp->get_quotes_collection_quotes();

        foreach ($quotesData as $data) {
            $id        = $data['id'];
            $text      = $data['text'];
            $author    = $data['author'];
            $createdAt = new DateTime($data['created_at']);

            $model = $this->quoteRepository->findByWpId($id);

            if (!$model) {
                $model = $this->quoteRepository->create();
                $model->setWpId($id);
            }

            $model
                ->setCreatedAt($createdAt)
                ->setAuthor($author)
                ->setText($text);

            $this->quoteRepository->save($model);
        }
    }

    /**
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Task\TaskException
     */
    private function importComments(): void
    {
        $comments_data = $this->wp->get_comments();

        $this->logger->info('Processing :total comments ', [
            ':total' => count($comments_data),
        ]);

        foreach ($comments_data as $data) {
            $wpID        = $data['id'];
            $wpParentID  = $data['parent_id'];
            $wpPostID    = $data['post_id'];
            $created_at  = new DateTime($data['created_at']);
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
                $model = $this->commentRepository->create();
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
                continue;
            }

            $model->setParent($parentModel);

            $model
                ->setEntity($this->contentPostEntity)
                ->setEntityItemID($post->getID());

            $model
                ->setIpAddress($authorIP)
                ->setUserAgent($userAgent)
                ->setCreatedAt($created_at);

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
                $model->initAsSpam();
            } elseif ($isTrash) {
                $model->initAsTrash();
            } elseif ($isApproved) {
                $model->initAsApproved();
            } else {
                $model->initAsPending();
            }

            $model->setMessage($message);

            try {
                $this->commentRepository->save($model);
            } catch (ORM_Validation_Exception $e) {
                $this->logger->warning('Comment with WP ID = :id is invalid, skipping :errors', [
                    ':id'     => $wpID,
                    ':errors' => json_encode($this->commentRepository->getValidationExceptionErrors($e)),
                ]);
            }
        }
    }

    private function importUsers(): void
    {
        $this->logger->info('Importing users...');

        $wpUsers = $this->wp->get_users();

        foreach ($wpUsers as $wpUser) {
            $wpLogin = $wpUser['login'];
            $wpEmail = $wpUser['email'];

            $userModel = $this->userRepository->searchBy($wpEmail) ?: $this->userRepository->searchBy($wpLogin);

            if (!$userModel) {
                $userModel = $this->userService->createUser($wpLogin, $wpEmail);
                $this->logger->info('User :login successfully imported', [':login' => $userModel->getUsername()]);
            } else {
                $this->logger->info('User :login already exists', [':login' => $userModel->getUsername()]);
            }
        }
    }
}
