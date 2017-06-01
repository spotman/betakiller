<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\Model\UserInterface;

abstract class AbstractAdminWidget extends AbstractBaseWidget
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    protected $user;

    /**
     * BarWidget constructor.
     *
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    public function render()
    {
        if (!$this->isAccessAllowed()) {

            if ($this->isEmptyResponseAllowed()) {
                return null;
            }

            throw new \HTTP_Exception_403('Permission denied');
        }

        return parent::render();
    }

    protected function isAccessAllowed()
    {
        /** @var \BetaKiller\Acl\Resource\AdminResource $adminResource */
        $adminResource = $this->aclHelper->getResource('Admin');

        return !$this->user->isGuest() && $adminResource->isEnabled();
    }

    protected function isEmptyResponseAllowed()
    {
        return false;
    }
}
