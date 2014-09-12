<?php defined('SYSPATH') OR die('No direct script access.');

class BetaKiller_Twig_Extension extends Twig_Extension {

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'BetaKiller';
    }

//    public function getGlobals()
//    {
//        return array(
////            'js'    => JS::instance(),
////            'css'   => CSS::instance(),
////            'meta'  => Meta::instance(),
//
////            'device'  => Device::factory(),
//
////            'profiler'   => Profiler::instance(),
//        );
//    }

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
                'assets',
                array($this, 'assets')
            ),

            new Twig_SimpleFunction(
                'meta',
                array($this, 'meta')
            ),

            new Twig_SimpleFunction('iface_url', array($this, 'iface_url')),

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

        );
    }

    public function getFilters()
    {
        return [

            new Twig_SimpleFilter('bool', function ($value) {
                return $value ? 'true' : 'false';
            }),

        ];
    }

    public function in_production()
    {
        return Kohana::in_production();
    }

    /**
     * Helper for adding JS files
     */
    public function js()
    {
        $instance = JS::instance();

        foreach ( func_get_args() as $js )
        {
            $instance->add($js);
        }
    }

    /**
     * Helper for adding JS builds (Require.JS, etc) in production environment
     */
    public function js_build()
    {
        if ( ! Kohana::in_production() )
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

        foreach ( func_get_args() as $js )
        {
            $instance->add($js);
        }
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

    /**
     * Только для статических ссылок!
     * Динамические нужно генерировать на бэкенде
     * @param $codename
     * @return string
     */
    public function iface_url($codename)
    {
        $iface = IFace::by_codename($codename);

        return $iface->url();
    }

    public function show_profiler()
    {
        return Profiler::render();
    }

    public function widget(array $context, $name, array $data = array())
    {
        $widget = Widget::factory($name);
        $widget->context(array_merge($context, $data));
        return $widget->render();
    }

    public function user_is_moderator()
    {
        $user = Env::user(TRUE);

        return $user AND $user->is_moderator();
    }

}
