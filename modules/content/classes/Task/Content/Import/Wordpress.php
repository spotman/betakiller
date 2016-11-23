<?php

use PHPHtmlParser\Dom;

class Task_Content_Import_Wordpress extends Minion_Task
{
    use \BetaKiller\Helper\Content;

    const ATTACH_PARSING_MODE_HTTP = 'http';
    const ATTACH_PARSING_MODE_LOCAL = 'local';

    const WP_OPTION_PARSING_MODE = 'betakiller_parsing_mode';
    const WP_OPTION_PARSING_PATH = 'betakiller_parsing_path';

    const CONTENT_ENTITY_ID = Model_ContentEntity::ARTICLES_ENTITY_ID;

    protected $attach_parsing_mode;
    protected $attach_parsing_path;

    protected function _execute(array $params)
    {
        $this->configure_dialog();

        // Attachments
        $this->import_attachments();

        // Posts
        $this->import_posts_and_pages();

        // Categories
        $this->import_categories();
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
                    throw new Task_Exception('Incorrect project URL');
            }
            elseif ($parsing_mode == self::ATTACH_PARSING_MODE_LOCAL)
            {
                $parsing_path = $this->read('Input absolute project path');

                $parsing_path = '/'.trim($parsing_path, '/');

                if (!is_dir($parsing_path) OR !file_exists($parsing_path))
                    throw new Task_Exception('Incorrect project path');
            }
        }

        $this->info('Parsing path is: '.$parsing_path);

        $this->attach_parsing_mode = $parsing_mode;
        $this->attach_parsing_path = $parsing_path;

        $wp->set_option(self::WP_OPTION_PARSING_MODE, $parsing_mode);
        $wp->set_option(self::WP_OPTION_PARSING_PATH, $parsing_path);
    }

    protected function import_attachments()
    {
        $wp = $this->wp();

        $attachments = $wp->get_attachments();

        $this->info('Processing attachments');

        foreach ($attachments as $attach)
        {
            try
            {
                $this->process_attachment($attach);
            }
            catch (Exception $e)
            {
                $this->warning('Error on processing attach with WP ID = :id'.PHP_EOL.':message', [
                    ':id'       =>  $attach['ID'],
                    ':message'  =>  $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * @return Model_ContentEntity
     */
    protected function get_content_entity()
    {
        static $content_entity;

        if (!$content_entity)
        {
            $content_entity = $this->model_factory_content_entity(self::CONTENT_ENTITY_ID);
        }

        return $content_entity;
    }

    protected function process_attachment(array $attach)
    {
        $wp_id = $attach['ID'];
        $mime = $attach['post_mime_type'];
        $url = $attach['guid'];

        $this->debug('Found attach with guid = :url', [':url' => $url]);

        /** @var Service_Content_WithAssets $service */
        $service = $this->service_content_from_mime($mime);

        /* $model = */ $this->store_attachment($service, $url, $wp_id);

        // TODO Save created_at + updated_at
//        $created_at = new DateTime($attach['post_date']);
//        $updated_at = new DateTime($attach['post_modified']);
    }

    /**
     * @param Service_Content_WithAssets $service
     * @param $url
     * @param $wp_id
     * @return Model_ContentElementInterface|Model_ORM_ContentElementTrait|Model_ORM_HasWordpressPathTrait|Model_ORM_ImportedFromWordpressTrait
     * @throws Task_Exception
     */
    protected function store_attachment(Service_Content_WithAssets $service, $url, $wp_id)
    {
        // Search for such file already exists
        $model = $service->find_file_by_wp_id($wp_id);

        if (!$model)
        {
            $url_path = parse_url($url, PHP_URL_PATH);
            $original_filename = basename($url);

            // Getting path for local file with attachment content
            $path = $this->get_attachment_path($url_path, $service->get_allowed_mime_types());

            if (!$path)
                throw new Task_Exception('Can not get path for guid = :url', [':url' => $url]);

            /** @var Model_ContentAttachmentElement $model */
            $model = $service->store_file($path, $original_filename, $this->get_content_entity());

            $model
                ->set_wp_id($wp_id) // Storing ID for text processing
                ->set_wp_path($url_path)
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
                throw new Task_Exception('Got :code status from :url', [
                    ':code' =>  $response->status(),
                    ':url'  => $url,
                ]);

            $real_mime = $response->headers('Content-Type');

            if (is_array($expected_mimes) AND !in_array($real_mime, $expected_mimes))
                throw new Task_Exception('Invalid mime-type: [:real], [:expected] expected', [
                    ':real'     =>  $real_mime,
                    ':expected' =>  implode('] or [', $expected_mimes),
                ]);

            $content = $response->body();

            if (!$content)
                throw new Task_Exception('Empty content for url [:url]', [':url' => $url,]);

            $path = tempnam(sys_get_temp_dir(), 'wp-attach-');

            file_put_contents($path, $content);

            return $path;
        }
        else if ($this->attach_parsing_mode == self::ATTACH_PARSING_MODE_LOCAL)
        {
            $path = $this->attach_parsing_path.'/'.trim($original_url_path, '/');

            if (!file_exists($path))
                throw new Task_Exception('No file exists at :path', [':path' => $path]);

            $this->debug('Getting attach at local path = :path', [':path' => $path]);

            $real_mime = File::mime($path);

            if (is_array($expected_mimes) AND !in_array($real_mime, $expected_mimes))
                throw new Task_Exception('Invalid mime-type: [:real], [:expected] expected', [
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

        $posts = $wp->get_posts_and_pages();

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

            /** @var Model_ContentItem $item_orm */
            $item_orm = ORM::factory('ContentItem');

            $item = $item_orm->find_by_wp_id($id);

            if ($type == $wp::POST_TYPE_PAGE)
            {
                $item->mark_as_page();
            }

            $item
                ->set_wp_id($id)
                ->set_uri($uri)
                ->set_label($name)
                ->set_content($content)
                ->set_title($title)
                ->set_description($description)
                ->set_created_at($created_at)
                ->set_updated_at($updated_at);

            $item->save();

            // Parsing all images first to get @alt and @title values
            $this->process_content_images($item);

            // Parsing custom tags next
            $this->process_custom_tags($item);

            // Processing YouTube <iframe> embeds
            $this->process_content_youtube_iframes($item);

            $this->post_process_article_text($item);

            // Saving model and getting its ID for further processing
            $item->save();

            // Link thumbnail images to post
            $this->process_thumbnails($item, $meta);

//            print_r($article->as_array());
//            print_r($meta);
//            die();

            $current++;
        }
    }

    protected function post_process_article_text(Model_ContentItem $item)
    {
        $text = $item->get_content();

        $text = $this->wp()->autop($text, false);

        $text = $this->update_links_on_content_elements($text);

        $item->set_content($text);
    }

    protected function update_links_on_content_elements($text)
    {
        $dom = new Dom;

        $dom
            ->addSelfClosingTag(CustomTag::instance()->get_self_closing_tags())
            ->load($text);

        $this->remove_links_on_content_images($dom->root);
        $this->update_links_on_attachments($dom->root);

        return $dom->root->outerHtml();
    }

    protected function remove_links_on_content_images(Dom\HtmlNode $root)
    {
        $tag = CustomTag::IMAGE;

        /** @var Dom\Collection $images */
        $images = $root->find('a > '.$tag);

        foreach ($images->getIterator() as $image) /** @var Dom\HtmlNode $image */
        {
            /** @var Dom\HtmlNode $link */
            $link = $image->getParent();

            /** @var Dom\HtmlNode $link_parent */
            $link_parent = $link->getParent();

            $image->setParent($link_parent);
            $link->delete();

            unset($link);
        }
    }

    protected function update_links_on_attachments(Dom\HtmlNode $root)
    {
        /** @var Dom\Collection $links */
        $links = $root->find('a');

        foreach ($links->getIterator() as $link)  /** @var Dom\HtmlNode $link */
        {
            $href = $link->getAttribute('href');

            // Skip non-attachment links
            if (strpos($href, '/wp-content/') === FALSE)
                continue;

            $path = parse_url($href, PHP_URL_PATH);

            $model = $this->model_factory_content_attachment_element()->find_by_wp_path($path);

            if (!$model)
                throw new Task_Exception('Unknown attachment href :url', [':url' => $path]);

            $new_url = $model->get_original_url();

            // Update link URL
            $link->setAttribute('href', $new_url);

            $this->debug('Link for :old was changed to :new', [':old' => $href, ':new' => $new_url]);
        }
    }

    protected function process_thumbnails(Model_ContentItem $item, array $meta)
    {
        $wp = $this->wp();
        $wp_id = $item->get_wp_id();

        $images_wp_ids = [];

        if ($wp->post_has_post_format($wp_id, 'gallery'))
        {
            $this->debug('Getting thumbnail images from from _format_gallery_images');

            // Getting images from meta._format_gallery_images
            $images_wp_ids += (array) unserialize($meta['_format_gallery_images']);
        }

        if (!$images_wp_ids && isset($meta['_thumbnail_id']))
        {
            $this->debug('Getting thumbnail image from from _thumbnail_id');

            // Getting thumbnail image from meta._thumbnail_id
            $images_wp_ids = [$meta['_thumbnail_id']];
        }

        if (!$images_wp_ids && $item->is_page())
        {
            // Allow plain pages without thumbnails
            return;
        }

        // Getting real IDs
        $images_ids = $this->model_factory_content_image_element()->find_ids_by_wp_ids($images_wp_ids);

        if ($images_ids)
        {
            $item->reset_thumbnails($images_ids);
        }
        else if ($images_wp_ids)
        {
//            print_r($meta);
            $this->warning('Some images can not be found with WP ids :ids', [
                ':ids' => implode(', ', $images_wp_ids)
            ]);
        }
        else
        {
            $this->warning('Article with uri [:uri] has no thumbnail', [
                ':uri' => $item->get_uri(),
            ]);
        }
    }

    protected function process_custom_tags(Model_ContentItem $item)
    {
        $handlers = new \Thunder\Shortcode\HandlerContainer\HandlerContainer;

        // [caption id="attachment_571" align="alignnone" width="780"]
        $handlers->add('caption', [$this, 'thunder_handler_caption']);

        // [gallery ids="253,261.260"]
        $handlers->add('gallery', [$this, 'thunder_handler_gallery']);

        // [wonderplugin_slider id="1"]
        $handlers->add('wonderplugin-slider', [$this, 'thunder_handler_wonderplugin']);

        $facade = \Thunder\Shortcode\ShortcodeFacade::create($handlers, new Thunder\Shortcode\Syntax\CommonSyntax());

        $content = $item->get_content();

        // Thunder does not understand lo-dash in tag name
        $content = str_replace('[wonderplugin_slider', '[wonderplugin-slider', $content);

        $content = $facade->process($content);

        $item->set_content($content);
    }

    public function thunder_handler_caption(\Thunder\Shortcode\Shortcode\ShortcodeInterface $s)
    {
        $this->debug('Caption found');

        $parameters = $s->getParameters();

        $image_wp_id = intval(str_replace('attachment_', '', $parameters['id']));
        unset($parameters['id']);

        $image = $this->model_factory_content_image_element()->find_by_wp_id($image_wp_id);

        if (!$image)
        {
            $this->warning('No image found by wp_id :id', [':id' => $image_wp_id]);
            return NULL;
        }

        // Removing <img /> tag
        $caption_text = trim(strip_tags($s->getContent()));

        $parameters['title'] = $caption_text;

        return $this->custom_tag_instance()->generate_html(CustomTag::CAPTION, $image->get_id(), $parameters);
    }

    public function thunder_handler_gallery(\Thunder\Shortcode\Shortcode\ShortcodeInterface $s)
    {
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

        $ids = $this->model_factory_content_image_element()->find_ids_by_wp_ids($wp_ids);

        if (!$ids)
        {
            $this->warning('No images found for gallery with WP IDs :ids', [':ids' => implode(', ', $wp_ids)]);
            return null;
        }

        $attributes = [
            'ids'       =>  implode(',', $ids),
            'type'      =>  $type,
            'columns'   =>  $columns,
        ];

        // No ID in this tag
        return $this->custom_tag_instance()->generate_html(CustomTag::GALLERY, NULL, $attributes);
    }

    public function thunder_handler_wonderplugin(\Thunder\Shortcode\Shortcode\ShortcodeInterface $s)
    {
        $id = $s->getParameter('id');

        $this->debug('Processing wonderplugin slider :id', [':id' => $id]);

        $config = $this->wp()->get_wonderplugin_slider_config($id);

        $images_ids = [];

        foreach ($config['slides'] as $slide)
        {
            $url = $slide['image'];
            $url_path = parse_url($url, PHP_URL_PATH);

            // Converting url to image ID
            $image = $this->model_factory_content_image_element()->find_by_wp_path($url_path);

            if (!$image)
                throw new Task_Exception('Unknown image in wonderplugin: :url', [':url' => $url]);

            $caption = $slide['alt'] ?: $slide['title'];

            $image->set_alt($caption)->set_title($caption)->save();

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

    protected function process_content_images(Model_ContentItem $item)
    {
        $text = $this->process_images_in_text($item->get_content(), $item->get_id());

        $item->set_content($text);
    }

    protected function process_images_in_text($text, $entity_item_id)
    {
        // Ищем упоминания о файлах
        preg_match_all('/<img[^>]+>/i', $text, $matches, PREG_SET_ORDER);

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
                // Создаём новый тег <image /> на замену <img />
                $target_tag = $this->process_img_tag($original_tag, $entity_item_id);
            }
            catch (Exception $e)
            {
                $this->warning(':message', [':message' => $e->getMessage()]);
            }

            // Если новый тег не сформирован, то просто переходим к следующему
            if (!$target_tag)
                continue;

            // Производим замену пути в тексте
            $text = str_replace($original_tag, $target_tag, $text);
        }

        return $text;
    }

    protected function process_img_tag($tag_string, $entity_item_id)
    {
        // Closing tag
        if (strpos($tag_string, '/>') === FALSE)
        {
            $tag_string = str_replace('>', '/>', $tag_string);
        }

        // Parsing
        $sx = simplexml_load_string($tag_string);

        if ($sx === FALSE)
            throw new Task_Exception('Image tag parsing failed on :string', [':string' => $tag_string]);

        // Getting attributes
        $attributes = iterator_to_array($sx->attributes());

        // Original URL
        $original_url = trim($attributes['src']);

        $this->debug('Found inline image :tag', [':tag' => $tag_string]);

        $wp_id = intval( @explode('wp-image-', $attributes['class'])[1] );

        if (!$wp_id)
            throw new Task_Exception('Can not determine WP ID from :str', [':str' => $tag_string]);

        /** @var Model_ContentImageElement $image */
        $image = $this->store_attachment($this->service_admin_image(), $original_url, $wp_id);
        
        $alt = trim(Arr::get($attributes, 'alt'));
        $title = trim(Arr::get($attributes, 'title'));

        // Save alt and title in image model
        if ($alt)
        {
            $image->set_alt($alt);
        }

        if ($title)
        {
            $image->set_title($title);
        }

        $image
            ->set_entity_item_id($entity_item_id)
            ->save();

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

        return $this->custom_tag_instance()->generate_html(CustomTag::IMAGE, $image->get_id(), $attributes);
    }

    protected function process_content_youtube_iframes(Model_ContentItem $item)
    {
        $text = $this->process_youtube_videos_in_text($item->get_content(), $item->get_id());

        $item->set_content($text);
    }

    protected function process_youtube_videos_in_text($text, $entity_item_id)
    {
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
            throw new Task_Exception('Youtube iframe tag parsing failed on :string', [':string' => $tag_string]);

        // Getting attributes
        $attributes = iterator_to_array($sx->attributes());

        // Original URL
        $original_url = trim($attributes['src']);

        $this->debug('Found youtube iframe :tag', [':tag' => $tag_string]);

        $service = $this->service_content_youtube();

        $youtube_id = $service->get_youtube_id_from_embed_url($original_url);

        if (!$youtube_id)
            throw new Task_Exception('Youtube iframe ID parsing failed on :string', [':string' => $tag_string]);

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

        $video
            ->set_uploaded_by($this->current_user())
            ->set_entity($this->get_content_entity())
            ->set_entity_item_id($entity_item_id)
            ->save();

        $this->info('Youtube video :id processed', [':id' => $youtube_id]);

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

        /** @var Model_ContentCategory $categories_orm */
        $categories_orm = ORM::factory('ContentCategory');

        /** @var Model_ContentItem $items_orm */
        $items_orm = ORM::factory('ContentItem');

        $total = count($categories);
        $current = 1;

        foreach ($categories as $term)
        {
            $wp_id = $term['term_id'];
            $uri = $term['slug'];
            $label = $term['name'];

            $this->info('[:current/:total] Processing category :uri', [
                ':uri'      => $uri,
                ':current'  => $current,
                ':total'    => $total,
            ]);

            $category = $categories_orm->find_by_wp_id($wp_id);

            $category
                ->set_wp_id($wp_id)
                ->set_uri($uri)
                ->set_label($label)
                ->save();

            // Find articles related to current category
            $posts_wp_ids = $this->wp()->get_posts_ids_linked_to_category($wp_id);

            // Check for any linked objects
            if ($posts_wp_ids)
            {
                // Get real article IDs
                $articles_ids = $items_orm->find_ids_by_wp_ids($posts_wp_ids);

                // Does articles exist?
                if ($articles_ids)
                {
                    // Link articles to category
                    $category->link_posts($articles_ids);
                }
            }

            $current++;
        }

        foreach ($categories as $term)
        {
            $wp_id = $term['term_id'];
            $parent_wp_id = $term['parent'];

            $category = $categories_orm->find_by_wp_id($wp_id);
            $parent = $categories_orm->find_by_wp_id($parent_wp_id);

            $category
                ->set_parent($parent)
                ->save();
        }
    }

    /**
     * @return WP
     */
    protected function wp()
    {
        return WP::instance();
    }
}
