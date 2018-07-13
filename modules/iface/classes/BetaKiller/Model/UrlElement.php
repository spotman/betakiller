<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\NotImplementedHttpException;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\WebHookModelInterface;

/**
 * Class UrlElement
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class UrlElement extends AbstractOrmBasedSingleParentTreeModel implements UrlElementInterface
{
    protected function configure(): void
    {
        $this->_table_name = 'url_elements';

        $this->belongs_to([
            'type'   => [
                'model'       => 'UrlElementType',
                'foreign_key' => 'type_id',
            ],
            'entity' => [
                'model'       => 'Entity',
                'foreign_key' => 'entity_id',
            ],
            'action' => [
                'model'       => 'EntityAction',
                'foreign_key' => 'entity_action_id',
            ],
            'zone'   => [
                'model'       => 'UrlElementZone',
                'foreign_key' => 'zone_id',
            ],
        ]);

        $this->has_one([
            'iface' => [
                'model'       => 'IFace',
                'foreign_key' => 'element_id',
            ],
            'webhook' => [
                'model'       => 'WebHook',
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
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault(): bool
    {
        return (bool)$this->is_default;
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
     * Returns iface url part
     *
     * @return string
     */
    public function getUri(): string
    {
        return $this->uri;
    }

    /**
     * @param string $value
     */
    public function setUri(string $value): void
    {
        $this->uri = $value;
    }

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $this->label = $value;
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl(): bool
    {
        return (bool)$this->is_dynamic;
    }

    /**
     * Returns TRUE if iface has multi-level tree-behavior url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour(): bool
    {
        return (bool)$this->is_tree;
    }

    /**
     * @return bool
     * @throws \BetaKiller\Exception\NotImplementedHttpException
     */
    public function hideInSiteMap(): bool
    {
        throw new NotImplementedHttpException('Call ::hideInSiteMap() on dedicated URL elements` classes');
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
     * @throws \Kohana_Exception
     */
    public function getZoneName(): string
    {
        return $this->getZoneRelation()->getName();
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

    public function isTypeWebHook(): bool
    {
        return $this->getTypeRelation()->isWebHook();
    }

    /**
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getIFaceModel(): IFaceModelInterface
    {
        if (!$this->isTypeIFace()) {
            throw new IFaceException('Can not get IFace model from UrlElement :codename instance of :class', [
                ':codename' => $this->getCodename(),
                ':class'    => \get_class($this),
            ]);
        }

        return $this->iface;
    }

    /**
     * @return \BetaKiller\Url\WebHookModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getWebHookModel(): WebHookModelInterface
    {
        if (!$this->isTypeWebHook()) {
            throw new IFaceException('Can not get WebHook model from UrlElement :codename instance of :class', [
                ':codename' => $this->getCodename(),
                ':class'    => \get_class($this),
            ]);
        }

        return $this->webhook;
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
     * @return \BetaKiller\Model\Entity
     */
    private function getEntityRelation(): Entity
    {
        return $this->entity;
    }

    /**
     * @return \BetaKiller\Model\EntityAction
     */
    private function getEntityActionRelation(): EntityAction
    {
        return $this->action;
    }

    /**
     * @return \BetaKiller\Model\UrlElementZone
     */
    private function getZoneRelation(): UrlElementZone
    {
        return $this->zone;
    }

    /**
     * @return \BetaKiller\Model\UrlElementAclRule
     */
    private function getAclRulesRelation(): UrlElementAclRule
    {
        return $this->acl_rules;
    }

    /**
     * @return \BetaKiller\Model\UrlElementType
     */
    private function getTypeRelation(): UrlElementType
    {
        return $this->type;
    }
}
