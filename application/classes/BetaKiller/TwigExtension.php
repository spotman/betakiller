<?php
namespace BetaKiller;

use BetaKiller\Model\IFaceZone;
use Device;
use HTML;
use Meta;
use Profiler;
use StaticFile;
use Twig_Extension;
use Twig_Filter;
use Twig_Function;

class TwigExtension extends Twig_Extension
{
    /**
     * @Inject
     * @var \JS
     */
    private $js;

    /**
     * @Inject
     * @var \CSS
     */
    private $css;

    /**
     * @Inject
     * @var \Meta
     */
    private $meta;

    /**
     * @Inject
     * @var \Assets
     */
    private $assets;

    /**
     * @Inject
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @Inject
     * @var \BetaKiller\Widget\WidgetFactory
     */
    private $widgetFactory;

    /**
     * @Inject
     * @var \BetaKiller\Helper\IFaceHelper
     */
    private $ifaceHelper;

    /**
     * TwigExtension constructor.
     *
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException
     */
    public function __construct()
    {
        \BetaKiller\DI\Container::getInstance()->injectOn($this);
    }

    public function getFunctions(): array
    {
        return [

            new Twig_Function(
                'js',
                [$this, 'js'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'js_build',
                [$this, 'jsBuild'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'css',
                [$this, 'css'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'static',
                [$this, 'getLinkToStaticFile'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'image',
                [$this, 'image'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'assets',
                [$this, 'assets']
            ),

            new Twig_Function(
                'meta',
                [$this, 'meta']
            ),

            new Twig_Function('isDevice', [$this, 'isDevice']),

            new Twig_Function(
                'profiler',
                [$this, 'showProfiler'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'widget',
                [$this, 'widget'],
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new Twig_Function(
                'in_production',
                [$this, 'inProduction']
            ),

            new Twig_Function(
                'in_public_zone',
                [$this, 'inPublicZone']
            ),

            new Twig_Function(
                'json_encode',
                'json_encode',
                ['is_safe' => ['html']]
            ),

            /**
             * Add element to <title> tag (will be combined automatically upon template render)
             */
            new Twig_Function(
                'title',
                [$this, 'title']
            ),

        ];
    }

    public function getFilters(): array
    {
        return [

            /**
             * Converts boolean value to JavaScript string representation
             */
            new Twig_Filter('bool', function ($value) {
                return $value ? 'true' : 'false';
            }),

            /**
             * International pluralization via translation strings
             * The first key-value pair would be used if no context provided
             *
             * @example ":count lots"|plural({ ":count": lotsCount })
             */
            new Twig_Filter('plural', function ($text, $values, $context = null) {
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
            new Twig_Filter('i18n', function (array $context, string $text, array $values = null) {
                // Use all surrounding context variables for simplicity
                $values = $values ? array_merge($context, $values) : $context;

                return __($text, $values);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

        ];
    }

    public function title(string $value)
    {
        return $this->meta->title($value, Meta::TITLE_APPEND);
    }

    /**
     * @return bool
     */
    public function inProduction(): bool
    {
        return $this->appEnv->inProductionMode();
    }

    /**
     * @return bool
     */
    public function inPublicZone(): bool
    {
        return $this->ifaceHelper->isCurrentIFaceZone(IFaceZone::PUBLIC_ZONE);
    }

    /**
     * Helper for adding JS files
     */
    public function js()
    {
        foreach (func_get_args() as $js) {
            if (mb_strpos($js, 'http') === 0 || mb_strpos($js, '//') === 0) {
                $this->js->addPublic($js);
            } else {
                $this->js->addStatic($js);
            }
        }
    }

    /**
     * Helper for adding JS builds (Require.JS, etc) in production environment
     */
    public function jsBuild()
    {
        if (!$this->inProduction()) {
            return;
        }

        foreach (func_get_args() as $js) {
            $this->js->addStatic($js);
        }
    }

    /**
     * Helper for adding CSS files
     */
    public function css()
    {
        foreach (func_get_args() as $css) {
            if (mb_strpos($css, 'http') === 0 || mb_strpos($css, '//') === 0) {
                $this->css->addPublic($css);
            } else {
                $this->css->addStatic($css);
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
    public function image(array $attributes, ?array $data = null, ?bool $forceSize = null): string
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
    public function getLinkToStaticFile(string $filename): string
    {
        return StaticFile::instance()->getLink($filename);
    }

    /**
     * Helper for adding assets
     *
     * @throws \HTTP_Exception_500
     */
    public function assets(): void
    {
        foreach (func_get_args() as $asset) {
            $this->assets->add($asset);
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
    public function meta($name = null, $value = null): ?string
    {
        if ($value === null && !is_array($name)) {
            return $this->meta->get($name);
        }

        $this->meta->set($name, $value);

        return null;
    }

    /**
     * @return string
     */
    public function showProfiler(): string
    {
        return Profiler::render();
    }

    /**
     * @return bool
     */
    public function isDevice(): bool
    {
        $device = new Device;

        return $device->is_mobile() || $device->is_tablet();
    }

    /**
     * @param array      $context
     * @param string     $name
     * @param array|null $data
     *
     * @return string
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function widget(array $context, string $name, array $data = null): string
    {
        if ($data) {
            $context = array_merge($context, $data);
        }

        $widget = $this->widgetFactory->create($name);
        $widget->setContext($context);

        return $widget->render();
    }
}
