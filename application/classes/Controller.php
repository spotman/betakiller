<?php

use BetaKiller\Helper\I18nHelper;

/**
 * Class Controller
 * Basic controller with common helpers
 */
abstract class Controller extends ControllerProxy
{
    use BetaKiller\Utils\Kohana\ControllerHelperTrait;

    protected const JSON_SUCCESS = Response::JSON_SUCCESS;
    protected const JSON_ERROR   = Response::JSON_ERROR;

    /**
     * @Inject
     * @var I18nHelper
     */
    private $i18n;

    /**
     * @Inject
     * @var \BetaKiller\Config\AppConfigInterface
     */
    private $appConfig;

    /**
     * Creates a new controller instance. Each controller must be constructed
     * with the request object that created it.
     *
     * @param   Request  $request  Request that created the controller
     * @param   Response $response The request's response
     *
     * @return  void
     * @throws \InvalidArgumentException
     * @throws \DI\DependencyException
     */
    public function __construct(\Request $request, \Response $response)
    {
        parent::__construct($request, $response);

        \BetaKiller\DI\Container::getInstance()->injectOn($this);
    }

    /**
     * @throws \BetaKiller\Exception
     */
    public function before(): void
    {
        parent::before();

        $this->checkConnectionProtocol();

        $this->i18n->initFromRequest($this->request);
    }

    /**
     * Checks for current protocol and makes redirect if it`s not equal to the base protocol
     */
    private function checkConnectionProtocol()
    {
        // Redirect only initial requests from HTTP
        if ($this->request->is_initial() && $this->request->getClientIp() !== '0.0.0.0') {
            $baseProtocol = parse_url($this->appConfig->getBaseUrl(), PHP_URL_SCHEME);

            $isSecureNeeded = ($baseProtocol === 'https');

            $isSecure = $this->request->secure();

            if (($isSecureNeeded && !$isSecure) || ($isSecure && !$isSecureNeeded)) {
                $url = $this->request->url($baseProtocol);
                $this->redirect($url);
            }
        }
    }

    public function after()
    {
        parent::after();

        $this->response->check_if_not_modified_since();
    }
}
