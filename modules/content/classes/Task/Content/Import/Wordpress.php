<?php

use BetaKiller\Task\TaskException;
use DiDom\Document;
use BetaKiller\Content\ImportedFromWordpressInterface;
use BetaKiller\Content\HasWordpressPathInterface;
use BetaKiller\Content\ContentElementInterface;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;
use Thunder\Shortcode\Serializer\TextSerializer;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Parser\RegexParser;

class Task_Content_Import_Wordpress extends Minion_Task
{
    use BetaKiller\Helper\ContentTrait;
    use BetaKiller\Helper\CurrentUserTrait;
    use BetaKiller\Helper\UserModelFactoryTrait;
    use BetaKiller\Helper\RoleModelFactoryTrait;

    const ATTACH_PARSING_MODE_HTTP = 'http';
    const ATTACH_PARSING_MODE_LOCAL = 'local';

    const WP_OPTION_PARSING_MODE = 'betakiller_parsing_mode';
    const WP_OPTION_PARSING_PATH = 'betakiller_parsing_path';

    const CONTENT_ENTITY_ID = Model_ContentEntity::POSTS_ENTITY_ID;

    protected $attach_parsing_mode;
    protected $attach_parsing_path;

    protected $unknown_bb_tags = [];

    protected $skip_before_date = null;

    protected function define_options()
    {
        return [
            'skip-before'   =>  null,
        ];
    }

    protected function _execute(array $params)
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

    protected function configure_dialog()
    {
        $wp = $this->wp();

        $parsing_mode = $wp->get_option(self::WP_OPTION_PARSING_MODE);

        if (!$parsing_mode)
        {
            $parsing_mode = $this->read('Select parsing mode', [
                self::ATTACH_PARSING_MODE_HTTP,
                self::ATTACH_PARSING_MODE_LOCAL,
            ]);
        }

        $this->info('Parsing mode is: '.$parsing_mode);


        $parsing_path = $wp->get_option(self::WP_OPTION_PARSING_PATH);

        if (!$parsing_path)
        {
            if ($parsing_mode == self::ATTACH_PARSING_MODE_HTTP)
            {
                $parsing_path = $this->read('Input fully qualified project URL');

                $parsing_path = rtrim($parsing_path, '/').'/';

                if (!Valid::url($parsing_path))
                    throw new TaskException('Incorrect project URL');
            }
            elseif ($parsing_mode == self::ATTACH_PARSING_MODE_LOCAL)
            {
                $parsing_path = $this->read('Input absolute project path');

                $parsing_path = '/'.trim($parsing_path, '/');

                if (!is_dir($parsing_path) OR !file_exists($parsing_path))
                    throw new TaskException('Incorrect project path');
            }
        }

        $this->info('Parsing path is: '.$parsing_path);

        $this->attach_parsing_mode = $parsing_mode;
        $this->attach_parsing_path = $parsing_path;

        $wp->set_option(self::WP_OPTION_PARSING_MODE, $parsing_mode);
        $wp->set_option(self::WP_OPTION_PARSING_PATH, $parsing_path);
    }

//    protected function process_attachments(array $attachments, Assets_Provider $provider = NULL)
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
     * @return Model_ContentEntity
     */
    protected function get_content_post_entity()
    {
        static $content_entity;

        if (!$content_entity)
        {
            $content_entity = $this->model_factory_content_entity(self::CONTENT_ENTITY_ID);
        }

        return $content_entity;
    }

    protected function process_attachment(array $attach, $entity_item_id, Assets_Provider $provider = NULL)
    {
        $wp_id = $attach['ID'];
        $url = $attach['guid'];

        if (!$wp_id || !$url)
            throw new TaskException('Empty attach data');

        $this->debug('Found attach with guid = :url', [':url' => $url]);

        if (!$provider)
        {
            $mime = $attach['post_mime_type'];

            $this->debug('Creating assets provider by MIME-type :mime', [':mime' => $mime]);

            // Detect and instantiate assets provider by file MIME-type
            $provider = $this->service_content_facade()->assets_provider_factory_from_mime($mime);
        }

        $model = $this->store_attachment($provider, $url, $wp_id, $entity_item_id);

        // Save created_at + updated_at
        $created_at = new DateTime($attach['post_date']);
        $updated_at = new DateTime($attach['post_modified']);

        if ($model instanceof Assets_Model_ORM_SeoImage) {
            $title = $attach['post_excerpt'];

            if ($title && !$model->get_title()) {
                $model->set_title($title);
            }
        }

        $model
            ->set_uploaded_at($created_at)
            ->set_last_modified_at($updated_at)
            ->save();

        return $model;
    }

