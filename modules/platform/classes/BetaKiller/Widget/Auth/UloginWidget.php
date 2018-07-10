<?php
namespace BetaKiller\Widget\Auth;

use BetaKiller\Widget\AbstractPublicWidget;
use Ulogin;
use Ulogin_Exception;

class UloginWidget extends AbstractPublicWidget
{
    public function getData(): array
    {
        $instance = $this->uloginFactory();

        $auth_callback = 'ulogin_auth_callback';
        $instance->set_javascript_callback($auth_callback);

        return [
            'token_login_url' => $instance->get_redirect_uri(),
            'auth_callback'   => $auth_callback,
            'ulogin_view'     => $instance->render(),
        ];
    }

    /**
     * @throws \Throwable
     * @throws \Ulogin_Exception
     */
    public function actionAuth()
    {
        $this->content_type_json();

        $uLogin = $this->uloginFactory();

        try {
            $uLogin->login();
            $this->send_json();
        } catch (Ulogin_Exception $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    /**
     * @return Ulogin
     */
    protected function uloginFactory(): Ulogin
    {
        return Ulogin::factory()
            ->set_redirect_uri($this->url('auth'));
    }
}
