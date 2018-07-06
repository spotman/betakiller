<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Model\UrlElementZone;
use BetaKiller\Url\UrlElementInterface;

abstract class AbstractXmlConfigModel implements UrlElementInterface
{
    /**
     * @var string
     */
    private $codename;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string|null
     */
    private $parentCodename;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var bool
     */
    private $hasDynamicUrl = false;

    /**
     * @var bool
     */
    private $hasTreeBehaviour = false;

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
    private $zone = UrlElementZone::ADMIN_ZONE;

    /**
     * @var string[]
     */
    private $aclRules = [];

    public static function factory(array $data)
    {
        /** @var static $instance */
        $instance = new static;
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
        throw new NotImplementedHttpException('Admin URL element model have no ID');
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function hasID(): bool
    {
        throw new NotImplementedHttpException('Admin URL element model have no ID');
    }

    /**
     * @return string
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getModelName(): string
    {
        throw new NotImplementedHttpException('Admin URL element model have no model name');
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
        throw new NotImplementedHttpException('Admin URL element model can not change uri');
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
        throw new NotImplementedHttpException('Admin URL element model can not change label');
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
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
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
            'label'          => $this->getLabel(),
            'parentCodename' => $this->getParentCodename(),
            'hasDynamicUrl'  => $this->hasDynamicUrl(),
            'hideInSiteMap'  => $this->hideInSiteMap(),
            'entity'         => $this->getEntityModelName(),
            'entityAction'   => $this->getEntityActionName(),
            'zone'           => $this->getZoneName(),
            'aclRules'       => $this->getAdditionalAclRules(),
        ];
    }

    public function fromArray(array $data): void
    {
        $this->uri      = $data['uri'];
        $this->codename = $data['codename'];
        $this->label    = $data['label'] ?? null;

        if (isset($data['parentCodename'])) {
            $this->parentCodename = (string)$data['parentCodename'];
        }

        if (isset($data['hasDynamicUrl'])) {
            $this->hasDynamicUrl = true;
        }

        if (isset($data['hasTreeBehaviour'])) {
            $this->hasTreeBehaviour = true;
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
}
