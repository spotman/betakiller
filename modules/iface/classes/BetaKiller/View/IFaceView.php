<?php
namespace BetaKiller\View;

use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Repository\IFaceLayoutRepository;
use BetaKiller\Repository\RepositoryException;
use BetaKiller\Url\IFaceModelInterface;

class IFaceView
{
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
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * @var \BetaKiller\Helper\I18nHelper
     */
    private $i18nHelper;

    /**
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\Helper\IFaceHelper               $ifaceHelper
     * @param \BetaKiller\View\HtmlHeadHelper              $headHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     * @param \BetaKiller\Helper\I18nHelper                $i18nHelper
     */
    public function __construct(
        IFaceLayoutRepository $layoutRepo,
        LayoutViewInterface $layoutView,
        IFaceHelper $ifaceHelper,
        HtmlHeadHelper $headHelper,
        ViewFactoryInterface $viewFactory,
        I18nHelper $i18nHelper
    ) {
        $this->layoutRepo  = $layoutRepo;
        $this->layoutView  = $layoutView;
        $this->headHelper  = $headHelper;
        $this->viewFactory = $viewFactory;
        $this->ifaceHelper = $ifaceHelper;
        $this->i18nHelper  = $i18nHelper;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function render(IFaceInterface $iface): string
    {
        $model = $iface->getModel();

        // Hack for dropping original iface data on processing exception error page
        $this->layoutView->clear();

        $viewPath  = $this->getViewPath($model);
        $ifaceView = $this->viewFactory->create($viewPath);

        // Getting IFace data
        $data = $iface->getData();

        // For changing wrapper from view via $_this->wrapper('html')
        $data['iface'] = [
            'codename' => $model->getCodename(),
            'label'    => $this->ifaceHelper->getLabel($model),
        ];

        foreach ($data as $key => $value) {
            $ifaceView->set($key, $value);
        }

        $this->headHelper
            ->setLang($this->i18nHelper->getLang())
            ->setContentType()
            ->setTitle($this->ifaceHelper->getTitle($model))
            ->setMetaDescription($this->ifaceHelper->getDescription($model))
            ->setCanonical($this->ifaceHelper->makeUrl($model, null, false));

        // Getting IFace layout
        $layoutCodename = $this->getLayoutCodename($model);

        $this->layoutView->setLayoutCodename($layoutCodename);

        return $this->layoutView->render($ifaceView);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function getLayoutCodename(IFaceModelInterface $model): string
    {
        $layoutCodename = $this->ifaceHelper->detectLayoutCodename($model);

        if (!$layoutCodename) {
            try {
                $defaultLayout = $this->layoutRepo->getDefault();
            } catch (RepositoryException $e) {
                throw IFaceException::wrap($e);
            }
            $layoutCodename = $defaultLayout->getCodename();
        }

        return $layoutCodename;
    }

    protected function getViewPath(IFaceModelInterface $model): string
    {
        return 'ifaces'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $model->getCodename());
    }
}
