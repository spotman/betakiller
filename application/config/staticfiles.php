<?php defined('SYSPATH') or die('No direct script access.');

return array(

    // Full path to site DOCROOT
    'path' => MultiSite::instance()->doc_root().DIRECTORY_SEPARATOR,

    // File extensions in which {staticfiles_url} and {# assets_base_url #} must be replaced
    'replace_url_exts' => array(
        'css',
        'js',
        'html',
        'twig',
    ),
);
