<?php

namespace BetaKiller\View;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Repository\IFaceLayoutRepository;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use LogicException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

readonly class DefaultIFaceRenderer implements IFaceRendererInterface
{
    public const IFACE_KEY_ROOT = '__iface__';
    public const IFACE_KEY_NAME = 'codename';
    public const IFACE_KEY_ZONE = 'zone';

    /**
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\Helper\UrlElementHelper          $elementHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     * @param \BetaKiller\View\TemplateContextFactory      $contextFactory
     */
    public function __construct(
        private IFaceLayoutRepository $layoutRepo,
        private LayoutViewInterface $layoutView,
        private UrlElementHelper $elementHelper,
        private ViewFactoryInterface $viewFactory,
        private TemplateContextFactory $contextFactory
    ) {
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface         $iface
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function render(IFaceInterface $iface, ServerRequestInterface $request): ResponseInterface
    {
        $model = $this->elementHelper->getInstanceModel($iface);

        if (!$model instanceof IFaceModelInterface) {
            throw new LogicException();
        }

        $codename = $model->getCodename();

        $dataPack = RequestProfiler::begin($request, $codename.' IFace data');

        $viewPath  = $iface->getTemplatePath();
        $ifaceView = $this->viewFactory->create($viewPath);

        // Getting IFace data
        foreach ($iface->getData($request) as $key => $value) {
            $ifaceView->set($key, $value);
        }

        RequestProfiler::end($dataPack);
        $prepareRenderPack = RequestProfiler::begin($request, $codename.' IFace prepare render');

        $ifaceView->set(self::IFACE_KEY_ROOT, [
            self::IFACE_KEY_NAME => $model->getCodename(),
            self::IFACE_KEY_ZONE => $model->getZoneName(),
        ]);

        // Create instance of renderer
        $context = $this->contextFactory->fromRequest($request);

        $seoPack = RequestProfiler::begin($request, $codename.' IFace SEO data');
        $this->elementHelper->expandIntoContext($model, $context);
        RequestProfiler::end($seoPack);

        // Detect IFace layout
        $context->setLayout($this->getLayoutCodename($model));

        RequestProfiler::end($prepareRenderPack);
        $renderPack = RequestProfiler::begin($request, $codename.' IFace render');

        $html = $this->layoutView->render($ifaceView, $context);

        RequestProfiler::end($renderPack);

        return ResponseHelper::html($html);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     */
    private function getLayoutCodename(IFaceModelInterface $model): string
    {
        $layoutCodename = $this->elementHelper->detectLayoutCodename($model);

        return $layoutCodename ?? $this->getDefaultLayoutCodename();
    }

    private function getDefaultLayoutCodename(): string
    {
        try {
            $layout = $this->layoutRepo->getDefault();

            return $layout->getCodename();
        } catch (RepositoryException $e) {
            throw UrlElementException::wrap($e);
        }
    }
}
