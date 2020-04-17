<?php
namespace BetaKiller;

use Aidantwoods\SecureHeaders\SecureHeaders;
use BetaKiller\Assets\StaticAssets;
use BetaKiller\Dev\RequestProfiler;
use BetaKiller\Helper\AppEnvInterface;
use BetaKiller\Helper\I18nHelper;
use BetaKiller\Helper\LoggerHelper;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\I18n\I18nFacade;
use BetaKiller\Url\ZoneInterface;
use BetaKiller\View\IFaceView;
use BetaKiller\Widget\WidgetFacade;
use HTML;
use Meta;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

final class TwigExtension extends AbstractExtension
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
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var string[][]
     */
    private $manifestCache = [];

    /**
     * @var \BetaKiller\IdentityConverterInterface
     */
    private $identityConverter;

    /**
     * TwigExtension constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface     $appEnv
     * @param \BetaKiller\Widget\WidgetFacade        $widgetFacade
     * @param \BetaKiller\IdentityConverterInterface $identityConverter
     * @param \Psr\Log\LoggerInterface               $logger
     */
    public function __construct(
        AppEnvInterface $appEnv,
        WidgetFacade $widgetFacade,
        IdentityConverterInterface $identityConverter,
        LoggerInterface $logger
    ) {
        $this->appEnv            = $appEnv;
        $this->widgetFacade      = $widgetFacade;
        $this->logger            = $logger;
        $this->identityConverter = $identityConverter;
    }

    public function getFunctions(): array
    {
        return [
            /**
             * Helper for adding JS files
             */
            new TwigFunction(
                'js',
                function (array $context, string $location, ?array $attributes = null, string $condition = null) {
                    $this->getStaticAssets($context)->addJs($location, $attributes, $condition);
                },
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            /**
             * Helper for adding CSS files
             */
            new TwigFunction(
                'css',
                function (array $context, string $location, ?array $attributes = null, string $condition = null): void {
                    $this->getStaticAssets($context)->addCss($location, $attributes, $condition);
                },
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            /**
             * Helper for getting link for custom static file
             */
            new TwigFunction(
                'static',
                function (array $context, string $path): string {
                    return $this->getStaticAssets($context)->getFullUrl($path);
                },
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            new TwigFunction(
                'static_content',
                function (array $context, string $path): string {
                    $fullPath = $this->getStaticAssets($context)->findFile($path);

                    if (!$fullPath) {
                        throw new Exception('Missing static asset ":path"', [
                            ':path' => $fullPath,
                        ]);
                    }

                    return \file_get_contents($fullPath);
                },
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            /**
             * Helper for creating <img> tag
             */
            new TwigFunction(
                'image',
                function (array $context, array $attributes, ?array $data = null, ?bool $forceSize = null): string {
                    if ($data) {
                        $attributes = array_merge($attributes, $data);
                    }

                    if (!$forceSize) {
                        unset($attributes['width'], $attributes['height']);
                    }

                    $src = $this->getStaticAssets($context)->getFullUrl($attributes['src']);

                    return HTML::image($src, array_filter($attributes));
                },
                ['needs_context' => true, 'is_safe' => ['html']]
            ),

            new TwigFunction(
                'csp',
                function (array $context, string $name, string $value, bool $reportOnly = null): void {
                    $request = $this->getRequest($context);
                    $csp     = ServerRequestHelper::getCsp($request);

                    if ($reportOnly) {
                        $csp->csp($name, $value, true);
                    } else {
                        $csp->csp($name, $value);
                    }
                },
                ['needs_context' => true]
            ),

            new TwigFunction(
                'js_nonce',
                function (array $context): string {
                    $request = $this->getRequest($context);

                    return ServerRequestHelper::getCsp($request)->cspNonce('script');
                },
                ['needs_context' => true]
            ),

            new TwigFunction(
                'css_nonce',
                function (array $context): string {
                    $request = $this->getRequest($context);

                    return ServerRequestHelper::getCsp($request)->cspNonce('style');
                },
                ['needs_context' => true]
            ),

            new TwigFunction(
                'kohanaProfiler',
                static function (): string {
                    return \View::factory('profiler/stats')->render();
                },
                ['is_safe' => ['html']]
            ),

            new TwigFunction(
                'entry',
                function (array $context, string $entryPoint, string $distDir = null): void {
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

                    $baseUrl = $assets->getBaseUrl();

                    if (isset($config['js'])) {
                        foreach ($config['js'] as $jsFileName) {
                            $assets->addJs((string)$baseUrl->withPath($jsFileName));
                        }
                    }

                    if (isset($config['css'])) {
                        foreach ($config['css'] as $cssFileName) {
                            $assets->addCss((string)$baseUrl->withPath($cssFileName));
                        }
                    }
                },
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new TwigFunction(
                'static_dist',
                function (array $context, string $name, string $distDir = null): string {
                    if (!$distDir) {
                        $distDir = $context['dist_dir'] ?? null;
                    }

                    if (!$distDir) {
                        throw new Exception('Pass dist dir as a second argument or set "dist_dir" variable in layout before using entry() function');
                    }

                    $assets = $this->getStaticAssets($context);

                    $fileData         = $this->manifestCache[$distDir] ?? null;
                    $manifestFileName = $distDir.\DIRECTORY_SEPARATOR.'manifest.json';
                    $manifestFullPath = $assets->findFile($manifestFileName);

                    if (!$fileData) {

                        if (!$manifestFullPath) {
                            throw new Exception('Missing file ":path", check webpack build and "dist_dir" variable', [
                                ':path' => $manifestFileName,
                            ]);
                        }

                        $fileContent = \file_get_contents($manifestFullPath);

                        if (!$fileContent) {
                            throw new Exception('Empty file ":path", check webpack build and "dist_dir" variable', [
                                ':path' => $manifestFullPath,
                            ]);
                        }

                        $this->manifestCache[$distDir] = $fileData = \json_decode($fileContent, true);
                    }

                    $key = $distDir.'/'.$name;

                    $recordPath = $fileData[$key] ?? null;

                    if (!$recordPath) {
                        throw new Exception('Missing record ":name" in file ":path"', [
                            ':path' => $manifestFullPath,
                            ':name' => $key,
                        ]);
                    }

                    return (string)$assets->getBaseUrl()->withPath($recordPath);
                },
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new TwigFunction(
                'lang',
                function (array $context): string {
                    return $this->getI18n($context)->getLang()->getIsoCode();
                },
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new TwigFunction(
                'widget',
                function (array $context, string $name, array $data = null): string {
                    $request = $this->getRequest($context);

                    $t = RequestProfiler::begin($request, sprintf('%s widget: init', $name));

                    if ($data) {
                        $context = array_merge($context, $data);
                    }

                    $widget = $this->widgetFacade->create($name);

                    RequestProfiler::end($t);

                    return $this->widgetFacade->render($widget, $request, $context);
                },
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new TwigFunction(
                'in_public_zone',
                static function (array $context): bool {
                    $zoneName = $context[IFaceView::IFACE_KEY][IFaceView::IFACE_ZONE_KEY];

                    return $zoneName === ZoneInterface::PUBLIC;
                },
                ['is_safe' => ['html'], 'needs_context' => true]
            ),

            new TwigFunction(
                'in_production',
                function (): bool {
                    return $this->appEnv->inProductionMode();
                }
            ),

            new TwigFunction(
                'in_staging',
                function (): bool {
                    return $this->appEnv->inStagingMode();
                }
            ),

            new TwigFunction(
                'in_testing',
                function (): bool {
                    return $this->appEnv->inTestingMode();
                }
            ),

            new TwigFunction(
                'in_dev',
                function (): bool {
                    return $this->appEnv->inDevelopmentMode();
                }
            ),

            new TwigFunction(
                'is_debug',
                function (): bool {
                    return $this->appEnv->isDebugEnabled();
                }
            ),

            new TwigFunction(
                'is_admin',
                function (array $context): bool {
                    $request = $this->getRequest($context);

                    if (ServerRequestHelper::isGuest($request)) {
                        return false;
                    }

                    return ServerRequestHelper::getUser($request)->hasAdminRole();
                },
                ['needs_context' => true]
            ),

            new TwigFunction(
                'env_mode',
                function (): string {
                    return $this->appEnv->getModeName();
                }
            ),

            new TwigFunction(
                'user_id',
                function (array $context): string {
                    $request = $this->getRequest($context);

                    if (ServerRequestHelper::isGuest($request)) {
                        return 'Guest';
                    }

                    $user = ServerRequestHelper::getUser($request);

                    return $this->identityConverter->encode($user);
                },
                ['needs_context' => true]
            ),

            new TwigFunction(
                'json_encode',
                'json_encode',
                ['is_safe' => ['html']]
            ),

            new TwigFunction(
                'log_error',
                function (string $message, array $params = null): void {
                    $params = $params ?? [];

                    $params = Exception::addPlaceholderPrefixToKeys($params);

                    LoggerHelper::logRawException($this->logger, new Exception($message, $params));
                }
            ),

            /**
             * Helper for adding HTML meta-headers in output
             */
            new TwigFunction(
                'meta',
                function (array $context, string $name, string $value): void {
                    $this->getMeta($context)->set($name, $value);
                },
                ['needs_context' => true]
            ),


            new TwigFunction(
                'meta_link',
                function (array $context, string $rel, string $href, array $attributes = null): void {
                    $this->getMeta($context)->addLink($rel, $href, $attributes);
                },
                ['needs_context' => true]
            ),

            /**
             * Add element to <title> tag (will be combined automatically upon template render)
             */
            new TwigFunction(
                'meta_title',
                function (array $context, string $value): void {
                    $this->getMeta($context)->setTitle($value, Meta::TITLE_APPEND);
                },
                ['needs_context' => true,]
            ),

            new TwigFunction(
                'meta_description',
                function (array $context, string $value): void {
                    $this->getMeta($context)->setDescription($value);
                },
                ['needs_context' => true,]
            ),

            new TwigFunction(
                'meta_share_description',
                function (array $context, string $value): void {
                    $this->getMeta($context)->setSocialDescription($value);
                },
                ['needs_context' => true,]
            ),

            new TwigFunction(
                'meta_share_image',
                function (array $context, string $url, bool $overwrite = null): void {
                    $meta = $this->getMeta($context);

                    if ($overwrite || !$meta->hasSocialImage()) {
                        $meta->setSocialImage($url);
                    }
                },
                ['needs_context' => true,]
            ),

            new TwigFunction(
                'meta_share_title',
                function (array $context, string $title, bool $overwrite = null): void {
                    $meta = $this->getMeta($context);

                    if ($overwrite || !$meta->hasSocialTitle()) {
                        $meta->setSocialTitle($title);
                    }
                },
                ['needs_context' => true,]
            ),

            new TwigFunction(
                'get_meta_share_title',
                function (array $context) {
                    // Act as a getter
                    return $this->getMeta($context)->getSocialTitle();
                },
                ['needs_context' => true,]
            ),

            new TwigFunction(
                'get_meta_share_description',
                function (array $context) {
                    // Act as a getter
                    return $this->getMeta($context)->getSocialDescription();
                },
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
            new TwigFilter('bool', static function ($value) {
                return $value ? 'true' : 'false';
            }),

            /**
             * International pluralization via translation strings
             * The first key-value pair would be used if no context provided
             *
             * @example ":count lots"|plural({ ":count": lotsCount })
             */
            new TwigFilter('plural', function (array $context, string $key, array $values = null, $form = null) {
                if (!\is_array($values)) {
                    $values = [
                        ':count' => (int)$values,
                    ];
                }

                $values = I18nFacade::addPlaceholderPrefixToKeys($values);

                return $this->getI18n($context)->pluralizeKeyName($key, $form ?? current($values), $values);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

            /**
             * I18n via translation strings
             *
             * @example ":count lots"|i18n({ ":count": lotsCount })
             */
            new TwigFilter('i18n', function (array $context, string $text, array $values = null) {
                if ($values) {
                    $values = I18nFacade::addPlaceholderPrefixToKeys($values);
                }

                return $this->getI18n($context)->translateKeyName($text, $values);
            }, ['needs_context' => true, 'is_safe' => ['html']]),

            /**
             * Calculate CSP hashes for provided content (<script> or <style> tags)
             * Use with Twig "apply" tag
             */
            new TwigFilter('csp_hash', function (array $context, string $text) {
                $request = $this->getRequest($context);

                $csp = ServerRequestHelper::getCsp($request);

                $this->processHashesForStyleTags($text, $csp);
                $this->processHashesForScriptTags($text, $csp);

                // Return unprocessed text
                return $text;
            }, ['needs_context' => true, 'is_safe' => ['html']]),
        ];
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

    private function getMeta(array $context): Meta
    {
        return $context[IFaceView::META_KEY];
    }

    private function processHashesForScriptTags(string $text, SecureHeaders $csp): void
    {
        foreach ($this->findTagsContents('script', $text) as $content) {
            $csp->cspHash('script', $content);
        }
    }

    private function processHashesForStyleTags(string $text, SecureHeaders $csp): void
    {
        foreach ($this->findTagsContents('style', $text) as $content) {
            $csp->cspHash('style', $content);
        }
    }

    private function findTagsContents(string $tagName, string $text): \Generator
    {
        /** @see http://www.regular-expressions.info/examples.html */
        $regex = '#<'.$tagName.'[^>]*>(.*?)<\/'.$tagName.'>#ims';

        if (!\preg_match_all($regex, $text, $matches, PREG_SET_ORDER)) {
            return;
        }

        foreach ($matches as $match) {
            yield $match[1]; // Remove cr/lf
        }
    }
}
