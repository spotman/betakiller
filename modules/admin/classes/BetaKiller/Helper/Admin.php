<?php
namespace BetaKiller\Helper;

trait Admin
{
    protected function service_admin_content_from_mime($mime)
    {
        return \Service_Admin_Content::service_instance_by_mime($mime);
    }

    protected function service_admin_image()
    {
        return \Service_Admin_Image::instance();
    }

    /**
     * @param null $id
     * @return \Model_AdminContentEntity
     */
    protected function model_factory_admin_content_entity($id = NULL)
    {
        return \ORM::factory('AdminContentEntity', $id);
    }

    /**
     * @param null $id
     * @return \Model_AdminImageFile
     */
    protected function model_factory_admin_image_file($id = NULL)
    {
        return \ORM::factory('AdminImageFile', $id);
    }

    /**
     * @param null $id
     * @return \Model_AdminAttachmentFile
     */
    protected function model_factory_admin_attachment_file($id = NULL)
    {
        return \ORM::factory('AdminAttachmentFile', $id);
    }

    protected function render_custom_html_tag($name, $id, array $attributes = [], $content = NULL)
    {
        $attributes = ['id' => $id] + $attributes;

        // Generating HTML-tag
        $node = '<'.$name;

        $node .= \HTML::attributes(array_filter($attributes));

        if ($content)
        {
            $node .= '>'.$content.'</'.$name.'>';
        }
        else
        {
            $node .= ' />';
        }

        return $node;
    }

    /**
    * @param string $text
    * @param string|string[] $filter_tags
    * @param callable $callback
    * @return string
    * @throws \Kohana_Exception
    */
    public static function parse_custom_html_tags($text, $filter_tags, callable $callback)
    {
        if (!is_array($filter_tags))
        {
            $filter_tags = [$filter_tags];
        }

        /** @url https://regex101.com/r/yF1bL4/3 */
        $pattern = '/<('.implode('|', $filter_tags).')[^\/>]*\/>/';

        if (!preg_match_all($pattern, $text, $matches, PREG_SET_ORDER))
            return $text;

        // Обходим каждый тег
        foreach ($matches as $match)
        {
            $tag_string = $match[0];
            $tag_name = $match[1];

            // Парсим тег
            $sx = simplexml_load_string($tag_string);

            if ($sx === FALSE)
                throw new \Kohana_Exception('Custom tag parsing failed on :string', [':string' => $tag_string]);

            // Получаем атрибуты
            $attributes = [];

            foreach ($sx->attributes() as $attr) /** @var $attr \SimpleXMLElement */
            {
                $name = $attr->getName();
                $value = (string) $attr;

                $attributes[$name] = $value;
            }

            $output = call_user_func($callback, $tag_name, $attributes);

            if ($output !== NULL)
            {
                $text = str_replace(
                    $tag_string,
                    $output,
                    $text
                );
            }
        }

        return $text;
    }

}
