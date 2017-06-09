<?php
namespace BetaKiller\IFace;

class HttpError401 extends AbstractHttpErrorIFace
{
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
