<?php
namespace BetaKiller\Model;

use BetaKiller\Exception;
use BetaKiller\Helper\I18nHelper;
use ORM;

class Entity extends ORM implements EntityModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     * @throws \Exception
     */
    protected function configure(): void
    {
        $this->_table_name = 'entities';
    }

    /**
     * Returns entity short name (may be used for url creating)
     *
     * @return string
     * @throws \Kohana_Exception
     */
    public function getSlug(): string
    {
        return (string)$this->get('slug');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\Entity
     */
    public function setSlug(string $value): EntityModelInterface
    {
        return $this->set('slug', $value);
    }

    /**
     * Returns model name of the current entity
     *
     * @return string
     */
    public function getLinkedModelName(): string
    {
        return (string)$this->get('model_name');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\Entity
     */
    public function setLinkedModelName(string $value): EntityModelInterface
    {
        return $this->set('model_name', $value);
    }

    /**
     * @param \BetaKiller\Helper\I18nHelper $i18n
     *
     * @return string
     */
    public function getLabel(I18nHelper $i18n): string
    {
        return $i18n->translate('entities.'.$this->getSlug());
    }

    /**
     * Returns instance of linked entity
     *
     * @param int $id
     *
     * @return \BetaKiller\Model\RelatedEntityInterface
     * @throws \BetaKiller\Exception
     */
    public function getLinkedEntityInstance($id): RelatedEntityInterface
    {
        // TODO Rewrite to EntityManager or something similar
        $name        = $this->getLinkedModelName();
        $model       = $this->model_factory($id, $name);
        $targetClass = RelatedEntityInterface::class;

        if (!($model instanceof $targetClass)) {
            throw new Exception('Entity model must be an instance of :target, :current given', [
                ':target'  => $targetClass,
                ':current' => \get_class($model),
            ]);
        }

        return $model;
    }
}
