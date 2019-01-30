<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\HitDomain;
use BetaKiller\Model\HitLink;
use BetaKiller\Model\HitMarker;
use BetaKiller\Model\HitMarkerInterface;
use BetaKiller\Model\HitPage;
use BetaKiller\Model\HitPageRedirect;
use BetaKiller\Model\HitPageRedirectInterface;
use BetaKiller\Repository\HitDomainRepository;
use BetaKiller\Repository\HitLinkRepository;
use BetaKiller\Repository\HitMarkerRepository;
use BetaKiller\Repository\HitPageRedirectRepository;
use BetaKiller\Repository\HitPageRepository;
use Psr\Http\Message\ServerRequestInterface;

class HitService
{
    public const UTM_SOURCE   = 'utm_source';
    public const UTM_MEDIUM   = 'utm_medium';
    public const UTM_CAMPAIGN = 'utm_campaign';
    public const UTM_CONTENT  = 'utm_content';
    public const UTM_TERM     = 'utm_term';

    public const UTM_KEYS = [
        self::UTM_SOURCE,
        self::UTM_MEDIUM,
        self::UTM_CAMPAIGN,
        self::UTM_CONTENT,
        self::UTM_TERM,
    ];

    /**
     * @var \BetaKiller\Repository\HitDomainRepository
     */
    private $domainRepo;

    /**
     * @var \BetaKiller\Repository\HitPageRepository
     */
    private $pageRepo;

    /**
     * @var \BetaKiller\Repository\HitLinkRepository
     */
    private $linkRepo;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

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
     * @param \BetaKiller\Repository\HitPageRepository         $pageRepo
     * @param \BetaKiller\Repository\HitDomainRepository       $domainRepo
     * @param \BetaKiller\Repository\HitLinkRepository         $linkRepo
     * @param \BetaKiller\Repository\HitMarkerRepository       $markerRepo
     * @param \BetaKiller\Repository\HitPageRedirectRepository $redirectRepo
     * @param \BetaKiller\Config\AppConfigInterface            $appConfig
     */
    public function __construct(
        HitPageRepository $pageRepo,
        HitDomainRepository $domainRepo,
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
     * @param string    $url
     * @param bool|null $createMissing
     *
     * @return \BetaKiller\Model\HitPage
     * @throws \BetaKiller\Repository\RepositoryException
     */
    public function getPageByFullUrl(string $url, ?bool $createMissing = null): HitPage
    {
        $createMissing = $createMissing ?? true;

        // External documents are addressed by path + query (no fragment)
        $domainName = parse_url($url, PHP_URL_HOST);

        if (!$domainName) {
            throw new ServiceException('Can not detect domain name in URL :url', [':url' => $url]);
        }

        $relativeUrl = explode($domainName, $url, 2)[1];

        // Find domain first
        $domain = $this->domainRepo->getByName($domainName);

        if (!$domain && $createMissing) {
            $domain = $this->createDomain($domainName);
        }

        if (!$domain) {
            throw new ServiceException('Can not find domain for URL :url', [':url' => $url]);
        }

        // Search for page in selected domain
        $page = $this->pageRepo->findByUri($domain, $relativeUrl);

        if (!$page && $createMissing) {
            $page = $this->createPage($domain, $relativeUrl);
        }

        if (!$page) {
            throw new ServiceException('Can not find page for URL :url', [':url' => $url]);
        }

        return $page;
    }

    public function getLinkBySourceAndTarget(HitPage $source, HitPage $target, ?bool $createMissing = null): HitLink
    {
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

    public function getMarkerFromRequest(ServerRequestInterface $request): ?HitMarkerInterface
    {
        $params = ServerRequestHelper::getUrlContainer($request);

        // Fetch UTM tags if exists
        $source   = $params->getQueryPart(self::UTM_SOURCE);
        $medium   = $params->getQueryPart(self::UTM_MEDIUM);
        $campaign = $params->getQueryPart(self::UTM_CAMPAIGN);
        $content  = $params->getQueryPart(self::UTM_CONTENT);
        $term     = $params->getQueryPart(self::UTM_TERM);

        if ($source && $medium && $campaign) {
            $marker = $this->markerRepo->find($source, $medium, $campaign, $content, $term);

            if (!$marker) {
                $marker = $this->createMarker($source, $medium, $campaign, $content, $term);
            }

            return $marker;
        }

        return null;
    }

    public function createDomain(string $domainName): HitDomain
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

    public function createPage(HitDomain $domain, string $relativeUrl): HitPage
    {
        $now = new \DateTimeImmutable;

        $page = new HitPage;

        $page
            ->setUri($relativeUrl)
            ->setDomain($domain)
            ->setFirstSeenAt($now)
            ->setLastSeenAt($now);

        $this->pageRepo->save($page);

        return $page;
    }

    public function createLink(HitPage $source, HitPage $target): HitLink
    {
        $now = new \DateTimeImmutable;

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
        string $source,
        string $medium,
        string $campaign,
        ?string $content,
        ?string $term
    ): ?HitMarkerInterface {
        $marker = new HitMarker;

        $marker
            ->setSource($source)
            ->setMedium($medium)
            ->setCampaign($campaign);

        if ($term) {
            $marker->setTerm($term);
        }

        if ($content) {
            $marker->setContent($content);
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
