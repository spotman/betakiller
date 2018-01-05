<?php
namespace BetaKiller\Widget;

use BetaKiller\Content\Shortcode\ShortcodeInterface;
use BetaKiller\IFace\Widget\AbstractBaseWidget;
use BetaKiller\IFace\Widget\WidgetException;

class ShortcodeWidget extends AbstractBaseWidget
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
     * @return array
     * @throws \BetaKiller\IFace\Widget\WidgetException
     */
    public function getData(): array
    {
        $shortcode = $this->getShortcode();

        // Set widget name equal to shortcode name for using it in a widget view
        $this->setName($shortcode->getTagName());

        return $shortcode->getWidgetData();
    }

    /**
     * @return \BetaKiller\Content\Shortcode\ShortcodeInterface
     * @throws \BetaKiller\IFace\Widget\WidgetException
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
     * @return string
     * @throws \BetaKiller\IFace\Widget\WidgetException
     */
    protected function getViewName(): string
    {
        $shortcode = $this->getShortcode();

        return 'Shortcode_'.$shortcode->getCodename();
    }
}
