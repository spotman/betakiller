<?php
namespace BetaKiller\IFace;

use BetaKiller\Exception\HttpExceptionInterface;

class HttpError401 extends AbstractHttpErrorIFace
{
    /**
     * @Inject
     * @var \BetaKiller\View\IFaceView
     */
    private $ifaceView;

    protected function getDefaultHttpException(): HttpExceptionInterface
    {
        return new \HTTP_Exception_401();
    }

    /**
     * @return string
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function render(): string
    {
        /** @var \BetaKiller\IFace\Auth\Login $loginIFace */
        $loginIFace = $this->ifaceHelper->createIFaceFromCodename('Auth_Login');

        return $this->ifaceView->render($loginIFace);
    }
}
