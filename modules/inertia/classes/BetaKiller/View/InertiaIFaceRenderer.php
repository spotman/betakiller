<?php

declare(strict_types=1);

namespace BetaKiller\View;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\InertiaIFaceInterface;
use Cherif\InertiaPsr15\Middleware\InertiaMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class InertiaIFaceRenderer implements IFaceRendererInterface
{
    public function __construct(
        private DefaultIFaceRenderer $defaultRenderer,
        private InertiaDataProviderInterface $dataProvider
    ) {
    }

    public function render(IFaceInterface $iface, ServerRequestInterface $request): ResponseInterface
    {
        if (!$iface instanceof InertiaIFaceInterface) {
            return $this->defaultRenderer->render($iface, $request);
        }

        /** @var \Cherif\InertiaPsr15\Service\InertiaInterface $inertia */
        $inertia = $request->getAttribute(InertiaMiddleware::INERTIA_ATTRIBUTE);

        $this->dataProvider->injectSharedData($request, $inertia);

        // Replace namespace separator with URL path separator
        $codename = str_replace('\\', '/', $iface->codename());

        $data = $iface->getData($request);

        return $inertia->render($codename, $data);
    }
}
