<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Url\UrlElementInterface;

abstract class AbstractOrmModelContainsUrlElement extends \ORM implements UrlElementInterface
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

        parent::configure();
    }

    protected function getUrlElement(): UrlElementInterface
    {
        return $this->element;
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
     * @return string
     */
    public function getLabel(): string
    {
        return $this->getUrlElement()->getLabel();
    }

    /**
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $this->getUrlElement()->setLabel($value);
    }

    /**
     * Returns TRUE if URL element is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return $this->getUrlElement()->isDefault();
    }

    /**
     * Returns TRUE if URL element provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool
    {
        return $this->getUrlElement()->hasDynamicUrl();
    }

    /**
     * Returns TRUE if URL element provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool
    {
        return $this->getUrlElement()->hasTreeBehaviour();
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
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        return $this->getUrlElement()->getAdditionalAclRules();
    }

    // TODO Chain create/update for nested UrlElement
}
