<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * TODO convert to the widget
 * Class Controller_Auth
 */
class Controller_Auth extends Controller_Basic {

    /**
     * @var string Default url for relocate after login
     */
    protected $redirect_url = "/";

    public function action_login()
    {
        // Покажем форму авторизации, если экшн вызван через HMVC
        if ( ! $this->request->is_initial() )
        {
            $this->show_login_form();
            return;
        }

        if ( $this->request->is_ajax() )
        {
            $this->response->content_type(Response::JSON);
        }

        // Разрешаем вызывать экшн незалогиненым пользователям
        $user = Env::user(TRUE);

        // Получаем адрес, на который нужно вернуться после авторизации
        $this->redirect_url = $this->request->query("return") ?: "/";

        // Если пользователь уже авторизован
        if ( $user )
        {
            // Если это AJAX-запрос из формы авторизации
            if ( $this->request->is_ajax() )
            {
                // Возвращаем соответствующий ответ
                $this->send_json(Response::JSON_SUCCESS);
                return;
            }
            // Иначе перенаправляем его на главную страницу
            else
            {
                HTTP::redirect($this->redirect_url);
            }
        }

        $user_login     = $this->request->post("user-login");
        $user_password  = $this->request->post("user-password");

        // Авторизация по POST-запросу из веб-формы
        if ( $user_login AND $user_password )
        {
            // TODO валидация данных перед проверкой

            // Проводим аутентификацию / авторизацию
            Env::auth()->login($user_login, $user_password);
        }

        // Если пользователь авторизован
        if ( Env::user(TRUE) )
        {
            // Если это AJAX-запрос
            if ( $this->request->is_ajax() )
            {
                // Возвращаем соответствующий ответ
                $this->send_json(Response::JSON_SUCCESS);
            }
            // Иначе перезагружаем страницу, чтобы не было назойливых сообщений после F5
            else
            {
                HTTP::redirect($this->redirect_url);
            }
        }
        else
        // Иначе показываем веб-форму
        {
            $this->show_login_form($user_login);
        }
    }

    protected function show_login_form($username = NULL)
    {
        $content = $this->view('login');
        $content->set('username', HTML::chars($username));
        $content->set('redirect_url', $this->redirect_url);

        $this->send_view($content);
    }

    public function action_logout()
    {
        // Sign out the user
        Env::auth()->logout(TRUE);

        // Redirect to the user account and then the signin page if logout worked as expected
        throw (new HTTP_Exception_401())->remove_redirect_url();
    }

}
