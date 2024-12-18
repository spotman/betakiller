<?php

declare(strict_types=1);

namespace BetaKiller\Assets;

use BetaKiller\Config\AssetsConfig;
use BetaKiller\Env\AppEnvInterface;
use HTML;
use Kohana;
use Psr\Http\Message\UriInterface;

class StaticAssets
{
    /**
     * CSS files
     *
     * @var array
     */
    private array $cssData = [];

    /**
     * Javascript files
     *
     * @var array
     */
    private array $jsData = [];

    /**
     * StaticAssets constructor.
     *
     * @param \BetaKiller\Env\AppEnvInterface $appEnv
     * @param \BetaKiller\Config\AssetsConfig $config
     */
    public function __construct(private readonly AppEnvInterface $appEnv, private readonly AssetsConfig $config)
    {
    }

    public function addJs(string $location, ?array $attributes = null, ?string $condition = null): void
    {
        if (!$this->isAbsoluteUrl($location)) {
            $location = $this->getFullUrl($location);
        }

        // Skip duplicate calls from widgets
        if (isset($this->jsData[$location])) {
            return;
        }

        $this->jsData[$location] = [
            'location'   => $location,
            'condition'  => $condition,
            'attributes' => $attributes ?? [],
        ];
    }

    public function addCss(string $location, ?array $attributes = null, ?string $condition = null): void
    {
        if (!$this->isAbsoluteUrl($location)) {
            $location = $this->getFullUrl($location);
        }

        // Skip duplicate calls from widgets
        if (isset($this->cssData[$location])) {
            return;
        }

        $this->cssData[$location] = [
            'location'   => $location,
            'condition'  => $condition,
            'attributes' => $attributes ?? [],
        ];
    }

    /**
     * @return string
     */
    public function renderJs(): string
    {
        $jsCode = '';

        foreach ($this->jsData as $jsData) {
            $jsCode .= $this->getScriptTag($jsData)."\n";
        }

        return $jsCode;
    }

    /**
     * @return string
     */
    public function renderCss(): string
    {
        $cssCode = '';

        // Not need to build one css file
        foreach ($this->cssData as $cssData) {
            $cssCode .= $this->getCssLink($cssData)."\n";
        }

        return $cssCode;
    }

    public function getBaseUrl(): UriInterface
    {
        $uri = $this->config->getBaseUri();

        $path = $uri->getPath().'/static/';

        return $uri->withPath($path);
    }

    /**
     * @param string $path
     *
     * @return string
     */
    public function getFullUrl(string $path): string
    {
        return $this->getBaseUrl().$path;
    }

    /**
     * Search for a file and return absolute path (or "null" if nothing found)
     *
     * @param string $relativePath
     *
     * @return string|null
     */
    public function findFile(string $relativePath): ?string
    {
        $info = pathinfo($relativePath);

        $dir  = $info['dirname'];
        $name = $info['filename'];
        $ext  = $info['extension'];

        $dir = ($dir !== '.')
            ? $dir.DIRECTORY_SEPARATOR
            : '';

        $path = 'static'.DIRECTORY_SEPARATOR.$dir.$name;

        $file = Kohana::find_file('assets', $path, $ext);

        return $file ? (string)$file : null;
    }

    public function getDeployPath(string $relativePath): string
    {
        return $this->getFullPath().$relativePath;
    }

    private function getBasePath(): string
    {
        return $this->getBaseUrl()->getPath();
    }

    private function getFullPath(): string
    {
        return $this->appEnv->getDocRootPath().$this->getBasePath();
    }

    private function isAbsoluteUrl(string $location): bool
    {
        return \mb_strpos($location, 'http') === 0
            || \mb_strpos($location, '//') === 0
            || \mb_strpos($location, '/') === 0;
    }

    /**
     * Gets html code of the css loading
     *
     * @param array $data
     *
     * @return string
     */
    private function getCssLink(array $data): string
    {
        $location   = $data['location'];
        $attributes = $data['attributes'];
        $condition  = $data['condition'];

        $code = HTML::style($location, $attributes + ['media' => 'all']);

        if ($condition) {
            $code = $this->wrapInCondition($code, $condition);
        }

        return $code;
    }

    /**
     * Gets html code of the script loading
     *
     * @param array $data
     *
     * @return string
     */
    private function getScriptTag(array $data): string
    {
        $location   = $data['location'];
        $attributes = $data['attributes'];
        $condition  = $data['condition'];

        $code = HTML::script($location, $attributes);

        if ($condition) {
            $code = $this->wrapInCondition($code, $condition);
        }

        return $code;
    }

    private function wrapInCondition(string $code, string $condition): string
    {
        return '<!--[if '.$condition.']>'.$code.'<![endif]-->';
    }
}
