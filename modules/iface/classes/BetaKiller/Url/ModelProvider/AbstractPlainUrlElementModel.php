<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Url\UrlElementInterface;

abstract class AbstractPlainUrlElementModel implements UrlElementInterface
{
    public const OPTION_CODENAME           = 'name';
    public const OPTION_PARENT             = 'parent';
    public const OPTION_URI                = 'uri';
    public const OPTION_QUERY              = 'query';
    public const OPTION_HAS_TREE_BEHAVIOUR = 'hasTreeBehaviour';
    public const OPTION_HIDE_IN_SITEMAP    = 'hideInSiteMap';
    public const OPTION_IS_DEFAULT         = 'isDefault';
    public const OPTION_ZONE               = 'zone';
    public const OPTION_ACL_RULES          = 'aclRules';

    /**
     * @var string
     */
    private $codename;

    /**
     * @var string|null
     */
    private $parentCodename;

    /**
     * @var bool
     */
    private $isDefault = false;

    /**
     * @var string
     */
    private $uri;

    /**
     * @var string[]
     */
    private $query = [];

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
    private $zone = '';

    /**
     * @var string[]
     */
    private $aclRules = [];

    /**
     * @param array $data
     *
     * @return \BetaKiller\Url\UrlElementInterface|static
     */
    public static function factory(array $data): UrlElementInterface
    {
        /** @var static $instance */
        $instance = new static;
        $instance->fromArray($data);

        return $instance;
    }

    /**
     * @return string
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function getID(): string
    {
        throw new NotImplementedHttpException('Config-based URL element model have no ID');
    }

    /**
     * @return bool
     */
    public function hasID(): bool
    {
        // Config-based models can not obtain ID
        return false;
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
     * Returns iface url part
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * Returns key-value pairs for "query param name" => "Url parameter binding"
     * Example: [ "u" => "User.id", "r" => "Role.codename" ]
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->query;
    }

    /**
     * @param string $uri
     *
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function setUri(string $uri): void
    {
        throw new NotImplementedHttpException('Config-based URL element model can not change uri');
    }

    /**
     * Returns TRUE if UrlElement provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool
    {
        return $this->hasDynamicUrl;
    }

    /**
     * Returns TRUE if UrlElement provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool
    {
        return $this->hasTreeBehaviour;
    }

    /**
     * Returns codename of the parent UrlElement or NULL
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
     * Returns zone codename where this IFace is placed
     *
     * @return string
     */
    public function getZoneName(): string
    {
        return $this->zone;
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return [
            self::OPTION_CODENAME           => $this->getCodename(),
            self::OPTION_URI                => $this->getUri(),
            self::OPTION_QUERY              => $this->getQueryParams(),
            self::OPTION_HAS_TREE_BEHAVIOUR => $this->hasTreeBehaviour(),
            self::OPTION_PARENT             => $this->getParentCodename(),
            self::OPTION_HIDE_IN_SITEMAP    => $this->isHiddenInSiteMap(),
            self::OPTION_ACL_RULES          => $this->getAdditionalAclRules(),
            self::OPTION_IS_DEFAULT         => $this->isDefault(),
            self::OPTION_ZONE               => $this->getZoneName(),
        ];
    }

    public function fromArray(array $data): void
    {
        $this->uri      = $data[self::OPTION_URI];
        $this->codename = $data[self::OPTION_CODENAME];

        if (isset($data[self::OPTION_IS_DEFAULT])) {
            $this->isDefault = true;
        }

        if (\mb_strpos($this->uri, '{') === 0 && \mb_strpos($this->uri, '}', -1) !== false) {
            $this->hasDynamicUrl = true;
        }

        if (isset($data[self::OPTION_QUERY])) {
            $query  = [];
            $values = explode(',', (string)$data[self::OPTION_QUERY]);
            // Remove unnecessary spaces
            $values = array_filter(array_map('trim', $values));

            foreach ($values as $value) {
                [$queryName, $binding] = explode('=', $value, 2);

                $query[$queryName] = $binding;
            }

            $this->query = $query;
        }

        if (isset($data[self::OPTION_HAS_TREE_BEHAVIOUR])) {
            $this->hasTreeBehaviour = true;
        }

        if (isset($data[self::OPTION_PARENT])) {
            $this->parentCodename = (string)$data[self::OPTION_PARENT];
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
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        return $this->aclRules;
    }
}
