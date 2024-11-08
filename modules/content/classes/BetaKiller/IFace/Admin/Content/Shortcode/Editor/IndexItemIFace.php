<?php
namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Url\Parameter\ID;
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

        /** @var \BetaKiller\Url\Parameter\ID|null $idParam */
        $idParam = $this->urlContainer->getParameterByClassName(ID::class);
        $id = $idParam ? $idParam->getValue() : null;

        return [
            'action'   => 'index',
            'template' => $editor->getTemplateName(),
        ] + $editor->getIndexIFaceData($entity, $id);
    }
}
