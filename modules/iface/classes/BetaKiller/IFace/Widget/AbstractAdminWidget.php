<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\Model\UserInterface;
use BetaKiller\Widget\AbstractBaseWidget;

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

        parent::__construct();
    }

    public function render(): string
    {
        if (!$this->isAccessAllowed()) {

            if ($this->isEmptyResponseAllowed()) {
                return '';
            }

            throw new \HTTP_Exception_403();
        }

        return parent::render();
    }

    protected function isAccessAllowed(): bool
    {
        // Hide admin widget from non-authorized users (this check lowers execution time also)
        if ($this->user->isGuest()) {
            return false;
        }

        /** @var \BetaKiller\Acl\Resource\AdminResource $adminResource */
        $adminResource = $this->aclHelper->getResource('Admin');

        return  $adminResource->isEnabled();
    }

    protected function isEmptyResponseAllowed()
    {
        return false;
    }
}
