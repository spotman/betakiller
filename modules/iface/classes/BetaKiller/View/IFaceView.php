<?php
namespace BetaKiller\View;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Helper\StringPatternHelper;
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
     * @var \BetaKiller\Helper\StringPatternHelper
     */
    private $stringPatternHelper;

    /**
     * IFaceView constructor.
     *
     * @param \BetaKiller\Repository\IFaceLayoutRepository $layoutRepo
     * @param \BetaKiller\View\LayoutViewInterface         $layoutView
     * @param \BetaKiller\View\HtmlHeadHelper              $headHelper
     * @param \BetaKiller\View\ViewFactoryInterface        $viewFactory
     * @param \BetaKiller\Helper\StringPatternHelper       $stringPatternHelper
     */
    public function __construct(
        IFaceLayoutRepository $layoutRepo,
        LayoutViewInterface $layoutView,
        HtmlHeadHelper $headHelper,
        ViewFactoryInterface $viewFactory,
        StringPatternHelper $stringPatternHelper
    ) {
        $this->layoutRepo          = $layoutRepo;
        $this->layoutView          = $layoutView;
        $this->headHelper          = $headHelper;
        $this->viewFactory         = $viewFactory;
        $this->stringPatternHelper = $stringPatternHelper;
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
            ->setMetaDescription($this->getIFaceDescription($iface))
            ->setCanonical($iface->url(null, false));

        // Getting IFace layout
        $layoutCodename = $this->getLayoutCodename($iface);

        $this->layoutView->setLayoutCodename($layoutCodename);

        return $this->layoutView->render($ifaceView);
    }

    private function getIFaceTitle(IFaceInterface $iface): string
    {
        $title = $iface->getTitle();

        if (!$title) {
            $title = $this->generateTitleFromLabels($iface);
        }

        return $this->stringPatternHelper->processPattern($title, SeoMetaInterface::TITLE_LIMIT);
    }

    private function generateTitleFromLabels(IFaceInterface $iface): string
    {
        $labels = [];

        $current = $iface;
        do {
            $labels[] = $current->getLabel();
        } while ($current = $current->getParent());

        return implode(' - ', array_filter($labels));
    }

    private function getIFaceDescription(IFaceInterface $iface): string
    {
        $description = $iface->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        return $this->stringPatternHelper->processPattern($description, SeoMetaInterface::DESCRIPTION_LIMIT);
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
