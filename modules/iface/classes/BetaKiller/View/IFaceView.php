<?php
namespace BetaKiller\View;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Helper\UrlElementHelper;
use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Repository\IFaceLayoutRepository;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\IFaceModelInterface;
use Psr\Http\Message\ServerRequestInterface;

class IFaceView
{
    public const REQUEST_KEY = '__request__';

    /**
     * @var \BetaKiller\Repository\IFaceLayoutRepository
     */
    private $layoutRepo;

    /**
     * @var \BetaKiller\View\LayoutViewInterface
     */
    private $layoutView;

    /**
     * @var \BetaKiller\View\HtmlHeadHelper
     */
    private $headHelper;

    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \BetaKiller\Helper\UrlElementHelper
     */
    private $elementHelper;

    /**
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\View\HtmlHeadHelper              $headHelper
     * @param \BetaKiller\Helper\UrlElementHelper          $elementHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     */
    public function __construct(
        IFaceLayoutRepository $layoutRepo,
        LayoutViewInterface $layoutView,
        HtmlHeadHelper $headHelper,
        UrlElementHelper $elementHelper,
        ViewFactoryInterface $viewFactory
    ) {
        $this->layoutRepo    = $layoutRepo;
        $this->layoutView    = $layoutView;
        $this->headHelper    = $headHelper;
        $this->viewFactory   = $viewFactory;
        $this->elementHelper = $elementHelper;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface         $iface
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function render(IFaceInterface $iface, ServerRequestInterface $request): string
    {
        $urlHelper = ServerRequestHelper::getUrlHelper($request);
        $params    = ServerRequestHelper::getUrlContainer($request);
        $i18n      = ServerRequestHelper::getI18n($request);

        $model = $iface->getModel();

        // Hack for dropping original iface data on processing exception error page
        $this->layoutView->clear();

        $viewPath  = $this->getViewPath($model);
        $ifaceView = $this->viewFactory->create($viewPath);

        // Getting IFace data
        foreach ($iface->getData($request) as $key => $value) {
            $ifaceView->set($key, $value);
        }

        // Send current request to widgets
        $ifaceView->set(self::REQUEST_KEY, $request);

        $ifaceView->set('__iface__', [
            'codename' => $model->getCodename(),
            'label'    => $this->elementHelper->getLabel($model, $params, $i18n),
        ]);

        $this->headHelper
            ->setLang($i18n->getLang())
            ->setContentType()
            ->setTitle($this->elementHelper->getTitle($model, $params, $i18n))
            ->setMetaDescription($this->elementHelper->getDescription($model, $params, $i18n))
            ->setCanonical($urlHelper->makeUrl($model, null, false));

        // Getting IFace layout
        $layoutCodename = $this->getLayoutCodename($model, $this->elementHelper);

        $this->layoutView->setLayoutCodename($layoutCodename);

        return $this->layoutView->render($ifaceView);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @param \BetaKiller\Helper\UrlElementHelper $helper
     *
     * @return string
     */
    private function getLayoutCodename(IFaceModelInterface $model, UrlElementHelper $helper): string
    {
        $layoutCodename = $helper->detectLayoutCodename($model);

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
