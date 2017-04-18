<?php
namespace BetaKiller\IFace;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\IFace\Url\UrlDispatcher;
use BetaKiller\IFace\Url\UrlParameters;
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
     * @var \IFace_Provider
     */
    private $ifaceProvider;

    /**
     * @var IFaceView
     * @Inject
     */
    private $ifaceView;

    /**
     * Returns URL query parts array for current HTTP request
     *
     * @param $key
     * @return array
     */
    abstract protected function getUrlQuery($key = NULL);

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
    public function getLabel(UrlParameters $params = null)
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
     * @param string $source
     * @param int|NULL $limit
     * @param UrlParameters|null $params
     *
     * @return string
     */
    private function processStringPattern($source, $limit = null, UrlParameters $params = null)
    {
        // Replace url parameters
        $source = $this->url_dispatcher()->replace_url_parameters_parts($source, $params);

        // Parse [N[...]] tags
        $pcre_pattern = '/\[([0-9]{1,2})\[([^\]]+)\]\]/';

        preg_match_all($pcre_pattern, $source, $matches, PREG_SET_ORDER);

        $tags = array();

        foreach ($matches as $match) {
            $key             = $match[0];
            $priority        = $match[1];
            $value           = $match[2];
            $tags[$priority] = array(
                'key'   => $key,
                'value' => $value,
            );
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
            Text::limit_chars($output, $limit, NULL, TRUE);
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
        $endTime = $reference->add($this->getExpiresInterval());

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
        return (string) $this->render();
    }

    /**
     * @return \BetaKiller\IFace\IFaceInterface|null
     */
    public function getParent()
    {
        if (!$this->parent) {
            $this->parent = $this->ifaceProvider->get_parent($this);
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
        return $this->url_dispatcher()->in_stack($this);
    }

    public function isCurrent(UrlParameters $parameters = NULL)
    {
        return $this->url_dispatcher()->is_current_iface($this, $parameters);
    }

    public function url(UrlParameters $parameters = NULL, $remove_cycling_links = TRUE, $with_domain = TRUE)
    {
        if ($remove_cycling_links && $this->isCurrent($parameters)) {
            return $this->appConfig->getCircularLinkHref();
        }

        $parts = array();

        if (!$this->isDefault()) {
            $current = $this;

            /** @var IFaceInterface $parent */
            $parent = NULL;

            do {
                $uri = $current->makeUri($parameters);

                if (!$uri)
                    return NULL;

                $parts[] = $uri;
                $parent  = $current->getParent();
                $current = $parent;
            } while ($parent);
        }

        $path = '/' . implode('/', array_reverse($parts));

        if ($this->isTrailingSlashEnabled()) {
            $path .= '/';
        }

        return $with_domain ? URL::site($path, TRUE) : $path;
    }

    protected function makeUri(UrlParameters $parameters = NULL)
    {
        return $this->url_dispatcher()->make_iface_uri($this, $parameters);
    }

    public function getUri()
    {
        return $this->getModel()->getUri();
    }

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function isTrailingSlashEnabled()
    {
        return $this->appConfig->isTrailingSlashEnabled();
    }

    /**
     * @return UrlDispatcher
     */
    protected function url_dispatcher()
    {
        return $this->urlDispatcher;
    }

    /**
     * @return \BetaKiller\IFace\Url\UrlParameters
     */
    protected function url_parameters()
    {
        return $this->url_dispatcher()->parameters();
    }
}
