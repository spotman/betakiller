<?php
namespace BetaKiller\View;

use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Repository\IFaceLayoutRepository;

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
     * @var \BetaKiller\View\ViewFactoryInterface
     */
    private $viewFactory;

    /**
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\View\HtmlHeadHelper              $headHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     */
    public function __construct(
        IFaceLayoutRepository $layoutRepo,
        LayoutViewInterface $layoutView,
        HtmlHeadHelper $headHelper,
        ViewFactoryInterface $viewFactory
    ) {
        $this->layoutRepo  = $layoutRepo;
        $this->layoutView  = $layoutView;
        $this->headHelper  = $headHelper;
        $this->viewFactory = $viewFactory;
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return string
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function render(IFaceInterface $iface): string
    {
        // Hack for dropping original iface data on processing exception error page
        $this->layoutView->clear();

        $viewPath  = $this->getViewPath($iface);
        $ifaceView = $this->viewFactory->create($viewPath);

        // Getting IFace data
        $data = $iface->getData();

        // For changing wrapper from view via $_this->wrapper('html')
        $data['iface'] = [
            'label'    => $iface->getLabel(),
            'codename' => $iface->getCodename(),
        ];

        foreach ($data as $key => $value) {
            $ifaceView->set($key, $value);
        }

        $this->headHelper
            ->setContentType()
            ->setTitle($this->getIFaceTitle($iface))
            ->setMetaDescription($iface->getDescription() ?: '') // Suppress errors for empty description in admin zone
            ->setCanonical($iface->url(null, false));

        // Getting IFace layout
        $layoutCodename = $this->getLayoutCodename($iface);

        $this->layoutView->setLayoutCodename($layoutCodename);

        return $this->layoutView->render($ifaceView);
    }

    private function getIFaceTitle(IFaceInterface $iface): string
    {
        $title = $iface->getTitle();

        if ($title) {
            return $title;
        }

        $labels = [];

        $current = $iface;
        do {
            $labels[] = $current->getLabel();
        } while ($current = $current->getParent());

        return implode(' - ', array_filter($labels));
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return string
     * @throws \BetaKiller\Repository\RepositoryException
     */
    private function getLayoutCodename(IFaceInterface $iface): string
    {
        $layoutCodename = $iface->getLayoutCodename();

        if (!$layoutCodename) {
            $defaultLayout  = $this->layoutRepo->getDefault();
            $layoutCodename = $defaultLayout->getCodename();
        }

        return $layoutCodename;
    }

    protected function getViewPath(IFaceInterface $iface): string
    {
        return 'ifaces'.DIRECTORY_SEPARATOR.str_replace('_', DIRECTORY_SEPARATOR, $iface->getCodename());
    }
}
