<?php
namespace BetaKiller\Api\Method\PhpException;

use BetaKiller\Helper\CurrentUserTrait;
use Spotman\Api\Method\AbstractApiMethod;

class ThrowHttpExceptionApiMethod extends AbstractApiMethod
{
    use PhpExceptionMethodTrait;
    use CurrentUserTrait;

    protected $code;

    /**
     * ResolveApiMethod constructor.
     *
     * @param string $code
     */
    public function __construct($code)
    {
        $this->code = (int)$code;
    }

    /**
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute()
    {
        $user = $this->current_user();

        throw \HTTP_Exception::factory($this->code, 'This is a test from :username', [':username' => $user->get_username()]);
    }
}
