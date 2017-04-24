<?php
namespace BetaKiller\IFace;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\Url\UrlParameters;
use BetaKiller\IFace\Url\UrlParametersInterface;
use BetaKiller\IFace\View\IFaceView;
use DateInterval;
use DateTime;
use Text;
use URL;

abstract class AbstractIFace implements IFaceInterface
{
    /**
     * @var IFaceModelInterface
     */
    protected $faceModel;

    /**
     * @var IFaceInterface Parent iface
     */
    protected $parent;

    /**
     * @var DateTime
     */
    protected $lastModified;

    /**
     * @var DateInterval
     */
    protected $expiresInterval;

    /**
     * @Inject
     * @var AppConfigInterface
     */
    private $appConfig;

    /**
     * @Inject
     * @var \BetaKiller\IFace\Url\UrlDispatcher
     */
    private $urlDispatcher;

    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceStack
     */
    private $ifaceStack;

    /**
     * @Inject
     * @var \BetaKiller\IFace\IFaceProvider
     */
    private $ifaceProvider;

    /**
     * @var IFaceView
     * @Inject
     */
    private $ifaceView;

    /**
     * @Inject
     * @var \BetaKiller\IFace\Url\UrlPrototypeHelper
     */
    private $prototypeHelper;

    /**
     * Returns URL query parts array for current HTTP request
     *
     * @todo Remove and replace in client code with RequestHelper
     * @deprecated
     *
     * @param $key
     *
     * @return array
     */
    abstract protected function getUrlQuery($key = null);

    public function __construct()
    {
        // Empty by default
    }

    /**
     * @return string
     */
    public function getCodename()
    {
        return $this->getModel()->getCodename();
    }

    /**
     * @return string
     */
    public function render()
    {
        // Getting IFace View instance and rendering
        return $this->ifaceView->render($this);
    }

    public function getLayoutCodename()
    {
        return $this->getModel()->getLayoutCodename();
    }

    /**
     * Returns processed label
     *
     * @param UrlParameters|null $params
     *
     * @return mixed
     */
    public function getLabel(UrlParametersInterface $params = null)
    {
        return $this->processStringPattern($this->getLabelSource(), null, $params);
    }

    /**
     * Returns processed title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->processStringPattern($this->getTitleSource(), 80); // Limit to 80 chars
    }

    /**
     * Returns processed description
     *
     * @return string
     */
    public function getDescription()
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
    public function setTitle($value)
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
    public function setDescription($value)
    {
        $this->getModel()->setDescription($value);

        return $this;
    }

