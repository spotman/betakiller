<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;
use BetaKiller\Model\UserInterface;

class I18nHelper
{
    private const KEY_REGEX   = '/^[a-z0-9_]+(?:[\.]{1}[a-z0-9_]+)+$/m';
    private const COOKIE_NAME = 'lang';

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
     *
     * @throws \BetaKiller\Exception
     */
    public function __construct(AppEnvInterface $appEnv, AppConfigInterface $appConfig)
    {
        $this->appEnv    = $appEnv;
        $this->appConfig = $appConfig;

        $this->initDefault();
    }

    /**
     * @throws \BetaKiller\Exception
     */
    private function initDefault(): void
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

    /**
     * @param \Request $request
     *
     * @throws \BetaKiller\Exception
     */
    public function initFromRequest(\Request $request): void
    {
        // Get lang from cookie
        $browserLang = $this->loadCookie();

        // Detect the browser` preferred lang if current lang is not set
        if (!$browserLang && !$this->appEnv->isCLI()) {
            /** @var \HTTP_Header $headers */
            $headers = $request->headers();

            $preferredLang = $headers->preferred_language($this->allowedLanguages);

            if ($preferredLang) {
                $browserLang = $preferredLang;
            }
        }

        if ($browserLang && !\in_array($browserLang, $this->allowedLanguages, true)) {
            throw new Exception('Unknown language :lang, only these are allowed: :allowed', [
                ':lang'    => $browserLang,
                ':allowed' => implode(', ', $this->allowedLanguages),
            ]);
        }

        if ($browserLang) {
            $this->setLang($browserLang);
        }

        $this->saveCookie();
    }

    public function initFromUser(UserInterface $user): void
    {
        if (!$user->isGuest() && $lang = $user->getLanguageName()) {
            $this->setLang($lang);
        }

        if (!$this->appEnv->isCLI()) {
            $this->saveCookie();
        }
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

    private function loadCookie(): ?string
    {
        return \Cookie::get(self::COOKIE_NAME);
    }

    private function saveCookie(): void
    {
        // Store lang in cookie
        \Cookie::set(self::COOKIE_NAME, $this->lang);
    }
}
