<?php
namespace BetaKiller\IFace;

class IFaceModelTree
{
    /**
     * @var \IFace_Model_Provider
     */
    protected $_model_provider;

    public function __construct(\IFace_Model_Provider $model_provider)
    {
        $this->_model_provider = $model_provider;
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return IFaceModelRecursiveIterator|IFaceModelInterface[]
     */
    public function getRecursiveIterator(IFaceModelInterface $parent = NULL)
    {
        return new IFaceModelRecursiveIterator($parent, $this->_model_provider);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    public function getRecursivePublicIterator(IFaceModelInterface $parent = NULL)
    {
        return $this->getRecursiveFilterIterator(function(IFaceModelInterface $model) {
            return !$this->is_admin_model($model);
        }, $parent);
    }

    /**
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    public function getRecursiveAdminIterator(IFaceModelInterface $parent = NULL)
    {
        return $this->getRecursiveFilterIterator(function(IFaceModelInterface $model) {
            return $this->is_admin_model($model);
        }, $parent);
    }

    /**
     * @param callable $callback
     * @param IFaceModelInterface|NULL $parent
     *
     * @return \RecursiveIteratorIterator|IFaceModelInterface[]
     */
    protected function getRecursiveFilterIterator(callable $callback, IFaceModelInterface $parent = NULL)
    {
        $iterator = $this->getRecursiveIterator($parent);

        $filter = new \RecursiveCallbackFilterIterator($iterator, $callback);

        return new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::SELF_FIRST);
    }

    public function is_admin_model(IFaceModelInterface $model)
    {
        return $model->get_uri() == 'admin' || ($model instanceof \IFace_Model_Provider_Admin_Model);
    }
}
