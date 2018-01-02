<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Model\IFace;
use ORM;

class IFaceModelProviderDatabase extends IFaceModelProviderAbstract
{
    /**
     * Returns list of root elements
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getRoot(): array
    {
        $orm = $this->createIFaceOrm();

        try {
            return $orm
                ->where($orm->object_column('parent_id'), 'IS', null)
                ->cached()
                ->find_all()
                ->as_array();
        } catch (\Kohana_Exception $e) {
            throw new IFaceException($e->getMessage(), null, $e->getCode(), $e);
        }
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getDefault(): ?IFaceModelInterface
    {
        $orm = $this->createIFaceOrm();

        try {
            $iface = $orm
            ->where($orm->object_column('is_default'), '=', true)
            ->cached()
            ->find();

            return $iface->loaded() ? $iface : null;
        } catch (\Kohana_Exception $e) {
            throw new IFaceException($e->getMessage(), null, $e->getCode(), $e);
        }
    }

    /**
     * Returns iface model by codename or throws exception if none was found
     *
     * @param string $codename
     *
     * @return IFaceModelInterface
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByCodename(string $codename): IFaceModelInterface
    {
        $orm = $this->createIFaceOrm();

        try {
            $iface = $orm
                ->where($orm->object_column('codename'), '=', $codename)
                ->cached()
                ->find();

            if (!$iface->loaded()) {
                throw new IFaceException('No IFace found by codename :codename', [':codename' => $codename]);
            }

            return $iface;
        } catch (\Kohana_Exception $e) {
            throw new IFaceException(':error', [':error' => $e->getMessage()], $e->getCode(), $e);
        }
    }

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, string $entityAction, string $zone): ?IFaceModelInterface
    {
        try {
            $orm = $this->createIFaceOrm();

            $iface = $orm
                ->where('entity.model_name', '=', $entity->getModelName())
                ->where('action.name', '=', $entityAction)
                ->where('zone.name', '=', $zone)
                ->find();

            return $iface->loaded() ? $iface : null;
        } catch (\Kohana_Exception $e) {
            throw new IFaceException($e->getMessage(), null, $e->getCode(), $e);
        }
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getByActionAndZone(string $action, string $zone): array
    {
        try {
            $orm = $this->createIFaceOrm();

            return $orm
                ->where('action.name', '=', $action)
                ->where('zone.name', '=', $zone)
                ->get_all();
        } catch (\Kohana_Exception $e) {
            throw new IFaceException($e->getMessage(), null, $e->getCode(), $e);
        }
    }

    /**
     * @return \BetaKiller\Model\IFace
     */
    protected function createIFaceOrm(): IFace
    {
        return ORM::factory('IFace');
    }
}
