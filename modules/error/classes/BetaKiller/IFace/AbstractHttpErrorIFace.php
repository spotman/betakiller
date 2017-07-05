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
