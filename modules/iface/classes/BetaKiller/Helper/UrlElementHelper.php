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
     */
    public function __construct(
        UrlElementTreeInterface $tree,
        StringPatternHelper $stringPatternHelper
    ) {
        $this->tree                = $tree;
        $this->stringPatternHelper = $stringPatternHelper;
    }

    public function setI18n(I18nHelper $i18n): void
    {
        $this->i18n = $i18n;
    }

    private function getI18n(): I18nHelper
    {
        if (!$this->i18n) {
            throw new \LogicException('UrlElementHelper is intended to use only for request-based processing');
        }

        return $this->i18n;
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
     * @param \BetaKiller\Url\IFaceModelInterface                  $model
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface|null $params
     * @param int|null                                             $limit
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getLabel(
        IFaceModelInterface $model,
        ?UrlContainerInterface $params = null,
        ?int $limit = null
    ): string {
        $label = $model->getLabel();

        if (!$label) {
            throw new UrlElementException('Missing label for :codename UrlElement', [
                ':codename' => $model->getCodename(),
            ]);
        }

        if ($this->getI18n()->isI18nKey($label)) {
            $label = __($label);
        }

        return $this->stringPatternHelper->processPattern($label, $limit, $params);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     */
    public function getTitle(IFaceModelInterface $model): string
    {
        $title = $model->getTitle();

        if (!$title) {
            $title = $this->makeTitleFromLabels($model);
        }

        if (!$title) {
            throw new UrlElementException('Can not compose title for IFace :codename', [
                ':codename' => $model->getCodename(),
            ]);
        }

        return $this->stringPatternHelper->processPattern($title, SeoMetaInterface::TITLE_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function getDescription(IFaceModelInterface $model): string
    {
        $description = $model->getDescription();

        if (!$description) {
            // Suppress errors for empty description in admin zone
            return '';
        }

        return $this->stringPatternHelper->processPattern($description, SeoMetaInterface::DESCRIPTION_LIMIT);
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @return string
     * @throws \BetaKiller\IFace\Exception\UrlElementException
     * @throws \BetaKiller\Url\UrlPrototypeException
     */
    public function makeTitleFromLabels(IFaceModelInterface $model): string
    {
        $labels  = [];
        $current = $model;

        do {
            $labels[] = $this->getLabel($current);
            $current  = $this->tree->getParent($current);
        } while ($current);

        return implode(' - ', array_filter($labels));
    }
}