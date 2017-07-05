<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 25.07.16
 * Time: 18:49
 */

use BetaKiller\IFace\Widget\AbstractBaseWidget;


class CustomTagFacade
{
    use \BetaKiller\Utils\Instance\Simple;

    const CAPTION    = 'caption';
    const GALLERY    = 'gallery';
    const PHOTO      = 'photo';
    const YOUTUBE    = 'youtube';
    const ATTACHMENT = 'attachment';

    const PHOTO_ZOOMABLE         = 'zoomable';
    const PHOTO_ZOOMABLE_ENABLED = 'true';

    public function getAllowedTags()
    {
        return [
            self::CAPTION,
            self::GALLERY,
            self::PHOTO,
            self::YOUTUBE,
            self::ATTACHMENT,
        ];
    }

    public function getSelfClosingTags()
    {
        return $this->getAllowedTags();
    }

    public function isSelfClosingTag($name)
    {
        return in_array($name, $this->getSelfClosingTags(), true);
    }

    public function generateHtml($name, $id = null, array $attributes = [], $content = null)
    {
        if ($id) {
            $attributes += ['id' => $id];
        }

        // Generating HTML-tag
        $node = '<'.$name;

        $node .= \HTML::attributes(array_filter($attributes));

        if (!$this->isSelfClosingTag($name) && $content) {
            $node .= '>'.$content.'</'.$name.'>';
        } else {
            $node .= ' />';
        }

        return $node;
    }

    /**
     * @param string          $text
     * @param string[]|null $filterTags
     * @param callable        $callback
     *
     * @return string
     * @throws \Kohana_Exception
     */
    public function parse($text, ?array $filterTags, callable $callback)
    {
        if ($filterTags === null) {
            $filterTags = $this->getAllowedTags();
        }

        /** @url https://regex101.com/r/yF1bL4/3 */
        $pattern = '/<('.implode('|', $filterTags).')[^\/>]*\/>/i';

        if (!preg_match_all($pattern, $text, $matches, PREG_SET_ORDER)) {
            return $text;
        }

        // Обходим каждый тег
        foreach ($matches as $match) {
            $tag_string = $match[0];
            $tag_name   = $match[1];

            // Парсим тег
            $sx = @simplexml_load_string($tag_string);

            if ($sx === false)
                throw new \Kohana_Exception('Custom tag parsing failed on :string', [':string' => $tag_string]);

            // Получаем атрибуты
            $attributes = [];

            foreach ($sx->attributes() as $attr) /** @var $attr \SimpleXMLElement */ {
                $name  = $attr->getName();
                $value = (string)$attr;

                $attributes[$name] = $value;
            }

            $output = call_user_func($callback, $tag_name, $attributes);

            if ($output !== null) {
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

        $widget = AbstractBaseWidget::factory($widget_name);

        $widget->setContext($attributes);

        return $widget->render();
    }

    public function process($text)
    {
        return $this->parse($text, null, function ($name, array $attributes) {
            return $this->render($name, $attributes);
        });
    }
}
