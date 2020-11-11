<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Url\IFaceModelInterface;
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
    public const OPTION_ENV                = 'env';

    public const OPTION_EXTENDS = 'extends';

    /**
     * @var string
     */
    private string $codename;

    /**
     * @var string|null
     */
    private ?string $parentCodename = null;

    /**
     * @var bool
     */
    private bool $isDefault = false;

    /**
     * @var string
     */
    private string $uri;

    /**
     * @var string[]
     */
    private array $query = [];

    /**
     * @var bool
     */
    private bool $hasDynamicUrl = false;

    /**
     * @var bool
     */
    private bool $hasTreeBehaviour = false;

    /**
     * @var string
     */
    private string $zone = '';

    /**
     * @var string[]
     */
    private array $aclRules = [];

    /**
     * @var string[]
     */
    private array $environments = [];

    /**
     * @var bool|null
     */
    private ?bool $isHiddenInSitemap = null;

    /**
     * AbstractPlainUrlElementModel constructor.
     */
    final public function __construct()
    {
        // No op here
    }

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
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        return $this->aclRules;
    }

    /**
     * @inheritDoc
     */
    public function hasEnvironmentRestrictions(): bool
    {
        return count($this->environments) > 0;
    }

    /**
     * @return string[]
     */
    public function getAllowedEnvironments(): array
    {
        return $this->environments;
    }

    /**
     * @inheritDoc
     */
    public function isHiddenInSiteMap(): bool
    {
        // Hide all secondary UrlElements by default (can be overrided in ifaces.xml)
        return $this->isHiddenInSitemap ?? !$this instanceof IFaceModelInterface;
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
            self::OPTION_QUERY              => $this->exportQuery(),
            self::OPTION_HAS_TREE_BEHAVIOUR => $this->hasTreeBehaviour(),
            self::OPTION_PARENT             => $this->getParentCodename(),
            self::OPTION_HIDE_IN_SITEMAP    => $this->isHiddenInSiteMap(),
            self::OPTION_ACL_RULES          => $this->exportAclRules(),
            self::OPTION_IS_DEFAULT         => $this->isDefault(),
            self::OPTION_ZONE               => $this->getZoneName(),
            self::OPTION_ENV                => $this->exportEnvironments(),
        ];
    }

    public function fromArray(array $data): void
    {
        $this->uri      = $data[self::OPTION_URI];
        $this->codename = $data[self::OPTION_CODENAME];

        $this->isDefault = $this->validateBooleanOption($data, self::OPTION_IS_DEFAULT, false);

        if (\mb_strpos($this->uri, '{') === 0 && \mb_strpos($this->uri, '}', -1) !== false) {
            $this->hasDynamicUrl = true;
        } else {
            $this->hasDynamicUrl = false;
        }

        if (isset($data[self::OPTION_QUERY])) {
            $this->importQuery((string)$data[self::OPTION_QUERY]);
        }

        $this->hasTreeBehaviour = $this->validateBooleanOption($data, self::OPTION_HAS_TREE_BEHAVIOUR, false);

        if (isset($data[self::OPTION_HIDE_IN_SITEMAP])) {
            $this->isHiddenInSitemap = $this->validateBooleanOption($data, self::OPTION_HIDE_IN_SITEMAP, false);
        }

        if (isset($data[self::OPTION_PARENT])) {
            $this->parentCodename = (string)$data[self::OPTION_PARENT];
        }

        if (isset($data[self::OPTION_ZONE])) {
            $this->zone = mb_strtolower($data[self::OPTION_ZONE]);
        }

        if (isset($data[self::OPTION_ACL_RULES])) {
            $this->importAclRules((string)$data[self::OPTION_ACL_RULES]);
        }

        if (isset($data[self::OPTION_ENV])) {
            $this->importEnvironments((string)$data[self::OPTION_ENV]);
        }
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return $this->asArray();
    }

    /**
     * @return string
     */
    abstract public static function getXmlTagName(): string;

    protected function validateBooleanOption(array $data, string $key, bool $default): bool
    {
        if (!isset($data[$key])) {
            return $default;
        }

        // Return plain bool without conversion
        if (\is_bool($data[$key])) {
            return $data[$key];
        }

        return \mb_strtolower((string)$data[$key]) === 'true';
    }

    protected function importQuery(string $queryString): void
    {
        $query  = [];
        $values = explode(',', $queryString);
        // Remove unnecessary spaces
        $values = array_filter(array_map('trim', $values));

        foreach ($values as $value) {
            [$queryName, $binding] = explode('=', $value, 2);

            $query[$queryName] = $binding;
        }

        $this->query = $query;
    }

    protected function exportQuery(): string
    {
        $items = [];

        foreach ($this->query as $name => $binding) {
            $items[] = sprintf('%s=%s', $name, $binding);
        }

        return implode(',', $items);
    }

    protected function importAclRules(string $rulesString): void
    {
        $values         = explode(',', $rulesString);
        $this->aclRules = array_filter(array_map('trim', $values));
    }

    protected function exportAclRules(): string
    {
        return implode(',', $this->aclRules);
    }

    protected function importEnvironments(string $envString): void
    {
        $values             = explode(',', $envString);
        $this->environments = array_filter(array_map('trim', $values));
    }

    protected function exportEnvironments(): string
    {
        return implode(',', $this->environments);
    }
}
