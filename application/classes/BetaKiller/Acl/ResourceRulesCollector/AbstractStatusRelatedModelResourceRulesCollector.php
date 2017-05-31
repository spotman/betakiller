<?php
namespace BetaKiller\Acl\ResourceRulesCollector;

use BetaKiller\Factory\OrmFactory;
use BetaKiller\Status\StatusModelInterface;
use Spotman\Acl\ResourceInterface;
use Spotman\Acl\ResourceRulesCollector\AbstractResourceRulesCollector;
use Spotman\Acl\ResourceRulesCollector\ResourceRulesCollectorInterface;

abstract class AbstractStatusRelatedModelResourceRulesCollector extends AbstractResourceRulesCollector
    implements ResourceRulesCollectorInterface
{
    /**
     * @var \BetaKiller\Factory\OrmFactory
     */
    private $ormFactory;

    /**
     * ContentPostResourceRulesCollector constructor.
     *
     * @param \Spotman\Acl\ResourceInterface $resource
     * @param \BetaKiller\Factory\OrmFactory $ormFactory
     */
    public function __construct(ResourceInterface $resource, OrmFactory $ormFactory)
    {
        parent::__construct($resource);
        $this->ormFactory = $ormFactory;
    }

    /**
     * Key=>Value pairs where key is a permission identity and value is an array of roles
     *
     * @return string[][]
     */
    protected function getPermissionsRoles()
    {
        $model = $this->getStatusModel();

        return $this->getStatusDefaultAccessList($model);
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $model
     *
     * @return array
     */
    private function getStatusDefaultAccessList(StatusModelInterface $model)
    {
        /** @var StatusModelInterface[] $statuses */
        $statuses = $model->get_all_nodes();
        $data     = [];

        foreach ($statuses as $status) {
            $data[] = $this->getStatusActionsDefaultAccessList($status);
            $data[] = $this->getStatusTransitionsDefaultAccessList($status);
        }

        return array_merge(...$data);
    }

    private function getStatusActionsDefaultAccessList(StatusModelInterface $status)
    {
        /** @var \BetaKiller\Acl\Resource\StatusRelatedEntityAclResourceInterface $resource */
        $resource = $this->resource;

        $actions = $resource->getStatusActionsList();
        $data    = [];

        foreach ($actions as $action) {
            $identity        = $resource->makeStatusPermissionIdentity($status, $action);
            $data[$identity] = $status->getStatusActionAllowedRoles($action);
        }

        return $data;
    }

    /**
     * @param \BetaKiller\Status\StatusModelInterface $statusModel
     *
     * @return array
     */
    private function getStatusTransitionsDefaultAccessList(StatusModelInterface $statusModel)
    {
        /** @var \BetaKiller\Acl\Resource\StatusRelatedEntityAclResourceInterface $resource */
        $resource = $this->resource;

        /** @var \BetaKiller\Status\StatusTransitionModelInterface[] $transitions */
        $transitions = $statusModel->get_target_transitions();
        $data        = [];

        foreach ($transitions as $transition) {
            $identity        = $resource->makeTransitionPermissionIdentity($statusModel, $transition->get_codename());
            $data[$identity] = $transition->getTransitionAllowedRolesNames();
        }

        return $data;
    }

    /**
     * @return \BetaKiller\Status\StatusModelInterface
     */
    private function getStatusModel()
    {
        $name = $this->resource->getResourceId();

        /** @var \BetaKiller\Status\StatusRelatedModelInterface $relatedModel */
        $relatedModel = $this->ormFactory->create($name);

        // TODO deal with getting status model
        return $relatedModel->get_current_status();
    }
}
