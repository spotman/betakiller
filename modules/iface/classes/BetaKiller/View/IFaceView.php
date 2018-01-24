<?php
namespace BetaKiller\View;

use BetaKiller\Helper\IFaceHelper;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Repository\IFaceLayoutRepository;
use BetaKiller\Repository\RepositoryException;

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
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\Helper\IFaceHelper               $ifaceHelper
     * @param \BetaKiller\View\HtmlHeadHelper              $headHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     */
    public function __construct(
        IFaceLayoutRepository $layoutRepo,
        LayoutViewInterface $layoutView,
        IFaceHelper $ifaceHelper,
        HtmlHeadHelper $headHelper,
        ViewFactoryInterface $viewFactory
    ) {
        $this->layoutRepo  = $layoutRepo;
        $this->layoutView  = $layoutView;
        $this->headHelper  = $headHelper;
        $this->viewFactory = $viewFactory;
        $this->ifaceHelper = $ifaceHelper;
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
     * @param \BetaKiller\IFace\IFaceModelInterface $model
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

    protected function getViewPath(IFaceModelInterface $iface): string
    {
        return 'ifaces'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $iface->getCodename());
    }
}
