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

    public function getGlobals()
    {
        return array(
            'js'    => JS::instance(),
            'css'   => CSS::instance(),
            'meta'  => Meta::instance(),

            'device'  => Device::factory(),

            'profiler'   => Profiler::instance(),
        );
    }

    public function getFunctions()
    {
        return array(
//            new Twig_SimpleFunction('js', array($this, 'js'), array('is_safe' => array('html'))),
//            new Twig_SimpleFunction('css', array($this, 'css'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('static', array($this, 'get_static_file'), array('is_safe' => array('html'))),

            new Twig_SimpleFunction('assets', array($this, 'load_assets')),

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
        );
    }

    public function widget(array $context, $name)
    {
        $widget = Widget::factory($name);
        $widget->context($context);
        return $widget->render();
    }

    public function get_static_file($filename)
    {
        return StaticFile::instance()->getLink($filename);
    }

    public function load_assets()
    {
        $instance = Assets::instance();

        foreach ( func_get_args() as $asset )
        {
            $instance->add($asset);
        }
    }

    /**
     * @todo генерация динамических url
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
}