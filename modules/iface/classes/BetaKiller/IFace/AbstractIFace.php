<?php
namespace BetaKiller\IFace;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Url\UrlDataSourceInterface;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlContainerInterface;
use DateInterval;
use DateTimeInterface;
use Text;
use URL;

abstract class AbstractIFace implements IFaceInterface
{
    /**
     * @var IFaceModelInterface
     */
    private $faceModel;

    /**
     * @var IFaceInterface|null Parent iface
     */
    private $parent;

    /**
     * @var DateTimeInterface|null
     */
    private $lastModified;

    /**
     * @var DateInterval|null
     */
    private $expiresInterval;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    protected $ifaceHelper;

    /**
     * @Inject
     * @var \BetaKiller\Helper\AclHelper
     */
    private $aclHelper;

    /**
     * @Inject
     * @var AppConfigInterface
     */
    private $appConfig;

    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @Inject
     * @var \BetaKiller\IFace\Url\UrlPrototypeHelper
     */
    private $prototypeHelper;

    public function __construct()
    {
        // Empty by default
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->getModel()->getCodename();
    }

    /**
     * @return string
     */
    public function render(): string
    {
        return $this->ifaceHelper->renderIFace($this);
    }

    public function getLayoutCodename(): string
    {
        if ($zone = $this->getModel()->getLayoutCodename()) {
            return $zone;
        }

        $parent = $this->getParent();

        if (!$parent) {
            throw new IFaceException('Can not detect layout codename for iface :codename', [':codename' => $this->getCodename()]);
        }

        return $parent->getLayoutCodename();
    }

    /**
     * Returns processed label
     *
     * @param UrlContainerInterface|null $params
     *
     * @return string
     */
    public function getLabel(UrlContainerInterface $params = null): string
    {
        return $this->processStringPattern($this->getLabelSource(), null, $params) ?: '';
    }

    /**
     * Returns processed title
     *
     * @return string
     */
    public function getTitle(): ?string
    {
        return $this->processStringPattern($this->getTitleSource(), 80); // Limit to 80 chars
    }

