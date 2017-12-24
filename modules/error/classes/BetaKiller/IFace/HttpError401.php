<?php
namespace BetaKiller\IFace;

class HttpError401 extends AbstractHttpErrorIFace
{
    protected function getDefaultHttpException(): \HTTP_Exception
    {
        return new \HTTP_Exception_401();
    }

    /**
     * @return string
     */
    public function render(): string
    {
        /** @var \BetaKiller\IFace\Auth\Login $loginIFace */
        $loginIFace = $this->ifaceHelper->createIFaceFromCodename('Auth_Login');

        return $loginIFace->render();
    }
}