    /**
     * @param Assets_Provider   $provider
     * @param string            $url
     * @param int               $wp_id
     * @param int|null          $entity_item_id
     *
*@return Model_ContentAttachmentElement
     * @throws TaskException
     */
    protected function store_attachment(Assets_Provider $provider, $url, $wp_id, $entity_item_id = NULL)
    {
        $orm = $provider->file_model_factory();

        if (!$orm instanceof ImportedFromWordpressInterface)
            throw new TaskException('Attachment model must be instance of :class', [':class' => ImportedFromWordpressInterface::class]);

        // Search for such file already exists
        $model = $orm->find_by_wp_id($wp_id);

        if (!$model)
        {
            $this->debug('Adding attach with WP ID = :id', [':id' => $wp_id]);

            $url_path = parse_url($url, PHP_URL_PATH);
            $original_filename = basename($url);

            // Getting path for local file with attachment content
            $path = $this->get_attachment_path($url_path, $provider->get_allowed_mime_types());

            if (!$path)
                throw new TaskException('Can not get path for guid = :url', [':url' => $url]);

            /** @var Model_ContentAttachmentElement $model */
            $model = $provider->store($path, $original_filename);

            if ($model instanceof ContentElementInterface)
            {
                // Storing entity
                $model->set_entity($this->get_content_post_entity());

                // Storing entity item ID
                if ($entity_item_id)
                {
                    $model->set_entity_item_id($entity_item_id);
                }
            }

            if ($model instanceof HasWordpressPathInterface)
            {
                // Storing WP ID
                $model->set_wp_path($url_path);
            }

            // Storing WP ID
            $model
                ->set_wp_id($wp_id)
                ->save();

            // Cleanup temp files
            if ($this->attach_parsing_mode == self::ATTACH_PARSING_MODE_HTTP)
            {
                unlink($path);
            }

            $this->info('Attach with WP ID = :id successfully stored', [':id' => $wp_id]);
        }
        else
        {
            $this->debug('Attach with WP ID = :id already exists', [':id' => $wp_id]);
        }

        return $model;
    }

    protected function get_attachment_path($original_url_path, $expected_mimes)
    {
        if ($this->attach_parsing_mode == self::ATTACH_PARSING_MODE_HTTP)
        {
            $url = $this->attach_parsing_path.ltrim($original_url_path, '/');

            $this->debug('Loading attach at url = :url', [':url' => $url]);

            $response = Request::factory($url)->execute();

            if ($response->status() != 200)
                throw new TaskException('Got :code status from :url', [
                    ':code' =>  $response->status(),
                    ':url'  => $url,
                ]);

            $real_mime = $response->headers('Content-Type');

            if (is_array($expected_mimes) AND !in_array($real_mime, $expected_mimes))
                throw new TaskException('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     =>  $real_mime,
                    ':expected' =>  implode('] or [', $expected_mimes),
                ]);

            $content = $response->body();

            if (!$content)
                throw new TaskException('Empty content for url [:url]', [':url' => $url,]);

            $path = tempnam(sys_get_temp_dir(), 'wp-attach-');

            file_put_contents($path, $content);

            return $path;
        }
        else if ($this->attach_parsing_mode == self::ATTACH_PARSING_MODE_LOCAL)
        {
            $path = $this->attach_parsing_path.'/'.trim($original_url_path, '/');

            if (!file_exists($path))
                throw new TaskException('No file exists at :path', [':path' => $path]);

            $this->debug('Getting attach at local path = :path', [':path' => $path]);

            $real_mime = File::mime($path);

            if (is_array($expected_mimes) AND !in_array($real_mime, $expected_mimes))
                throw new TaskException('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     =>  $real_mime,
                    ':expected' =>  implode('] or [', $expected_mimes),
                ]);

