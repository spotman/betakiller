<?php
namespace BetaKiller\Helper;

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
     * I18n constructor.
     *
     * @param \BetaKiller\Helper\AppEnv $appEnv
     */
    public function __construct(AppEnv $appEnv)
    {
        $this->appEnv = $appEnv;
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

        $allowedLanguages = \I18n::lang_list();

        if ($userLang && !\in_array($userLang, $allowedLanguages, true)) {
            throw new Exception('Unknown language :lang, only these are allowed: :allowed', [
                ':lang'    => $userLang,
                ':allowed' => implode(', ', $allowedLanguages),
            ]);
        }

        // Detect the browser` preferred lang if current lang is not set
        if (!$userLang) {
            /** @var \HTTP_Header $headers */
            $headers  = $request->headers();
            $userLang = $headers->preferred_language($allowedLanguages);
        }

        // Use app`s main language as a fallback
        if (!$userLang) {
            $userLang = $allowedLanguages[0];
        }

        $this->setLang($userLang);

        // Save all absent i18n keys if in development env
        if ($this->appEnv->inDevelopmentMode()) {
            register_shutdown_function([\I18n::class, 'write']);
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

    private function loadCookie(): string
    {
        return \Cookie::get(self::COOKIE_NAME);
    }

    private function saveCookie(): void
    {
        // Store lang in cookie
        \Cookie::set(self::COOKIE_NAME, $this->lang);
    }
}
