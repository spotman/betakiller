<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Helper\RequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\UrlContainerHelper;
use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Model\UserInterface;

class Login extends AbstractIFace
{
    /**
     * @var string Default url for relocate after successful login
     */
    protected $redirectUrl;

    /**
     * @var string
     */
    private $redirectUrlQueryParam = 'redirect_url';

    /**
     * @var string
     */
    private $currentUrl;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @var \BetaKiller\Helper\ResponseHelper
     */
    private $responseHelper;

    /**
     * @var \BetaKiller\Helper\RequestHelper
     */
    private $requestHelper;

    /**
     * @var \BetaKiller\Helper\UrlContainerHelper
     */
    private $urlParametersHelper;

    public function __construct(
        UserInterface $user,
        RequestHelper $reqHelper,
        ResponseHelper $respHelper,
        UrlContainerHelper $urlParamsHelper
    ) {
        parent::__construct();

        $this->user                = $user;
        $this->requestHelper       = $reqHelper;
        $this->responseHelper      = $respHelper;
        $this->urlParametersHelper = $urlParamsHelper;

        $this->detectRedirectUrl();
    }

    private function detectRedirectUrl(): void
    {
        $currentUrl = $this->requestHelper->getCurrentUrl();

        if ($currentUrl) {
            $queryString      = http_build_query($this->requestHelper->getUrlQueryParts());
            $this->currentUrl = '/'.ltrim($currentUrl, '/');

            if ($queryString) {
                $this->currentUrl .= '?'.$queryString;
            }

            $redirectQueryPart = $this->urlParametersHelper->getQueryPart($this->redirectUrlQueryParam);

            // Initialize redirect url
            $this->redirectUrl = urldecode($redirectQueryPart) ?: $this->currentUrl;
        }
    }

    /**
     * @throws \HTTP_Exception_302
     */
    public function before(): void
    {
        $this->setModelUri();

        // If user already authorized (skip this step in CLI mode)
        if (PHP_SAPI !== 'cli' && !$this->user->isGuest()) {
            if ($this->redirectUrl === $this->currentUrl) {
                // Prevent infinite loops
                $this->redirectUrl = '/';
            }

            // Redirect him
            $this->responseHelper->redirect($this->redirectUrl);
        }
    }

    public function getData(): array
    {
        return [
            'redirect_url' => $this->redirectUrl,
        ];
    }

    private function setModelUri(): void
    {
        $uri = $this->getModel()->getUri();

        $redirectQuery = $this->redirectUrl
            ? '?'.$this->redirectUrlQueryParam.'='.urlencode($this->redirectUrl)
            : null;

        $this->getModel()->setUri($uri.$redirectQuery);
    }
}
