<?php defined('SYSPATH') OR die('No direct script access.');



/**
 * @author Spotman denis.terehov@tsap-spb.ru
 *
 * excel import into k.planet.web
 */

Route::set('excel-import-index', 'excel-import')
    ->defaults(array(
    'module'     => 'excel-import',
    'directory'  => 'ExcelImport',
    'controller' => 'index',
    'action'     => 'index',
));

Route::set('excel-import-actualize', 'excel-import/actualize/form(/<action>)')
    ->defaults(array(
    'module'     => 'excel-import',
    'directory'  => 'ExcelImport/Actualize',
    'controller' => 'Orders',
    'action'     => 'index',
));

Route::set('excel-import-load', 'excel-import/load-orders/form(/<action>)')
    ->defaults(array(
    'module'     => 'excel-import',
    'directory'  => 'ExcelImport/Load',
    'controller' => 'Orders',
    'action'     => 'index',
));
/*
Route::set('excel-import-controller', 'excel-import/<controller>(/<action>)')
    ->defaults(array(
    'module'     => 'excel-import',
    'directory'  => 'Excelimport',
    'action'     => 'index',
));
*/
