<?php

namespace BetaKiller\IFace\Admin\Content\Shortcode\Editor;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\EntityModelInterface;
use BetaKiller\Url\Parameter\ID;
use Psr\Http\Message\ServerRequestInterface;

readonly class IndexItemIFace extends AbstractEditor
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
        $editor = $this->getShortcodeEditor($request);

        /** @var EntityModelInterface|null $entity */
        $entity = ServerRequestHelper::getEntity($request, EntityModelInterface::class);

        /** @var \BetaKiller\Url\Parameter\ID|null $idParam */
        $idParam = ServerRequestHelper::getParameter($request, ID::class);
        $id      = $idParam?->getValue();

        return [
                'action'   => 'index',
                'template' => $editor->getTemplateName(),
            ] + $editor->getIndexIFaceData($entity, $id);
    }
}
