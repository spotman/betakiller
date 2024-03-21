<?php
declare(strict_types=1);

namespace BetaKiller\Factory;

use BetaKiller\Model\User;
use BetaKiller\Model\UserInterface;

class UserFactory implements UserFactoryInterface
{
    public function __construct(private readonly EntityFactoryInterface $entityFactory)
    {
    }

    public function create(UserInfo $info): UserInterface
    {
        /** @var UserInterface $user */
        $user = $this->entityFactory->create(User::getModelName());

        if ($user::isCreatedAtRequired()) {
            $user->setCreatedAt();
        }

        if ($user::isIpAddressEnabled() && $info->ip) {
            $user->setCreatedFromIP($info->ip);
        }

        if ($user::isEmailEnabled() && $info->email) {
            $user->setEmail($info->email);
        }

        if ($user::isPhoneEnabled() && $info->phone) {
            $user->setPhone($info->phone);
        }

        if ($user::isUsernameEnabled() && $info->username) {
            $user->setUsername($info->username);
        }

        if ($user::isFirstNameEnabled() && $info->firstName) {
            $user->setFirstName($info->firstName);
        }

        if ($user::isLastNameEnabled() && $info->lastName) {
            $user->setLastName($info->lastName);
        }

        return $user;
    }
}
