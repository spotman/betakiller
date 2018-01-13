<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

class EditItem extends AbstractEditor
{
    /**
     * Returns data for View
     *
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getData(): array
    {
        $editor = $this->getShortcodeEditor();

        $shortcode = $this->shortcodeFacade->createFromEntity($this->shortcodeEntity);

        $queryPartsKeys = $this->urlContainer->getQueryPartsKeys();

        foreach ($queryPartsKeys as $key) {
            $value = $this->urlContainer->getQueryPart($key);
            $shortcode->setAttribute($key, $value);
        }

        return [
            'template' => $editor->getTemplateName(),
            'action'   => 'edit',
        ] + $editor->getEditIFaceData($shortcode);
    }
}
