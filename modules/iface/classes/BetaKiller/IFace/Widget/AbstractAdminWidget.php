<?php
namespace BetaKiller\IFace\Widget;

use BetaKiller\Helper\CurrentUserTrait;
use BetaKiller\Model\UserInterface;

abstract class AbstractAdminWidget extends BaseWidget
{
    use CurrentUserTrait;

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
        return !$this->user->isGuest() && $this->user->is_admin_allowed();
    }

    protected function isEmptyResponseAllowed()
    {
        return false;
    }
}
