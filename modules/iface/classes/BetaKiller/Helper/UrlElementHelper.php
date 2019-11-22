<?php
namespace BetaKiller\Helper;

use BetaKiller\I18n\I18nFacade;
use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementWithLabelInterface;
use BetaKiller\Url\UrlElementWithLayoutInterface;

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
     * @var \BetaKiller\I18n\I18nFacade
     */
    private $i18n;

    /**
     * UrlElementHelper constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \BetaKiller\I18n\I18nFacade             $i18n
     * @param \BetaKiller\Helper\StringPatternHelper  $stringPatternHelper
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        I18nFacade $i18n,
        StringPatternHelper $stringPatternHelper
    ) {
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
        $this->i18n                = $i18n;
    }

    /**
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return \BetaKiller\Url\IFaceModelInterface|null
     * @throws \BetaKiller\Url\UrlElementException
     */
    public static function getCurrentIFaceModel(UrlElementStack $stack): ?IFaceModelInterface
    {
        $element = $stack->hasCurrent() ? $stack->getCurrent() : null;

        if ($element && !$element instanceof IFaceModelInterface) {
            throw new UrlElementException('Current URL element :codename is not an IFace, :class given', [
                ':codename' => $element->getCodename(),
                ':class'    => get_class($element),
            ]);
        }

        return $element;
    }

    /**
     * @param string                          $zone
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return bool
     * @throws \BetaKiller\Url\UrlElementException
     */
    public static function isCurrentZone(string $zone, UrlElementStack $stack): bool
    {
        $currentIFace = self::getCurrentIFaceModel($stack);
        $currentZone  = $currentIFace ? $currentIFace->getZoneName() : null;

        return $currentZone === $zone;
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface $model
     *
     * @return string|null
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function detectLayoutCodename(UrlElementInterface $model): ?string
    {
        $layoutCodename = null;
        $current        = $model;

        // Climb up the tree for a layout codename
        do {
            if ($current instanceof UrlElementWithLayoutInterface) {
                $layoutCodename = $current->getLayoutCodename();
            }
        } while (!$layoutCodename && $current = $this->tree->getParent($current));

        return $layoutCodename;
    }

    /**
     * @param \BetaKiller\Url\UrlElementWithLabelInterface    $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Model\LanguageInterface             $lang
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getLabel(
        UrlElementWithLabelInterface $model,
        UrlContainerInterface $params,
        LanguageInterface $lang
    ): string {
        $label = $model->getLabel();

        if (!$label) {
            throw new UrlElementException('Missing label for :codename UrlElement', [
                ':codename' => $model->getCodename(),
            ]);
        }

        if (I18nFacade::isI18nKey($label)) {
            $label = $this->i18n->translateKeyName($lang, $label);
        }

        return $this->stringPatternHelper->processPattern($label, $params);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Model\LanguageInterface             $lang
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getTitle(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        LanguageInterface $lang
    ): string {
        $title = $model->getTitle();

        if (!$title) {
            $title = $this->makeTitleFromLabels($model, $params, $lang);
        }

        if (!$title) {
            throw new UrlElementException('Can not compose title for IFace :codename', [
                ':codename' => $model->getCodename(),
            ]);
        }

        if (I18nFacade::isI18nKey($title)) {
            $title = $this->i18n->translateKeyName($lang, $title);
        }

        return $this->stringPatternHelper->processPattern($title, $params, SeoMetaInterface::TITLE_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Model\LanguageInterface             $lang
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getDescription(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        LanguageInterface $lang
    ): string {
        $description = $model->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        if (I18nFacade::isI18nKey($description)) {
            $description = $this->i18n->translateKeyName($lang, $description);
        }

        return $this->stringPatternHelper->processPattern($description, $params, SeoMetaInterface::DESCRIPTION_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface             $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Model\LanguageInterface             $lang
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    private function makeTitleFromLabels(
        IFaceModelInterface $model,
        UrlContainerInterface $params,
        LanguageInterface $lang
    ): string {
        $labels  = [];
        $current = $model;

        do {
            if ($current instanceof UrlElementWithLabelInterface) {
                $labels[] = $this->getLabel($current, $params, $lang);
            }
            $current = $this->tree->getParent($current);
        } while ($current);

        return implode(' - ', array_filter($labels));
    }
}
