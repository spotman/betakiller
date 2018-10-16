<?php
namespace BetaKiller\Widget;

use BetaKiller\Content\Shortcode\ShortcodeInterface;
use Psr\Http\Message\ServerRequestInterface;

final class ShortcodeWidget extends AbstractPublicWidget
{
    /**
     * @var ShortcodeInterface
     */
    private $shortcode;

    public function setShortcode(ShortcodeInterface $shortcode): void
    {
        $this->shortcode = $shortcode;
    }

    /**
     * Returns data for View rendering
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @param array                                    $context
     *
     * @return array
     * @throws \BetaKiller\Widget\WidgetException
     */
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $shortcode = $this->getShortcode();

        // Set widget name equal to shortcode name for using it in a widget view
        $this->setName($shortcode->getTagName());

        return $shortcode->getWidgetData();
    }

    /**
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\Widget\WidgetException
     */
    private function getShortcode(): ShortcodeInterface
    {
        if (!$this->shortcode) {
            throw new WidgetException(':class needs instance of :needs', [
                ':class' => self::class,
                ':needs' => ShortcodeInterface::class,
            ]);
        }

        return $this->shortcode;
    }

    /**
     * Returns name of the view (underscores instead of directory separator)
     *
     * @return string
     * @throws \BetaKiller\Widget\WidgetException
     */
    public function getViewName(): string
    {
        $shortcode = $this->getShortcode();

        return 'Shortcode_'.$shortcode->getCodename();
    }
}
