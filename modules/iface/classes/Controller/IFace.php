<?php defined('SYSPATH') OR die('No direct script access.');

use \BetaKiller\IFace\IFaceInterface;
use \BetaKiller\IFace\Cache\IFaceCache;

class Controller_IFace extends Controller
{
    use \BetaKiller\Helper\CurrentUserTrait;
    use \BetaKiller\Helper\IFaceTrait;

    public function action_render()
    {
        $uri = $this->get_request_uri();

        $dispatcher = $this->url_dispatcher();

        // Getting current IFace
        $iface = $dispatcher->parse_uri($uri);

        // If this is default IFace and client requested non-slash uri, redirect client to /
        if ( $uri !== '/' && $iface->isDefault() ) {
            $this->redirect('/');
        }

        if ($uri && $uri !== '/') {
            $has_trailing_slash = (substr($uri, -1, 1) === '/');

            $is_trailing_slash_enabled = $iface->isTrailingSlashEnabled();

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

    protected function processIFaceCache(IFaceInterface $iface)
    {
        // Skip caching if request method is not GET nor HEAD
        if (!in_array($this->request->method(), ['GET', 'HEAD'], true)) {
            return;
        }

        // Skip caching for authorized users
        if ($this->current_user(true)) {
            return;
        }

        /** @var IFaceCache $cache */
        $cache = \BetaKiller\DI\Container::instance()->get(IFaceCache::class);
        $cache->process($iface);
    }

    /**
     * @return string
     */
    protected function get_request_uri()
    {
        return $this->request->uri();
    }
}
