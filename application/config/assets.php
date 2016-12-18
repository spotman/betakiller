<?php defined('SYSPATH') OR die('No direct script access.');

return array(

    'jquery'    =>  array(
        'js'    =>  array('jquery/jquery-1.11.1.min.js', /* 'jquery/jquery-migrate-1.2.1.js', */ 'jquery/jquery.utils.js')
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
     * @link http://ivaynberg.github.com/select2/
     */
    'jquery.select2'     => array(
        'js'        =>  TRUE,
        'css'       =>  array('jquery/select2/select2.css', 'jquery/select2/select2-bootstrap.css')
    ),

    /**
     * @link http://github.com/slashingweapon/jsonTools
     */
    'jquery.jsonRPC'     => array(
        'js'        =>  'jquery/jsonTools.js',
    ),

    /**
     * @link https://github.com/adchsm/Slidebars
     */
    'jquery.slidebars'  => array(
        'js'            =>  'jquery/slidebars/slidebars.min.js',
        'css'           =>  'jquery/slidebars/slidebars.min.css',
    ),

//    /**
//     * @link http://www.wookmark.com/jquery-plugin
//     */
//    'jquery.wookmark'   => array(
//        'js'        =>  'jquery/jquery.wookmark.min.js',
//    ),
//
    /**
     * @link http://getbootstrap.com/
     */
    'bootstrap'     => array(
        'js'        =>  'bootstrap/v3/js/bootstrap.min.js',
        'css'       =>  array('bootstrap/v3/css/bootstrap.min.css', 'bootstrap/v3/css/equal-height-columns.css' )
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
        'js'        =>  'bootstrap/hover-dropdown/bootstrap-hover-dropdown.min.js',
    ),

    /**
     * @link http://underscorejs.org/
     */
    'underscore'    => array(
        'js'        =>  'underscore/underscore.js',
    ),

    /**
     * @link http://www.tinymce.com/
     */
    'tinyMCE'       => array(
        'js'        =>  TRUE,   // Use custom method
    ),

    'font.awesome'  =>  array(
        'css'       =>  'fonts/font-awesome/css/font-awesome.css'
    ),

    /**
     * @link http://masonry.desandro.com/
     */
    'masonry'       => array(
        'js'        =>  'jquery/masonry/masonry.pkgd.js',
    ),

    /**
     * @link http://imagesloaded.desandro.com/
     */
    'imagesLoaded'  => array(
        'js'        =>  'imagesLoaded/imagesloaded.pkgd.js',
    ),

    'require.js'     =>  array(
        'js'        =>  array('require.js/require.js', 'require.js/betakiller.config.js', 'require.js/app.config.js'),
    ),

);
