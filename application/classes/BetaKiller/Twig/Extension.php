<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Twig_Extension extends Twig_Extension
{
    use BetaKiller\Helper\Base;

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'BetaKiller';
    }

    public function getFunctions()
    {
        return array(

            new Twig_SimpleFunction(
                'js',
                array($this, 'js'),
                array('is_safe' => array('html'))
            ),

            new Twig_SimpleFunction(
                'js_build',
                array($this, 'js_build'),
                array('is_safe' => array('html'))
            ),

            new Twig_SimpleFunction(
                'css',
                array($this, 'css'),
                array('is_safe' => array('html'))
            ),

            new Twig_SimpleFunction(
                'static',
                array($this, 'get_link_to_static_file'),
                array('is_safe' => array('html'))
            ),

            new Twig_SimpleFunction(
                'image',
                array($this, 'image'),
                array('is_safe' => array('html'))
            ),

            new Twig_SimpleFunction(
                'assets',
                array($this, 'assets')
            ),

            new Twig_SimpleFunction(
                'meta',
                array($this, 'meta')
            ),

//            new Twig_SimpleFunction('iface_url', array($this, 'iface_url')),

            new Twig_SimpleFunction('is_device', array($this, 'is_device')),

            new Twig_SimpleFunction(
                'profiler',
                array($this, 'show_profiler'),
                array('is_safe' => array('html'))
            ),

            new Twig_SimpleFunction(
                'widget',
                array($this, 'widget'),
                array('is_safe' => array('html'), 'needs_context' => true)
            ),

            new Twig_SimpleFunction(
                'in_production',
                array($this, 'in_production')
            ),

            new Twig_SimpleFunction(
                'user_is_moderator',
                array($this, 'user_is_moderator')
            ),

            new Twig_SimpleFunction(
                'json_encode',
                'json_encode',
                array('is_safe' => array('html'))
            ),

            /**
             * Добавляет элемент в тег <title> (автоматически срендерится при обработке шаблона)
             */
            new Twig_SimpleFunction(
                'title',
                function($value) {
                    Meta::instance()->title($value, Meta::TITLE_APPEND);
                }
            ),

            /**
             * Возвращает ссылку на родительский IFace
             */
            new Twig_SimpleFunction(
                'parent_iface_url',
                function() {
                    return $this->url_dispatcher()
                        ->current_iface()
                        ->get_parent()
                        ->url($this->url_parameters());
                }
            ),

        );
    }

    public function getFilters()
    {
        return [

            /**
             * Converts boolean value to JavaScript string representation
             */
            new Twig_SimpleFilter('bool', function ($value) {
                return $value ? 'true' : 'false';
            }),

            /**
             * International pluralization via translation strings
             * The first key-value pair would be used if no context provided
             * @example ":count lots"|plural({ ":count": lotsCount })
             */
            new Twig_SimpleFilter('plural', function ($text, array $values, $context = NULL) {
                return ___($text, $context ?: current($values), $values);
            }),

        ];
    }

    public function in_production()
    {
        return Kohana::in_production(TRUE);
    }

    /**
     * Helper for adding JS files
     */
    public function js()
    {
        $instance = JS::instance();

        foreach ( func_get_args() as $js )
        {
            if ( mb_substr($js, 0, 4) == 'http' || mb_substr($js, 0, 2) == '//' )
                $instance->add_public($js);
            else
                $instance->add_static($js);
        }
    }

    /**
     * Helper for adding JS builds (Require.JS, etc) in production environment
     */
    public function js_build()
    {
        // TODO Временно отключаем пока не сделаны билды
        if ( TRUE OR ! Kohana::in_production() )
            return;

        $instance = JS::instance();

        foreach ( func_get_args() as $js )
        {
            $instance->add($js);
        }
    }

    /**
     * Helper for adding CSS files
     */
    public function css()
    {
        $instance = CSS::instance();

        foreach ( func_get_args() as $css )
        {
            if ( mb_substr($css, 0, 4) == 'http' || mb_substr($css, 0, 2) == '//')
                $instance->add_public($css);
            else
                $instance->add_static($css);
        }
    }

    public function image(array $attributes, array $data = [], $force_size = false)
    {
        $attributes = array_merge($attributes, $data);

        if (!$force_size)
        {
            unset($attributes['width'], $attributes['height']);
        }

        $title = $attributes['title'];
        $alt = $attributes['alt'];

        $attributes['title'] = $title ?: $alt;

        $src = $attributes['src'];

        return HTML::image($src, array_filter($attributes));
    }

    /**
     * Helper for getting link for custom static file
     *
     * @param string $filename
     * @return string
     */
    public function get_link_to_static_file($filename)
    {
        return StaticFile::instance()->getLink($filename);
    }

    /**
     * Helper for adding assets
     */
    public function assets()
    {
        $instance = Assets::instance();

        foreach ( func_get_args() as $asset )
        {
            $instance->add($asset);
        }
    }

    /**
     * Helper for adding HTML meta-headers in output
     *
     * @param string $name
     * @param null $value
     * @return string|null
     */
    public function meta($name = NULL, $value = NULL)
    {
        $instance = Meta::instance();

        if ( $value === NULL AND ! is_array($name) )
        {
            return $instance->get($name);
        }
        else
        {
            $instance->set($name, $value);
            return NULL;
        }
    }

    public function show_profiler()
    {
        return Profiler::render();
    }

    public function is_device()
    {
        return Device::instance()->is_portable();
    }

    public function widget(array $context, $name, array $data = array())
    {
        $widget = $this->widget_factory($name);
        $widget->setContext(array_merge($context, $data));
        return $widget->render();
    }

    public function user_is_moderator()
    {
        $user = $this->current_user(TRUE);

        return $user AND $user->is_moderator();
    }
}
