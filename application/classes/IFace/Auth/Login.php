<?php

use BetaKiller\IFace\IFace;
use BetaKiller\Model\UserInterface;

class IFace_Auth_Login extends IFace
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

    public function __construct(UserInterface $user)
    {
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

    public function before()
    {
        // If user already authorized
        if (!$this->user->isGuest()) {
            if ($this->redirectUrl === $this->selfUrl) {
                // Prevent infinite loops
                $this->redirectUrl = '/';
            }

            // Redirect him
            $this->redirect($this->redirectUrl);
        }
    }

    public function getData()
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

    public function getUri()
    {
        $redirect_query = $this->redirectUrl
            ? '?'.$this->redirectUrlQueryParam.'='.urlencode($this->redirectUrl)
            : null;

        return parent::getUri().$redirect_query;
    }
}
