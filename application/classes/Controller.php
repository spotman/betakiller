<?php

use BetaKiller\Helper\I18n;

/**
 * Class Controller
 * Basic controller with common helpers
 */
abstract class Controller extends Controller_Proxy
{
    use BetaKiller\Utils\Kohana\ControllerHelperTrait;

    protected const JSON_SUCCESS = Response::JSON_SUCCESS;
    protected const JSON_ERROR   = Response::JSON_ERROR;

    /**
     * @Inject
     * @var I18n
     */
    private $i18n;

    /**
     * @Inject
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    protected static $_after_callbacks = [];

    /**
     * @throws \BetaKiller\Exception
     * @throws \DI\DependencyException
     * @throws \InvalidArgumentException
     */
    public function before(): void
    {
        parent::before();

        \BetaKiller\DI\Container::getInstance()->injectOn($this);

        $this->check_connection_protocol();

        $this->i18n->initialize($this->request);
    }

    /**
     * Checks for current protocol and makes redirect if it`s not equal to the base protocol
     */
    protected function check_connection_protocol()
    {
        // Redirect only initial requests from HTTP
        if ($this->request->is_initial() && $this->request->client_ip() !== '0.0.0.0') {
            $base_protocol = parse_url($this->appConfig->getBaseUrl(), PHP_URL_SCHEME);

            $is_secure_needed = ($base_protocol === 'https');

            $is_secure = $this->request->secure();

            if (($is_secure_needed && !$is_secure) || ($is_secure && !$is_secure_needed)) {
                $url = $this->request->url($base_protocol);
                $this->redirect($url);
            }
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
