<?php
namespace BetaKiller\IFace\Auth;

use BetaKiller\Helper\RequestHelper;
use BetaKiller\Helper\ResponseHelper;
use BetaKiller\Helper\UrlParametersHelper;
use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Model\UserInterface;

class Login extends AbstractIFace
{
    /**
     * @var string Default url for relocate after successful login
     */
    protected $redirectUrl = null;

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
     * @var \BetaKiller\Helper\UrlParametersHelper
     */
    private $urlParametersHelper;

    public function __construct(
        UserInterface $user,
        RequestHelper $reqHelper,
        ResponseHelper $respHelper,
        UrlParametersHelper $urlParamsHelper
    ) {
        parent::__construct();

        $this->user = $user;
        $this->requestHelper = $reqHelper;
        $this->responseHelper = $respHelper;
        $this->urlParametersHelper = $urlParamsHelper;

        $this->detectRedirectUrl();
    }

    private function detectRedirectUrl(): void
    {
        $currentUrl = $this->requestHelper->getCurrentUrl();

        if ($currentUrl) {
            $queryString = http_build_query($this->requestHelper->getUrlQueryParts());
            $this->currentUrl = '/'.ltrim($currentUrl, '/');

            if ($queryString) {
                $this->currentUrl .= '?'.$queryString;
            }

            $redirectQueryPart = $this->urlParametersHelper->getQueryPart($this->redirectUrlQueryParam);

            // Initialize redirect url
            $this->redirectUrl = urldecode($redirectQueryPart) ?: $this->currentUrl;
        }
    }

    public function before(): void
    {
        // If user already authorized
        if (!$this->user->isGuest()) {
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

    protected function setRedirectUrl($redirectUrl)
    {
        $this->redirectUrl = $redirectUrl;

        return $this;
    }

    public function getUri(): string
    {
        $redirect_query = $this->redirectUrl
            ? '?'.$this->redirectUrlQueryParam.'='.urlencode($this->redirectUrl)
            : null;

        return parent::getUri().$redirect_query;
    }
}
