<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\DI\Container;
use BetaKiller\IFace\IFaceInterface;
use BetaKiller\IFace\Cache\IFaceCache;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Model\UserInterface;

/**
 * Class Controller_IFace
 * @todo Refactoring to ControllerIFace + KohanaRequestAdapter/KohanaResponseAdapter
 */
class Controller_IFace extends Controller
{
    public function action_render()
    {
        $uri = $this->get_request_uri();

        /** @var UrlDispatcher $dispatcher */
        $dispatcher = Container::getInstance()->get(UrlDispatcher::class);

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

        $this->last_modified($iface->getLastModified());
        $this->expires($iface->getExpiresDateTime());

        $this->send_string($output);
    }

    private function processIFaceCache(IFaceInterface $iface)
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
    private function getAppConfig()
    {
        return Container::getInstance()->get(AppConfigInterface::class);
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    private function getCurrentUser()
    {
        return Container::getInstance()->get(UserInterface::class);
    }

    /**
     * @return string
     */
    private function get_request_uri()
    {
        return $this->request->uri();
    }
}
