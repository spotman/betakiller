<?php
namespace BetaKiller\Helper;

use BetaKiller\Config\AppConfigInterface;
use BetaKiller\Exception;

class I18n
{
    private const COOKIE_NAME = 'lang';

    /**
     * @var string
     */
    private $lang;

    /**
     * @var \BetaKiller\Helper\AppEnv
     */
    private $appEnv;

    /**
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * I18n constructor.
     *
     * @param \BetaKiller\Helper\AppEnv             $appEnv
     * @param \BetaKiller\Config\AppConfigInterface $appConfig
     */
    public function __construct(AppEnv $appEnv, AppConfigInterface $appConfig)
    {
        $this->appEnv = $appEnv;
        $this->appConfig = $appConfig;
    }

    /**
     * @param \Request $request
     *
     * @throws \BetaKiller\Exception
     */
    public function initialize(\Request $request): void
    {
        // Get lang from cookie
        $userLang = $this->loadCookie();

        $allowedLanguages = $this->appConfig->getAllowedLanguages();

        if (!$allowedLanguages) {
            throw new Exception('Define app languages in config/app.php');
        }

        // Detect the browser` preferred lang if current lang is not set
        if (!$userLang && !$this->appEnv->isCLI()) {
            /** @var \HTTP_Header $headers */
            $headers  = $request->headers();

            if ($preferredLang = $headers->preferred_language($allowedLanguages)) {
                $userLang = $preferredLang;
            }
        }

        // Use app`s main language as a fallback
        if (!$userLang) {
            $userLang = $allowedLanguages[0];
        }

        if (!\in_array($userLang, $allowedLanguages, true)) {
            throw new Exception('Unknown language :lang, only these are allowed: :allowed', [
                ':lang'    => $userLang,
                ':allowed' => implode(', ', $allowedLanguages),
            ]);
        }

        $this->setLang($userLang);

        // Save all absent i18n keys if in development env
        if ($this->appEnv->inDevelopmentMode()) {
            \I18n::saveMissingKeys();
        }
    }

    public function getLang(): string
    {
        return $this->lang;
    }

    public function setLang(string $value): void
    {
        $this->lang = $value;

        // Set I18n lang
        \I18n::lang($value);

        $this->saveCookie();
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
