<?php defined('SYSPATH') or die('No direct script access.');

/**
 * A patch for the Internationalization (i18n) class.
 *
 * @package    I18n
 * @author Mikito Takada
 */

class I18n extends Kohana_I18n {

    const COOKIE_NAME = 'lang';

    /**
     * @var  string  source language: en-us, es-es, zh-cn, etc
     */
    public static $source = 'en';

    /**
     * @var array Cache of missing strings
     */
    protected static $_cache_missing = array();

    /**
     * @var array List of app languages
     */
    protected static $_lang_list = array();

    /**
     * Getter/setter for list of allowed languages
     * @param array $list
     * @return array
     */
    public static function lang_list(array $list = NULL)
    {
        if ( $list )
        {
            static::$_lang_list = $list;
        }

        return static::$_lang_list;
    }

    /**
     * Returns translation of a string. If no translation exists, the original
     * string will be returned. No parameters are replaced.
     *
     *     $hello = I18n::get('Hello friends, my name is :name');
     *
     * @param   $string string  text to translate
     * @param   $lang string   target language
     * @return  string
     */
    public static function get($string, $lang = NULL)
    {
        if ( ! $lang )
        {
            // Use the global target language
            $lang = static::$lang;
        }

        // Load the translation table for this language
        $table = static::load($lang);

        // Return the translated string if it exists
        if ( isset($table[$string]) )
        {
            return $table[$string];
        }
        else
        {
            // Пробуем определить текущий модуль
            $module = Request::current() ? Request::current()->module() : NULL;

            $key = $module ?: 'application';

            // Translated string does not exist
            // Store the original string as missing - still makes sense to store the original string so that loading the untranslated file will work.
            static::$_cache_missing[$key][$lang][$string] = $string;
            return $string;
        }
    }

    public static function write()
    {
        // something new must be added for anything to happen
        if ( empty(static::$_cache_missing) )
            return;

        // echo "<h1>missing</h1>";

        foreach ( static::$_cache_missing as $module => $data )
        {
            self::put_data($module, $data[static::$lang]);
        }
    }

    protected static function put_data($module, $data)
    {
        // Если указан конкретный модуль, пишем найденную строку в языковой файл соответствующего модуля
        // Иначе пишем в общий файл в /application
        $savepath = ( $module == 'application' ? APPPATH  : MODPATH.$module.'/' ).'i18n/';

        // check that the path exists
        if ( ! file_exists($savepath) )
        {
            // if not, create directory
            mkdir($savepath, 0777, true);
        }

        // Формируем имя файла
        $filename = static::$lang.'.php';
        $full_file_path = $savepath . $filename;

        // Получаем текущее содержимое языкового файла, если он есть
        $current_app_lang_data = file_exists($full_file_path) ? include $full_file_path : array();

        // Если файл поломан, ничего не делаем
        if ( ! is_array($current_app_lang_data) )
            return;

        $content = static::make_content(array_merge($data, $current_app_lang_data));

        // backup old file - if the file size is different.
        if ( file_exists($full_file_path) AND ( filesize($full_file_path) != strlen($content) ) )
        {
            // Backing up current config
            $old_content = file_get_contents($full_file_path);
            $backup_name = $savepath.static::$lang.'_'.date('Y_m_d__H_i_s').'.php';
            $result = file_put_contents($backup_name, $old_content);

            // Backup failed! Don't write the file.
            if ( ! $result )
                return;
        }

        // Save the file
        file_put_contents($full_file_path, $content, LOCK_EX);
    }

    protected static function make_content(array $data)
    {
        return '<?php defined("SYSPATH") OR die("No direct script access.");
/**
 * Translation file in language: '.static::$lang.'
 * Automatically generated from previous translation file.
 */
return '.var_export($data, true).';';
    }
}