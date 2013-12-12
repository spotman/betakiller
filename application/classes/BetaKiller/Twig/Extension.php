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
        );
    }

    public function getFunctions()
    {
        return array(
//            new Twig_SimpleFunction('js', array($this, 'js'), array('is_safe' => array('html'))),
//            new Twig_SimpleFunction('css', array($this, 'css'), array('is_safe' => array('html'))),
            new Twig_SimpleFunction('static', array($this, 'statics')),

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

//    public function css()
//    {
//        $this->statics(CSS::instance(), func_get_args());
//    }
//
//    public function js()
//    {
//        $this->statics(JS::instance(), func_get_args());
//    }
//
    public function statics()
    {
        $this->include_static_files(Statics::instance(), func_get_args());
    }

    protected function include_static_files($object, $files)
    {
        foreach ( $files as $file )
        {
            call_user_func(array($object, $file));
        }
    }

}