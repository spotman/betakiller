<?php

class CSS {

    /**
     * Хелпер к добавлению локально размещённого стиля
     * (чтобы в будущем перенести все файлы в static-files и поменять здесь add_public на add_static)
     * @param $filename
     * @return string Код для вставки стиля на страницу
     */
    public static function add($filename)
    {
        return self::add_public($filename);
    }

    /**
     * Добавляет файл стиля по http пути
     * @param $url
     * @return string Код для вставки стиля на страницу
     */
    public static function add_public($url)
    {
        // Добавляем слеш в начале, если его нет
        if ( mb_substr($url, 0, 4) != 'http' AND mb_substr($url, 0, 1) != "/" )
        {
            $url = "/". $url;
        }

        return StaticCss::instance()->addCss($url);
    }

    /**
     * Добавляет файл стиля, размещённый в одной из директорий static-files
     * @param $filename
     * @return string Код для вставки стиля на страницу
     */
    public static function add_static($filename)
    {
        return StaticCss::instance()->addCssStatic($filename);
    }

    /**
     * Добавляет инлайн стиль в документ
     * @param $string
     */
    public static function add_inline($string)
    {
        StaticCss::instance()->addCssInline($string);
    }

    /**
     * Добавляет стиль для CRM (файл используется в нескольких разных модулях, поэтому создан этот хелпер)
     */
    public static function add_crm_style()
    {
        self::add_static("crm/crm.css");
    }

    public static function get_files()
    {
        return StaticCss::instance()->getCss();
    }

    public static function get_inline()
    {
        return StaticCss::instance()->getCssInline();
    }

    public static function get_all()
    {
        return self::get_files() . self::get_inline();
    }


    public static function jquery_ui()
    {
        self::add_static("jquery/ui/css/smoothness/jquery-ui-1.9.2.custom.css");
    }

    public static function jquery_validation() {}

    public static function jquery_fileupload()
    {
        self::add_static("jquery/fileupload/jquery.fileupload-ui.css");
    }

    public static function jquery_chosen()
    {
        self::add_static("jquery/chosen/chosen.css");
    }

    public static function jquery_qtip()
    {
        self::add_static("jquery/qtip/jquery.qtip.css");
    }

    public static function jquery_pnotify()
    {
        self::add_static("jquery/pnotify/jquery.pnotify.default.css");
    }

    /**
     * Хелпер для добавления плагина выбора времени
     * @link http://jonthornton.github.io/jquery-timepicker/
     */
    public static function jquery_timepicker()
    {
        self::add_static("jquery/timepicker/jquery.timepicker.css");
    }

    public static function bootstrap()
    {
        return self::add_static("bootstrap/css/bootstrap.css");
    }
}