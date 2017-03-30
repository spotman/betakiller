<?php

use BetaKiller\Helper\ErrorHelperTrait;
use BetaKiller\Helper\CurrentUserTrait;
use Spotman\Api\ApiModelCrud;
use Spotman\Api\ApiModelException;

// TODO Restrict access to this model via ACL to developers only

class API_Model_PhpException extends ApiModelCrud
{
    use ErrorHelperTrait;
    use CurrentUserTrait;

    public function resolve($hash)
    {
        $phpException = $this->findByHash($hash);

        $this->phpExceptionStorageFactory()->resolve($phpException, $this->current_user());
    }

    public function ignore($hash)
    {
        $phpException = $this->findByHash($hash);

        $this->phpExceptionStorageFactory()->ignore($phpException, $this->current_user());
    }

    public function delete($hash)
    {
        $phpException = $this->findByHash($hash);

        $this->phpExceptionStorageFactory()->delete($phpException);
    }

    public function throwHTTP($code)
    {
        $user = $this->current_user();

        throw HTTP_Exception::factory((int) $code, 'This is a test from :username', [':username' => $user->get_username()]);
    }

    protected function findByHash($hash)
    {
        $model = $this->phpExceptionStorageFactory()->findByHash($hash);

        if (!$model) {
            throw new ApiModelException('Incorrect php exception hash :value', [':value' => $hash]);
        }

        return $model;
    }

    protected function model($id = NULL)
    {
        // No direct usage
        throw new HTTP_Exception_501('Not implemented');
    }
}
