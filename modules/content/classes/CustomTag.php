<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 25.07.16
 * Time: 18:49
 */

use\BetaKiller\IFace\Widget;


class CustomTag
{
    use \BetaKiller\Utils\Instance\Simple;

    const CAPTION       = 'caption';
    const GALLERY       = 'gallery';
    const IMAGE         = 'image';
    const YOUTUBE       = 'youtube';
    const ATTACHMENT    = 'attachment';

    public function get_allowed_tags()
    {
        return [
            self::CAPTION,
            self::GALLERY,
            self::IMAGE,
            self::YOUTUBE,
            self::ATTACHMENT,
        ];
    }

    public function get_self_closing_tags()
    {
        return $this->get_allowed_tags();
    }

    public function is_self_closing_tag($name)
    {
        return in_array($name, $this->get_self_closing_tags());
    }

    public function generate_html($name, $id = NULL, array $attributes = [], $content = NULL)
    {
        if ($id)
        {
            $attributes = $attributes + ['id' => $id];
        }

        // Generating HTML-tag
        $node = '<'.$name;

        $node .= \HTML::attributes(array_filter($attributes));

        if (!$this->is_self_closing_tag($name) && $content)
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
    public function parse($text, $filter_tags, callable $callback)
    {
        if ($filter_tags === TRUE)
        {
            $filter_tags = $this->get_allowed_tags();
        }

        if (!is_array($filter_tags)) {
            $filter_tags = [$filter_tags];
        }

        /** @url https://regex101.com/r/yF1bL4/3 */
        $pattern = '/<(' . implode('|', $filter_tags) . ')[^\/>]*\/>/i';

        if (!preg_match_all($pattern, $text, $matches, PREG_SET_ORDER))
        {
            return $text;
        }

        // Обходим каждый тег
        foreach ($matches as $match) {
            $tag_string = $match[0];
            $tag_name   = $match[1];

            // Парсим тег
            $sx = simplexml_load_string($tag_string);

            if ($sx === FALSE)
                throw new \Kohana_Exception('Custom tag parsing failed on :string', [':string' => $tag_string]);

            // Получаем атрибуты
            $attributes = [];

            foreach ($sx->attributes() as $attr) /** @var $attr \SimpleXMLElement */ {
                $name  = $attr->getName();
                $value = (string)$attr;

                $attributes[$name] = $value;
            }

            $output = call_user_func($callback, $tag_name, $attributes);

            if ($output !== NULL) {
                $text = str_replace(
                    $tag_string,
                    $output,
                    $text
                );
            }
        }

        return $text;
    }

    public function render($name, array $attributes = [])
    {
        $widget_name = 'CustomTag_'.ucfirst($name);

        $widget = Widget::factory($widget_name);

        $widget->setContext($attributes);

        return $widget->render();
    }

    public function process($text)
    {
        return $this->parse($text, TRUE, function($name, array $attributes) {
            return $this->render($name, $attributes);
        });
    }
}
