<?php
namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
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
     * UrlElementHelper constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\Helper\StringPatternHelper  $stringPatternHelper
     */
    public function __construct(UrlElementTreeInterface $tree, StringPatternHelper $stringPatternHelper)
    {
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
    }

    /**
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public static function getCurrentIFaceModel(UrlElementStack $stack): ?IFaceModelInterface
    {
        $element = $stack->hasCurrent() ? $stack->getCurrent() : null;

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
        $layoutCodename = null;
        $current        = $model;

        // Climb up the tree for a layout codename
        do {
            if ($current instanceof IFaceModelInterface) {
                $layoutCodename = $current->getLayoutCodename();
            }
        } while (!$layoutCodename && $current = $this->tree->getParent($current));

        return $layoutCodename;
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\I18nHelper                   $i18n
     * @param int|null                                        $limit
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getLabel(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        I18nHelper $i18n,
        ?int $limit = null
    ): string {
        $label = $model->getLabel();

        if (!$label) {
            throw new UrlElementException('Missing label for :codename UrlElement', [
                ':codename' => $model->getCodename(),
            ]);
        }

        if (I18nFacade::isI18nKey($label)) {
            $label = $i18n->translateKeyName($label);
        }

        return $this->stringPatternHelper->processPattern($label, $params, $limit);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\I18nHelper                   $i18n
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getTitle(IFaceModelInterface $model, UrlContainerInterface $params, I18nHelper $i18n): string
    {
        $title = $model->getTitle();

        if (!$title) {
            $title = $this->makeTitleFromLabels($model, $params, $i18n);
        }

        if (!$title) {
            throw new UrlElementException('Can not compose title for IFace :codename', [
                ':codename' => $model->getCodename(),
            ]);
        }

        if (I18nFacade::isI18nKey($title)) {
            $title = $i18n->translateKeyName($title);
        }

        return $this->stringPatternHelper->processPattern($title, $params, SeoMetaInterface::TITLE_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\I18nHelper                   $i18n
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getDescription(IFaceModelInterface $model, UrlContainerInterface $params, I18nHelper $i18n): string
    {
        $description = $model->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        if (I18nFacade::isI18nKey($description)) {
            $description = $i18n->translateKeyName($description);
        }

        return $this->stringPatternHelper->processPattern($description, $params, SeoMetaInterface::DESCRIPTION_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Helper\I18nHelper                   $i18n
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function makeTitleFromLabels(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        I18nHelper $i18n
    ): string {
        $labels  = [];
        $current = $model;

        do {
            if ($current instanceof IFaceModelInterface) {
                $labels[] = $this->getLabel($current, $params, $i18n);
            }
            $current = $this->tree->getParent($current);
        } while ($current);

        return implode(' - ', array_filter($labels));
    }
}
