<?php

use BetaKiller\Assets\Model\AssetsModelImageInterface;
use BetaKiller\Assets\Provider\AssetsProviderInterface;
use BetaKiller\Content\CustomTag\AttachmentCustomTag;
use BetaKiller\Content\CustomTag\CaptionCustomTag;
use BetaKiller\Content\CustomTag\GalleryCustomTag;
use BetaKiller\Content\CustomTag\PhotoCustomTag;
use BetaKiller\Content\CustomTag\YoutubeCustomTag;
use BetaKiller\Model\ContentImage;
use BetaKiller\Model\ContentPost;
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
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

class Task_Content_Import_Wordpress extends AbstractTask
{
    const ATTACH_PARSING_MODE_HTTP  = 'http';
    const ATTACH_PARSING_MODE_LOCAL = 'local';

    const WP_OPTION_PARSING_MODE = 'betakiller_parsing_mode';
    const WP_OPTION_PARSING_PATH = 'betakiller_parsing_path';

    protected $attach_parsing_mode;

    protected $attach_parsing_path;

    protected $unknown_bb_tags = [];

    protected $skip_before_date;

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
     * @var \BetaKiller\Helper\AssetsHelper
     * @Inject
     */
    private $assetsHelper;

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
     * @var \CustomTagFacade
     */
    private $customTagFacade;

    /**
     * @Inject
     * @var \WP
     */
    private $wp;

    protected function define_options(): array
    {
        return [
            'skip-before' => null,
        ];
    }

    protected function _execute(array $params): void
    {
        if ($params['skip-before']) {
            $this->skip_before_date = new DateTime($params['skip-before']);
        }

        $this->configure_dialog();

        // Users
        $this->import_users();

        // Posts
        $this->import_posts_and_pages();

        // Categories
        $this->import_categories();

        // Comments to posts and pages
        $this->import_comments();

        // Quotes plugin
        $this->import_quotes();
    }

    protected function configure_dialog(): void
    {
        $parsing_mode = $this->wp->get_option(self::WP_OPTION_PARSING_MODE);

        if (!$parsing_mode) {
            $parsing_mode = $this->read('Select parsing mode', [
                self::ATTACH_PARSING_MODE_HTTP,
                self::ATTACH_PARSING_MODE_LOCAL,
            ]);
        }

        $this->info('Parsing mode is: '.$parsing_mode);


        $parsing_path = $this->wp->get_option(self::WP_OPTION_PARSING_PATH);

        if (!$parsing_path) {
            if ($parsing_mode === self::ATTACH_PARSING_MODE_HTTP) {
                $parsing_path = $this->read('Input fully qualified project URL');

                $parsing_path = rtrim($parsing_path, '/').'/';

                if (!Valid::url($parsing_path)) {
                    throw new TaskException('Incorrect project URL');
                }
            } elseif ($parsing_mode === self::ATTACH_PARSING_MODE_LOCAL) {
                $parsing_path = $this->read('Input absolute project path');

                $parsing_path = '/'.trim($parsing_path, '/');

                if (!is_dir($parsing_path) || !file_exists($parsing_path)) {
                    throw new TaskException('Incorrect project path');
                }
            }
        }

        $this->info('Parsing path is: '.$parsing_path);

        $this->attach_parsing_mode = $parsing_mode;
        $this->attach_parsing_path = $parsing_path;

        $this->wp->set_option(self::WP_OPTION_PARSING_MODE, $parsing_mode);
        $this->wp->set_option(self::WP_OPTION_PARSING_PATH, $parsing_path);
    }

//    protected function process_attachments(array $attachments, AbstractAssetsProvider $provider = NULL)
//    {
//        $this->info('Processing attachments');
//
//        foreach ($attachments as $attach)
//        {
//            try
//            {
//                $this->process_attachment($attach, , $provider);
//            }
//            catch (Exception $e)
//            {
//                $this->warning('Error on processing attach with WP ID = :id'.PHP_EOL.':message', [
//                    ':id'       =>  $attach['ID'],
//                    ':message'  =>  $e->getMessage(),
//                ]);
//            }
//        }
//    }

