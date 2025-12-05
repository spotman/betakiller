<?php

declare(strict_types=1);

namespace BetaKiller\View;

use BetaKiller\Exception\LogicException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\Url\UrlElementWithLayoutInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class DefaultInertiaTemplateContextFactory implements InertiaTemplateContextFactoryInterface
{
    public function __construct(private TemplateContextFactory $factory, private UrlElementHelper $elementHelper)
    {
    }

    public function fromRequest(ServerRequestInterface $request): TemplateContext
    {
        $urlElement = ServerRequestHelper::getUrlElementStack($request)->getCurrent();

        if (!$urlElement instanceof UrlElementWithLayoutInterface) {
            throw new LogicException('Inertia must be pointed to :class', [
                ':class' => UrlElementWithLayoutInterface::class,
            ]);
        }

        $layout = $this->elementHelper->detectLayoutCodename($urlElement);

        if (!$layout) {
            throw new LogicException('Inertia view must have layout defined');
        }

        return $this->factory->fromRequest($request)
            // Render full-featured HTML page
            ->wrapInHtml5()
            // Use specific layout
            ->setLayout($layout);
    }
}
