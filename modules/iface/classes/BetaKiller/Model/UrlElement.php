<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\Url\EntityLinkedUrlElementInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementException;
use BetaKiller\Url\UrlElementInterface;

/**
 * Class UrlElement
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller\Url
 */
class UrlElement extends AbstractOrmBasedSingleParentTreeModel implements EntityLinkedUrlElementInterface
{
    protected function configure(): void
    {
        $this->_table_name = 'url_elements';

        $this->belongs_to([
            'type'   => [
                'model'       => 'UrlElementType',
                'foreign_key' => 'type_id',
            ],
            'zone'   => [
                'model'       => 'UrlElementZone',
                'foreign_key' => 'zone_id',
            ],
            'entity' => [
                'model'       => 'Entity',
                'foreign_key' => 'entity_id',
            ],
            'action' => [
                'model'       => 'EntityAction',
                'foreign_key' => 'entity_action_id',
            ],
        ]);

        $this->has_one([
            'iface' => [
                'model'       => 'IFace',
                'foreign_key' => 'element_id',
            ],
        ]);

        $this->has_many([
            'acl_rules' => [
                'model'       => 'UrlElementAclRule',
                'foreign_key' => 'element_id',
            ],
        ]);

        $this->load_with([
            'type',
            'entity',
            'action',
            'zone',
        ]);

        parent::configure();
    }

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get('codename');
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->get('uri');
    }

    /**
     * @param string $value
     */
    public function setUri(string $value): void
    {
        $this->set('uri', $value);
    }

    /**
     * Returns key-value pairs for "query param name" => "Url parameter binding"
     * Example: [ "u" => "User.id", "r" => "Role.codename" ]
     *
     * @return array
     */
    public function getQueryParams(): array
    {
        throw new NotImplementedHttpException();
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool)$this->get('is_default');
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool
    {
        return (bool)$this->get('is_dynamic');
    }

    /**
     * Returns TRUE if iface has multi-level tree-behavior url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool
    {
        return (bool)$this->get('is_tree');
    }

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->get('label');
    }

    /**
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $this->set('label', $value);
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function isHiddenInSiteMap(): bool
    {
        throw new NotImplementedHttpException('Call ::isHiddenInSiteMap() on dedicated URL elements` classes');
    }

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        /** @var \BetaKiller\Model\UrlElementAclRule[] $rules */
        $rules  = $this->getAclRulesRelation()->get_all();
        $output = [];

        foreach ($rules as $rule) {
            $output[] = $rule->getCombinedRule();
        }

        return $output;
    }

    public function isTypeIFace(): bool
    {
        return $this->getTypeRelation()->isIFace();
    }

    /**
     * @return IFaceModelInterface
     * @throws \BetaKiller\Url\UrlElementException
     */
    public function getIFaceModel(): IFaceModelInterface
    {
        if (!$this->isTypeIFace()) {
            throw new UrlElementException('Can not get IFace model from UrlElement :codename instance of :class', [
                ':codename' => $this->getCodename(),
                ':class'    => \get_class($this),
            ]);
        }

        return $this->get('iface');
    }

    public function getDedicatedObject(): UrlElementInterface
    {
        switch (true) {
            case $this->isTypeIFace():
                return $this->getIFaceModel();

            default:
                throw new UrlElementException('Unknown type of URL element for codename :codename', [
                    ':codename' => $this->getCodename(),
                ]);
        }
    }

    /**
     * Returns parent UrlElement codename (if parent exists)
     *
     * @return null|string
     */
    public function getParentCodename(): ?string
    {
        $parent = $this->getParent();

        return $parent ? $parent->getCodename() : null;
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string|null
     */
    public function getEntityModelName(): ?string
    {
        $entity = $this->getEntityRelation();

        return $entity->loaded() ? $entity->getLinkedModelName() : null;
    }

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string|null
     */
    public function getEntityActionName(): ?string
    {
        $entityAction = $this->getEntityActionRelation();

        return $entityAction->loaded() ? $entityAction->getName() : null;
    }

    /**
     * Returns zone codename where this IFace is placed
     *
     * @return string
     */
    public function getZoneName(): string
    {
        return $this->getZoneRelation()->getName();
    }

    /**
     * @return \BetaKiller\Model\Entity
     */
    private function getEntityRelation(): Entity
    {
        return $this->get('entity');
    }

    /**
     * @return \BetaKiller\Model\EntityAction
     */
    private function getEntityActionRelation(): EntityAction
    {
        return $this->get('action');
    }

    /**
     * @return \BetaKiller\Model\UrlElementZone
     */
    private function getZoneRelation(): UrlElementZone
    {
        return $this->get('zone');
    }

    /**
     * @return \BetaKiller\Model\UrlElementAclRule
     */
    private function getAclRulesRelation(): UrlElementAclRule
    {
        return $this->get('acl_rules');
    }

    /**
     * @return \BetaKiller\Model\UrlElementType
     */
    private function getTypeRelation(): UrlElementType
    {
        return $this->get('type');
    }
}
