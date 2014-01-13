<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * Class Assets
 * @todo переписать всё это на конфиг, чтобы не писать каждый раз методы для новых ассетов
 * @todo оставить возможность создавать кастомные методы для подключения ассетов (в этом случае конфиг игнорируется)
 */

class Assets {

    use Util_Singleton;

    protected $_config;

    public function add($name)
    {
        $method_name = $this->make_method_name($name);

        // Search for specific method in current class
        if ( method_exists($this, $method_name) )
        {
            $this->$method_name();
        }
        else
        {
            $config = $this->config()->get($name);

            if ( ! $config )
                throw new HTTP_Exception_500('Unknown asset :name', array(':name' => $name));

            // TODO process dependencies

            if ( isset($config['js']) )
            {
                $this->process_static($this->js(), $method_name, $config['js']);
            }

            if ( isset($config['css']) )
            {
                $this->process_static($this->css(), $method_name, $config['css']);
            }
        }

        return $this;
    }

    protected function config()
    {
        if ( ! $this->_config )
        {
            $this->_config = Kohana::config('assets');
        }

        return $this->_config;
    }

    protected function css()
    {
        return CSS::instance();
    }

    protected function js()
    {
        return JS::instance();
    }

    /**
     * @param JS|CSS $object
     * @param string $method_name
     * @param mixed $files
     * @throws HTTP_Exception_500
     */
    protected function process_static($object, $method_name, $files)
    {
        // Search for specific method in object
        if ( $files === TRUE AND method_exists($object, $method_name) )
        {
            $object->$method_name();
        }
        else if ( $files !== TRUE )
        {
            if ( ! is_array($files) )
            {
                $files = array($files);
            }

            foreach ( $files as $file )
            {
                $object->add($file);
            }
        }
        else
            throw new HTTP_Exception_500('Can not process asset static :method', array(':method' => $method_name));
    }

    protected function make_method_name($name)
    {
        return str_replace('.', '_', $name);
    }

//    /**
//     * Хелпер для добавления библиотеки jquery на страницу
//     * @return $this
//     */
//    public function jquery()
//    {
//        $this->js()->jquery();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления jquery.ui на страницу (вместе с локализацией)
//     * @return $this
//     */
//    public function jquery_ui()
//    {
//        $this->js()->jquery_ui();
//        $this->css()->jquery_ui();
//        return $this;
//    }
//
//    /**
//     * Хелпер для добавления jquery.validate на страницу (вместе с локализацией)
//     * @return $this
//     */
//    public function jquery_validation()
//    {
//        $this->js()->jquery_validation();
//        $this->css()->jquery_validation();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления jquery.fileupload на страницу
//     * @return $this
//     */
//    public function jquery_fileupload()
//    {
//        $this->js()->jquery_fileupload();
//        $this->css()->jquery_fileupload();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления jquery.chosen на страницу
//     * @return $this
//     */
//    public function jquery_chosen()
//    {
//        $this->js()->jquery_chosen();
//        $this->css()->jquery_chosen();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления jquery.qtip на страницу
//     * @link http://craigsworks.com/projects/qtip2/
//     * @return $this
//     */
//    public function jquery_qtip()
//    {
//        $this->js()->jquery_qtip();
//        $this->css()->jquery_qtip();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления jquery.pnotify на страницу
//     * @link http://pinesframework.org/pnotify/
//     * @return $this
//     */
//    public function jquery_pnotify()
//    {
//        $this->js()->jquery_pnotify();
//        $this->css()->jquery_pnotify();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления jquery.jeditable на страницу
//     * @link http://www.appelsiini.net/projects/jeditable
//     * @return $this
//     */
//    public function jquery_jeditable()
//    {
//        $this->js()->jquery_jeditable();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления плагина выбора времени
//     * @link http://jonthornton.github.io/jquery-timepicker/
//     * @return $this
//     */
//    public function jquery_timepicker()
//    {
//        $this->js()->jquery_timepicker();
//        $this->css()->jquery_timepicker();
//        return $this;
//    }

//    /**
//     * Helper for adding Twitter Bootstrap JS/CSS
//     *
//     * @param string $version
//     * @return $this
//     */
//    public function bootstrap($version = Assets::BOOTSTRAP_V3)
//    {
//        $this->js()->bootstrap($version);
//        $this->css()->bootstrap($version);
//        return $this;
//    }

//    /**
//     * Хелпер для добавления bootstrap диалоговых окон: алертов, конфирмов, промптов
//     * @return $this
//     */
//    public function bootstrap_bootbox()
//    {
//        $this->js()->bootstrap_bootbox();
//        return $this;
//    }
//
//    public function bootstrap_hover_dropdown()
//    {
//        $this->js()->bootstrap_hover_dropdown();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления библиотеки underscore
//     * @link http://underscorejs.org/
//     * @return $this
//     */
//    public function underscore()
//    {
//        $this->js()->underscore();
//        return $this;
//    }

//    /**
//     * Хелпер для добавления редактора tinyMCE
//     * @link http://www.tinymce.com/
//     * @return $this
//     */
//    public function tinyMCE()
//    {
//        $this->js()->tinyMCE();
//        return $this;
//    }

//    public function masonry()
//    {
//        $this->js()->masonry();
//        return $this;
//    }
//
//    public function jquery_mmenu()
//    {
//        $this->js()->jquery_mmenu();
//        $this->css()->jquery_mmenu();
//        return $this;
//    }

}