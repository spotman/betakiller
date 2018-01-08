<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\IFaceZone;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentTrait;
use HTTP_Exception_501;

class IFaceModelProviderXmlConfigModel implements IFaceModelInterface
{
    use TreeModelSingleParentTrait;

    /**
     * @var IFaceModelProviderXmlConfig
     */
    private $provider;

    /**
     * @var string
     */
    private $codename;

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
    private $zone = IFaceZone::PUBLIC_ZONE;

    /**
     * @var string[]
     */
    private $aclRules = [];

    public static function factory($data, IFaceModelProviderXmlConfig $provider)
    {
        /** @var self $instance */
        $instance = new static;
        $instance->fromArray($data);
        $instance->setProvider($provider);

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
     * @throws HTTP_Exception_501
     */
    public function getID(): string
    {
        throw new HTTP_Exception_501('Admin IFace model have no ID');
    }

    /**
     * @return bool
     * @throws \HTTP_Exception_501
     */
    public function hasID(): bool
    {
        throw new HTTP_Exception_501('Admin IFace model have no ID');
    }

    /**
     * @return string
     * @throws \HTTP_Exception_501
     */
    public function getModelName(): string
    {
        throw new HTTP_Exception_501('Admin IFace model have no model name');
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
     * Return parent iface model or NULL
     *
     * @return IFaceModelInterface|null
     */
    public function getParent(): ?IFaceModelInterface
    {
        if (!$this->parentCodename) {
            return null;
        }

        return $this->getProvider()->getByCodename($this->parentCodename);
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
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getRoot()
    {
        return $this->getProvider()->getRoot();
    }

    /**
     * Returns list of child iface models
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren()
    {
        return $this->getProvider()->getChildren($this);
    }

    /**
     * @param string|null $columnName
     *
     * @return void
     * @throws \HTTP_Exception_501
     */
    public function getAllChildren(string $columnName = null)
    {
        throw new HTTP_Exception_501(':method not implemented yet', [':method' => __METHOD__]);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function setParent(TreeModelSingleParentInterface $parent = null)
    {
        throw new HTTP_Exception_501('Admin model can not change parent');
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws HTTP_Exception_501
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        throw new HTTP_Exception_501('Admin model can not change title');
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     * @throws HTTP_Exception_501
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        throw new HTTP_Exception_501('Admin model can not change description');
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
     * @return IFaceModelProviderXmlConfig
     */
    protected function getProvider(): IFaceModelProviderXmlConfig
    {
        if (!$this->provider) {
            throw new IFaceException('Provider is not defined, set it via setProvider() method');
        }

        return $this->provider;
    }

    public function setProvider(IFaceModelProviderXmlConfig $provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
