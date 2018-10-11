<?php
declare(strict_types=1);

namespace BetaKiller\I18n;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;
use BetaKiller\Helper\AppEnvInterface;

class I18nFacade
{
    /**
     * @var \BetaKiller\Helper\AppEnvInterface
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * "lang codename" => "default locale"
     *
     * @var array
     */
    private $languagesConfig;

    /**
     * @var string[]
     */
    private $allowedLanguages;

    public function __construct(AppEnvInterface $appEnv, AppConfigInterface $appConfig)
    {
        $this->appEnv    = $appEnv;
        $this->appConfig = $appConfig;

        $this->init();
    }

    private function init(): void
    {
        $this->languagesConfig  = $this->appConfig->getAllowedLanguages();
        $this->allowedLanguages = \array_keys($this->languagesConfig);

        if (!$this->allowedLanguages) {
            throw new Exception('Define app languages in config/app.php');
        }

        // Save all absent i18n keys if in development env
        if ($this->appEnv->inDevelopmentMode()) {
            \I18n::saveMissingKeys();
        }
    }

    public function hasLanguage(string $lang): bool
    {
        return isset($this->languagesConfig[$lang]);
    }

    public function getDefaultLanguage(): string
    {
        // First language is primary
        return $this->allowedLanguages[0];
    }

    public function getAllowedLanguages(): array
    {
        return $this->allowedLanguages;
    }

    public function getLanguageLocale(string $lang): string
    {
        return $this->languagesConfig[$lang];
    }

    public function translate(string $lang, string $key, array $values = null): string
    {
        $string = \I18n::get($key, $lang);

        if ($values) {
            // Add prefix if does not exists
            $values = \I18n::addPlaceholderPrefixToKeys($values);
        }

        return empty($values) ? $string : strtr($string, $values);
    }
}
