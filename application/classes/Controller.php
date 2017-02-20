<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Controller
 * Basic controller with common helpers
 */
abstract class Controller extends Controller_Proxy
{
    use BetaKiller\Helper\CurrentUserTrait;
    use BetaKiller\Utils\Kohana\ControllerHelperTrait;

    const JSON_SUCCESS = Response::JSON_SUCCESS;
    const JSON_ERROR = Response::JSON_ERROR;

    protected static $_after_callbacks = array();

    public function before()
    {
        parent::before();

        $this->check_connection_protocol();

        $this->init_i18n();
    }

    /**
     * Checks for current protocol and makes redirect if it`s not equal to the base protocol
     */
    protected function check_connection_protocol()
    {
        $base_protocol = parse_url(Kohana::$base_url, PHP_URL_SCHEME);

        $is_secure_needed = ($base_protocol == 'https');

        $is_secure = $this->request->secure();

        if ( ($is_secure_needed && !$is_secure) || ($is_secure && !$is_secure_needed))
        {
            $url = $this->request->url($base_protocol);
            $this->redirect($url);
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
        $user_lang = Cookie::get(I18n::COOKIE_NAME);

        $allowed_languages = I18n::lang_list();

        if ($user_lang AND ! in_array($user_lang, $allowed_languages) )
            throw new HTTP_Exception_500(
                'Unknown language :lang, only these are allowed: :allowed',
                array(':lang' => $user_lang, ':allowed' => implode(', ', $allowed_languages))
            );

        // If current lang is not set
        if ( ! $user_lang )
        {
            // Get current user
            $user = $this->current_user(TRUE);

            // If user is authorized
            if ( $user )
            {
                // Get its lang
                $user_lang = $user->get_language_name();
            }
            // Else detect the preferred lang
            else
            {
                /** @var HTTP_Header $headers */
                $headers = $this->request->headers();
                $user_lang = $headers->preferred_language($allowed_languages);
            }
        }

        if (!$user_lang) {
            $user_lang = I18n::lang_list()[0];
        }

        // Store lang in cookie
        Cookie::set(I18n::COOKIE_NAME, $user_lang);

        // Set I18n lang
        I18n::lang($user_lang);

        // Save all absent keys if in development env
        if ( ! Kohana::in_production(TRUE) ) {
            register_shutdown_function(array("I18n", "write"));
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
     * @return View
     */
    protected function view($file = NULL)
    {
        $path = $this->request->directory()
            ? $this->request->directory() . DIRECTORY_SEPARATOR . $this->request->controller()
            : $this->request->controller();

        $file = $file ?: $this->request->action();

        $view = $this->view_factory($path . DIRECTORY_SEPARATOR . $file);

        return $view;
    }

    /**
     * Helper for View::factory()
     *
     * @param string $file
     * @param array $data
     * @return View
     */
    protected function view_factory($file = NULL, array $data = NULL)
    {
        return View::factory($file, $data);
    }
}
