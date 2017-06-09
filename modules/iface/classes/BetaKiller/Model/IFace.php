<?php
namespace BetaKiller\Model;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentOrm;

/**
 * Class IFace
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class IFace extends TreeModelSingleParentOrm implements IFaceModelInterface
{
    protected function _initialize(): void
    {
        $this->belongs_to([
            'layout' => [
                'model'       => 'Layout',
                'foreign_key' => 'layout_id',
            ],
        ]);

        $this->belongs_to([
            'entity' => [
                'model'       => 'Entity',
                'foreign_key' => 'entity_id',
            ],
        ]);

        $this->belongs_to([
            'action' => [
                'model'       => 'EntityAction',
                'foreign_key' => 'entity_action_id',
            ],
        ]);

        $this->belongs_to([
            'zone' => [
                'model'       => 'IFaceZone',
                'foreign_key' => 'zone_id',
            ],
        ]);

        $this->has_many([
            'aclRules' => [
                'model' => 'IFaceAclRule',
                'foreign_key' => 'iface_id',
            ],
        ]);

        $this->load_with([
            'layout',
            'entity',
            'action',
            'zone',
        ]);

        $this->load_with([
            'parent:layout',
            'parent:entity',
            'parent:action',
            'parent:zone',
        ]);

        parent::_initialize();
    }

    /**
     * Returns list of child iface models
     *
     * @return IFaceModelInterface[]|$this[]
     */
    public function getChildren()
    {
        return parent::getChildren();
    }

    /**
     * Return parent iface model or NULL
     *
     * @return IFaceModelInterface|\BetaKiller\Utils\Kohana\TreeModelOrmBase
     */
    public function getParent()
    {
        return parent::getParent();
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
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->get('codename');
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
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->get('title');
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->get('description');
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
     * Returns layout model
     *
     * @return Layout
     */
    private function getLayoutRelation(): Layout
    {
        return $this->get('layout');
    }

    /**
     * Returns layout codename
     *
     * @return string
     */
    public function getLayoutCodename(): ?string
    {
        $layout = $this->getLayoutRelation();

        // TODO Allow null layout so it will climb up the ifaces tree to detect layout (if it fails then exception fired)
        if (!$layout->loaded()) {
            $layout = $layout->get_default();
        }

        return $layout->get_codename();
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
     * @return bool
     */
    public function hideInSiteMap(): bool
    {
        return (bool)$this->get('hide_in_site_map');
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return $this
     */
    public function setTitle(string $value)
    {
        return $this->set('title', (string)$value);
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return $this
     */
    public function setDescription(string $value)
    {
        return $this->set('description', (string)$value);
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): string
    {
        return $this->getEntityRelation()->getLinkedModelName();
    }

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string
     */
    public function getEntityActionName(): string
    {
        return $this->getEntityActionRelation()->getName();
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
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        /** @var \BetaKiller\Model\IFaceAclRule[] $rules */
        $rules = $this->getAclRulesRelation()->get_all();
        $output = [];

        foreach ($rules as $rule) {
            $output[] = $rule->getCombinedRule();
        }

        return $output;
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
     * @return \BetaKiller\Model\IFaceZone
     */
    private function getZoneRelation(): IFaceZone
    {
        return $this->get('zone');
    }

    /**
     * @return \BetaKiller\Model\IFaceAclRule
     */
    private function getAclRulesRelation(): IFaceAclRule
    {
        return $this->get('aclRules');
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray(): array
    {
        return $this->as_array();
    }

    /**
     * Place here additional query params
     *
     * @return $this
     */
    protected function additionalTreeModelFiltering()
    {
        // No filtering needed
        return $this;
    }
}
