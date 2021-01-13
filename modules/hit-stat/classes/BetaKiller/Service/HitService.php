<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Model\HitDomain;
use BetaKiller\Model\HitDomainInterface;
use BetaKiller\Model\HitLink;
use BetaKiller\Model\HitMarker;
use BetaKiller\Model\HitMarkerInterface;
use BetaKiller\Model\HitPage;
use BetaKiller\Model\HitPageInterface;
use BetaKiller\Model\HitPageRedirect;
use BetaKiller\Model\HitPageRedirectInterface;
use BetaKiller\Repository\HitDomainRepositoryInterface;
use BetaKiller\Repository\HitLinkRepository;
use BetaKiller\Repository\HitMarkerRepository;
use BetaKiller\Repository\HitPageRedirectRepository;
use BetaKiller\Repository\HitPageRepositoryInterface;
use BetaKiller\Url\Container\UrlContainerInterface;
use BetaKiller\Url\Parameter\UtmCampaignUrlParameter;
use BetaKiller\Url\Parameter\UtmContentUrlParameter;
use BetaKiller\Url\Parameter\UtmMediumUrlParameter;
use BetaKiller\Url\Parameter\UtmSourceUrlParameter;
use BetaKiller\Url\Parameter\UtmTermUrlParameter;
use DateTimeImmutable;
use Psr\Http\Message\UriInterface;

class HitService
{
    public const UTM_QUERY_KEYS = [
        UtmSourceUrlParameter::QUERY_KEY,
        UtmMediumUrlParameter::QUERY_KEY,
        UtmCampaignUrlParameter::QUERY_KEY,
        UtmContentUrlParameter::QUERY_KEY,
        UtmTermUrlParameter::QUERY_KEY,
    ];

    /**
     * @var \BetaKiller\Repository\HitDomainRepositoryInterface
     */
    private HitDomainRepositoryInterface $domainRepo;

    /**
     * @var \BetaKiller\Repository\HitPageRepositoryInterface
     */
    private HitPageRepositoryInterface $pageRepo;

    /**
     * @var \BetaKiller\Repository\HitLinkRepository
     */
    private $linkRepo;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private AppConfigInterface $appConfig;

    /**
     * @var \BetaKiller\Repository\HitMarkerRepository
     */
    private $markerRepo;

    /**
     * @var \BetaKiller\Repository\HitPageRedirectRepository
     */
    private $redirectRepo;

    /**
     * HitService constructor.
     *
     * @param \BetaKiller\Repository\HitPageRepositoryInterface   $pageRepo
     * @param \BetaKiller\Repository\HitDomainRepositoryInterface $domainRepo
     * @param \BetaKiller\Repository\HitLinkRepository            $linkRepo
     * @param \BetaKiller\Repository\HitMarkerRepository          $markerRepo
     * @param \BetaKiller\Repository\HitPageRedirectRepository    $redirectRepo
     * @param \BetaKiller\Config\AppConfigInterface               $appConfig
     */
    public function __construct(
        HitPageRepositoryInterface $pageRepo,
        HitDomainRepositoryInterface $domainRepo,
        HitLinkRepository $linkRepo,
        HitMarkerRepository $markerRepo,
        HitPageRedirectRepository $redirectRepo,
        AppConfigInterface $appConfig
    ) {
        $this->domainRepo   = $domainRepo;
        $this->pageRepo     = $pageRepo;
        $this->linkRepo     = $linkRepo;
        $this->appConfig    = $appConfig;
        $this->markerRepo   = $markerRepo;
        $this->redirectRepo = $redirectRepo;
    }

    /**
     * @param \Psr\Http\Message\UriInterface $uri
     * @param bool|null                      $createMissing
     *
     * @return \BetaKiller\Model\HitPage
     * @throws \BetaKiller\Exception\ValidationException
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Service\ServiceException
     */
    public function getPageByFullUrl(UriInterface $uri, ?bool $createMissing = null): HitPage
    {
        $createMissing = $createMissing ?? true;

        // External documents are addressed by path + query (no fragment)
        $domainName = $uri->getHost();

        if (!$domainName) {
            throw new ServiceException('Can not detect domain name in URL :url', [':url' => (string)$uri]);
        }

        $relativeUrl = $uri->getPath();

        // Find domain first
        $domain = $this->domainRepo->getByName($domainName);

        if (!$domain && $createMissing) {
            $domain = $this->createDomain($domainName);
        }

        if (!$domain) {
            throw new ServiceException('Can not find domain for URL :url', [':url' => (string)$uri]);
        }

        // Search for page in selected domain
        $page = $this->pageRepo->findByUri($domain, $relativeUrl);

        if (!$page && $createMissing) {
            $page = $this->createPage($domain, $relativeUrl);
        }

        if (!$page) {
            throw new ServiceException('Can not find page for URL :url', [':url' => (string)$uri]);
        }

        return $page;
    }

