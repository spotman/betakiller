<?php

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Url\UrlContainerInterface;
use BetaKiller\Url\UrlDispatcher;

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
     * @var \BetaKiller\IFace\IFaceFactory
     */
    private $ifaceFactory;

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
     * @throws \BetaKiller\Factory\FactoryException
     * @throws \PageCache\PageCacheException
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function action_render(): void
    {
        $uri        = $this->getRequestUri();
        $queryParts = $this->getRequestQueryParts();

        $this->urlContainer->setQueryParts($queryParts);

        // Getting current IFace
        $model = $this->urlDispatcher->process($uri, $this->request->client_ip());
        $iface = $this->ifaceFactory->createFromModel($model);

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ($uri !== '/' && $model->isDefault() && !$model->hasDynamicUrl()) {
            $this->redirect('/');
        }

        if ($uri && $uri !== '/') {
            $has_trailing_slash = (substr($uri, -1) === '/');

            $is_trailing_slash_enabled = $this->appConfig->isTrailingSlashEnabled();

            if ($has_trailing_slash && !$is_trailing_slash_enabled) {
                // Permanent redirect
                $this->redirect(rtrim($uri, '/'), 301);
            } elseif (!$has_trailing_slash && $is_trailing_slash_enabled) {
                // Permanent redirect
                $this->redirect($uri.'/', 301);
            }
        }

        // Starting hook
        $iface->before();

        // Processing page cache if no URL query parameters
        if (!$queryParts) {
            $this->processIFaceCache($iface);
        }

        try {
            $output = $this->ifaceView->render($iface);

            // Final hook
            $iface->after();

            if ($unusedParts = $this->urlContainer->getUnusedQueryPartsKeys()) {
                throw new HTTP_Exception_400('Request have unused query parts: :keys', [
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
     * @param \BetaKiller\IFace\IFaceInterface $iface
     *
     * @throws \PageCache\PageCacheException
     */
    private function processIFaceCache(IFaceInterface $iface): void
    {
        // Skip caching if request method is not GET nor HEAD
        if (!in_array($this->request->method(), ['GET', 'HEAD'], true)) {
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
