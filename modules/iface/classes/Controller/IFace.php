<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\DI\Container;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\IFace\Url\UrlParametersInterface;

/**
 * Class Controller_IFace
 * @todo Refactoring to ControllerIFace + KohanaRequestAdapter/KohanaResponseAdapter
 */
class Controller_IFace extends Controller
{
    /**
     * @throws \HTTP_Exception_400
     */
    public function action_render(): void
    {
        /** @var UrlDispatcher $dispatcher */
        $dispatcher = Container::getInstance()->get(UrlDispatcher::class);

        /** @var UrlParametersInterface $params */
        $params = Container::getInstance()->get(UrlParametersInterface::class);

        $uri = $this->getRequestUri();
        $queryParts = $this->getRequestQueryParts();

        $params->setQueryParts($queryParts);

        // Getting current IFace
        $iface = $dispatcher->process($uri);

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ($uri !== '/' && $iface->isDefault() && !$iface->getModel()->hasDynamicUrl()) {
            $this->redirect('/');
        }

        if ($uri && $uri !== '/') {
            $has_trailing_slash = (substr($uri, -1) === '/');

            $is_trailing_slash_enabled = $this->getAppConfig()->isTrailingSlashEnabled();

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

        // Processing page cache
        $this->processIFaceCache($iface);

        $output = $iface->render();

        // Final hook
        $iface->after();

        if ($unusedParts = $params->getUnusedQueryPartsKeys()) {
            throw new HTTP_Exception_400('Request have unused query parts: :keys', [
                ':keys' => implode(', ', $unusedParts),
            ]);
        }

        $this->last_modified($iface->getLastModified());
        $this->expires($iface->getExpiresDateTime());

        $this->send_string($output);
    }

    private function processIFaceCache(IFaceInterface $iface): void
    {
        // Skip caching if request method is not GET nor HEAD
        if (!in_array($this->request->method(), ['GET', 'HEAD'], true)) {
            return;
        }

        $user = $this->getCurrentUser();

        // Skip caching for authorized users
        if (!$user->isGuest()) {
            return;
        }

        /** @var IFaceCache $cache */
        $cache = Container::getInstance()->get(IFaceCache::class);
        $cache->process($iface);
    }

    /**
     * @return \BetaKiller\Config\AppConfigInterface
     */
    private function getAppConfig(): AppConfigInterface
    {
        return Container::getInstance()->get(AppConfigInterface::class);
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    private function getCurrentUser(): UserInterface
    {
        return Container::getInstance()->get(UserInterface::class);
    }

    /**
     * @return string
     */
    private function getRequestUri(): string
    {
        return $this->request->uri();
    }

    /**
     * @return string[]
     */
    private function getRequestQueryParts(): array
    {
        return $this->request->query();
    }
}