            return $path;
        }

        return NULL;
    }

    protected function import_posts_and_pages()
    {
        $wp = $this->wp();

        $posts = $wp->get_posts_and_pages($this->skip_before_date);

        $total = $posts->count();
        $current = 1;

        foreach ($posts as $post)
        {
            $id = $post['ID'];
            $uri = $post['post_name'];
            $name = $post['post_title'];
            $type = $post['post_type'];
            $content = $post['post_content'];
            $created_at = new DateTime($post['post_date']);
            $updated_at = new DateTime($post['post_modified']);

            $meta = $wp->get_post_meta($id);
            $title = isset($meta['_aioseop_title']) ? $meta['_aioseop_title'] : NULL;
            $description = isset($meta['_aioseop_description']) ? $meta['_aioseop_description'] : NULL;

            $this->info('[:current/:total] Processing article :uri', [
                ':uri'      => $uri,
                ':current'  => $current,
                ':total'    => $total,
            ]);

            $content_post_orm = $this->model_factory_content_post();

            $model = $content_post_orm->find_by_wp_id($id);

            // Detect is this is a new record
            $is_new = !$model->get_id();

            if ($type == $wp::POST_TYPE_PAGE)
            {
                $model->mark_as_page();
            }

            $model
                ->set_wp_id($id)
                ->set_uri($uri)
                ->set_label($name)
                ->set_content($content)
                ->set_title($title)
                ->set_description($description);

            // Saving model and getting its ID for further processing
            $model->save();

            // Link thumbnail images to post
            $this->process_thumbnails($model, $meta);

            if ($model->get_content()) {
                // Parsing custom tags next
                $this->process_custom_tags($model);

                // Processing YouTube <iframe> embeds
                $this->process_content_youtube_iframes($model);

                $this->post_process_article_text($model);
            } else {
                $this->warning('Post has no content at :uri', [':uri' => $uri]);
            }

            // Saving original creating and modification dates
            $model
                ->set_created_at($created_at)
                ->set_updated_at($updated_at);

            // Auto publishing for new posts (we are importing only published posts)
            if ($is_new) {
                $model->publish();
            }

            // Saving model content
            $model->save();

            $current++;
        }

        $this->notify_about_bb_tags();
    }

    protected function notify_about_bb_tags()
    {
        foreach ($this->unknown_bb_tags as $tag => $url) {
            $this->notice('Found unknown BB tag [:name] at :url', [':name' => $tag, ':url' => $url]);
        }
    }

    protected function post_process_article_text(Model_ContentPost $item)
    {
        $this->debug('Text post processing...');

        $text = $item->get_content();

        $text = $this->wp()->autop($text, false);

        $document = new Document();

        // Make custom tags self-closing

        $document->loadHtml($text, LIBXML_PARSEHUGE|LIBXML_NONET); //, LIBXML_NOEMPTYTAG

        $body = $document->find('body')[0];

        if ($body) {
            // Parsing all images first to get @alt and @title values
            $this->process_images_in_text($document, $item->get_id());

            // Process attachments first coz they are images inside links
            $this->update_links_on_attachments($document, $item->get_id());
//            $this->remove_links_on_content_images($document);

            $text = $body->innerHtml(LIBXML_PARSEHUGE|LIBXML_NONET);
            $item->set_content($text);
        } else {
            $this->warning('Post parsing error for :url', [':url' => $item->get_uri()]);
        }
    }

