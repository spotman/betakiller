<?php

declare(strict_types=1);

namespace BetaKiller\View;

use BetaKiller\Assets\StaticAssetsFactory;
use BetaKiller\Helper\ServerRequestHelper;
use Meta;
use Psr\Http\Message\ServerRequestInterface;

final readonly class TemplateContextFactory
{
    public function __construct(private StaticAssetsFactory $assetsFactory)
    {
    }

    public function fromRequest(ServerRequestInterface $request): TemplateContext
    {
        // Fetch current language (can be altered in IFace::getData())
        $i18n = ServerRequestHelper::getI18n($request);
        $lang = $i18n->getLang();

        $meta   = new Meta();
        $assets = $this->assetsFactory->create();

        return new TemplateContext($request, $meta, $assets, $lang);
    }
}
