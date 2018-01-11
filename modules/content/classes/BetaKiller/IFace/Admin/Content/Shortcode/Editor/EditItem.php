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

        return [
            'template' => $editor->getTemplateName(),
            'action'   => 'index',
        ] + $editor->getEditIFaceData();
    }
}
