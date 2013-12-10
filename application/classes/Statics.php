<?php defined('SYSPATH') OR die('No direct script access.');

class Statics {

    use Util_Singleton;

    protected function css()
    {
        return CSS::instance();
    }

    protected function js()
    {
        return JS::instance();
    }

    /**
     * Хелпер для добавления библиотеки jquery на страницу
     * @return $this
     */
    public function jquery()
    {
        $this->js()->jquery();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.ui на страницу (вместе с локализацией)
     * @return $this
     */
    public function jquery_ui()
    {
        $this->js()->jquery_ui();
        $this->css()->jquery_ui();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.validate на страницу (вместе с локализацией)
     * @return $this
     */
    public function jquery_validation()
    {
        $this->js()->jquery_validation();
        $this->css()->jquery_validation();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.fileupload на страницу
     * @return $this
     */
    public function jquery_fileupload()
    {
        $this->js()->jquery_fileupload();
        $this->css()->jquery_fileupload();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.chosen на страницу
     * @return $this
     */
    public function jquery_chosen()
    {
        $this->js()->jquery_chosen();
        $this->css()->jquery_chosen();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.qtip на страницу
     * @link http://craigsworks.com/projects/qtip2/
     * @return $this
     */
    public function jquery_qtip()
    {
        $this->js()->jquery_qtip();
        $this->css()->jquery_qtip();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.pnotify на страницу
     * @link http://pinesframework.org/pnotify/
     * @return $this
     */
    public function jquery_pnotify()
    {
        $this->js()->jquery_pnotify();
        $this->css()->jquery_pnotify();
        return $this;
    }

    /**
     * Хелпер для добавления jquery.jeditable на страницу
     * @link http://www.appelsiini.net/projects/jeditable
     * @return $this
     */
    public function jquery_jeditable()
    {
        $this->js()->jquery_jeditable();
        return $this;
    }

    /**
     * Хелпер для добавления плагина выбора времени
     * @link http://jonthornton.github.io/jquery-timepicker/
     * @return $this
     */
    public function jquery_timepicker()
    {
        $this->js()->jquery_timepicker();
        $this->css()->jquery_timepicker();
        return $this;
    }

    /**
     * Хелпер для добавления twitter bootstrap на страницу
     * @return $this
     */
    public function bootstrap()
    {
        $this->js()->bootstrap();
        $this->css()->bootstrap();
        return $this;
    }

    /**
     * Хелпер для добавления bootstrap диалоговых окон: алертов, конфирмов, промптов
     * @return $this
     */
    public function bootstrap_bootbox()
    {
        $this->js()->bootstrap_bootbox();
        return $this;
    }

    /**
     * Хелпер для добавления библиотеки underscore
     * @link http://underscorejs.org/
     * @return $this
     */
    public function underscore()
    {
        $this->js()->underscore();
        return $this;
    }

    /**
     * Хелпер для добавления редактора tinyMCE
     * @link http://www.tinymce.com/
     * @return $this
     */
    public function tinyMCE()
    {
        $this->js()->tinyMCE();
        return $this;
    }

}