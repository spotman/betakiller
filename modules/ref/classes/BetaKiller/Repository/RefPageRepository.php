<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\RefDomain;
use BetaKiller\Model\RefPage;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class RefPageRepository
 *
 * @package BetaKiller\Repository
 * @method RefPage findById(int $id)
 * @method RefPage create()
 * @method RefPage[] getAll()
 */
class RefPageRepository extends AbstractOrmBasedRepository
{
    /**
     * @Inject
     * @var \BetaKiller\Repository\RefDomainRepository
     */
    private $domainRepository;

    /**
     * @Inject
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * @param string    $url
     * @param bool|null $createMissing
     *
     * @return \BetaKiller\Model\RefPage
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function findByFullUrl(string $url, ?bool $createMissing = null): RefPage
    {
        $createMissing = $createMissing ?? true;

        // Get current domain
        $siteUrl    = $this->appConfig->getBaseUrl();
        $siteDomain = parse_url($siteUrl, PHP_URL_HOST);

        // External documents are addressed by path + query (no fragment)
        $domainName = parse_url($url, PHP_URL_HOST);

        if (!$domainName) {
            throw new RepositoryException('Can not detect domain name in URL :url', [':url' => $url]);
        }

        $urlPart = explode($domainName, $url)[1];

        // Find domain first
        $domain = $this->domainRepository->getByName($domainName);

        if (!$domain && $createMissing) {
            $domain = $this->domainRepository->create()
                ->setName($domainName);

            if ($domainName === $siteDomain) {
                // Internal hit
                $domain->markAsInternal();
            } else {
                // External hit
                $domain->markAsExternal();
            }

            $this->domainRepository->save($domain);
        }

        if (!$domain) {
            throw new RepositoryException('Can not create domain instance for URL :url', [':url' => $url]);
        }

        // Search for page in selected domain
        $page = $this->getByUri($domain, $urlPart);

        if (!$page && $createMissing) {
            $page = $this->create()
                ->setUri($urlPart)
                ->setDomain($domain);

            $this->save($page);
        }

        if (!$page) {
            throw new RepositoryException('Can not create page instance for URL :url', [':url' => $url]);
        }

        return $page;
    }

    public function getByUri(RefDomain $domain, string $uri): ?RefPage
    {
        $orm = $this->getOrmInstance();

        $this->filterDomain($orm, $domain);
        $model = $orm->where('uri', '=', $uri)->find();

        return $model->loaded() ? $model : null;
    }

    private function filterDomain(OrmInterface $orm, RefDomain $domain): RefPageRepository
    {
        $orm->where('domain.id', '=', $domain->getID());

        return $this;
    }
}
