<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use BetaKiller\Helper\LoggerHelperTrait;
use BetaKiller\Model\DispatchableEntityInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UrlParameterInterface;
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\CacheInterface;

class UrlDispatcherCacheWrapper implements UrlDispatcherInterface
{
    use LoggerHelperTrait;

    public const CACHE_TTL = 86400; // 1 day

    /**
     * @var \BetaKiller\Url\UrlDispatcherInterface
     */
    private $proxy;

    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * UrlDispatcherCacheWrapper constructor.
     *
     * @param \BetaKiller\Url\UrlDispatcherInterface  $proxy
     * @param \Psr\SimpleCache\CacheInterface         $cache
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     * @param \Psr\Log\LoggerInterface                $logger
     */
    public function __construct(
        UrlDispatcherInterface $proxy,
        CacheInterface $cache,
        UrlElementTreeInterface $tree,
        LoggerInterface $logger
    ) {
        $this->proxy  = $proxy;
        $this->cache  = $cache;
        $this->tree   = $tree;
        $this->logger = $logger;
    }

    /**
     * @param string                                          $uri
     *
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     *
     * @return void
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function process(string $uri, UrlElementStack $stack, UrlContainerInterface $params): void
    {
        $cacheKey = $this->getUrlCacheKey($uri);

        // Check cache for stack and url params for current URL
        if (!$this->restoreDataFromCache($cacheKey, $stack, $params)) {
            $this->proxy->process($uri, $stack, $params);

            // Cache stack + url parameters (between HTTP requests) for current URL
            $this->storeDataInCache($cacheKey, $params, $stack);
        }
    }

    /**
     * @param string                                          $cacheKey
     *
     * @param \BetaKiller\Url\Container\UrlContainerInterface $params
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function storeDataInCache(string $cacheKey, UrlContainerInterface $params, UrlElementStack $stack): bool
    {
        $stackData  = $stack->getCodenames();
        $paramsData = $params->getAllParameters();

        foreach ($paramsData as $param) {
            if (!$this->isParameterSerializable($param)) {
                $this->logger->debug('Skip caching non-serializable parameter');

                return false;
            }
        }

        $this->cache->set($cacheKey, serialize([
            'stack'      => $stackData,
            'parameters' => $paramsData,
        ]), self::CACHE_TTL);

        return true;
    }

    /**
     * @param string                                          $cacheKey
     * @param \BetaKiller\Url\UrlElementStack                 $stack
     * @param \BetaKiller\Url\Container\UrlContainerInterface $urlParameters
     *
     * @return bool
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    private function restoreDataFromCache(
        string $cacheKey,
        UrlElementStack $stack,
        UrlContainerInterface $urlParameters
    ): bool {
        $serializedData = $this->cache->get($cacheKey);

        if (!$serializedData) {
            return false;
        }

        $data = unserialize($serializedData, [
            UrlElementInterface::class,
            DispatchableEntityInterface::class,
        ]);

        if (!$data || !\is_array($data)) {
            // Log and keep processing as no cache was found
            $this->logger->warning('Cached UrlDispatcher data is incorrect', ['cachedData' => print_r($data, true)]);

            return false;
        }

        /** @var array $stackData */
        $stackData = $data['stack'];

        /** @var \BetaKiller\Url\Parameter\UrlParameterInterface[] $paramsData */
        $paramsData = $data['parameters'];

        try {
            // Restore url parameters first so iface access can be checked
            foreach ($paramsData as $key => $value) {
                if (!($value instanceof UrlParameterInterface)) {
                    throw new UrlDispatcherException('Cached data for url parameters is incorrect');
                }

                if (!$this->isParameterSerializable($value)) {
                    $this->logger->debug('Skip unpacking data from non-serializable parameter');

                    return false;
                }

                $urlParameters->setParameter($value);
            }

            // Restore ifaces and push them into stack (with access check)
            foreach ($stackData as $ifaceCodename) {
                $elementModel = $this->tree->getByCodename($ifaceCodename);
                $stack->push($elementModel);
            }

            return true;
        } catch (\Throwable $e) {
            // Log and keep processing as no cache was found
            $this->logException($this->logger, $e, 'Error on unpacking UrlDispatcher data');

            // Wipe the cached data to prevent errors
            $this->cache->delete($cacheKey);
        }

        return false;
    }

    private function isParameterSerializable(UrlParameterInterface $param): bool
    {
        // TODO Deal with this (remove or refactor to using some kind of interface)
        return (bool)$param;
    }

    private function getUrlCacheKey(string $url): string
    {
        return 'urlDispatcher.'.sha1($url);
    }
}
