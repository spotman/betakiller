<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Url\Parameter\IdUrlParameter;
use Psr\Http\Message\ServerRequestInterface;

class IndexItemIFace extends AbstractEditor
{
    /**
     * Returns data for View
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Content\Shortcode\ShortcodeException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \BetaKiller\Url\Container\UrlContainerException
     */
    public function getData(ServerRequestInterface $request): array
    {
        $editor = $this->getShortcodeEditor();

        /** @var EntityModelInterface|null $entity */
        $entity = $this->urlContainer->getEntityByClassName(EntityModelInterface::class);

        /** @var \BetaKiller\Url\Parameter\IdUrlParameter|null $idParam */
        $idParam = $this->urlContainer->getParameterByClassName(IdUrlParameter::class);
        $id = $idParam ? $idParam->getID() : null;

        return [
            'action'   => 'index',
            'template' => $editor->getTemplateName(),
        ] + $editor->getIndexIFaceData($entity, $id);
    }
}
