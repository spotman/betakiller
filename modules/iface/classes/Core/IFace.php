<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Core_IFace {

    /**
     * @var IFace_Model
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
    protected $_expires;

    /**
     * Creates IFace instance from it`s codename
     *
     * @param string $codename IFace codename
     * @return static
     * @throws IFace_Exception
     */
    public static function by_codename($codename)
    {
        return static::provider()->by_codename($codename);
    }

    /**
     * Creates instance of IFace from model
     *
     * @param IFace_Model $model
     * @return static
     */
    public static function factory(IFace_Model $model)
    {
        return static::provider()->from_model($model);
    }

    protected static function provider()
    {
        return IFace_Provider::instance();
    }

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
        return $this->get_view()->render();
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
        $source = Env::url_dispatcher()->replace_url_parameters_parts($source);

        // Parse [N[...]] tags
        $pcre_pattern = '/\[([0-9]{1,2})\[([^\]]+)\]\]/';

        preg_match_all($pcre_pattern, $source, $matches, PREG_SET_ORDER);

        $tags = array();

        foreach ( $matches as $match )
        {
            $key = $match[0];
            $priority = $match[1];
            $value = $match[2];
            $tags[$priority] = array(
                'key'   =>  $key,
                'value' =>  $value,
            );
        }

        $output = $source;

        if ( $tags )
        {
            // Sort tags via keys in straight order
            ksort($tags);

            // Iteration counter
            $i = 0;
            $max_loops = count($tags);

            while ( $i < $max_loops AND $output != '' )
            {
                $output = $source;

                // Replace tags
                foreach ($tags as $tag)
                {
                    $output = str_replace($tag['key'], $tag['value'], $output);
                }

                if ( $limit AND mb_strlen($output) > $limit )
                {
                    $drop = array_pop($tags);
                    $source = trim(str_replace($drop['key'], '', $source));
                    $i++;
                }
                else
                    break;
            }
        }

        if ( $limit AND mb_strlen($output) > $limit )
        {
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
    public function get_data()
    {
        // Empty by default
        return array();
    }

    /**
     * @param \DateTime|NULL $last_modified
     */
    public function set_last_modified(DateTime $last_modified)
    {
        $this->_last_modified = $last_modified;
    }

    /**
     * @return \DateTime
     */
    public function get_last_modified()
    {
        return $this->_last_modified;
    }

    /**
     * @return \DateTime
     */
    public function get_default_last_modified()
    {
        return new \DateTime();
    }

    /**
     * @return DateInterval
     */
    public function get_default_expires_interval()
    {
        return new \DateInterval('PT1H');
    }

    /**
     * @param \DateInterval|NULL $expires
     */
    public function set_expires_interval(DateInterval $expires)
    {
        $this->_expires = $expires;
    }

    /**
     * @return \DateInterval
     */
    public function get_expires_interval()
    {
        return $this->_expires;
    }

    public function __toString()
    {
        return (string) $this->render();
    }

    public function get_parent()
    {
        if ( ! $this->_parent )
        {
            $this->_parent = $this->provider()->get_parent($this);
        }

        return $this->_parent;
    }

    function set_parent(IFace $parent)
    {
        $this->_parent = $parent;
        return $this;
    }

    /**
     * Getter for current iface model
     *
     * @return IFace_Model
     */
    public function get_model()
    {
        return $this->_model;
    }

    /**
     * Setter for current iface model
     *
     * @param IFace_Model $model
     * @return $this
     */
    public function set_model(IFace_Model $model)
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
        return URL_Dispatcher::instance()->in_stack($this);
    }

    public function url(URL_Parameters $parameters = NULL, $with_domain = TRUE)
    {
        $parts = array();

        if ( ! $this->is_default() )
        {
            $current = $this;

            /** @var IFace $parent */
            $parent = NULL;

            do
            {
                $uri = $current->make_uri($parameters);

                if ( !$uri )
                    return NULL;

                $parts[] = $uri;
                $parent = $current->get_parent();
                $current = $parent;
            }
            while ( $parent );
        }

        $path = '/'.implode('/', array_reverse($parts));

        return $with_domain ? URL::site($path, TRUE) : $path;
    }

    protected function make_uri(URL_Parameters $parameters = NULL)
    {
        $uri = $this->get_uri();

        if ( !$uri )
            throw new IFace_Exception('IFace :codename must have uri');

        return $this->get_model()->has_dynamic_url()
            ? URL_Dispatcher::instance()->make_url_parameter_part($uri, $parameters)
            : $uri;
    }

    protected function get_uri()
    {
        return $this->get_model()->get_uri();
    }

    public function get_view()
    {
        return View_IFace::factory($this);
    }

}
