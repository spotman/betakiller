<?php defined('SYSPATH') OR die('No direct script access.');
/**
 * Created by PhpStorm.
 * User: spotman
 * Date: 14.03.16
 * Time: 19:48
 */

// Route for sitemap.xml generator => Controller_Sitemap::index
Route::set('sitemap-xml', 'sitemap.xml')
    ->defaults(array(
        'controller' => 'Sitemap',
        'action'     => 'index',
        'module'     => 'sitemap',
    ));
