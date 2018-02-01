<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Model\SingleParentTreeModelInterface;

class IFaceModelProviderXmlConfigModel implements IFaceModelInterface
{
    /**
     * @var string
     */
    private $codename;

    /**
     * @var IFaceModelInterface
     */
    private $parent;

    /**
     * @var string|null
     */
    private $parentCodename;

    /**
     * @var string
     */
    private $uri;

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
    private $hasDynamicUrl = false;

    /**
     * @var bool
     */
    private $hasTreeBehaviour = false;

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
    private $zone = IFaceZone::ADMIN_ZONE;

    /**
     * @var string[]
     */
    private $aclRules = [];

    public static function factory(array $data)
    {
        /** @var self $instance */
        $instance = new self;
        $instance->fromArray($data);

        return $instance;
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        // Admin IFaces can not have "is_default" marker
        return false;
    }

    /**
     * @return string
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getID(): string
    {
        throw new NotImplementedHttpException('Admin IFace model have no ID');
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function hasID(): bool
    {
        throw new NotImplementedHttpException('Admin IFace model have no ID');
    }

    /**
     * @return string
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getModelName(): string
    {
        throw new NotImplementedHttpException('Admin IFace model have no model name');
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $uri
     *
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setUri(string $uri): void
    {
        throw new NotImplementedHttpException('Admin model can not change uri');
    }

    /**
     * Returns codename of parent IFace or NULL
     *
     * @return string|null
     */
    public function getParentCodename(): ?string
    {
        return $this->parentCodename;
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
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
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
     */
    public function setLabel(string $value): void
    {
        $this->label = $value;
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
        return [
            'codename'       => $this->getCodename(),
            'uri'            => $this->getUri(),
            'parentCodename' => $this->getParentCodename(),
            'label'          => $this->getLabel(),
            'title'          => $this->getTitle(),
            'hasDynamicUrl'  => $this->hasDynamicUrl(),
            'hideInSiteMap'  => $this->hideInSiteMap(),
            'layoutCodename' => $this->getLayoutCodename(),
            'entity'         => $this->getEntityModelName(),
            'entityAction'   => $this->getEntityActionName(),
            'zone'           => $this->getZoneName(),
            'aclRules'       => $this->getAdditionalAclRules(),
        ];
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
        $this->uri      = $data['uri'];
        $this->codename = $data['codename'];

        $this->label = $data['label'] ?? null;
        $this->title = $data['title'] ?? null;

        if (isset($data['parentCodename'])) {
            $this->parentCodename = (string)$data['parentCodename'];
        }

        if (isset($data['hasDynamicUrl'])) {
            $this->hasDynamicUrl = true;
        }

        if (isset($data['hasTreeBehaviour'])) {
            $this->hasTreeBehaviour = true;
        }

        if (isset($data['hideInSiteMap'])) {
            $this->hideInSiteMap = true;
        }

        if (isset($data['layoutCodename'])) {
            $this->layoutCodename = (string)$data['layoutCodename'];
        }

        if (isset($data['entity'])) {
            $this->entityName = (string)$data['entity'];
        }

        if (isset($data['entityAction'])) {
            $this->entityAction = (string)$data['entityAction'];
        }

        if (isset($data['zone'])) {
            $this->zone = mb_strtolower($data['zone']);
        }

        if (isset($data['aclRules'])) {
            $values         = explode(',', (string)$data['aclRules']);
            $this->aclRules = array_filter(array_map('trim', $values));
        }
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
     * @return bool
     */
    public function hideInSiteMap(): bool
    {
        return $this->hideInSiteMap;
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
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        return $this->aclRules;
    }

    /**
     * Return parent model or null
     *
     * @return SingleParentTreeModelInterface|mixed|static|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \BetaKiller\Model\SingleParentTreeModelInterface|null $parent
     */
    public function setParent(SingleParentTreeModelInterface $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return SingleParentTreeModelInterface[]
     */
    public function getAllParents(): array
    {
        $current = $this;
        $parents = [];

        do {
            $current = $current->getParent();

            if ($current) {
                $parents[] = $current;
            }
        } while ($current);

        return $parents;
    }
}
