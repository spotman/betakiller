<?php

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception\BadRequestHttpException;
use BetaKiller\Exception\FoundHttpException;
use BetaKiller\Exception\PermanentRedirectHttpException;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;
use BetaKiller\Url\WebHookModelInterface;

/**
 * Class Controller_IFace
 */
class Controller_IFace extends Controller
{
    /**
     * @Inject
     * @var UserInterface
     */
    private $user;

    /**
     * @Inject
     * @var AppConfigInterface
     */
    private $appConfig;

    /**
     * @Inject
     * @var UrlDispatcher
     */
    private $urlDispatcher;

    /**
     * @Inject
     * @var \BetaKiller\Factory\IFaceFactory
     */
    private $ifaceFactory;

    /**
     * @Inject
     * @var \BetaKiller\Factory\WebHookFactory
     */
    private $webHookFactory;

    /**
     * @Inject
     * @var UrlContainerInterface
     */
    private $urlContainer;

    /**
     * @Inject
     * @var IFaceCache
     */
    private $ifaceCache;

    /**
     * @Inject
     * @var \BetaKiller\View\IFaceView
     */
    private $ifaceView;

    /**
     * @throws \Spotman\Acl\Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \BetaKiller\MessageBus\MessageBusException
     * @throws \BetaKiller\Exception\PermanentRedirectHttpException
     * @throws \BetaKiller\Exception\FoundHttpException
     * @throws \BetaKiller\Exception\SeeOtherHttpException
     * @throws \BetaKiller\Exception\NotFoundHttpException
     * @throws \BetaKiller\Auth\AuthorizationRequiredException
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \PageCache\PageCacheException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function action_render(): void
    {
        $uri        = $this->getRequestUri();
        $queryParts = $this->getRequestQueryParts();

        $this->urlContainer->setQueryParts($queryParts);

        // Getting current URL element
        $urlElement = $this->urlDispatcher->process($uri, $this->request->client_ip(), $this->request->referrer());

        if ($urlElement instanceof WebHookModelInterface) {
            $this->processWebHook($urlElement);
        } elseif ($urlElement instanceof IFaceModelInterface) {
            // If this is default IFace and client requested non-slash uri, redirect client to /
            if ($uri !== '/' && $urlElement->isDefault() && !$urlElement->hasDynamicUrl()) {
                throw new FoundHttpException('/');
            }

            if ($uri && $uri !== '/') {
                $hasTrailingSlash       = (substr($uri, -1) === '/');
                $isTrailingSlashEnabled = $this->appConfig->isTrailingSlashEnabled();

                if ($hasTrailingSlash && !$isTrailingSlashEnabled) {
                    throw new PermanentRedirectHttpException(rtrim($uri, '/'));
                }

                if (!$hasTrailingSlash && $isTrailingSlashEnabled) {
                    throw new PermanentRedirectHttpException($uri.'/');
                }
            }

            $this->processIFace($urlElement);
        } else {
            throw new IFaceException('Unknown UrlElement type :codename', [
                ':codename' => $urlElement->getCodename(),
            ]);
        }
    }

    /**
     * @param \BetaKiller\Url\IFaceModelInterface $model
     *
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \PageCache\PageCacheException
     */
    private function processIFace(IFaceModelInterface $model): void
    {
        $iface = $this->ifaceFactory->createFromUrlElement($model);

        // Starting hook
        $iface->before();

        // Processing page cache if no URL query parameters
        if (!$this->urlContainer->getQueryPartsKeys()) {
            $this->processIFaceCache($iface);
        }

        try {
            $output = $this->ifaceView->render($iface);

            // Final hook
            $iface->after();

            $unusedParts = $this->urlContainer->getUnusedQueryPartsKeys();

            if ($unusedParts) {
                throw new BadRequestHttpException('Request have unused query parts: :keys', [
                    ':keys' => implode(', ', $unusedParts),
                ]);
            }

            $this->last_modified($iface->getLastModified());
            $this->expires($iface->getExpiresDateTime());

            $this->send_string($output);
        } catch (\Throwable $e) {
            // Prevent response caching
            $this->ifaceCache->disable();

            throw $e;
        }
    }

    /**
     * @param \BetaKiller\Url\WebHookModelInterface $model
     *
     * @throws \BetaKiller\Factory\FactoryException
     */
    private function processWebHook(WebHookModelInterface $model): void
    {
        $webHook = $this->webHookFactory->createFromUrlElement($model);

        $webHook->process();
    }

    /**
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \PageCache\PageCacheException
     */
    private function processIFaceCache(IFaceInterface $iface): void
    {
        // Skip caching if request method is not GET nor HEAD
        if (!\in_array($this->request->method(), ['GET', 'HEAD'], true)) {
            return;
        }

        // Skip caching for authorized users
        if (!$this->user->isGuest()) {
            return;
        }

        $this->ifaceCache->process($iface);
    }

    /**
     * @return string
     */
    private function getRequestUri(): string
    {
        return $this->request->url();
    }

    /**
     * @return string[]
     */
    private function getRequestQueryParts(): array
    {
        return $this->request->query();
    }
}
