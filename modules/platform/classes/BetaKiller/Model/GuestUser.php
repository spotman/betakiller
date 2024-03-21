<?php
declare(strict_types=1);

namespace BetaKiller\Model;

final class GuestUser extends User implements GuestUserInterface
{
    use GuestUserTrait;
}
