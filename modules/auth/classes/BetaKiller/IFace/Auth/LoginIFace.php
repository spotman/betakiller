<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Url\BeforeProcessingInterface;
use Psr\Http\Message\ServerRequestInterface;

class LoginIFace extends AbstractIFace implements BeforeProcessingInterface
{
    public const URL = '/login/';

    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * LoginIFace constructor.
     *
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(UserUrlDetectorInterface $urlDetector)
    {
        $this->urlDetector = $urlDetector;
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function beforeProcessing(ServerRequestInterface $request): void
    {
        $user = ServerRequestHelper::getUser($request);

        // If user already authorized
        if (!$user->isGuest()) {
            $url = $this->urlDetector->detect($user);

            // Redirect him to the right place (this is a fallback if an authorized user visited /login)
            throw new FoundHttpException($url);
        }
    }

    public function getData(ServerRequestInterface $request): array
    {
        return [];
    }
}
