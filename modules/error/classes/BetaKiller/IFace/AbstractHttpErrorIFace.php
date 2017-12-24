<?php
namespace BetaKiller\IFace;

abstract class AbstractHttpErrorIFace extends AbstractIFace
{
    /**
     * @var \Throwable
     */
    private $exception;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before(): void
    {
        if (!$this->exception) {
            $this->exception = $this->getDefaultHttpException();
        }

        parent::before();
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    public function getData(): array
    {
        /** @var \BetaKiller\IFace\Auth\Login $loginIFace */
        $loginIFace = $this->ifaceHelper->createIFaceFromCodename('Auth_Login');

        return [
            'label'     => $this->getLabel(),
            'message'   => \HTTP_Exception::getUserMessage($this->exception),
            'login_url' => $loginIFace->url(),
            'is_guest'  => $this->user->isGuest(),
        ];
    }

    public function setException(\Throwable $e): AbstractHttpErrorIFace
    {
        $this->exception = $e;

        return $this;
    }

    abstract protected function getDefaultHttpException(): \HTTP_Exception;

    /**
     * Returns label source/pattern
     *
     * @return string
     */
    public function getLabelSource(): string
    {
        $i18nKey = \HTTP_Exception::getErrorLabelI18nKey($this->exception);

        return __($i18nKey);
    }
}
