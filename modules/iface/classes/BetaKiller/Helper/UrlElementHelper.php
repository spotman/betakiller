<?php

namespace BetaKiller\Helper;

use BetaKiller\Model\LanguageInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInstanceInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementStack;
use BetaKiller\Url\UrlElementTreeInterface;
use BetaKiller\Url\UrlElementWithLabelInterface;
use BetaKiller\Url\UrlElementWithLayoutInterface;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\View\TemplateContext;

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
    public function __construct(
        UrlElementTreeInterface $tree,
        StringPatternHelper $stringPatternHelper
    ) {
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
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
     * @param \BetaKiller\Url\ZoneInterface   $zone
     * @param \BetaKiller\Url\UrlElementStack $stack
     *
     * @return bool
     * @throws \BetaKiller\Url\UrlElementException
     */
    public static function isCurrentZone(ZoneInterface $zone, UrlElementStack $stack): bool
    {
        $currentIFace = self::getCurrentIFaceModel($stack);
        $currentZone  = $currentIFace?->getZoneName();

        return $currentZone === $zone->getName();
    }

    public function getInstanceModel(UrlElementInstanceInterface $instance): UrlElementInterface
    {
        return $this->tree->getByCodename($instance::codename());
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

        return $this->stringPatternHelper->process($label, $params, $lang);
    }

    /**
     * @param \BetaKiller\Url\UrlElementInterface&\BetaKiller\Helper\SeoMetaInterface $model
     * @param \BetaKiller\Url\Container\UrlContainerInterface                         $params
     * @param \BetaKiller\Model\LanguageInterface                                     $lang
     *
     * @return string
     * @throws \BetaKiller\I18n\I18nException
     * @throws \BetaKiller\Url\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getTitle(
        UrlElementInterface&SeoMetaInterface $model,
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

        return $this->stringPatternHelper->process($title, $params, $lang, SeoMetaInterface::TITLE_LIMIT);
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
        SeoMetaInterface $model,
        UrlContainerInterface $params,
        LanguageInterface $lang
    ): string {
        $description = $model->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        return $this->stringPatternHelper->process($description, $params, $lang, SeoMetaInterface::DESCRIPTION_LIMIT);
    }

    public function expandIntoContext(UrlElementInterface $element, TemplateContext $context): void
    {
        $request = $context->getRequest();

        // Errors may be fired early and UrlHelper may be not initialized
        if (!ServerRequestHelper::hasUrlHelper($request)) {
            return;
        }

        $urlHelper = ServerRequestHelper::getUrlHelper($request);

        $context->setMetaCanonical($urlHelper->makeUrl($element, null, false));

        if ($element instanceof IFaceModelInterface) {
            $params = ServerRequestHelper::getUrlContainer($request);
            $i18n   = ServerRequestHelper::getI18n($request);

            // Fetch current language (can be altered in IFace::getData())
            $lang = $i18n->getLang();

            $context
                ->setTitle($this->getTitle($element, $params, $lang))
                ->setMetaDescription($this->getDescription($element, $params, $lang));
        }
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
        UrlElementInterface $model,
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
