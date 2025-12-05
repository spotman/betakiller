<?php

declare(strict_types=1);

namespace BetaKiller\View;

use BetaKiller\Dev\RequestProfiler;
use Cherif\InertiaPsr15\Model\Page;
use Cherif\InertiaPsr15\Service\RootViewProviderInterface;
use Psr\Http\Message\ServerRequestInterface;

final readonly class InertiaRootViewProvider implements RootViewProviderInterface
{
    public function __construct(
        private ViewFactoryInterface $viewFactory,
        private LayoutViewInterface $layoutView,
        private InertiaTemplateContextFactoryInterface $contextFactory,
        private ServerRequestInterface $request
    ) {
    }

    public function __invoke(Page $page): string
    {
        $rvp = RequestProfiler::begin($this->request, 'Inertia.js root view');

        $view = $this->viewFactory->create('@templates/inertia');

        $view->set('page', $page);

        $context = $this->contextFactory->fromRequest($this->request);

        $html = $this->layoutView->render($view, $context);

        RequestProfiler::end($rvp);

        return $html;
    }
}
