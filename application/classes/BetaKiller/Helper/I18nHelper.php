<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;

class I18nHelper
{
    private const KEY_REGEX = '/^[a-z0-9_]+(?:[\.]{1}[a-z0-9_]+)+$/m';

    /**
     * @var string
     */
    private $lang;

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

    /**
     * I18n constructor.
     *
     * @param \BetaKiller\Helper\AppEnvInterface    $appEnv
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     */
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

        // Use app`s main language as a default one
        $this->setLang($this->getAppDefaultLanguage());

        // Save all absent i18n keys if in development env
        if ($this->appEnv->inDevelopmentMode()) {
            \I18n::saveMissingKeys();
        }
    }

    public function getAppDefaultLanguage(): string
    {
        // First language is primary
        return $this->allowedLanguages[0];
    }

    public function getAllowedLanguages(): array
    {
        return $this->allowedLanguages;
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $value): void
    {
        if (!isset($this->languagesConfig[$value])) {
            throw new Exception('Unknown language :lang, only these are allowed: :allowed', [
                ':lang'    => $value,
                ':allowed' => implode(', ', $this->allowedLanguages),
            ]);
        }

        $this->lang = $value;

        // Set I18n lang
        \I18n::lang($value);
    }

    public function getLocale(): string
    {
        $lang = $this->lang ?: $this->getAppDefaultLanguage();

        return $this->languagesConfig[$lang];
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function isI18nKey(string $key): bool
    {
        return (bool)preg_match(self::KEY_REGEX, $key);
    }

    public function translate(string $lang, string $key, array $values = null): string
    {
        return __($key, $values, $lang);
    }
}
