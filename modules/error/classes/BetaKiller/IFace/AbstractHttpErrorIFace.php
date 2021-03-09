<?php
namespace BetaKiller\IFace;

use BetaKiller\Helper\ServerRequestHelper;
use Psr\Http\Message\ServerRequestInterface;

abstract class AbstractHttpErrorIFace extends AbstractIFace
{
    protected $exception;

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
//        $redirectData = [
//            AuthWidget::REDIRECT_KEY => ServerRequestHelper::getUrl($request),
//        ];

        $user  = !ServerRequestHelper::isGuest($request) ? ServerRequestHelper::getUser($request) : null;
        $email = $user ? $user->getEmail() : null;

        return [
            'email' => $email,
//            'login_url' => LoginIFace::URL.'?'.http_build_query($redirectData),
//            'is_guest'  => ServerRequestHelper::isGuest($request),
        ];
    }

    public function setException(\Throwable $e): self
    {
        $this->exception = $e;

        return $this;
    }
}
