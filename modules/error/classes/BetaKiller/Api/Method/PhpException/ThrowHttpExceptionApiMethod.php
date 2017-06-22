<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Model\UserInterface;

class ThrowHttpExceptionApiMethod extends AbstractPhpExceptionApiMethod
{
    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var int
     */
    private $code;

    /**
     * ThrowHttpExceptionApiMethod constructor.
     *
     * @param int                             $code
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(int $code, UserInterface $user)
    {
        $this->code = $code;
        $this->user = $user;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        throw \HTTP_Exception::factory($this->code, 'This is a test from :username',
            [':username' => $this->user->get_username()]);
    }
}
