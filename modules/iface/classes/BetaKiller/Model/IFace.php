<?php
namespace BetaKiller\Model;

use BetaKiller\Helper\SeoMetaInterface;
use BetaKiller\Url\IFaceModelInterface;

/**
 * Class IFace
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller\Url
 */
class IFace extends AbstractOrmModelContainsUrlElement implements IFaceModelInterface
{
    protected function configure(): void
    {
        $this->belongs_to([
            'layout' => [
                'model'       => 'IFaceLayout',
                'foreign_key' => 'layout_id',
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

        $this->load_with([
            'layout',
            'entity',
            'action',
            'zone',
        ]);

        parent::configure();
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns layout model
     *
     * @return IFaceLayout
     */
    private function getLayoutRelation(): IFaceLayout
    {
        return $this->layout;
    }

    /**
     * Returns layout codename
     * Allow null layout so it will be detected via climbing up the IFaces tree
     *
     * @return string
     */
    public function getLayoutCodename(): ?string
    {
        $layout = $this->getLayoutRelation();

        return $layout->loaded() ? $layout->getCodename() : null;
    }

    /**
     * @return bool
     */
    public function isHiddenInSiteMap(): bool
    {
        return (bool)$this->hide_in_site_map;
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
     * @param string $value
     */
    public function setLabel(string $value): void
    {
        $this->label = $value;
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     */
    public function setTitle(string $value): SeoMetaInterface
    {
        $this->title = $value;

        return $this;
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return SeoMetaInterface
     */
    public function setDescription(string $value): SeoMetaInterface
    {
        $this->description = $value;

        return $this;
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
}
