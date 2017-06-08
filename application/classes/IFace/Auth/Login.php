<?php

use BetaKiller\IFace\AbstractIFace;
use BetaKiller\Model\UserInterface;

class IFace_Auth_Login extends AbstractIFace
{
    /**
     * @var string Default url for relocate after successful login
     */
    protected $redirectUrl = null;

    protected $redirectUrlQueryParam = 'redirect_url';

    protected $selfUrl;

    /**
     * @var UserInterface
     */
    protected $user;

    /**
     * @Inject
     * @var \BetaKiller\Helper\ResponseHelper
     */
    private $responseHelper;

    public function __construct(UserInterface $user)
    {
        parent::__construct();

        $this->user = $user;

        $request = Request::current();

        if ($request) {
            $queryString   = http_build_query($request->query());
            $this->selfUrl = '/'.ltrim($request->uri(), '/');

            if ($queryString) {
                $this->selfUrl .= '?'.$queryString;
            }

            // Initialize redirect url
            $this->redirectUrl = urldecode($request->query($this->redirectUrlQueryParam)) ?: $this->selfUrl;
        }
    }

    public function before(): void
    {
        // If user already authorized
        if (!$this->user->isGuest()) {
            if ($this->redirectUrl === $this->selfUrl) {
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

    public function redirectToCurrentIFace()
    {
        $current = $this->ifaceHelper->getCurrentIFace();
        $url = $current->url(null, false);

        return $this->setRedirectUrl($url);
    }

    public function getUri(): string
    {
        $redirect_query = $this->redirectUrl
            ? '?'.$this->redirectUrlQueryParam.'='.urlencode($this->redirectUrl)
            : null;

        return parent::getUri().$redirect_query;
    }
}
