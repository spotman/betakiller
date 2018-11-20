<?php
namespace BetaKiller;

use BetaKiller\Assets\StaticAssets;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\View\IFaceView;
use BetaKiller\Widget\WidgetFacade;
use HTML;
use Meta;
use Psr\Http\Message\ServerRequestInterface;
use Twig_Extension;
use Twig_Filter;
use Twig_Function;

class TwigExtension extends Twig_Extension
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Widget\WidgetFacade
     */
    private $widgetFacade;

    /**
     * TwigExtension constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface $appEnv
     * @param \BetaKiller\Widget\WidgetFacade    $widgetFacade
     */
    public function __construct(AppEnvInterface $appEnv, WidgetFacade $widgetFacade)
    {
        $this->appEnv       = $appEnv;
        $this->widgetFacade = $widgetFacade;
    }

    public function getFunctions(): array
    {
        return [

            new Twig_Function(
                'js',
                [$this, 'js'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            new Twig_Function(
                'css',
                [$this, 'css'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            new Twig_Function(
                'static',
                [$this, 'getLinkToStaticFile'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            new Twig_Function(
                'image',
                [$this, 'image'],
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            new Twig_Function(
                'csp',
                [$this, 'csp'],
                ['needs_context' => true]
            ),

            new Twig_Function(
                'meta',
                [$this, 'meta'],
                ['needs_context' => true]
            ),

            new Twig_Function(
                'kohanaProfiler',
                [$this, 'showKohanaProfiler'],
                ['is_safe' => ['html']]
            ),

            new Twig_Function(
                'entry',
                [$this, 'webpackEntry'],
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new Twig_Function(
                'lang',
                [$this, 'getCurrentLang'],
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new Twig_Function(
                'widget',
                [$this, 'widget'],
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new Twig_Function(
                'in_public_zone',
                [$this, 'inPublicZone'],
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new Twig_Function(
                'in_production',
                [$this, 'inProduction']
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
                [$this, 'title'],
                ['needs_context' => true,]
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
            new Twig_Filter('plural', function (array $context, string $key, array $values = null, $form = null) {
                if (!\is_array($values)) {
                    $values = [
                        ':count' => (int)$values,
                    ];
                }

                $values = I18nFacade::addPlaceholderPrefixToKeys($values);

                $lang = $this->getI18n($context)->getLang();

                return $this->getI18n($context)->pluralize($key, $form ?? current($values), $values, $lang);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

            /**
             * I18n via translation strings
             *
             * @example ":count lots"|i18n({ ":count": lotsCount })
             */
            new Twig_Filter('i18n', function (array $context, string $text, array $values = null) {
                if ($values) {
                    $values = I18nFacade::addPlaceholderPrefixToKeys($values);
                }

                return $this->getI18n($context)->translate($text, $values);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

        ];
    }

    public function title(array $context, string $value)
    {
        return $this->getMeta($context)->title($value, Meta::TITLE_APPEND);
    }

    /**
     * @return bool
     */
    public function inProduction(): bool
    {
        return $this->appEnv->inProductionMode();
    }

    /**
     * Helper for adding JS files
     *
     * @param array  $context
     * @param string $location
     */
    public function js(array $context, string $location): void
    {
        $this->getStaticAssets($context)->addJs($location);
    }

    /**
     * Helper for adding CSS files
     *
     * @param array  $context
     * @param string $location
     */
    public function css(array $context, string $location): void
    {
        $this->getStaticAssets($context)->addCss($location);
    }

    /**
     * Helper for creating <img> tag
     *
     * @param array      $context
     * @param array      $attributes
     * @param array|null $data
     * @param bool|null  $forceSize
     *
     * @return string
     */
    public function image(array $context, array $attributes, ?array $data = null, ?bool $forceSize = null): string
    {
        if ($data) {
            $attributes = array_merge($attributes, $data);
        }

        if (!$forceSize) {
            unset($attributes['width'], $attributes['height']);
        }

        $src = $this->getLinkToStaticFile($context, $attributes['src']);

        return HTML::image($src, array_filter($attributes));
    }

    /**
     * Helper for getting link for custom static file
     *
     * @param array  $context
     * @param string $path
     *
     * @return string
     */
    public function getLinkToStaticFile(array $context, string $path): string
    {
        return $this->getStaticAssets($context)->getFullUrl($path);
    }

    /**
     * Helper for adding HTML meta-headers in output
     *
     * @param array        $context
     * @param string|array $name
     * @param null         $value
     *
     * @return string|null
     */
    public function meta(array $context, $name = null, $value = null): ?string
    {
        $this->getMeta($context)->set($name, $value);

        return null;
    }

    /**
     * @return string
     * @throws \View_Exception
     */
    public function showKohanaProfiler(): string
    {
        return \View::factory('profiler/stats')->render();
    }

    public function csp(array $context, string $name, string $value, bool $reportOnly = null): void
    {
        $request = $this->getRequest($context);
        $csp     = ServerRequestHelper::getCsp($request);

        if ($reportOnly) {
            $csp->csp($name, $value, true);
        } else {
            $csp->csp($name, $value);
        }
    }

    public function webpackEntry(array $context, string $entryPoint, string $distDir = null): void
    {
        if (!$distDir) {
            $distDir = $context['dist_dir'] ?? null;
        }

        if (!$distDir) {
            throw new Exception('Pass dist dir as a second argument or set "dist_dir" variable in layout before using entry() function');
        }

        $assets = $this->getStaticAssets($context);

        $fileName = $distDir.\DIRECTORY_SEPARATOR.'entrypoints.json';
        $fullPath = $assets->findFile($fileName);

        if (!$fullPath) {
            throw new Exception('Missing file ":path", check webpack build and "dist_dir" variable', [
                ':path' => $fileName,
            ]);
        }

        $fileContent = \file_get_contents($fullPath);

        if (!$fileContent) {
            throw new Exception('Empty file ":path", check webpack build and "dist_dir" variable', [
                ':path' => $fullPath,
            ]);
        }

        $fileData = \json_decode($fileContent, true);

        $config = $fileData['entrypoints'][$entryPoint] ?? null;

        if (!$config) {
            throw new Exception('Missing entry ":name" in file ":path"', [
                ':path' => $fullPath,
                ':name' => $entryPoint,
            ]);
        }

        if (isset($config['js'])) {
            foreach ($config['js'] as $jsFileName) {
                $assets->addJs('/'.$jsFileName);
            }
        }

        if (isset($config['css'])) {
            foreach ($config['css'] as $cssFileName) {
                $assets->addCss('/'.$cssFileName);
            }
        }
    }

    public function getCurrentLang(array $context): string
    {
        return $this->getI18n($context)->getLang();
    }

    /**
     * @param array      $context
     * @param string     $name
     * @param array|null $data
     *
     * @return string
     * @throws \BetaKiller\Auth\AccessDeniedException
     * @throws \BetaKiller\Factory\FactoryException
     */
    public function widget(array $context, string $name, array $data = null): string
    {
        if ($data) {
            $context = array_merge($context, $data);
        }

        $request = $this->getRequest($context);

        $widget = $this->widgetFacade->create($name);

        return $this->widgetFacade->render($widget, $request, $context);
    }

    public function inPublicZone(array $context): bool
    {
        $zoneName = $context[IFaceView::IFACE_KEY][IFaceView::IFACE_ZONE_KEY];

        return $zoneName === ZoneInterface::PUBLIC;
    }

    private function getRequest(array $context): ServerRequestInterface
    {
        return $context[IFaceView::REQUEST_KEY];
    }

    private function getI18n(array $context): I18nHelper
    {
        return $context[IFaceView::I18N_KEY];
    }

    private function getStaticAssets(array $context): StaticAssets
    {
        return $context[IFaceView::ASSETS_KEY];
    }

    private function getMeta(array $context): \Meta
    {
        return $context[IFaceView::META_KEY];
    }
}
