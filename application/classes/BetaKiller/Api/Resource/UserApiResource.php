<?php
namespace BetaKiller\Api\Resource;

use HTML;
use Spotman\Api\ModelCrudApiResource;

abstract class UserApiResource extends ModelCrudApiResource
{
    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var \BetaKiller\Repository\UserRepository
     */
    private $userRepository;

    protected function updateProfile($data): void
    {
        $user = $this->user;

        $user->forceAuthorization();

        $data = (object)$data;

        if (isset($data->firstName)) {
            $user->set_first_name(HTML::chars($data->firstName));
        }

        if (isset($data->lastName)) {
            $user->set_last_name(HTML::chars($data->lastName));
        }

        if (isset($data->phone)) {
            $user->set_phone(HTML::chars($data->phone));
        }

        $this->userRepository->save($user);
    }
}
