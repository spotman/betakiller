<?php
namespace BetaKiller\IFace;

use BetaKiller\Model\UserInterface;

abstract class AbstractHttpErrorIFace extends AbstractIFace
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * AbstractHttpErrorIFace constructor.
     *
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
//        parent::__construct();

        $this->user = $user;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        /** @var \BetaKiller\IFace\Auth\Login $loginIFace */
        $loginIFace = $this->ifaceHelper->createIFaceFromCodename('Auth_Login');

        return [
            'login_url' => $this->ifaceHelper->makeIFaceUrl($loginIFace),
            'is_guest'  => $this->user->isGuest(),
        ];
    }
}
