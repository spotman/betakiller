<?php
namespace BetaKiller\IFace;

use BetaKiller\Error\ExceptionHandler;
use BetaKiller\Exception\HttpExceptionInterface;
use BetaKiller\Model\UserInterface;

abstract class AbstractHttpErrorIFace extends AbstractIFace
{
    /**
     * @var ExceptionHandler
     */
    private $exceptionHandler;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * AbstractHttpErrorIFace constructor.
     *
     * @param \BetaKiller\Model\UserInterface    $user
     * @param \BetaKiller\Error\ExceptionHandler $exceptionHandler
     */
    public function __construct(UserInterface $user, ExceptionHandler $exceptionHandler)
    {
        parent::__construct();

        $this->user             = $user;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     * @throws \BetaKiller\Exception
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getData(): array
    {
        /** @var \BetaKiller\IFace\Auth\Login $loginIFace */
        $loginIFace = $this->ifaceHelper->createIFaceFromCodename('Auth_Login');

        return [
            'label'     => $this->getLabel(),
            'login_url' => $loginIFace->url(),
            'is_guest'  => $this->user->isGuest(),
        ];
    }

    /**
     * Returns plain label
     *
     * @return string
     * @throws \BetaKiller\Exception
     */
    public function getLabel(): string
    {
        $exception = $this->getDefaultHttpException();

        return $this->exceptionHandler->getExceptionMessage($exception);
    }

    abstract protected function getDefaultHttpException(): HttpExceptionInterface;
}