//    protected function remove_links_on_content_images(Document $root)
//    {
//        $this->debug('Removing links on content images...');
//
//        $tag = CustomTag::PHOTO;
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

    protected function update_links_on_attachments(Document $document, $post_id)
    {
        $this->debug('Updating links on attachments...');

        $links = $document->find('a');

        foreach ($links as $link)
        {
            $href = $link->getAttribute('href');

            // Skip non-attachment links
            if (strpos($href, '/wp-content/') === FALSE)
                continue;

            $attach = $this->wp()->get_attachment_by_path($href);

            if (!$attach)
                throw new TaskException('Unknown attachment href :url', [':url' => $href]);

            $model = $this->process_attachment($attach, $post_id);

            $new_url = $model->get_original_url();

            $attach = $document->createElement(CustomTag::ATTACHMENT, NULL, [
                'id'    =>  $model->get_id(),
            ]);

            $link->replace($attach);

            $this->debug('Link for :old was changed to :new', [':old' => $href, ':new' => $new_url]);
        }
    }

    protected function process_thumbnails(Model_ContentPost $post, array $meta)
    {
        $wp = $this->wp();
        $wp_id = $post->get_wp_id();

        $wp_images_ids = [];

        if ($wp->post_has_post_format($wp_id, 'gallery')) {
            $this->debug('Getting thumbnail images from from _format_gallery_images');

            // Getting images from meta._format_gallery_images
            $wp_images_ids += (array) unserialize($meta['_format_gallery_images']);
        }

        if (!$wp_images_ids && isset($meta['_thumbnail_id'])) {
            $this->debug('Getting thumbnail image from from _thumbnail_id');

            // Getting thumbnail image from meta._thumbnail_id
            $wp_images_ids = [$meta['_thumbnail_id']];
        }

        if (!$wp_images_ids)
        {
            // Allow plain pages without thumbnails
            if ($post->is_article())
            {
                $this->warning('Article with uri [:uri] has no thumbnail', [
                    ':uri' => $post->get_uri(),
                ]);
            }

            return;
        }

        // Getting data for each thumbnail
        $images_wp_data = $wp->get_attachments(NULL, $wp_images_ids);

        if (!$images_wp_data)
        {
            $this->warning('Some images can not be found with WP ids :ids', [
                ':ids' => implode(', ', $wp_images_ids)
            ]);

            return;
        }

        $provider = $this->assets_provider_content_post_thumbnail();

        foreach ($images_wp_data as $image_data)
        {
            /** @var Model_ContentPostThumbnail $image_model */
            $image_model = $this->process_attachment($image_data, $post->get_id(), $provider);

            // Linking image to post
            $image_model->set_post($post)->save();
        }
    }

    protected function process_custom_tags(Model_ContentPost $item)
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
        $handlers->setDefault(function(ShortcodeInterface $s) use ($item) {
            $serializer = new TextSerializer();
            $name = $s->getName();

            if (!isset($this->unknown_bb_tags[$name])) {
                $this->unknown_bb_tags[$name] = $item->get_public_url();
                $this->debug('Unknown BB-code found [:name], keep it', [':name' => $name]);
            }

            return $serializer->serialize($s);
        });

        $parser = new RegexParser;
        $processor = new Processor($parser, $handlers);

        $content = $item->get_content();
        $content = $processor->process($content);
        $item->set_content($content);
    }

    public function thunder_handler_caption(ShortcodeInterface $s, Model_ContentPost $post)
    {
        $this->debug('[caption] found');

        $parameters = $s->getParameters();

        $image_wp_id = intval(str_replace('attachment_', '', $parameters['id']));
        unset($parameters['id']);

        // Find image in WP and process attachment
        $wp_image_data = $this->wp()->get_attachment_by_id($image_wp_id);

        if (!$wp_image_data)
        {
            $this->warning('No image found by wp_id :id', [':id' => $image_wp_id]);
            return NULL;
        }

        $image = $this->process_attachment($wp_image_data, $post->get_id());

        // Removing <img /> tag
        $caption_text = trim(strip_tags($s->getContent()));

        $parameters['title'] = $caption_text;

        return $this->custom_tag_instance()->generate_html(CustomTag::CAPTION, $image->get_id(), $parameters);
    }

    public function thunder_handler_gallery(\Thunder\Shortcode\Shortcode\ShortcodeInterface $s, Model_ContentPost $post)
    {
        $this->debug('[gallery] found');

        $wp_ids_string = $s->getParameter('ids');
        $type = $s->getParameter('type');
        $columns = (int) $s->getParameter('columns');

        if (strpos($type, 'slider') !== FALSE)
        {
            $type = 'slider';
        }

        // Removing spaces
        $wp_ids_string = str_replace(' ', '', $wp_ids_string);
        $wp_ids = explode(',', $wp_ids_string);

        $wp_images = $this->wp()->get_attachments(NULL, $wp_ids);

        if (!$wp_images)
        {
            $this->warning('No images found for gallery with WP IDs :ids', [':ids' => implode(', ', $wp_ids)]);
            return NULL;
        }

        $images_ids = [];

        // Process every image in set
        foreach ($wp_images as $wp_image_data)
        {
            $model = $this->process_attachment($wp_image_data, $post->get_id());
            $images_ids[] = $model->get_id();
        }

        $attributes = [
            'ids'       =>  implode(',', $images_ids),
            'type'      =>  $type,
            'columns'   =>  $columns,
        ];

        // No ID in this tag
        return $this->custom_tag_instance()->generate_html(CustomTag::GALLERY, NULL, $attributes);
    }

    public function thunder_handler_wonderplugin(\Thunder\Shortcode\Shortcode\ShortcodeInterface $s, Model_ContentPost $post)
    {
        $this->debug('[wonderplugin_slider] found');

        $id = $s->getParameter('id');

        $this->debug('Processing wonderplugin slider :id', [':id' => $id]);

        $config = $this->wp()->get_wonderplugin_slider_config($id);

        $images_ids = [];

        foreach ($config['slides'] as $slide) {
            $url = $slide['image'];
//            $url_path = parse_url($url, PHP_URL_PATH);

            // Searching for image
            $wp_image = $this->wp()->get_attachment_by_path($url);

            if (!$wp_image)
                throw new TaskException('Unknown image in wonderplugin: :url', [':url' => $url]);

            // Processing image

            /** @var Model_ContentImageElement $image */
            $image = $this->process_attachment($wp_image, $post->get_id());

            $caption = $slide['title'] ?: $slide['alt'];

            if ($caption) {
                $image->set_title($caption)->save();
            }

            $this->debug('Adding image :id to wonderplugin slider', [':id' => $image->get_id()]);

            $images_ids[] = $image->get_id();
        }

        $attributes = [
            'ids'   =>  implode(',', $images_ids),
            'type'  =>  'slider',
        ];

        // No ID in this tag
        return $this->custom_tag_instance()->generate_html(CustomTag::GALLERY, NULL, $attributes);
    }

    /**
     * @param Document  $root
     * @param int       $entity_item_id
     */
    protected function process_images_in_text(Document $root, $entity_item_id)
    {
        $images = $root->find('img');

        foreach ($images as $image) {
            $target_tag = NULL;

            try {
                // Creating new <photo /> tag as replacement for <img />
                $target_tag = $this->process_img_tag($image, $entity_item_id);
            }
            catch (Exception $e) {
                $this->warning(':message', [':message' => $e->getMessage()]);
            }

            // Exit if something went wrong
            if (!$target_tag)
                continue;

            $this->debug('Replacement tag is :tag', [':tag' => $target_tag->html()]);

            $parent = $image->parent();
            $parent_of_parent = $parent->parent();

            $parent_tag_name = $parent->getNode()->nodeName;

            $this->debug('Parent tag name is :name', [':name' => $parent_tag_name]);

            // Remove links to content images coz they would be added automatically
            if ($parent_tag_name == "a" && $parent->attr('href') == $image->attr('src')) {
                // Mark image as "zoomable"
                $target_tag->attr(CustomTag::PHOTO_ZOOMABLE, CustomTag::PHOTO_ZOOMABLE_ENABLED);
                $parent->replace($target_tag);
            } else {
                $image->replace($target_tag);
            }

            $this->debug('Parent html is :html', [':html' => $parent_of_parent->html()]);
        }
    }

    protected function process_img_tag(\DiDom\Element $node, $entity_item_id)
    {
        // Getting attributes
        $attributes = $node->attributes();

        // Original URL
        $original_url = trim($attributes['src']);

        $this->debug('Found inline image :tag', [':tag' => $node->html()]);

        $wp_image = $this->wp()->get_attachment_by_path($original_url);

        if (!$wp_image)
            throw new TaskException('Unknown image with src :value', [':value' => $original_url]);

        /** @var Model_ContentImageElement $image */
        $image = $this->process_attachment($wp_image, $entity_item_id);
        
        $alt = trim(Arr::get($attributes, 'alt'));
        $title = trim(Arr::get($attributes, 'title'));

        // Save alt and title in image model
        if ($alt) {
            $image->set_alt($alt);
        }

        if ($title) {
            $image->set_title($title);
        }

        $image->save();

        // Removing unnecessary attributes
        unset(
            $attributes['id'],
            $attributes['src'],
            $attributes['alt'],
            $attributes['title']
        );

        // Convert old full-size images to responsive images
        if (isset($attributes['width']) AND $attributes['width'] == 780) // TODO move 780 to config
        {
            unset(
                $attributes['width'],
                $attributes['height']
            );
        }

        $attributes['id'] = $image->get_id();

        return $node->getDocument()->createElement(CustomTag::PHOTO, NULL, $attributes);
    }

    protected function process_content_youtube_iframes(Model_ContentPost $item)
    {
        $text = $this->process_youtube_videos_in_text($item->get_content(), $item->get_id());

        $item->set_content($text);
    }

    protected function process_youtube_videos_in_text($text, $entity_item_id)
    {
        $this->debug('Processing Youtube iframe tags...');

        $pattern = '/<iframe[\s]+?src="(http[s]*:\/\/)?www\.youtube\.com\/embed\/([a-zA-Z0-9-_]{11})[^"]*"[^>]*?><\/iframe>/';

        // <iframe width="854" height="480" src="https://www.youtube.com/embed/xfTfeWTOxHk" frameborder="0" allowfullscreen></iframe>

        // Ищем упоминания о файлах
        preg_match_all($pattern, $text, $matches, PREG_SET_ORDER);

        // Выходим, если ничего не нашли
        if (!$matches)
            return $text;

        foreach ($matches as $match)
        {
            // Изначальный тег
            $original_tag = $match[0];
            $target_tag = NULL;

            try
            {
                // Создаём новый тег <youtube /> на замену <img />
                $target_tag = $this->process_youtube_iframe_tag($original_tag, $entity_item_id);
            }
            catch (Exception $e)
            {
                $this->warning(':message', [':message' => $e->getMessage()]);
            }

            // Если новый тег не сформирован, то просто переходим к следующему
            if (!$target_tag)
                continue;

            // Производим замену в тексте
            $text = str_replace($original_tag, $target_tag, $text);
        }

        return $text;
    }

    protected function process_youtube_iframe_tag($tag_string, $entity_item_id)
    {
        // Parsing
        $sx = simplexml_load_string($tag_string);

        if ($sx === FALSE)
            throw new TaskException('Youtube iframe tag parsing failed on :string', [':string' => $tag_string]);

        // Getting attributes
        $attributes = iterator_to_array($sx->attributes());

        // Original URL
        $original_url = trim($attributes['src']);

        $this->debug('Found youtube iframe :tag', [':tag' => $tag_string]);

        $service = $this->service_content_youtube();

        $youtube_id = $service->get_youtube_id_from_embed_url($original_url);

        if (!$youtube_id)
            throw new TaskException('Youtube iframe ID parsing failed on :string', [':string' => $tag_string]);

        $video = $service->find_record_by_youtube_id($youtube_id);

        $width = trim(Arr::get($attributes, 'width'));
        $height = trim(Arr::get($attributes, 'height'));

        // Save width and height in video model if they not set
        if ($width AND !$video->get_width())
        {
            $video->set_width($width);
        }

        if ($height AND !$video->get_height())
        {
            $video->set_height($height);
        }

        if ($video->changed())
        {
            $this->info('Youtube video :id processed', [':id' => $youtube_id]);
        }

        $video
            ->set_uploaded_by($this->current_user())
            ->set_entity($this->get_content_post_entity())
            ->set_entity_item_id($entity_item_id)
            ->save();

        $attributes = [
            'width'     =>  $width,
            'height'    =>  $height,
        ];

        return $this->custom_tag_instance()->generate_html(CustomTag::YOUTUBE, $video->get_id(), $attributes);
    }

    /**
     * Import all categories with WP IDs
     */
    protected function import_categories()
    {
        $categories = $this->wp()->get_categories_with_posts();

        $items_orm = $this->model_factory_content_post();

        $total = count($categories);
        $current = 1;

        foreach ($categories as $term) {
            $wp_id = $term['term_id'];
            $uri = $term['slug'];
            $label = $term['name'];

            $this->info('[:current/:total] Processing category :uri', [
                ':uri'      => $uri,
                ':current'  => $current,
                ':total'    => $total,
            ]);

            $categories_orm = $this->model_factory_content_category();
            $category = $categories_orm->find_by_wp_id($wp_id);

            $category
                ->set_wp_id($wp_id)
                ->set_uri($uri)
                ->set_label($label)
                ->save();

            // Find articles related to current category
            $posts_wp_ids = $this->wp()->get_posts_ids_linked_to_category($wp_id);

            // Check for any linked objects
            if ($posts_wp_ids) {
                // Get real article IDs
                $articles_ids = $items_orm->find_ids_by_wp_ids($posts_wp_ids);

                // Does articles exist?
                if ($articles_ids) {
                    // Link articles to category
                    $category->link_posts($articles_ids);
                }
            }

            $current++;
        }

        foreach ($categories as $term) {
            $wp_id = (int) $term['term_id'];
            $parent_wp_id = (int) $term['parent'];

            // Skip categories without parent
            if (!$parent_wp_id) {
                continue;
            }

            $categories_orm = $this->model_factory_content_category();
            $category = $categories_orm->find_by_wp_id($wp_id);
            $parent = $categories_orm->find_by_wp_id($parent_wp_id);

            $category
                ->set_parent($parent)
                ->save();
        }
    }

    protected function import_quotes()
    {
        $quotes_data = $this->wp()->get_quotes_collection_quotes();

        foreach ($quotes_data as $data) {
            $id = $data['id'];
            $text = $data['text'];
            $author = $data['author'];
            $created_at = new DateTime($data['created_at']);

            $model = $this->model_factory_quote($id);

            $model
                ->set_id($id)
                ->set_created_at($created_at)
                ->set_author($author)
                ->set_text($text)
                ->save();
        }
    }

    protected function import_comments()
    {
        $comments_data = $this->wp()->get_comments();

        $this->info('Processing :total comments ', [
            ':total'    => count($comments_data),
        ]);

        foreach ($comments_data as $data) {
            $wpID = $data['id'];
            $wpParentID = $data['parent_id'];
            $wpPostID = $data['post_id'];
            $created_at = new DateTime($data['created_at']);
            $authorName = $data['author_name'];
            $authorEmail = $data['author_email'];
            $authorIP = $data['author_ip_address'];
            $message = $data['message'];
            $wpApproved = $data['approved'];
            $userAgent = $data['user_agent'];

            /** @var Model_ContentPost $post */
            $post = $this->model_factory_content_post()->find_by_wp_id($wpPostID);
            $postID = $post->get_id();

            if (!$postID) {
                throw new TaskException('Unknown WP post ID [:post] used as reference in WP comment :comment', [
                    ':post' =>  $wpPostID,
                    ':comment'  =>  $wpID,
                ]);
            }

            /** @var Model_ContentComment|null $model */
            $model = $this->model_factory_content_comment()->find_by_wp_id($wpID);

            $parent = $wpParentID ? $this->model_factory_content_comment()->find_by_wp_id($wpParentID) : null;

            if ($wpParentID && !$parent->get_id()) {
                throw new TaskException('Unknown WP comment parent ID [:parent] used as reference in WP comment :comment', [
                    ':parent'   =>  $wpParentID,
                    ':comment'  =>  $wpID,
                ]);
            }

            // Skip existing comments coz they may be edited after import
            if ($model->get_id()) {
                continue;
            }

            $model
                ->set_parent($parent)
                ->set_entity($this->get_content_post_entity())
                ->set_entity_item_id($post->get_id())
                ->set_created_at($created_at)
                ->set_user_agent($userAgent)
                ->set_ip_address($authorIP);

            // Detecting user by name
            $authorUser = $this->model_factory_user()->search_by($authorName);

            if ($authorUser) {
                $model->set_author_user($authorUser);
            } else {
                $model->set_guest_author_name($authorName)->set_guest_author_email($authorEmail);
            }

            $isApproved = ($wpApproved == 1);
            $isSpam = (mb_strtolower($wpApproved) == 'spam');
            $isTrash = (mb_strtolower($wpApproved) == 'trash');

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

            $model->save();
        }
    }

    protected function import_users()
    {
        $this->info('Importing users...');

        $wpUsers = $this->wp()->get_users();

        foreach ($wpUsers as $wpUser) {
            $wpLogin = $wpUser['login'];
            $wpEmail = $wpUser['email'];

            $userModel = $this->model_factory_user()->search_by($wpEmail) ?: $this->model_factory_user()->search_by($wpLogin);

            if (!$userModel) {
                $userModel = $this->create_new_user($wpLogin, $wpEmail);
                $this->info('User :login successfully imported', [':login' => $userModel->get_username()]);
            } else {
                $this->info('User :login already exists', [':login' => $userModel->get_username()]);
            }
        }
    }

    protected function create_new_user($login, $email)
    {
        // TODO move this to system-wide service

        // Generate random password
        $password = md5(microtime());

        $roleOrm = $this->model_factory_role();

        $basicRoles = [
            $roleOrm->get_guest_role(),
            $roleOrm->get_login_role(),
        ];

        $model = $this->model_factory_user()
            ->set_username($login)
            ->set_password($password)
            ->set_email($email)
            ->create();

        foreach ($basicRoles as $role) {
            $model->add_role($role);
        }

        return $model;
    }

    /**
     * @return WP
     */
    protected function wp()
    {
        return WP::instance();
    }
}