    /**
     * Returns processed description
     *
     * @return string
     */
    public function getDescription(): ?string
    {
        return $this->processStringPattern($this->getDescriptionSource());
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return $this
     */
    public function setTitle(string $value)
    {
        $this->getModel()->setTitle($value);

        return $this;
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return $this
     */
    public function setDescription(string $value)
    {
        $this->getModel()->setDescription($value);

        return $this;
    }

    /**
     * Pattern consists of tags like [N[Text]] where N is tag priority
     *
     * @param string                     $source
     * @param int|NULL                   $limit
     * @param UrlContainerInterface|null $params
     *
     * @todo maybe extract to another helper class
     *
     * @return string
     */
    private function processStringPattern(?string $source, ?int $limit = null, UrlContainerInterface $params = null): ?string
    {
        if (!$source) {
            return null;
        }

        // Replace url parameters
        $source = $this->prototypeHelper->replaceUrlParametersParts($source, $params);

        // Parse [N[...]] tags
        $pcre_pattern = '/\[([\d]{1,2})\[([^\]]+)\]\]/';

        /** @var array[] $matches */
        preg_match_all($pcre_pattern, $source, $matches, PREG_SET_ORDER);

        $tags = [];

        foreach ($matches as list($key, $priority, $value)) {
            $tags[$priority] = [
                'key'   => $key,
                'value' => $value,
            ];
        }

        $output = $source;

        if ($tags) {
            // Sort tags via keys in straight order
            ksort($tags);

            // Iteration counter
            $i         = 0;
            $max_loops = count($tags);

            while ($i < $max_loops && mb_strlen($output) > 0) {
                $output = $source;

                // Replace tags
                foreach ($tags as $tag) {
                    $output = str_replace($tag['key'], $tag['value'], $output);
                }

                if ($limit && mb_strlen($output) > $limit) {
                    $drop   = array_pop($tags);
                    $source = trim(str_replace($drop['key'], '', $source));
                    $i++;
                } else {
                    break;
                }
            }
        }

        if ($limit && mb_strlen($output) > $limit) {
            // Dirty limit
            Text::limit_chars($output, $limit, null, true);
        }

        return $output;
    }

    /**
     * Returns label source/pattern
     *
     * @return string
     */
    public function getLabelSource(): string
    {
        return $this->getModel()->getLabel();
    }

    /**
     * Returns title source/pattern
     *
     * @return string
     */
    public function getTitleSource(): ?string
    {
        return $this->getModel()->getTitle();
    }

    /**
     * Returns description source/pattern
     *
     * @return string
     */
    public function getDescriptionSource(): ?string
    {
        return $this->getModel()->getDescription();
    }

    /**
     * @param \DateTimeInterface $last_modified
     *
     * @return $this
     */
    public function setLastModified(\DateTimeInterface $last_modified)
    {
        $this->lastModified = $last_modified;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastModified(): DateTimeInterface
    {
        return $this->lastModified ?: $this->getDefaultLastModified();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDefaultLastModified(): DateTimeInterface
    {
        return new \DateTime();
    }

    /**
     * @return DateInterval
     */
    public function getDefaultExpiresInterval(): DateInterval
    {
        return new \DateInterval('PT1H');
    }

    /**
     * @param \DateInterval|NULL $expires
     *
     * @return $this
     */
    public function setExpiresInterval(DateInterval $expires)
    {
        $this->expiresInterval = $expires;

        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getExpiresInterval(): DateInterval
    {
        return $this->expiresInterval ?: $this->getDefaultExpiresInterval();
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiresDateTime(): DateTimeInterface
    {
        return (new \DateTime())->add($this->getExpiresInterval());
    }

    /**
     * @return int
     */
    public function getExpiresSeconds(): int
    {
        $reference = new \DateTimeImmutable;
        $endTime   = $reference->add($this->getExpiresInterval());

        return $endTime->getTimestamp() - $reference->getTimestamp();
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before(): void
    {
        // Empty by default
    }

    /**
     * This hook executed after real IFace processing only (on every request if IFace output was not cached)
     * Place here the code that needs to be executed only after real IFace processing (collect performance stat, etc)
     */
    public function after(): void
    {
        // Empty by default
    }

    public function __toString(): string
    {
        return (string)$this->render();
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function getParent(): ?IFaceInterface
    {
        if (!$this->parent) {
            $this->parent = $this->ifaceProvider->getParent($this);
        }

        return $this->parent;
    }

    public function setParent(IFaceInterface $parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface[]
     */
    public function getChildren(): array
    {
        return $this->ifaceProvider->getChildren($this);
    }

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    public function getModel(): IFaceModelInterface
    {
        return $this->faceModel;
    }

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     *
     * @return $this
     */
    public function setModel(IFaceModelInterface $model)
    {
        $this->faceModel = $model;

        return $this;
    }

    public function isDefault(): bool
    {
        return $this->getModel()->isDefault();
    }

    public function isInStack(): bool
    {
        return $this->ifaceHelper->isInStack($this);
    }

    public function isCurrent(UrlContainerInterface $parameters = null): bool
    {
        return $this->ifaceHelper->isCurrentIFace($this, $parameters);
    }

    /**
     * Returns model name of the linked entity
     *
     * @return string
     */
    public function getEntityModelName(): ?string
    {
        return $this->getModel()->getEntityModelName();
    }

    /**
     * Returns entity [primary] action, applied by this IFace
     *
     * @return string
     */
    public function getEntityActionName(): ?string
    {
        return $this->getModel()->getEntityActionName();
    }

    /**
     * Returns zone codename where this IFace is placed
     * Inherits zone from parent iface
     *
     * @return string
     */
    public function getZoneName(): string
    {
        if ($zone = $this->getModel()->getZoneName()) {
            return $zone;
        }

        $parent = $this->getParent();

        if (!$parent) {
            throw new IFaceException('Can not detect zone for iface :codename', [':codename' => $this->getCodename()]);
        }

        return $parent->getZoneName();
    }

    /**
     * Returns array of additional ACL rules in format <ResourceName>.<permissionName> (eq, ["Admin.enabled"])
     *
     * @return string[]
     */
    public function getAdditionalAclRules(): array
    {
        return $this->getModel()->getAdditionalAclRules();
    }

    public function url(UrlContainerInterface $parameters = null, ?bool $removeCyclingLinks = null, ?bool $withDomain = null): string
    {
        $removeCyclingLinks = $removeCyclingLinks ?? true;
        $withDomain = $withDomain ?? true;

        if ($removeCyclingLinks && $this->isCurrent($parameters)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts   = [];
        $current = $this;

        $parent = null;

        do {
            $uri = $current->makeUri($parameters);

            if (!$uri) {
                throw new IFaceException('Can not make URI for :codename IFace', [':codename' => $current->getCodename()]);
            }

            if ($uri === UrlDispatcher::DEFAULT_URI && $this->isDefault()) {
                $uri = null;
            }

            $parts[] = $uri;
            $parent  = $current->getParent();
            $current = $parent;
        } while ($parent);

        $path = '/'.implode('/', array_reverse($parts));

        if ($this->appConfig->isTrailingSlashEnabled()) {
            // Add trailing slash before query parameters
            $split = explode('?', $path, 2);
            $split[0] .= '/';
            $path = implode('?', $split);
        }

        return $withDomain ? URL::site($path, true) : $path;
    }

    private function makeUri(UrlContainerInterface $parameters = null): string
    {
        // Allow IFace to add custom url generating logic
        $uri = $this->getUri();
        $model = $this->getModel();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [':codename' => $this->getCodename()]);
        }

        // Static IFaces has raw uri value
        if (!$model->hasDynamicUrl()) {
            return $uri;
        }

        return $this->prototypeHelper->getCompiledPrototypeValue($uri, $parameters, $model->hasTreeBehaviour());
    }

    public function getUri(): string
    {
        return $this->getModel()->getUri();
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     * @param int|null                                    $limit
     *
     * @return string[]
     */
    public function getPublicAvailableUrls(UrlContainerInterface $params, ?int $limit = null): array
    {
        if (!$this->getModel()->hasDynamicUrl()) {
            // Make static URL
            return [$this->makeAvailableUrl($params)];
        }

        return $this->getDynamicModelAvailableUrls($params, $limit);
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlContainerInterface $params
     * @param int|null                                    $limit
     *
     * @return string[]
     */
    private function getDynamicModelAvailableUrls(UrlContainerInterface $params, ?int $limit = null): array
    {
        $prototype  = $this->prototypeHelper->fromIFaceUri($this);
        $dataSource = $this->prototypeHelper->getDataSourceInstance($prototype);

        $urlsBlocks = $this->getDataSourceAvailableUrls($dataSource, $prototype->getModelKey(), $params, $limit);

        // Empty $urlBlocks leads array_merge() to return null
        return array_filter($urlsBlocks ? array_merge(...$urlsBlocks) : []);
    }

    private function getDataSourceAvailableUrls(UrlDataSourceInterface $dataSource, string $key, UrlContainerInterface $params, ?int $limit = null): array
    {
        $items = $dataSource->getItemsByUrlKey($key, $params, $limit);
        $urlsBlocks  = [];

        foreach ($items as $item) {
            // Save current item to parameters registry
            $params->setParameter($item, true);

            // Make dynamic URL
            $urlsBlocks[] = [$this->makeAvailableUrl($params)];

            // Recursion for trees
            if ($this->getModel()->hasTreeBehaviour()) {
                // Recursion for tree behaviour
                $urlsBlocks[] = $this->getDataSourceAvailableUrls($dataSource, $key, $params, $limit);
            }
        }

        return $urlsBlocks;
    }

    private function makeAvailableUrl(UrlContainerInterface $params = null): string
    {
        if (!$this->aclHelper->isIFaceAllowed($this, $params)) {
            return null;
        }

        return $this->url($params, false); // Disable cycling links removing
    }
}
