<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Url\EntityLinkedUrlElementInterface;

abstract class AbstractOrmModelContainsUrlElement extends \ORM implements EntityLinkedUrlElementInterface
{
    protected function configure(): void
    {
        $this->belongs_to([
            'element' => [
                'model'       => 'UrlElement',
                'foreign_key' => 'element_id',
            ],
        ]);

        $this->load_with([
            'element',
        ]);
    }

    protected function getUrlElement(): EntityLinkedUrlElementInterface
    {
        return $this->get('element');
    }

    /**
     * Returns codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->getUrlElement()->getCodename();
    }

    /**
     * Returns parent element codename (if parent exists)
     *
     * @return null|string
     */
    public function getParentCodename(): ?string
    {
        return $this->getUrlElement()->getParentCodename();
    }

    /**
     * Returns element`s url part
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->getUrlElement()->getUri();
    }

    /**
     * @param string $value
     */
    public function setUri(string $value): void
    {
        $this->getUrlElement()->setUri($value);
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->getUrlElement()->isDefault();
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool
    {
        return $this->getUrlElement()->hasDynamicUrl();
    }

    /**
     * Returns TRUE if iface has multi-level tree-behavior url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool
    {
        return $this->getUrlElement()->hasTreeBehaviour();
    }

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        return $this->getUrlElement()->getAdditionalAclRules();
    }

    /**
     * Returns zone codename where this URL element is placed
     *
     * @return string
     */
    public function getZoneName(): string
    {
        return $this->getUrlElement()->getZoneName();
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string
    {
        return $this->getUrlElement()->getEntityModelName();
    }

    /**
     * Returns entity [primary] action, applied by this URL element
     *
     * @return string
     */
    public function getEntityActionName(): ?string
    {
        return $this->getUrlElement()->getEntityActionName();
    }

    // TODO Chain create/update for nested UrlElement
}
