<?php
namespace BetaKiller\IFace\Core;

use BetaKiller\Config\AppConfigInterface;
use DateInterval;
use DateTime;
use IFace_Exception;
use BetaKiller\IFace\IFaceModelInterface;
use Text;
use URL;
use URL_Dispatcher;
use URL_Parameters;
use View_IFace;

abstract class IFace
{

    /**
     * @var IFaceModelInterface
     */
    protected $_model;

    /**
     * @var IFace Parent iface
     */
    protected $_parent;

    /**
     * @var DateTime
     */
    protected $_last_modified;

    /**
     * @var DateInterval
     */
    protected $_expiresInterval;

    /**
     * @Inject
     * @var AppConfigInterface
     */
    private $_app_config;

    /**
     * @Inject
     * @var \URL_Dispatcher
     */
    private $_url_dispatcher;

    /**
     * @Inject
     * @var \IFace_Provider
     */
    private $_iface_provider;

    /**
     * @var \View_IFace
     */

    /**
     * @Inject
     * @var View_IFace
     */
    private $_view_iface;

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
    public function get_codename()
    {
        return $this->get_model()->get_codename();
    }

    /**
     * @return string
     */
    public function render()
    {
        // Getting IFace View instance and rendering
        return $this->_view_iface->render($this);
    }

    public function get_layout_codename()
    {
        return $this->get_model()->get_layout_codename();
    }

    /**
     * Returns processed label
     *
     * @return mixed
     */
    public function get_label()
    {
        return $this->process_string_pattern($this->get_label_source());
    }

    /**
     * Returns processed title
     *
     * @return string
     */
    public function get_title()
    {
        return $this->process_string_pattern($this->get_title_source(), 80); // Limit to 80 chars
    }

    /**
     * Returns processed description
     *
     * @return string
     */
    public function get_description()
    {
        return $this->process_string_pattern($this->get_description_source());
    }

    /**
     * Pattern consists of tags like [N[Text]] where N is tag priority
     *
     * @param string $source
     * @param int|NULL $limit
     * @return string
     */
    private function process_string_pattern($source, $limit = NULL)
    {
        // Replace url parameters
        $source = $this->url_dispatcher()->replace_url_parameters_parts($source);

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

            while ($i < $max_loops AND $output != '') {
                $output = $source;

                // Replace tags
                foreach ($tags as $tag) {
                    $output = str_replace($tag['key'], $tag['value'], $output);
                }

                if ($limit AND mb_strlen($output) > $limit) {
                    $drop   = array_pop($tags);
                    $source = trim(str_replace($drop['key'], '', $source));
                    $i++;
                } else
                    break;
            }
        }

        if ($limit AND mb_strlen($output) > $limit) {
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
    public function get_label_source()
    {
        return $this->get_model()->get_label();
    }

    /**
     * Returns title source/pattern
     *
     * @return string
     */
    public function get_title_source()
    {
        return $this->get_model()->get_title();
    }

    /**
     * Returns description source/pattern
     *
     * @return string
     */
    public function get_description_source()
    {
        return $this->get_model()->get_description();
    }

    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @return array
     */
    abstract public function get_data();

    /**
     * @param \DateTime|NULL $last_modified
     * @return $this
     */
    public function setLastModified(DateTime $last_modified)
    {
        $this->_last_modified = $last_modified;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getLastModified()
    {
        return $this->_last_modified ?: $this->getDefaultLastModified();
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
        $this->_expiresInterval = $expires;

        return $this;
    }

    /**
     * @return \DateInterval
     */
    public function getExpiresInterval()
    {
        return $this->_expiresInterval ?: $this->getDefaultExpiresInterval();
    }

    /**
     * @return \DateTime
     */
    public function getExpiresDateTime()
    {
        return (new DateTime())->add($this->getExpiresInterval());
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

    public function get_parent()
    {
        if (!$this->_parent) {
            $this->_parent = $this->_iface_provider->get_parent($this);
        }

        return $this->_parent;
    }

    public function set_parent(IFace $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Getter for current iface model
     *
     * @return IFaceModelInterface
     */
    public function get_model()
    {
        return $this->_model;
    }

    /**
     * Setter for current iface model
     *
     * @param IFaceModelInterface $model
     * @return $this
     */
    public function set_model(IFaceModelInterface $model)
    {
        $this->_model = $model;
        return $this;
    }

    public function is_default()
    {
        return $this->get_model()->is_default();
    }

    public function is_in_stack()
    {
        return $this->url_dispatcher()->in_stack($this);
    }

    public function is_current(URL_Parameters $parameters = NULL)
    {
        return $this->url_dispatcher()->is_current_iface($this, $parameters);
    }

    public function url(URL_Parameters $parameters = NULL, $remove_cycling_links = TRUE, $with_domain = TRUE)
    {
        if ($remove_cycling_links && $this->is_current($parameters)) {
            return $this->_app_config->get_circular_link_href();
        }

        $parts = array();

        if (!$this->is_default()) {
            $current = $this;

            /** @var IFace $parent */
            $parent = NULL;

            do {
                $uri = $current->make_uri($parameters);

                if (!$uri)
                    return NULL;

                $parts[] = $uri;
                $parent  = $current->get_parent();
                $current = $parent;
            } while ($parent);
        }

        $path = '/' . implode('/', array_reverse($parts));

        if ($this->is_trailing_slash_enabled()) {
            $path .= '/';
        }

        return $with_domain ? URL::site($path, TRUE) : $path;
    }

    protected function make_uri(URL_Parameters $parameters = NULL)
    {
        return $this->url_dispatcher()->make_iface_uri($this, $parameters);
    }

    public function get_uri()
    {
        return $this->get_model()->get_uri();
    }

    /**
     * Returns TRUE if trailing slash is needed in url
     *
     * @return bool
     */
    public function is_trailing_slash_enabled()
    {
        return $this->_app_config->is_trailing_slash_enabled();
    }

//    /**
//     * Returns array of IFaces for breadcrumbs rendering
//     * Override this method if your IFace tree is not equal to real semantic
//     *
//     * @return \BetaKiller\IFace\Core\IFace[]
//     */
//    public function get_breadcrumbs_iface_stack()
//    {
//        return $this->url_dispatcher()->stack();
//    }
//
//    /**
//     * Returns url parameters prepared for breadcrumbs rendering
//     * Override this method if your IFace tree is not equal to real semantic
//     *
//     * @return URL_Parameters
//     */
//    public function get_breadcrumbs_url_parameters()
//    {
//        return $this->url_parameters();
//    }

    public function get_view()
    {
        // TODO DI
        return View_IFace::factory();
    }

    /**
     * @return URL_Dispatcher
     */
    protected function url_dispatcher()
    {
        return $this->_url_dispatcher;
    }

    /**
     * @return \URL_Parameters
     */
    protected function url_parameters()
    {
        return $this->url_dispatcher()->parameters();
    }
}
