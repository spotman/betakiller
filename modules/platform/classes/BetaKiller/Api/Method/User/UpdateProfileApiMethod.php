<?php
namespace BetaKiller\Api\Method\User;

use BetaKiller\Model\UserInterface;
use BetaKiller\Repository\UserRepository;
use HTML;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;

class UpdateProfileApiMethod extends AbstractApiMethod
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepository;

    /**
     * @var array
     */
    private $data;

    /**
     * UpdateProfileApiMethod constructor.
     *
     * @param array                                 $data
     * @param \BetaKiller\Model\UserInterface       $user
     * @param \BetaKiller\Repository\UserRepository $userRepository
     */
    public function __construct(
        array $data,
        UserInterface $user,
        UserRepository $userRepository
    ) {
        $this->data           = $data;
        $this->user           = $user;
        $this->userRepository = $userRepository;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(): ?ApiMethodResponse
    {
        $user = $this->user;

        $user->forceAuthorization();

        $data = (object)$this->data;

        if (isset($data->firstName)) {
            $user->setFirstName(HTML::chars($data->firstName));
        }

        if (isset($data->lastName)) {
            $user->setLastName(HTML::chars($data->lastName));
        }

        if (isset($data->phone)) {
            $user->setPhone(HTML::chars($data->phone));
        }

        $this->userRepository->save($user);

        return null;
    }
}
