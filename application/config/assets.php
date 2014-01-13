<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'jquery'    =>  array(
        'js'    =>  array('jquery/jquery-1.8.3.js', 'jquery/utils.js')
    ),

    'jquery.ui'     => array(
        'js'        =>  TRUE,   // Use custom method
        'css'       =>  TRUE,   // Use custom method
    ),

    'jquery.validation'     => array(
        'js'        =>  TRUE,   // Use custom method
    ),

    'jquery.fileupload'     => array(
        'js'        =>  array('jquery/fileupload/jquery.fileupload.js', 'jquery/fileupload/jquery.iframe-transport.js'),
        'css'       =>  'jquery/fileupload/jquery.fileupload-ui.css'
    ),

    'jquery.chosen'     => array(
        'js'        =>  'jquery/chosen/chosen.jquery.min.js',
        'css'       =>  'jquery/chosen/chosen.css'
    ),

    'jquery.cookie'     => array(
        'js'        =>  'jquery/jquery.cookie.js',
    ),

    /**
     * @link http://craigsworks.com/projects/qtip2/
     */
    'jquery.qtip'     => array(
        'js'        =>  'jquery/qtip/jquery.qtip.js',
        'css'       =>  'jquery/qtip/jquery.qtip.css'
    ),

    /**
     * @link http://pinesframework.org/pnotify/
     */
    'jquery.pnotify'     => array(
        'js'        =>  'jquery/pnotify/jquery.pnotify.js',
        'css'       =>  'jquery/pnotify/jquery.pnotify.default.css'
    ),

    /**
     * @link http://www.appelsiini.net/projects/jeditable
     */
    'jquery.jeditable'     => array(
        'js'        =>  'jquery/jeditable/jquery.jeditable.js',
    ),

    /**
     * @link http://jonthornton.github.io/jquery-timepicker/
     */
    'jquery.timepicker'     => array(
        'js'        =>  'jquery/timepicker/jquery.timepicker.js',
        'css'       =>  'jquery/timepicker/jquery.timepicker.css'
    ),

    /**
     * @link http://mmenu.frebsite.nl/
     */
    'jquery.mmenu'     => array(
        'js'        =>  'jquery/mmenu/jquery.mmenu.all.min.js',
        'css'       =>  'jquery/mmenu/jquery.mmenu.all.css'
    ),

    /**
     * @link http://getbootstrap.com/
     */
    'bootstrap'     => array(
        'js'        =>  'bootstrap/v3/js/bootstrap.js',
        'css'       =>  'bootstrap/v3/css/bootstrap.css'
    ),

    /**
     * @link
     */
    'bootstrap.bootbox'     => array(
        'js'        =>  'bootstrap/bootbox/bootbox.min.js',
    ),

    /**
     * @link https://github.com/CWSpear/bootstrap-hover-dropdown
     */
    'bootstrap.hover_dropdown'     => array(
        'js'        =>  'bootstrap/hover-dropdown/bootstrap-hover-dropdown.js',
    ),

    /**
     * @link http://underscorejs.org/
     */
    'underscore'     => array(
        'js'        =>  'underscore/underscore.js',
    ),

    /**
     * @link http://www.tinymce.com/
     */
    'tinyMCE'     => array(
        'js'        =>  TRUE,   // Use custom method
    ),

    'font.awesome'  =>  array(
        'css'       =>  'fonts/font-awesome/css/font-awesome.css'
    )

);