    /**
     * Pattern consists of tags like [N[Text]] where N is tag priority
     *
     * @param string                      $source
     * @param int|NULL                    $limit
     * @param UrlParametersInterface|null $params
     *
     * @todo maybe extract to another helper class
     *
     * @return string
     */
    private function processStringPattern($source, $limit = null, UrlParametersInterface $params = null)
    {
        // Replace url parameters
        $source = $this->prototypeHelper->replaceUrlParametersParts($source, $params);

        // Parse [N[...]] tags
        $pcre_pattern = '/\[([0-9]{1,2})\[([^\]]+)\]\]/';

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
    public function getLabelSource()
    {
        return $this->getModel()->getLabel();
    }

    /**
     * Returns title source/pattern
     *
     * @return string
     */
    public function getTitleSource()
    {
        return $this->getModel()->getTitle();
    }

    /**
     * Returns description source/pattern
     *
     * @return string
     */
    public function getDescriptionSource()
    {
        return $this->getModel()->getDescription();
    }

    /**
     * @param \DateTime|NULL $last_modified
     *
     * @return $this
     */
    public function setLastModified(DateTime $last_modified)
    {
        $this->lastModified = $last_modified;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->lastModified ?: $this->getDefaultLastModified();
    }

    /**
     * @return \DateTime
     */
    public function getDefaultLastModified()
    {
        return new \DateTime();
    }

    /**
     * @return DateInterval
     */
    public function getDefaultExpiresInterval()
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
    public function getExpiresInterval()
    {
        return $this->expiresInterval ?: $this->getDefaultExpiresInterval();
    }

    /**
     * @return \DateTime
     */
    public function getExpiresDateTime()
    {
        return (new \DateTime())->add($this->getExpiresInterval());
    }

    /**
     * @return int
     */
    public function getExpiresSeconds()
    {
        $reference = new \DateTimeImmutable;
        $endTime   = $reference->add($this->getExpiresInterval());

        return $endTime->getTimestamp() - $reference->getTimestamp();
    }

    /**
     * This hook executed before IFace processing (on every request regardless of caching)
     * Place here code that needs to be executed on every IFace request (increment views counter, etc)
     */
    public function before()
    {
        // Empty by default
    }

    /**
     * This hook executed after real IFace processing only (on every request if IFace output was not cached)
     * Place here the code that needs to be executed only after real IFace processing (collect performance stat, etc)
     */
    public function after()
    {
        // Empty by default
    }

    public function __toString()
    {
        return (string)$this->render();
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function getParent()
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
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    public function getModel()
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

    public function isDefault()
    {
        return $this->getModel()->isDefault();
    }

    public function isInStack()
    {
        return $this->ifaceStack->has($this);
    }

    public function isCurrent(UrlParametersInterface $parameters = null)
    {
        return $this->ifaceStack->isCurrent($this, $parameters);
    }

    public function url(UrlParametersInterface $parameters = null, $removeCyclingLinks = true, $withDomain = true)
    {
        if ($removeCyclingLinks && $this->isCurrent($parameters)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts = [];

        if (!$this->isDefault()) {
            $current = $this;

            /** @var IFaceInterface $parent */
            $parent = null;

            do {
                $uri = $current->makeUri($parameters);

                if (!$uri) {
                    return null;
                }

                $parts[] = $uri;
                $parent  = $current->getParent();
                $current = $parent;
            } while ($parent);
        }

        $path = '/'.implode('/', array_reverse($parts));

        if ($this->appConfig->isTrailingSlashEnabled()) {
            $path .= '/';
        }

        return $withDomain ? URL::site($path, true) : $path;
    }

    private function makeUri(UrlParametersInterface $parameters = null)
    {
        $model = $this->getModel();

        $uri = $model->getUri();

        if (!$uri) {
            throw new IFaceException('IFace :codename must have uri', [':codename' => $model->getCodename()]);
        }

        // Static IFaces has raw uri value
        if (!$model->hasDynamicUrl()) {
            return $uri;
        }

        return $this->prototypeHelper->getCompiledPrototypeValue($uri, $parameters, $model->hasTreeBehaviour());
    }

    public function getUri()
    {
        return $this->getModel()->getUri();
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $params
     * @param int|null                                     $limit
     * @param bool                                         $withDomain
     *
     * @return string[]
     */
    public function getAvailableUrls(UrlParametersInterface $params, $limit = null, $withDomain = true)
    {
        if (!$this->getModel()->hasDynamicUrl()) {
            // Make static URL
            return [$this->makeAvailableUrl($params, $withDomain)];
        }

        return $this->getDynamicModelAvailableUrls($params, $limit, $withDomain);
    }

    /**
     * @param \BetaKiller\IFace\Url\UrlParametersInterface $params
     * @param int|null                                     $limit
     * @param bool                                         $withDomain
     *
     * @return string[]
     */
    private function getDynamicModelAvailableUrls(UrlParametersInterface $params, $limit = null, $withDomain = true)
    {
        $prototype = $this->prototypeHelper->fromIFaceUri($this);
        $model     = $this->prototypeHelper->getModelInstance($prototype);

        $modelName = $prototype->getModelName();
        $modelKey  = $prototype->getModelKey();

        $items = $model->getAvailableItemsByUrlKey($modelKey, $params, $limit);
        $urls  = [];

        foreach ($items as $item) {
            // Save current item to parameters registry
            $params->set($modelName, $item, true);

            // Make dynamic URL
            $urls[] = $this->makeAvailableUrl($params, $withDomain);

            // Recursion for trees
            if ($this->getModel()->hasTreeBehaviour()) {
                // Recursion for tree behaviour
                $urls = array_merge($urls, $this->getDynamicModelAvailableUrls($params, $limit, $withDomain));
            }
        }

        return $urls;
    }

    private function makeAvailableUrl(UrlParametersInterface $params = null, $withDomain = true)
    {
        return $this->url($params, false, $withDomain); // Disable cycling links removing
    }
}
