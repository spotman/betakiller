<?php
declare(strict_types=1);

namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Url\UrlElementInterface;

abstract class AbstractPlainUrlElementModel implements UrlElementInterface
{
    public const OPTION_CODENAME        = 'name';
    public const OPTION_PARENT          = 'parent';
    public const OPTION_URI             = 'uri';
    public const OPTION_HIDE_IN_SITEMAP = 'hideInSiteMap';
    public const OPTION_ACL_RULES       = 'aclRules';

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
        throw new NotImplementedHttpException('Config-based URL element model can not change uri');
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
            self::OPTION_PARENT          => $this->getParentCodename(),
            self::OPTION_HIDE_IN_SITEMAP => $this->isHiddenInSiteMap(),
            self::OPTION_ACL_RULES       => $this->getAdditionalAclRules(),
        ];
    }

    public function fromArray(array $data): void
    {
        $this->uri      = $data[self::OPTION_URI];
        $this->codename = $data[self::OPTION_CODENAME];

        if (isset($data[self::OPTION_PARENT])) {
            $this->parentCodename = (string)$data[self::OPTION_PARENT];
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
