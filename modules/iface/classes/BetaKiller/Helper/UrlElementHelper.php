<?php
namespace BetaKiller\Helper;

use BetaKiller\IFace\Exception\UrlElementException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use Spotman\Api\ApiMethodResponse;

class UrlElementHelper
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \BetaKiller\Helper\StringPatternHelper
     */
    private $stringPatternHelper;

    /**
     * @var \BetaKiller\Helper\I18nHelper
     */
    private $i18n;

    /**
     * UrlElementHelper constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Helper\StringPatternHelper  $stringPatternHelper
     * @param \BetaKiller\Helper\I18nHelper           $i18n
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        StringPatternHelper $stringPatternHelper,
        I18nHelper $i18n
    ) {
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
        $this->i18n                = $i18n;
    }

    /**
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public static function getCurrentIFaceModel(UrlElementStack $stack): ?IFaceModelInterface
    {
        $element = $stack->getCurrent();

        if ($element && !$element instanceof IFaceModelInterface) {
            throw new UrlElementException('Current URL element :codename is not an IFace, :class given', [
                ':codename' => $element->getCodename(),
                ':class'    => \get_class($element),
            ]);
        }

        return $element;
    }

    /**
     * @param string                          $zone
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return bool
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public static function isCurrentZone(string $zone, UrlElementStack $stack): bool
    {
        $currentIFace = self::getCurrentIFaceModel($stack);
        $currentZone  = $currentIFace ? $currentIFace->getZoneName() : null;

        return $currentZone === $zone;
    }

    /**
     * @param \Spotman\Api\ApiMethodResponse   $response
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @return mixed
     */
    public static function processApiResponse(ApiMethodResponse $response, IFaceInterface $iface)
    {
        $iface->setLastModified($response->getLastModified());

        return $response->getData();
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function detectLayoutCodename(IFaceModelInterface $model): ?string
    {
        $current = $model;

        // Climb up the IFace tree for a layout codename
        do {
            $layoutCodename = $current->getLayoutCodename();
        } while (!$layoutCodename && $current = $this->tree->getParentIFaceModel($current));

        return $layoutCodename;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param int|null                                        $limit
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getLabel(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        ?int $limit = null
    ): string {
        $label = $model->getLabel();

        if (!$label) {
            throw new UrlElementException('Missing label for :codename UrlElement', [
                ':codename' => $model->getCodename(),
            ]);
        }

        if ($this->i18n->isI18nKey($label)) {
            $label = $this->i18n->translate($label);
        }

        return $this->stringPatternHelper->processPattern($label, $params, $limit);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getTitle(IFaceModelInterface $model, UrlContainerInterface $params): string
    {
        $title = $model->getTitle();

        if (!$title) {
            $title = $this->makeTitleFromLabels($model, $params);
        }

        if (!$title) {
            throw new UrlElementException('Can not compose title for IFace :codename', [
                ':codename' => $model->getCodename(),
            ]);
        }

        return $this->stringPatternHelper->processPattern($title, $params, SeoMetaInterface::TITLE_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getDescription(IFaceModelInterface $model, UrlContainerInterface $params): string
    {
        $description = $model->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        return $this->stringPatternHelper->processPattern($description, $params, SeoMetaInterface::DESCRIPTION_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function makeTitleFromLabels(IFaceModelInterface $model, UrlContainerInterface $params): string
    {
        $labels  = [];
        $current = $model;

        do {
            $labels[] = $this->getLabel($current, $params);
            $current  = $this->tree->getParent($current);
        } while ($current);

        return implode(' - ', array_filter($labels));
    }
}
