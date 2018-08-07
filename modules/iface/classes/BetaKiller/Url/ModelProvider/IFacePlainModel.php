<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;

class IFacePlainModel extends AbstractPlainUrlElementModel implements IFaceModelInterface
{
    public const OPTION_LABEL              = 'label';
    public const OPTION_TITLE              = 'title';
    public const OPTION_LAYOUT             = 'layout';
    public const OPTION_IS_DEFAULT         = 'isDefault';
    public const OPTION_HAS_DYNAMIC_URL    = 'hasDynamicUrl';
    public const OPTION_HAS_TREE_BEHAVIOUR = 'hasTreeBehaviour';
    public const OPTION_ENTITY_NAME        = 'entity';
    public const OPTION_ENTITY_ACTION      = 'entityAction';
    public const OPTION_ZONE               = 'zone';
    public const OPTION_MENU               = 'menu';

    /**
     * @var bool
     */
    private $isDefault = false;

    /**
     * @var bool
     */
    private $hasDynamicUrl = false;

    /**
     * @var bool
     */
    private $hasTreeBehaviour = false;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $title;

    /**
     * Admin IFaces have "admin" layout by default
     *
     * @var string|null
     */
    private $layoutCodename;

    /**
     * @var bool
     */
    private $hideInSiteMap = false;

    /**
     * @var string|null
     */
    private $entityName;

    /**
     * @var string|null
     */
    private $entityAction;

    /**
     * @var string
     */
    private $zone;

    /**
     * @var string
     */
    private $menu;

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->isDefault;
    }

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label ?: '';
    }

    /**
     * @param string $value
     *
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setLabel(string $value): void
    {
        throw new NotImplementedHttpException('Config-based URL element model can not change label');
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        throw new NotImplementedHttpException('Admin model can not change title');
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        throw new NotImplementedHttpException('Admin model can not change description');
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        // Admin IFace does not need description
        return null;
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return array_merge(parent::asArray(), [
            self::OPTION_LABEL           => $this->getLabel(),
            self::OPTION_TITLE           => $this->getTitle(),
            self::OPTION_LAYOUT          => $this->getLayoutCodename(),
            self::OPTION_IS_DEFAULT      => $this->isDefault(),
            self::OPTION_HAS_DYNAMIC_URL => $this->hasDynamicUrl(),
            self::OPTION_ENTITY_NAME     => $this->getEntityModelName(),
            self::OPTION_ENTITY_ACTION   => $this->getEntityActionName(),
            self::OPTION_ZONE            => $this->getZoneName(),
            self::OPTION_MENU            => $this->getMenuName(),
        ]);
    }

    /**
     * Returns layout codename
     *
     * @return string
     */
    public function getLayoutCodename(): ?string
    {
        return $this->layoutCodename;
    }

    public function fromArray(array $data): void
    {
        $this->label = $data[self::OPTION_LABEL] ?? null;
        $this->title = $data[self::OPTION_TITLE] ?? null;

        if (isset($data[self::OPTION_IS_DEFAULT])) {
            $this->isDefault = true;
        }

        if (isset($data[self::OPTION_HAS_DYNAMIC_URL])) {
            $this->hasDynamicUrl = true;
        }

        if (isset($data[self::OPTION_HAS_TREE_BEHAVIOUR])) {
            $this->hasTreeBehaviour = true;
        }

        if (isset($data[self::OPTION_ENTITY_NAME])) {
            $this->entityName = (string)$data[self::OPTION_ENTITY_NAME];
        }

        if (isset($data[self::OPTION_ENTITY_ACTION])) {
            $this->entityAction = (string)$data[self::OPTION_ENTITY_ACTION];
        }

        if (isset($data[self::OPTION_HIDE_IN_SITEMAP])) {
            $this->hideInSiteMap = true;
        }

        if (isset($data[self::OPTION_LAYOUT])) {
            $this->layoutCodename = (string)$data[self::OPTION_LAYOUT];
        }

        if (isset($data[self::OPTION_ZONE])) {
            $this->zone = mb_strtolower($data[self::OPTION_ZONE]);
        }

        if (isset($data[self::OPTION_MENU])) {
            $this->menu = mb_strtolower($data[self::OPTION_MENU]);
        }

        parent::fromArray($data);
    }

    /**
     * @return bool
     */
    public function isHiddenInSiteMap(): bool
    {
        return $this->hideInSiteMap;
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool
    {
        return $this->hasDynamicUrl;
    }

    /**
     * Returns TRUE if iface provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool
    {
        return $this->hasTreeBehaviour;
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string
    {
        return $this->entityName;
    }

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string
     */
    public function getEntityActionName(): ?string
    {
        return $this->entityAction;
    }

    /**
     * Returns zone codename where this IFace is placed
     *
     * @return string
     */
    public function getZoneName(): string
    {
        return $this->zone;
    }

    /**
     * Returns menu codename to which URL is assigned
     *
     * @return null|string
     */
    public function getMenuName(): ?string
    {
        return $this->menu;
    }
}
