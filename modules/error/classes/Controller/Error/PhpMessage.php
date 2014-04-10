<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Error_PhpMessage extends Controller_Developer {

    // public    $template = 'templates/frontend';

    /**
     * @var Model_Error_Message_Php
     */
    protected $model;

    public function before()
    {
        parent::before();

        $hash = $this->request->param("hash");

        /** @var Model_Error_Message_Php model */
        $this->model = Mango::factory("Error_Message_Php");

        // Пробуем найти ошибку по её хешу
        $this->model->find_by_hash($hash);

        if ( ! $this->model->loaded() )
            throw new HTTP_Exception_Verbal("Ошибки с таким хешом не существует");
    }

    /**
     * Показыывает сводную информацию о выбранной ошибке
     */
    public function action_show()
    {
        // $this->jquery()->bootstrap();

        $content = $this->view('message');

//        $content = View::factory("error/php/message");
        $content->hash = $this->model->get_hash();
        $content->trace = $this->model->get_trace();

        // Адреса ресурсов, по которым возникла ошибка
        $content->urls = $this->model->get_urls();

        // Пути к файлам, в которых возникла ошибка
        $content->paths = $this->model->get_paths();

        // Счётчик общего кол-ва появлений ошибки
        $content->counter = $this->model->get_counter();

        // Timestamp времени последнего упоминания об ошибке
        $content->time = $this->model->get_time();

        // История изменений
        $content->history = $this->model->get_formatted_history();

        // Ошибка исправлена?
        $content->is_resolved = $this->model->is_resolved();

        $this->send_view($content);
    }

    /**
     * Удаляет выбранную ошибку из базы
     */
    public function action_delete()
    {
        $this->model->delete();
        $this->redirect("/errors/php");
    }

    /**
     * Отмечает ошибку как исправленную
     */
    public function action_resolved()
    {
        $this->model->mark_resolved();
        $this->model->update();
        $this->redirect("/errors/php");
    }

}