    /**
     * @return Entity
     */
    protected function get_content_post_entity(): Entity
    {
        static $contentEntity;

        if (!$contentEntity) {
            $contentEntity = $this->entityRepository->findByModelName('ContentPost');
        }

        return $contentEntity;
    }

    protected function process_attachment(
        array $attach,
        int $entityItemID,
        AssetsProviderInterface $provider = null
    ): WordpressAttachmentInterface {
        $wp_id = $attach['ID'];
        $url   = $attach['guid'];

        if (!$wp_id || !$url) {
            throw new TaskException('Empty attach data');
        }

        $this->debug('Found attach with guid = :url', [':url' => $url]);

        if (!$provider) {
            $mime = $attach['post_mime_type'];

            $this->debug('Creating assets provider by MIME-type :mime', [':mime' => $mime]);

            // Detect and instantiate assets provider by file MIME-type
            $provider = $this->contentHelper->createAssetsProviderFromMimeType($mime);
        }

        $model = $this->store_attachment($provider, $url, $wp_id, $entityItemID);

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
     * @param int                     $wp_id
     * @param int|null                $entityItemID
     *
     * @return \BetaKiller\Model\WordpressAttachmentInterface
     * @throws TaskException
     */
    protected function store_attachment(
        AssetsProviderInterface $provider,
        string $url,
        int $wp_id,
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
//        /** @var \BetaKiller\Model\WordpressAttachmentInterface $model */
        $model = $repository->findByWpID($wp_id);

        if ($model) {
            $this->debug('Attach with WP ID = :id already exists, data = :data', [
                ':id'   => $wp_id,
                ':data' => $model->toJson(),
            ]);

            return $model;
        }

        $this->debug('Adding attach with WP ID = :id', [':id' => $wp_id]);

        $url_path          = parse_url($url, PHP_URL_PATH);
        $original_filename = basename($url);

        // Getting path for local file with attachment content
        $path = $this->get_attachment_path($url_path, $provider->getAllowedMimeTypes());

        if (!$path) {
            throw new TaskException('Can not get path for guid = :url', [':url' => $url]);
        }

        /** @var \BetaKiller\Model\WordpressAttachmentInterface $model */
        $model = $provider->store($path, $original_filename, $this->user);

        if ($model instanceof \BetaKiller\Model\EntityModelRelatedInterface) {
            // Storing entity
            $model->setEntity($this->get_content_post_entity());

            // Storing entity item ID
            if ($entityItemID) {
                $model->setEntityItemID($entityItemID);
            }
        }

        // Storing WP path and ID
        $model->setWpPath($url_path);
        $model->setWpId($wp_id);
        $provider->saveModel($model);

        // Cleanup temp files
        if ($this->attach_parsing_mode === self::ATTACH_PARSING_MODE_HTTP) {
            unlink($path);
        }

        $this->info('Attach with WP ID = :id successfully stored', [':id' => $wp_id]);

        return $model;
    }

    protected function get_attachment_path($original_url_path, $expected_mimes)
    {
        if ($this->attach_parsing_mode === self::ATTACH_PARSING_MODE_HTTP) {
            $url = $this->attach_parsing_path.ltrim($original_url_path, '/');

            $this->debug('Loading attach at url = :url', [':url' => $url]);

            // TODO Replace with system-wide crawler
            $request  = Request::factory($url);
            $response = $request->execute();

            if ($response->status() !== 200) {
                throw new TaskException('Got :code status from :url', [
                    ':code' => $response->status(),
                    ':url'  => $url,
                ]);
            }

            $real_mime = $response->headers('Content-Type');

            if (is_array($expected_mimes) && !in_array($real_mime, $expected_mimes, true)) {
                throw new TaskException('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     => $real_mime,
                    ':expected' => implode('] or [', $expected_mimes),
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

        if ($this->attach_parsing_mode === self::ATTACH_PARSING_MODE_LOCAL) {
            $path = $this->attach_parsing_path.'/'.trim($original_url_path, '/');

            if (!file_exists($path)) {
                throw new TaskException('No file exists at :path', [':path' => $path]);
            }

            $this->debug('Getting attach at local path = :path', [':path' => $path]);

            $real_mime = File::mime($path);

            if (is_array($expected_mimes) && !in_array($real_mime, $expected_mimes, true)) {
                throw new TaskException('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     => $real_mime,
                    ':expected' => implode('] or [', $expected_mimes),
                ]);
            }

            return $path;
        }

        return null;
    }

    protected function import_posts_and_pages(): void
    {
        $posts = $this->wp->get_posts_and_pages($this->skip_before_date);

        $total   = $posts->count();
        $current = 1;

        foreach ($posts as $post) {
            $wpID       = $post['ID'];
            $uri        = $post['post_name'];
            $name       = $post['post_title'];
            $type       = $post['post_type'];
            $content    = $post['post_content'];
            $created_at = new DateTime($post['post_date']);
            $updated_at = new DateTime($post['post_modified']);

            $meta        = $this->wp->get_post_meta($wpID);
            $title       = $meta['_aioseop_title'] ?? null;
            $description = $meta['_aioseop_description'] ?? null;

            $this->info('[:current/:total] Processing article :uri', [
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
            $this->process_thumbnails($model, $meta);

            if ($model->getContent()) {
                // Parsing custom tags next
                $this->process_custom_tags($model);

                // Processing YouTube <iframe> embeds
                $this->process_content_youtube_iframes($model);

                $this->post_process_article_text($model);
            } else {
                $this->warning('Post has no content at :uri', [':uri' => $uri]);
            }

            // Saving original creating and modification dates
            $model->setCreatedAt($created_at);
            $model->setUpdatedAt($updated_at);

            // Actualize revision with imported data
            $model->setLatestRevisionAsActual();

            // Auto publishing for new posts (we are importing only published posts)
            if ($isNew) {
                $model->complete(); // Publishing would be done automatically
            }

            // Saving model content
            $this->postRepository->save($model);

            $current++;
        }

        $this->notify_about_bb_tags();
    }

    protected function notify_about_bb_tags(): void
    {
        foreach ($this->unknown_bb_tags as $tag => $url) {
            $this->notice('Found unknown BB tag [:name] at :url', [':name' => $tag, ':url' => $url]);
        }
    }

    protected function post_process_article_text(ContentPost $item): void
    {
        $this->debug('Text post processing...');

        $text = $item->getContent();

        $text = $this->wp->autop($text, false);

        $document = new Document();

        // Make custom tags self-closing
        $document->loadHtml($text, LIBXML_PARSEHUGE | LIBXML_NONET);

        $body = $document->find('body')[0];

        if ($body) {
            // Parsing all images first to get @alt and @title values
            $this->process_images_in_text($document, $item->getID());

            // Process attachments first coz they are images inside links
            $this->update_links_on_attachments($document, $item->getID());
//            $this->remove_links_on_content_images($document);

            $text = $body->innerHtml();
            $text = $this->removeClosingCustomTags($text);
            $item->setContent($text);
        } else {
            $this->warning('Post parsing error for :url', [':url' => $item->getUri()]);
        }
    }

    /**
     * Dirty hack for php-dom library that creates CustomTagFacade elements with closing tags
     *
     * @param string $text
     *
     * @return string
     */
    private function removeClosingCustomTags($text): string
    {
        foreach ($this->customTagFacade->getSelfClosingTags() as $tag) {
            $text = str_replace('></'.$tag.'>', ' />', $text);
        }

        return $text;
    }

//    protected function remove_links_on_content_images(Document $root)
//    {
//        $this->debug('Removing links on content images...');
//
//        $tag = CustomTagFacade::TAG_NAME;
//
//        $images = $root->find('a > '.$tag);
//
//        foreach ($images as $image)
//        {
//            $link = $image->parent();
//
//            $link->replace($image);
//
////            $link->remove();
//            unset($link);
//        }
//    }

    protected function update_links_on_attachments(Document $document, $post_id): void
    {
        $this->debug('Updating links on attachments...');

        $links = $document->find('a');

        foreach ($links as $link) {
            $href = $link->getAttribute('href');

            // Skip non-attachment links
            if (strpos($href, '/wp-content/') === false) {
                continue;
            }

            $attach = $this->wp->get_attachment_by_path($href);

            if (!$attach) {
                throw new TaskException('Unknown attachment href :url', [':url' => $href]);
            }

            $model = $this->process_attachment($attach, $post_id);

            $new_url = $this->assetsHelper->getOriginalUrl($model);

            // TODO Если внутри ссылки есть картинка, которая ведёт на ту же картинку, то надо заменить этот блок картинкой со спецатрибутами zoomable

            // TODO Links with images inside must be marked as type="button"
            // TODO Links without child elements => type="link" text="..."

            $attach = $document->createElement(AttachmentCustomTag::TAG_NAME, null, [
                'id' => $model->getID(),
            ]);

            $link->replace($attach);

            $this->debug('Link for :old was changed to :new', [':old' => $href, ':new' => $new_url]);
        }
    }

    protected function process_thumbnails(ContentPost $post, array $meta): void
    {
        $wp_id = $post->getWpId();

        $wp_images_ids = [];

        if ($this->wp->post_has_post_format($wp_id, 'gallery')) {
            $this->debug('Getting thumbnail images from from _format_gallery_images');

            // Getting images from meta._format_gallery_images
            $wp_images_ids += (array)unserialize($meta['_format_gallery_images'], false);
        }

        if (!$wp_images_ids && isset($meta['_thumbnail_id'])) {
            $this->debug('Getting thumbnail image from from _thumbnail_id');

            // Getting thumbnail image from meta._thumbnail_id
            $wp_images_ids = [$meta['_thumbnail_id']];
        }

        if (!$wp_images_ids) {
            if ($post->needsThumbnails()) {
                $this->warning('Article with uri [:uri] has no thumbnail', [
                    ':uri' => $post->getUri(),
                ]);
            }

            return;
        }

        // Getting data for each thumbnail
        $images_wp_data = $this->wp->get_attachments(null, $wp_images_ids);

        if (!$images_wp_data) {
            $this->warning('Some images can not be found with WP ids :ids', [
                ':ids' => implode(', ', $wp_images_ids),
            ]);

            return;
        }


        $provider = $this->contentHelper->getPostThumbnailAssetsProvider();

        foreach ($images_wp_data as $image_data) {
            /** @var \BetaKiller\Model\ContentPostThumbnailInterface $image_model */
            $image_model = $this->process_attachment($image_data, $post->getID(), $provider);

            // Linking image to post
            $image_model->setPost($post);

            $provider->saveModel($image_model);
        }
    }

    protected function process_custom_tags(ContentPost $item): void
    {
        $this->debug('Processing custom tags...');

        $handlers = new \Thunder\Shortcode\HandlerContainer\HandlerContainer();

        // [caption id="attachment_571" align="alignnone" width="780"]
        $handlers->add('caption', function (ShortcodeInterface $shortcode) use ($item) {
            return $this->thunder_handler_caption($shortcode, $item);
        });

        // [gallery ids="253,261.260"]
        $handlers->add('gallery', function (ShortcodeInterface $shortcode) use ($item) {
            return $this->thunder_handler_gallery($shortcode, $item);
        });

        // [wonderplugin_slider id="1"]
        $handlers->add('wonderplugin_slider', function (ShortcodeInterface $shortcode) use ($item) {
            return $this->thunder_handler_wonderplugin($shortcode, $item);
        });

        // All unknown shortcodes
        $handlers->setDefault(function (ShortcodeInterface $s) use ($item) {
            $serializer = new TextSerializer();
            $name       = $s->getName();

            if (!isset($this->unknown_bb_tags[$name])) {
                $this->unknown_bb_tags[$name] = $this->ifaceHelper->getReadEntityUrl($item, IFaceZone::PUBLIC_ZONE);
                $this->debug('Unknown BB-code found [:name], keep it', [':name' => $name]);
            }

            return $serializer->serialize($s);
        });

        $parser    = new RegexParser;
        $processor = new Processor($parser, $handlers);

        $content = $item->getContent();
        $content = $processor->process($content);
        $item->setContent($content);
    }

    public function thunder_handler_caption(ShortcodeInterface $s, ContentPost $post): ?string
    {
        $this->debug('[caption] found');

        $parameters = $s->getParameters();

        $image_wp_id = (int)str_replace('attachment_', '', $parameters['id']);
        unset($parameters['id']);

        // Find image in WP and process attachment
        $wp_image_data = $this->wp->get_attachment_by_id($image_wp_id);

        if (!$wp_image_data) {
            $this->warning('No image found by wp_id :id', [':id' => $image_wp_id]);

            return null;
        }

        $image = $this->process_attachment($wp_image_data, $post->getID());

        // Removing <img /> tag
        $caption_text = trim(strip_tags($s->getContent()));

        $parameters['title'] = $caption_text;

        return $this->customTagFacade->generateHtml(CaptionCustomTag::TAG_NAME, $image->getID(), $parameters);
    }

    public function thunder_handler_gallery(ShortcodeInterface $s, ContentPost $post): ?string
    {
        $this->debug('[gallery] found');

        $wp_ids_string = $s->getParameter('ids');
        $type          = $s->getParameter('type');
        $columns       = (int)$s->getParameter('columns');

        if (strpos($type, 'slider') !== false) {
            $type = 'slider';
        }

        // Removing spaces
        $wp_ids_string = str_replace(' ', '', $wp_ids_string);
        $wp_ids        = explode(',', $wp_ids_string);

        $wp_images = $this->wp->get_attachments(null, $wp_ids);

        if (!$wp_images) {
            $this->warning('No images found for gallery with WP IDs :ids', [':ids' => implode(', ', $wp_ids)]);

            return null;
        }

        $images_ids = [];

        // Process every image in set
        foreach ($wp_images as $wp_image_data) {
            $model        = $this->process_attachment($wp_image_data, $post->getID());
            $images_ids[] = $model->getID();
        }

        $attributes = [
            'ids'     => implode(',', $images_ids),
            'type'    => $type,
            'columns' => $columns,
        ];

        // No ID in this tag
        return $this->customTagFacade->generateHtml(GalleryCustomTag::TAG_NAME, null, $attributes);
    }

    public function thunder_handler_wonderplugin(ShortcodeInterface $s, ContentPost $post): string
    {
        $this->debug('[wonderplugin_slider] found');

        $id = $s->getParameter('id');

        $this->debug('Processing wonderplugin slider :id', [':id' => $id]);

        $config = $this->wp->get_wonderplugin_slider_config($id);

        /** @var array $slides */
        $slides = $config['slides'];

        $images_ids = [];

        foreach ($slides as $slide) {
            $url = $slide['image'];
//            $url_path = parse_url($url, PHP_URL_PATH);

            // Searching for image
            $wp_image = $this->wp->get_attachment_by_path($url);

            if (!$wp_image) {
                throw new TaskException('Unknown image in wonderplugin: :url', [':url' => $url]);
            }

            if (!$wp_image['post_excerpt']) {
                $caption = $slide['title'] ?: $slide['alt'];

                // Preset image title
                $wp_image['post_excerpt'] = $caption;
            }

            // Processing image

            /** @var \BetaKiller\Assets\Model\AssetsModelImageInterface $image */
            $image = $this->process_attachment($wp_image, $post->getID());


            $this->debug('Adding image :id to wonderplugin slider', [':id' => $image->getID()]);

            $images_ids[] = $image->getID();
        }

        $attributes = [
            'ids'  => implode(',', $images_ids),
            'type' => 'slider',
        ];

        // No ID in this tag
        return $this->customTagFacade->generateHtml(GalleryCustomTag::TAG_NAME, null, $attributes);
    }

    /**
     * @param Document $root
     * @param int      $entity_item_id
     */
    protected function process_images_in_text(Document $root, $entity_item_id): void
    {
        $images = $root->find('img');

        foreach ($images as $image) {
            $targetTag = null;

            try {
                // Creating new <photo /> tag as replacement for <img />
                $targetTag = $this->processImgTag($image, $entity_item_id);
            } catch (Throwable $e) {
                $this->warning(':message', [':message' => $e->getMessage()]);
            }

            // Exit if something went wrong
            if (!$targetTag) {
                continue;
            }

            $this->debug('Replacement tag is :tag', [':tag' => $targetTag->html()]);

            $parent           = $image->parent();
            $parent_of_parent = $parent->parent();

            $parentTagName = $parent->getNode()->nodeName;

            $this->debug('Parent tag name is :name', [':name' => $parentTagName]);

            // Remove links to content images coz they would be added automatically
            if ($parentTagName === 'a' && $parent->attr('href') === $image->attr('src')) {
                // Mark image as "zoomable"
                $targetTag->attr(PhotoCustomTag::ATTRIBUTE_ZOOMABLE_NAME, PhotoCustomTag::ATTRIBUTE_ZOOMABLE_ENABLED);
                $parent->replace($targetTag);
            } else {
                $image->replace($targetTag);
            }

            $this->debug('Parent html is :html', [':html' => $parent_of_parent->html()]);
        }
    }

    protected function processImgTag(\DiDom\Element $node, $entity_item_id): \DiDom\Element
    {
        // Getting attributes
        $attributes = $node->attributes();

        // Original URL
        $original_url = trim($attributes['src']);

        $this->debug('Found inline image :tag', [':tag' => $node->html()]);

        $wp_image = $this->wp->get_attachment_by_path($original_url);

        if (!$wp_image) {
            throw new TaskException('Unknown image with src :value', [':value' => $original_url]);
        }

        /** @var ContentImage $image */
        $image = $this->process_attachment($wp_image, $entity_item_id);

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
        if (isset($attributes['width']) && $attributes['width'] === 780) // TODO move 780 to config
        {
            unset(
                $attributes['width'],
                $attributes['height']
            );
        }

        $attributes['id'] = $image->getID();

        $document = $node->getDocument();
        $element  = $document->createElement(PhotoCustomTag::TAG_NAME);

        foreach ($attributes as $key => $value) {
            $element->setAttribute($key, $value);
        }

        return $element;
//        return $node->getDocument()->createElement(CustomTagFacade::TAG_NAME, null, $attributes);
    }

    protected function process_content_youtube_iframes(ContentPost $item): void
    {
        $text = $this->process_youtube_videos_in_text($item->getContent(), $item->getID());

        $item->setContent($text);
    }

    protected function process_youtube_videos_in_text($text, $entity_item_id)
    {
        $this->debug('Processing Youtube iframe tags...');

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
            $original_tag = $match[0];
            $target_tag   = null;

            try {
                // Создаём новый тег <youtube /> на замену <iframe />
                $target_tag = $this->processYoutubeIFrameTag($original_tag, $entity_item_id);
            } catch (\Throwable $e) {
                $this->warning($e->getMessage());
            }

            // Если новый тег не сформирован, то просто переходим к следующему
            if (!$target_tag) {
                continue;
            }

            // Производим замену в тексте
            $text = str_replace($original_tag, $target_tag, $text);
        }

        return $text;
    }

    protected function processYoutubeIFrameTag($tag_string, $entity_item_id): string
    {
        // Parsing
        $sx = simplexml_load_string($tag_string);

        if ($sx === false) {
            throw new TaskException('Youtube iframe tag parsing failed on :string', [':string' => $tag_string]);
        }

        // Getting attributes
        $attributes = iterator_to_array($sx->attributes());

        // Original URL
        $original_url = trim($attributes['src']);

        $this->debug('Found youtube iframe :tag', [':tag' => $tag_string]);

        $youtubeID = $this->youtubeRepository->getYoutubeIdFromEmbedUrl($original_url);

        if (!$youtubeID) {
            throw new TaskException('Youtube iframe ID parsing failed on :string', [':string' => $tag_string]);
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
            $this->info('Youtube video :id processed', [':id' => $youtubeID]);
        }

        $video
            ->setUploadedBy($this->user)
            ->setEntity($this->get_content_post_entity())
            ->setEntityItemID($entity_item_id);

        $this->youtubeRepository->save($video);

        $attributes = [
            'width'  => $width,
            'height' => $height,
        ];

        return $this->customTagFacade->generateHtml(YoutubeCustomTag::TAG_NAME, $video->getID(), $attributes);
    }

    /**
     * Import all categories with WP IDs
     */
    protected function import_categories(): void
    {
        $categories = $this->wp->get_categories_with_posts();

        $total   = count($categories);
        $current = 1;

        foreach ($categories as $term) {
            $wpID  = $term['term_id'];
            $uri   = $term['slug'];
            $label = $term['name'];

            $this->info('[:current/:total] Processing category :uri', [
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
            $posts_wp_ids = $this->wp->get_posts_ids_linked_to_category($wpID);

            // Check for any linked objects
            if ($posts_wp_ids) {
                // Get real article IDs
                $articles_ids = $this->postRepository->findIDsByWpIDs($posts_wp_ids);

                // Does articles exist?
                if ($articles_ids) {
                    // Link articles to category
                    $category->linkPosts($articles_ids);
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

            /** @var \BetaKiller\Model\ContentCategoryInterface|null $category */
            $category = $this->categoryRepository->findByWpID($wpID);

            /** @var \BetaKiller\Model\ContentCategoryInterface|null $parent */
            $parent = $this->categoryRepository->findByWpID($parentWpID);

            $category->setParent($parent);

            $this->categoryRepository->save($category);
        }
    }

    protected function import_quotes(): void
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

    protected function import_comments(): void
    {
        $comments_data = $this->wp->get_comments();

        $this->info('Processing :total comments ', [
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
                ->setEntity($this->get_content_post_entity())
                ->setEntityItemID($post->getID());

            $model
                ->set_ip_address($authorIP)
                ->set_user_agent($userAgent)
                ->set_created_at($created_at);

            // Detecting user by name
            $authorUser = $this->userRepository->searchBy($authorName);

            if ($authorUser) {
                $model->set_author_user($authorUser);
            } else {
                $model->set_guest_author_name($authorName)->set_guest_author_email($authorEmail);
            }

            $isApproved = ((int)$wpApproved === 1);
            $isSpam     = (mb_strtolower($wpApproved) === 'spam');
            $isTrash    = (mb_strtolower($wpApproved) === 'trash');

            if ($isSpam) {
                $model->init_as_spam();
            } elseif ($isTrash) {
                $model->init_as_trash();
            } elseif ($isApproved) {
                $model->init_as_approved();
            } else {
                $model->init_as_pending();
            }

            $model->set_message($message);

            try {
                $this->commentRepository->save($model);
            } catch (ORM_Validation_Exception $e) {
                $this->warning('Comment with WP ID = :id is invalid, skipping :errors', [
                    ':id'     => $wpID,
                    ':errors' => json_encode($this->commentRepository->getValidationExceptionErrors($e)),
                ]);
            }
        }
    }

    protected function import_users(): void
    {
        $this->info('Importing users...');

        $wpUsers = $this->wp->get_users();

        foreach ($wpUsers as $wpUser) {
            $wpLogin = $wpUser['login'];
            $wpEmail = $wpUser['email'];

            $userModel = $this->userRepository->searchBy($wpEmail) ?: $this->userRepository->searchBy($wpLogin);

            if (!$userModel) {
                $userModel = $this->userRepository->createNewUser($wpLogin, $wpEmail);
                $this->info('User :login successfully imported', [':login' => $userModel->getUsername()]);
            } else {
                $this->info('User :login already exists', [':login' => $userModel->getUsername()]);
            }
        }
    }
}
