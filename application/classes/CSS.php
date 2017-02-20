<?php defined('SYSPATH') OR die('No direct script access.');

class CSS {

    use \BetaKiller\Utils\Instance\SingletonTrait;

    /**
     * Хелпер к добавлению локально размещённого стиля
     * @param $filename
     * @return string Код для вставки стиля на страницу
     */
    public function add($filename)
    {
        return $this->add_static($filename);
    }

    /**
     * Добавляет файл стиля по http пути
     * @param $url
     * @return string Код для вставки стиля на страницу
     */
    public function add_public($url)
    {
        // Добавляем слеш в начале, если его нет
        if ( mb_substr($url, 0, 4) != 'http' AND mb_substr($url, 0, 1) != '/' )
        {
            $url = '/'. $url;
        }

        return StaticCss::instance()->addCss($url);
    }

    /**
     * Добавляет файл стиля, размещённый в одной из директорий static-files
     * @param $filename
     * @return string Код для вставки стиля на страницу
     */
    public function add_static($filename)
    {
        return StaticCss::instance()->addCssStatic($filename);
    }

    /**
     * Добавляет инлайн стиль в документ
     * @param $string
     */
    public function add_inline($string)
    {
        StaticCss::instance()->addCssInline($string);
    }

    public function get_files()
    {
        return StaticCss::instance()->getCss();
    }

    public function get_inline()
    {
        return StaticCss::instance()->getCssInline();
    }

    public function get_all()
    {
        return $this->get_files() . $this->get_inline();
    }


//    public function jquery_ui()
//    {
//        return $this->add_static('jquery/ui/css/smoothness/jquery-ui-1.9.2.custom.css');
//    }

//    public function jquery_validation() {}

//    public function jquery_fileupload()
//    {
//        return $this->add_static('jquery/fileupload/jquery.fileupload-ui.css');
//    }

//    public function jquery_chosen()
//    {
//        return $this->add_static('jquery/chosen/chosen.css');
//    }
//
//    public function jquery_qtip()
//    {
//        return $this->add_static('jquery/qtip/jquery.qtip.css');
//    }
//
//    public function jquery_pnotify()
//    {
//        return $this->add_static('jquery/pnotify/jquery.pnotify.default.css');
//    }

//    /**
//     * Хелпер для добавления плагина выбора времени
//     * @link http://jonthornton.github.io/jquery-timepicker/
//     */
//    public function jquery_timepicker()
//    {
//        return $this->add_static('jquery/timepicker/jquery.timepicker.css');
//    }
//
//    /**
//     * Helper for JQuery mobile menu plugin
//     *
//     * @link http://mmenu.frebsite.nl/
//     */
//    public function jquery_mmenu()
//    {
//        $this->add_static('jquery/mmenu/jquery.mmenu.all.css');
//    }


    public function bootstrap()
    {
        return $this->add_static('bootstrap/v3/css/bootstrap.css');
    }
}