    public function getLinkBySourceAndTarget(
        HitPageInterface $source,
        HitPageInterface $target,
        ?bool $createMissing = null
    ): HitLink {
        $createMissing = $createMissing ?? true;

        $link = $this->linkRepo->findBySourceAndTarget($source, $target);

        if (!$link && $createMissing) {
            $link = $this->createLink($source, $target);
        }

        if (!$link) {
            throw new ServiceException('Can not find link for ":source" => ":target"', [
                ':source' => $source->getID(),
                ':target' => $target->getID(),
            ]);
        }

        return $link;
    }

    /**
     * @param string $url
     *
     * @return \BetaKiller\Model\HitPageRedirectInterface
     * @throws \BetaKiller\Repository\RepositoryException
     * @throws \BetaKiller\Exception\ValidationException
     */
    public function getPageRedirectByUrl(string $url): HitPageRedirectInterface
    {
        $model = $this->redirectRepo->findByUrl($url);

        if (!$model) {
            $model = $this->createPageRedirect($url);
        }

        return $model;
    }

    public function getMarkerFromUrlContainer(UrlContainerInterface $params): ?HitMarkerInterface
    {
        // Fetch UTM tags if exists
        $source   = $params->getQueryPart(UtmSourceUrlParameter::QUERY_KEY);
        $medium   = $params->getQueryPart(UtmMediumUrlParameter::QUERY_KEY);
        $campaign = $params->getQueryPart(UtmCampaignUrlParameter::QUERY_KEY);
        $content  = $params->getQueryPart(UtmContentUrlParameter::QUERY_KEY);
        $term     = $params->getQueryPart(UtmTermUrlParameter::QUERY_KEY);

        if (!$source && !$medium && !$campaign && !$content && !$term) {
            return null;
        }

        $marker = $this->markerRepo->find($source, $medium, $campaign, $content, $term);

        if (!$marker) {
            $marker = $this->createMarker($source, $medium, $campaign, $content, $term);
        }

        return $marker;
    }

    public function createDomain(string $domainName): HitDomainInterface
    {
        $domain = new HitDomain;

        $domain->setName($domainName);

        // Get current domain
        $siteDomain = $this->appConfig->getBaseUri()->getHost();

        if ($domainName === $siteDomain) {
            // Internal hit
            $domain->markAsInternal();
        } else {
            // External hit
            $domain->markAsExternal();
        }

        $this->domainRepo->save($domain);

        return $domain;
    }

    public function createPage(HitDomainInterface $domain, string $relativeUrl): HitPage
    {
        $now = new DateTimeImmutable;

        $page = new HitPage;

        $page
            ->setUri($relativeUrl)
            ->setDomain($domain)
            ->setFirstSeenAt($now)
            ->setLastSeenAt($now);

        $this->pageRepo->save($page);

        return $page;
    }

    public function createLink(HitPageInterface $source, HitPageInterface $target): HitLink
    {
        $now = new DateTimeImmutable;

        $page = new HitLink;

        $page
            ->setSource($source)
            ->setTarget($target)
            ->setFirstSeenAt($now)
            ->setLastSeenAt($now);

        $this->linkRepo->save($page);

        return $page;
    }

    public function createMarker(
        ?string $source,
        ?string $medium,
        ?string $campaign,
        ?string $content,
        ?string $term
    ): ?HitMarkerInterface {
        $marker = new HitMarker;

        if ($source) {
            $marker->setSource($source);
        }

        if ($medium) {
            $marker->setMedium($medium);
        }

        if ($campaign) {
            $marker->setCampaign($campaign);
        }

        if ($content) {
            $marker->setContent($content);
        }

        if ($term) {
            $marker->setTerm($term);
        }

        $this->markerRepo->save($marker);

        return $marker;
    }

    public function createPageRedirect(string $url): HitPageRedirectInterface
    {
        $model = new HitPageRedirect();

        $model->setUrl($url);

        // Store fresh model
        $this->redirectRepo->save($model);

        return $model;
    }
}
