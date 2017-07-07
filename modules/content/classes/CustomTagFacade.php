<?php
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 25.07.16
 * Time: 18:49
 */

use BetaKiller\Content\CustomTag\AttachmentCustomTag;
use BetaKiller\Content\CustomTag\CaptionCustomTag;
use BetaKiller\Content\CustomTag\CustomTagException;
use BetaKiller\Content\CustomTag\GalleryCustomTag;
use BetaKiller\Content\CustomTag\PhotoCustomTag;
use BetaKiller\Content\CustomTag\YoutubeCustomTag;

class CustomTagFacade
{
    use \BetaKiller\Utils\Instance\SingletonTrait;

    /**
     * @Inject
     * @var \BetaKiller\IFace\WidgetFactory
     */
    private $widgetFactory;

    public function getAllowedTags()
    {
        return [
            CaptionCustomTag::TAG_NAME,
            GalleryCustomTag::TAG_NAME,
            PhotoCustomTag::TAG_NAME,
            YoutubeCustomTag::TAG_NAME,
            AttachmentCustomTag::TAG_NAME,
        ];
    }

    public function getSelfClosingTags(): array
    {
        return $this->getAllowedTags();
    }

    public function isSelfClosingTag(string $name): bool
    {
        return in_array($name, $this->getSelfClosingTags(), true);
    }

    public function generateHtml(string $name, int $id = null, array $attributes = null, string $content = null): string
    {
        $attributes = $attributes ?? [];

        if ($id) {
            $attributes += ['id' => $id];
        }

        // Generating HTML-tag
        $node = '<'.$name;

        $node .= \HTML::attributes(array_filter($attributes));

        if ($content && !$this->isSelfClosingTag($name)) {
            $node .= '>'.$content.'</'.$name.'>';
        } else {
            $node .= ' />';
        }

        return $node;
    }

    /**
     * @param string        $text
     * @param string[]|null $filterTags
     * @param callable      $callback
     *
     * @return string
     * @throws \BetaKiller\Content\CustomTag\CustomTagException
     */
    public function parse($text, ?array $filterTags, callable $callback): string
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

            if ($sx === false) {
                throw new CustomTagException('Custom tag parsing failed on :string', [':string' => $tag_string]);
            }

            // Получаем атрибуты
            $attributes = [];

            foreach ($sx->attributes() as $attr) /** @var $attr \SimpleXMLElement */ {
                $name  = $attr->getName();
                $value = (string)$attr;

                $attributes[$name] = $value;
            }

            $output = $callback($tag_name, $attributes);

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

    public function render(string $name, ?array $attributes = null): string
    {
        $widgetName = 'CustomTag_'.ucfirst($name);

        $widget = $this->widgetFactory->create($widgetName);

        if ($attributes) {
            $widget->setContext($attributes);
        }

        return $widget->render();
    }

    public function process(string $text): string
    {
        return $this->parse($text, null, function ($name, array $attributes) {
            return $this->render($name, $attributes);
        });
    }
}
