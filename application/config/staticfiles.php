<?php

return array(

    // Full path to site DOCROOT
    'path' => MultiSite::instance()->docRoot().DIRECTORY_SEPARATOR,

    // File extensions in which {staticfiles_url} and {# assets_base_url #} must be replaced
    'replace_url_exts' => array(
        'css',
        'js',
        'html',
        'twig',
    ),
);
