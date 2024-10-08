<?php
namespace BetaKiller\View;

use BetaKiller\Assets\StaticAssetsFactory;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Repository\IFaceLayoutRepository;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use Meta;
use Psr\Http\Message\ServerRequestInterface;

class IFaceView
{
    public const REQUEST_KEY    = '__request__';
    public const ASSETS_KEY     = '__assets__';
    public const META_KEY       = '__meta__';
    public const I18N_KEY       = '__i18n__';
    public const IFACE_KEY      = '__iface__';
    public const IFACE_NAME_KEY = 'codename';
    public const IFACE_ZONE_KEY = 'zone';

    /**
     * @var \BetaKiller\Repository\IFaceLayoutRepository
     */
    private $layoutRepo;

    /**
     * @var \BetaKiller\View\LayoutViewInterface
     */
    private $layoutView;

    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    /**
     * @var \BetaKiller\Assets\StaticAssetsFactory
     */
    private $assetsFactory;

    /**
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\Helper\UrlElementHelper          $elementHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     * @param \BetaKiller\Assets\StaticAssetsFactory       $assetsFactory
     */
    public function __construct(
        IFaceLayoutRepository $layoutRepo,
        LayoutViewInterface $layoutView,
        UrlElementHelper $elementHelper,
        ViewFactoryInterface $viewFactory,
        StaticAssetsFactory $assetsFactory
    ) {
        $this->layoutRepo    = $layoutRepo;
        $this->layoutView    = $layoutView;
        $this->viewFactory   = $viewFactory;
        $this->elementHelper = $elementHelper;
        $this->assetsFactory = $assetsFactory;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface         $iface
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function render(IFaceInterface $iface, ServerRequestInterface $request): string
    {
        $codename = $iface->getCodename();
        $model    = $iface->getModel();

        $dataPack = RequestProfiler::begin($request, $codename.' IFace data');

        $viewPath  = $this->getViewPath($model);
        $ifaceView = $this->viewFactory->create($viewPath);

        // Getting IFace data
        foreach ($iface->getData($request) as $key => $value) {
            $ifaceView->set($key, $value);
        }

        RequestProfiler::end($dataPack);
        $prepareRenderPack = RequestProfiler::begin($request, $codename.' IFace prepare render');

        // Fetch current language (can be altered in IFace::getData())
        $i18n = ServerRequestHelper::getI18n($request);
        $lang = $i18n->getLang();

        // Send current request to widgets
        $ifaceView->set(self::REQUEST_KEY, $request);

        // Send i18n instance
        $ifaceView->set(self::I18N_KEY, $i18n);

        $ifaceView->set(self::IFACE_KEY, [
            self::IFACE_NAME_KEY => $model->getCodename(),
            self::IFACE_ZONE_KEY => $model->getZoneName(),
        ]);

        // Detect IFace layout
        $layoutCodename = $this->getLayoutCodename($model);

        // Create instance of renderer
        $meta         = new Meta;
        $assets       = $this->assetsFactory->create();
        $renderHelper = new HtmlRenderHelper($meta, $assets);

        // Errors may be fired early and UrlHelper may be not initialized
        if (ServerRequestHelper::hasUrlHelper($request)) {
            $urlHelper = ServerRequestHelper::getUrlHelper($request);
            $meta->setCanonical($urlHelper->makeUrl($model, null, false));

            $params = ServerRequestHelper::getUrlContainer($request);

            $titleDescPack = RequestProfiler::begin($request, $codename.' IFace title/description');

            $renderHelper
                ->setTitle($this->elementHelper->getTitle($model, $params, $lang))
                ->setMetaDescription($this->elementHelper->getDescription($model, $params, $lang));

            RequestProfiler::end($titleDescPack);
        }

        $renderHelper
            ->setLang($lang)
            ->setContentType()
            ->setLayoutCodename($layoutCodename);

        RequestProfiler::end($prepareRenderPack);
        $renderPack = RequestProfiler::begin($request, $codename.' IFace render');

        $result = $this->layoutView->render($ifaceView, $renderHelper);

        RequestProfiler::end($renderPack);

        return $result;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     */
    private function getLayoutCodename(IFaceModelInterface $model): string
    {
        $layoutCodename = $this->elementHelper->detectLayoutCodename($model);

        return $layoutCodename ?: $this->getDefaultLayoutCodename();
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

    protected function getViewPath(IFaceModelInterface $model): string
    {
        return 'ifaces'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $model->getCodename());
    }
}
