<?php defined('SYSPATH') OR die('No direct script access.');

use BetaKiller\IFace\Widget\AbstractBaseWidget;

class BetaKiller_Twig_Extension extends Twig_Extension
{
    public function getFunctions()
    {
        return [

            new Twig_SimpleFunction(
                'js',
                [$this, 'js'],
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'js_build',
                [$this, 'js_build'],
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'css',
                [$this, 'css'],
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'static',
                [$this, 'get_link_to_static_file'],
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'image',
                [$this, 'image'],
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'assets',
                [$this, 'assets']
            ),

            new Twig_SimpleFunction(
                'meta',
                [$this, 'meta']
            ),

//            new Twig_SimpleFunction('iface_url', array($this, 'iface_url')),

            new Twig_SimpleFunction('is_device', [$this, 'is_device']),

            new Twig_SimpleFunction(
                'profiler',
                [$this, 'show_profiler'],
                ['is_safe' => ['html']]
            ),

            new Twig_SimpleFunction(
                'widget',
                [$this, 'widget'],
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new Twig_SimpleFunction(
                'in_production',
                [$this, 'in_production']
            ),

            new Twig_SimpleFunction(
                'in_staging',
                [$this, 'in_staging']
            ),

            new Twig_SimpleFunction(
                'json_encode',
                'json_encode',
                ['is_safe' => ['html']]
            ),

            /**
             * Добавляет элемент в тег <title> (автоматически срендерится при обработке шаблона)
             */
            new Twig_SimpleFunction(
                'title',
                function ($value) {
                    Meta::instance()->title($value, Meta::TITLE_APPEND);
                }
            ),

        ];
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
             *
             * @example ":count lots"|plural({ ":count": lotsCount })
             */
            new Twig_SimpleFilter('plural', function ($text, $values, $context = null) {
                if (!is_array($values)) {
                    $values = [
                        ':count' => (int)$values,
                    ];
                }

                return ___($text, $context ?: current($values), $values);
            }),

            /**
             * I18n via translation strings
             *
             * @example ":count lots"|i18n({ ":count": lotsCount })
             */
            new Twig_SimpleFilter('i18n', function ($text, array $values = null) {
                return __($text, $values);
            }),

        ];
    }

    public function in_production()
    {
        return Kohana::in_production();
    }

    public function in_staging()
    {
        return Kohana::in_staging();
    }

    /**
     * Helper for adding JS files
     */
    public function js()
    {
        $instance = JS::instance();

        foreach (func_get_args() as $js) {
            if (mb_strpos($js, 'http') === 0 || mb_strpos($js, '//') === 0) {
                $instance->add_public($js);
            } else {
                $instance->add_static($js);
            }
        }
    }

    /**
     * Helper for adding JS builds (Require.JS, etc) in production environment
     */
    public function js_build()
    {
//         Временно отключаем пока не сделаны билды
//
//        if (!Kohana::in_production())
//            return;
//
//        $instance = JS::instance();
//
//        foreach (func_get_args() as $js) {
//            $instance->add($js);
//        }
    }

    /**
     * Helper for adding CSS files
     */
    public function css()
    {
        $instance = CSS::instance();

        foreach (func_get_args() as $css) {
            if (mb_strpos($css, 'http') === 0 || mb_strpos($css, '//') === 0) {
                $instance->add_public($css);
            } else {
                $instance->add_static($css);
            }
        }
    }

    /**
     * Helper for creating <img> tag
     *
     * @param array      $attributes
     * @param array|null $data
     * @param bool|null  $forceSize
     *
     * @return string
     */
    public function image(array $attributes, array $data = null, $forceSize = null)
    {
        if ($data) {
            $attributes = array_merge($attributes, $data);
        }

        if (!$forceSize) {
            unset($attributes['width'], $attributes['height']);
        }

        $title = $attributes['title'];
        $alt   = $attributes['alt'];

        $attributes['title'] = $title ?: $alt;

        $src = $attributes['src'];

        return HTML::image($src, array_filter($attributes));
    }

    /**
     * Helper for getting link for custom static file
     *
     * @param string $filename
     *
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

        foreach (func_get_args() as $asset) {
            $instance->add($asset);
        }
    }

    /**
     * Helper for adding HTML meta-headers in output
     *
     * @param string|array $name
     * @param null         $value
     *
     * @return string|null
     */
    public function meta($name = null, $value = null)
    {
        $instance = Meta::instance();

        if ($value === null && !is_array($name)) {
            return $instance->get($name);
        }

        $instance->set($name, $value);

        return null;
    }

    public function show_profiler()
    {
        return Profiler::render();
    }

    public function is_device()
    {
        $device = new Device;

        return $device->is_mobile() || $device->is_tablet();
    }

    public function widget(array $context, $name, array $data = null)
    {
        if ($data) {
            $context = array_merge($context, $data);
        }

        $widget = AbstractBaseWidget::factory($name);
        $widget->setContext($context);

        return $widget->render();
    }
}
