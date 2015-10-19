<? defined('SYSPATH') OR die('No direct script access.');

class JS {

    use \BetaKiller\Utils\Instance\Singleton;

    /**
     * Хелпер к добавлению локально размещённого скрипта
     * @param $filename
     * @return string Код для вставки скрипта на страницу
     */
    public function add($filename)
    {
        return $this->add_static($filename);
    }

    /**
     * Добавляет файл скрипта по http пути
     * @param $url
     * @return string Код для вставки скрипта на страницу
     */
    public function add_public($url)
    {
        // Добавляем слеш в начале, если его нет
        if ( mb_substr($url, 0, 4) != 'http' AND mb_substr($url, 0, 1) != '/' )
        {
            $url = '/'. $url;
        }

        return StaticJs::instance()->addJs($url);
    }

    /**
     * Добавляет файл скрипта, размещённый в одной из директорий static-files
     * @param $filename
     * @return string Код для вставки скрипта на страницу
     */
    public function add_static($filename)
    {
        return StaticJs::instance()->addJsStatic($filename);
    }

    /**
     * Добавляет инлайн скрипт в документ
     * @param $string
     */
    public function add_inline($string)
    {
        StaticJs::instance()->addJsInline($string);
    }

    /**
     * Добавляет javascript-код, который будет выполнен после полной загрузки страницы
     * @param $string
     */
    public function add_onload($string)
    {
        StaticJs::instance()->addJsOnload($string);
    }

    public function get_files()
    {
        return StaticJs::instance()->getJs();
    }

    public function get_inline()
    {
        return StaticJs::instance()->getJsInline();
    }

    public function get_all()
    {
        return $this->get_files() . $this->get_inline();
    }

//    public function jquery()
//    {
//        $this->add_static('jquery/jquery-1.8.3.js');
//
//        // Добавляем наши маленькие плагины и утилиты
//        $this->jquery_utils();
//    }

    public function jquery_ui()
    {
        $this->add_static('jquery/ui/jquery-ui-1.9.2.custom.js');

        $lang_name = i18n::lang();

        // Локализация datepicker
        if ( $lang_name != 'en' )
        {
            $this->add_static('jquery/ui/jquery.ui.datepicker-'.$lang_name.'.js');
        }

        $this->add_inline('$.datepicker.setDefaults({ dateFormat: "dd.mm.yy" });');
    }

    public function jquery_validation()
    {
        $this->add_static('jquery/validate/jquery.validate.min.js');

        $lang_name = i18n::lang();

        // локализация jquery.validate
        if ( $lang_name != 'en' )
        {
            $this->add_static('jquery/validate/messages_'.$lang_name.'.js');
        }
    }

    public function jquery_select2()
    {
        $this->add_static('jquery/select2/select2.js');

        $lang_name = i18n::lang();

        // Localize
        if ( $lang_name != 'en' )
        {
            $this->add_static('jquery/select2/select2_locale_'.$lang_name.'.js');
        }
    }

//    public function jquery_fileupload()
//    {
//        $this->add_static('jquery/fileupload/jquery.fileupload.js');
//        $this->add_static('jquery/fileupload/jquery.iframe-transport.js');
//    }

//    public function jquery_chosen()
//    {
//        $this->add_static('jquery/chosen/chosen.jquery.min.js');
//    }

//    public function jquery_cookie()
//    {
//        $this->add_static('jquery/jquery.cookie.js');
//    }

//    /**
//     * Хелпер для добавления jquery.qtip на страницу
//     * @link http://craigsworks.com/projects/qtip2/
//     */
//    public function jquery_qtip()
//    {
//        $this->add_static('jquery/qtip/jquery.qtip.js');
//    }
//
//    /**
//     * Хелпер для добавления jquery.pnotify на страницу
//     * @link http://pinesframework.org/pnotify/
//     */
//    public function jquery_pnotify()
//    {
//        $this->add_static('jquery/pnotify/jquery.pnotify.js');
//    }
//
//    /**
//     * @link http://www.appelsiini.net/projects/jeditable
//     */
//    public function jquery_jeditable()
//    {
//        $this->add_static('jquery/jeditable/jquery.jeditable.js');
//    }

//    /**
//     * Наши утилиты
//     */
//    public function jquery_utils()
//    {
//        $this->add_static('jquery/utils.js');
//    }

//    /**
//     * Хелпер для добавления плагина выбора времени
//     * @link http://jonthornton.github.io/jquery-timepicker/
//     */
//    public function jquery_timepicker()
//    {
//        $this->add_static('jquery/timepicker/jquery.timepicker.js');
//    }

    public function bootstrap()
    {
        return $this->add_static('bootstrap/v3/js/bootstrap.js');
    }

//    public function bootstrap_bootbox()
//    {
//        return $this->add_static('bootstrap/bootbox/bootbox.min.js');
//    }


//    /**
//     * Хелпер для добавления библиотеки underscore
//     * @link http://underscorejs.org/
//     */
//    public function underscore()
//    {
//        return $this->add_static('underscore/underscore.js');
//    }

    /**
     * Хелпер для добавления редактора tinyMCE
     * @link http://www.tinymce.com/
     */
    public function tinyMCE()
    {
        $this->add_static('tiny_mce/tiny_mce.js');
        $this->add_inline('tinyMCE.baseURL = "{static_url}tiny_mce"');
    }

//    /**
//     * Helper for Masonry brick layout plugin
//     *
//     * @link http://masonry.desandro.com/
//     */
//    public function masonry()
//    {
//        $this->add_static('masonry/masonry.pkgd.js');
//    }

//    /**
//     * Helper for JQuery mobile menu plugin
//     *
//     * @link http://mmenu.frebsite.nl/
//     */
//    public function jquery_mmenu()
//    {
//        $this->add_static('jquery/mmenu/jquery.mmenu.all.min.js');
//    }

//    /**
//     * Устанавливает document.domain на текущей странице
//     * @param null|string $domain
//     */
//    public function set_document_domain($domain = NULL)
//    {
//        if ( ! $domain )
//        {
//            $domain = Cookie::$domain;
//        }
//
//        $this->add_inline('document.domain = "'.$domain.'"');
//    }
}
