<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Url\IdUrlParameter;

class IndexItem extends AbstractEditor
{
    /**
     * Returns data for View
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\UrlContainerException
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function getData(): array
    {
        $editor = $this->getShortcodeEditor();

        /** @var EntityModelInterface|null $entity */
        $entity = $this->urlContainer->getEntity(EntityModelInterface::URL_CONTAINER_KEY);

        /** @var \BetaKiller\Url\IdUrlParameter|null $idParam */
        $idParam = $this->urlContainer->getParameterByClassName(IdUrlParameter::class);
        $id = $idParam ? $idParam->getID() : null;

        return [
            'action'   => 'index',
            'template' => $editor->getTemplateName(),
        ] + $editor->getIndexIFaceData($entity, $id);
    }
}
