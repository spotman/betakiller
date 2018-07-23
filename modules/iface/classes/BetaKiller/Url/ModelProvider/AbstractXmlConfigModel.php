<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\ZoneInterface;

abstract class AbstractXmlConfigModel implements UrlElementInterface
{
    public const OPTION_CODENAME           = 'name';
    public const OPTION_LABEL              = 'label';
    public const OPTION_PARENT             = 'parent';
    public const OPTION_URI                = 'uri';
    public const OPTION_IS_DEFAULT         = 'isDefault';
    public const OPTION_HIDE_IN_SITEMAP    = 'hideInSiteMap';
    public const OPTION_HAS_DYNAMIC_URL    = 'hasDynamicUrl';
    public const OPTION_HAS_TREE_BEHAVIOUR = 'hasTreeBehaviour';
    public const OPTION_ENTITY_NAME        = 'entity';
    public const OPTION_ENTITY_ACTION      = 'entityAction';
    public const OPTION_ZONE               = 'zone';
    public const OPTION_ACL_RULES          = 'aclRules';

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
    private $zone = ZoneInterface::ADMIN;

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
        return $this->isDefault;
    }

    /**
     * @return string
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getID(): string
    {
        throw new NotImplementedHttpException('XML-based URL element model have no ID');
    }

    /**
     * @return bool
     */
    public function hasID(): bool
    {
        // Models from XML config can not obtain ID
        return false;
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
        throw new NotImplementedHttpException('XML-based URL element model can not change uri');
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
        throw new NotImplementedHttpException('XML-based URL element model can not change label');
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
            self::OPTION_CODENAME        => $this->getCodename(),
            self::OPTION_URI             => $this->getUri(),
            self::OPTION_LABEL           => $this->getLabel(),
            self::OPTION_PARENT          => $this->getParentCodename(),
            self::OPTION_IS_DEFAULT      => $this->isDefault(),
            self::OPTION_HAS_DYNAMIC_URL => $this->hasDynamicUrl(),
            self::OPTION_HIDE_IN_SITEMAP => $this->hideInSiteMap(),
            self::OPTION_ENTITY_NAME     => $this->getEntityModelName(),
            self::OPTION_ENTITY_ACTION   => $this->getEntityActionName(),
            self::OPTION_ZONE            => $this->getZoneName(),
            self::OPTION_ACL_RULES       => $this->getAdditionalAclRules(),
        ];
    }

    public function fromArray(array $data): void
    {
        $this->uri      = $data[self::OPTION_URI];
        $this->codename = $data[self::OPTION_CODENAME];
        $this->label    = $data[self::OPTION_LABEL] ?? null;

        if (isset($data[self::OPTION_PARENT])) {
            $this->parentCodename = (string)$data[self::OPTION_PARENT];
        }

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

        if (isset($data[self::OPTION_ZONE])) {
            $this->zone = mb_strtolower($data[self::OPTION_ZONE]);
        }

        if (isset($data[self::OPTION_ACL_RULES])) {
            $values         = explode(',', (string)$data[self::OPTION_ACL_RULES]);
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
