<?php

/**
 * Class Controller
 * Basic controller with common helpers
 */
abstract class Controller extends Controller_Proxy
{
    use BetaKiller\Utils\Kohana\ControllerHelperTrait;

    const JSON_SUCCESS = Response::JSON_SUCCESS;
    const JSON_ERROR   = Response::JSON_ERROR;

    /**
     * @Inject
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    protected static $_after_callbacks = [];

    public function before()
    {
        \BetaKiller\DI\Container::getInstance()->injectOn($this);

        parent::before();

        $this->check_connection_protocol();

        $this->init_i18n();
    }

    /**
     * Checks for current protocol and makes redirect if it`s not equal to the base protocol
     */
    protected function check_connection_protocol()
    {
        // Redirect only initial requests from HTTP
        if($this->request->is_initial() && $this->request->client_ip() !== '0.0.0.0') {
            $base_protocol = parse_url(Kohana::$base_url, PHP_URL_SCHEME);

            $is_secure_needed = ($base_protocol === 'https');

            $is_secure = $this->request->secure();

            if (($is_secure_needed && !$is_secure) || ($is_secure && !$is_secure_needed)) {
                $url = $this->request->url($base_protocol);
                $this->redirect($url);
            }
        }
    }

    /**
     * I18n initialization
     *
     * @throws HTTP_Exception_500
     */
    protected function init_i18n()
    {
        // Get lang from cookie
        $userLang = Cookie::get(I18n::COOKIE_NAME);

        $allowed_languages = I18n::lang_list();

        if ($userLang && !in_array($userLang, $allowed_languages, true)) {
            throw new HTTP_Exception_500('Unknown language :lang, only these are allowed: :allowed', [
                ':lang' => $userLang,
                ':allowed' => implode(', ', $allowed_languages),
            ]);
        }

        // If current lang is not set
        if (!$userLang) {
            // If user is authorized
            if ($this->user->isGuest()) {
                // Get its lang
                $userLang = $this->user->getLanguageName();
            } // Else detect the preferred lang
            else {
                /** @var HTTP_Header $headers */
                $headers   = $this->request->headers();
                $userLang = $headers->preferred_language($allowed_languages);
            }
        }

        if (!$userLang) {
            $userLang = I18n::lang_list()[0];
        }

        // Store lang in cookie
        Cookie::set(I18n::COOKIE_NAME, $userLang);

        // Set I18n lang
        I18n::lang($userLang);

        // Save all absent keys if in development env
        if (!Kohana::in_production(true)) {
            register_shutdown_function([I18n::class, 'write']);
        }
    }

    public function after()
    {
        parent::after();

        $this->response->check_if_not_modified_since();
    }


    /**
     * View Factory for current request (directory/controller/action)
     *
     * @param string|null $file View file path (relative to directory/controller)
     *
     * @return View
     */
    protected function view($file = null)
    {
        $path = $this->request->directory()
            ? $this->request->directory().DIRECTORY_SEPARATOR.$this->request->controller()
            : $this->request->controller();

        $file = $file ?: $this->request->action();

        return $this->view_factory($path.DIRECTORY_SEPARATOR.$file);
    }

    /**
     * Helper for View::factory()
     *
     * @param string $file
     * @param array  $data
     *
     * @return View
     */
    protected function view_factory($file = null, array $data = null)
    {
        return View::factory($file, $data);
    }
}
