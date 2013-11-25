<? defined('SYSPATH') OR die('No direct script access.');

class JS {

    use Singleton;

    /**
     * Хелпер к добавлению локально размещённого скрипта
     * @param $filename
     * @return string Код для вставки скрипта на страницу
     */
    public static function add($filename)
    {
        return self::add_static($filename);
    }

    /**
     * Добавляет файл скрипта по http пути
     * @param $url
     * @return string Код для вставки скрипта на страницу
     */
    public static function add_public($url)
    {
        // Добавляем слеш в начале, если его нет
        if ( mb_substr($url, 0, 4) != 'http' AND mb_substr($url, 0, 1) != "/" )
        {
            $url = "/". $url;
        }

        return StaticJs::instance()->addJs($url);
    }

    /**
     * Добавляет файл скрипта, размещённый в одной из директорий static-files
     * @param $filename
     * @return string Код для вставки скрипта на страницу
     */
    public static function add_static($filename)
    {
        return StaticJs::instance()->addJsStatic($filename);
    }

    /**
     * Добавляет инлайн скрипт в документ
     * @param $string
     */
    public static function add_inline($string)
    {
        StaticJs::instance()->addJsInline($string);
    }

    /**
     * Добавляет javascript-код, который будет выполнен после полной загрузки страницы
     * @param $string
     */
    public static function add_onload($string)
    {
        StaticJs::instance()->addJsOnload($string);
    }

    public static function get_files()
    {
        return StaticJs::instance()->getJs();
    }

    public static function get_inline()
    {
        return StaticJs::instance()->getJsInline();
    }

    public static function get_all()
    {
        return self::get_files() . self::get_inline();
    }

    public static function jquery()
    {
        self::add_static("jquery/jquery-1.8.3.js");

        // Добавляем наши маленькие плагины и утилиты
        self::jquery_utils();
    }

    public static function jquery_ui()
    {
        self::add_static("jquery/ui/jquery-ui-1.9.2.custom.js");

        $lang_name = i18n::lang();

        // Локализация datepicker
        if ( $lang_name != "en" )
        {
            self::add_static("jquery/ui/jquery.ui.datepicker-$lang_name.js");
        }

        self::add_inline('$.datepicker.setDefaults({ dateFormat: "dd.mm.yy" });');
    }

    public static function jquery_validation()
    {
        self::add_static("jquery/validate/jquery.validate.min.js");

        $lang_name = i18n::lang();

        // локализация jquery.validate
        if ( $lang_name != "en" )
        {
            self::add_static("jquery/validate/messages_$lang_name.js");
        }
    }

    public static function jquery_fileupload()
    {
        self::add_static("jquery/fileupload/jquery.fileupload.js");
        self::add_static("jquery/fileupload/jquery.iframe-transport.js");
    }

    public static function jquery_chosen()
    {
        self::add_static("jquery/chosen/chosen.jquery.min.js");
    }

    public static function jquery_cookie()
    {
        self::add_static("jquery/jquery.cookie.js");
    }

    /**
     * Хелпер для добавления jquery.qtip на страницу
     * @link http://craigsworks.com/projects/qtip2/
     */
    public static function jquery_qtip()
    {
        self::add_static("jquery/qtip/jquery.qtip.js");
    }

    /**
     * Хелпер для добавления jquery.pnotify на страницу
     * @link http://pinesframework.org/pnotify/
     */
    public static function jquery_pnotify()
    {
        self::add_static("jquery/pnotify/jquery.pnotify.js");
    }

    /**
     * @link http://www.appelsiini.net/projects/jeditable
     */
    public static function jquery_jeditable()
    {
        self::add_static("jquery/jeditable/jquery.jeditable.js");
    }

    /**
     * Наши утилиты
     */
    public static function jquery_utils()
    {
        self::add_static("jquery/utils.js");
    }

    /**
     * Хелпер для добавления плагина выбора времени
     * @link http://jonthornton.github.io/jquery-timepicker/
     */
    public static function jquery_timepicker()
    {
        self::add_static("jquery/timepicker/jquery.timepicker.js");
    }

    public static function bootstrap()
    {
        self::add_static("bootstrap/js/bootstrap.js");
    }

    public static function bootstrap_bootbox()
    {
        self::add_static("bootstrap/bootbox/bootbox.min.js");
    }

    /**
     * Хелпер для добавления библиотеки underscore
     * @link http://underscorejs.org/
     */
    public static function underscore()
    {
        self::add_static("underscore/underscore.js");
    }

    /**
     * Хелпер для добавления редактора tinyMCE
     * @link http://www.tinymce.com/
     */
    public static function tinyMCE()
    {
        self::add_static("tiny_mce/tiny_mce.js");
        self::add_inline('tinyMCE.baseURL = "{static_url}tiny_mce"');
    }

    public static function js_error_catcher()
    {
        self::add_static("js-error-catcher.js");
    }

    /**
     * Устанавливает document.domain на текущей странице
     * @param null|string $domain
     * @todo выпилить, когда мы полностью перейдём на Kohana и откажемся от субдоменов
     */
    public static function set_document_domain($domain = NULL)
    {
        if ( ! $domain )
        {
            $domain = Cookie::$domain;
        }

        self::add_inline("document.domain = '$domain'");
    }
}