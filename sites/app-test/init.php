<?php defined('SYSPATH') OR die('No direct script access.');

/**
* The default language
*/
I18n::lang('ru');

/**
 * The list of allowed languages
 */
$allowed_languages = array('ru', 'en');
I18n::lang_list($allowed_languages);