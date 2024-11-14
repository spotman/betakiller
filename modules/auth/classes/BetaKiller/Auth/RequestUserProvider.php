<?php

declare(strict_types=1);

namespace BetaKiller\Auth;

use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\UserInterface;
use BetaKiller\Service\AuthService;
use Psr\Http\Message\ServerRequestInterface;

final class RequestUserProvider
{

    /**
     * Cached User instance
     *
     * @var \BetaKiller\Model\UserInterface|null
     */
    private ?UserInterface $user = null;

    /**
     * RequestUserProvider constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \BetaKiller\Service\AuthService          $auth
     */
    public function __construct(private readonly ServerRequestInterface $request, private readonly AuthService $auth)
    {
    }

    public function fetch(): UserInterface
    {
        if (!$this->user) {
            $this->user = $this->detect($this->request);
        }

        return $this->user;
    }

    public function isFetched(): bool
    {
        return $this->user !== null;
    }

    private function detect(ServerRequestInterface $request): UserInterface
    {
        $session = ServerRequestHelper::getSession($request);

        $u    = RequestProfiler::begin($request, 'Fetch User from Session');
        $user = $this->auth->getSessionUser($session);
        RequestProfiler::end($u);

        return $user;
    }
}
