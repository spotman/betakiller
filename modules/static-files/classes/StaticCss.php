<?php defined('SYSPATH') or die('No direct access allowed.');

class StaticCss extends Kohana_StaticCss {

    protected function prepareCss($style, $url)
    {
        // Производим замену всех относительных путей к изображениям на абсолютные

        $path = dirname($url) ."/";
        $search = '#url\((?!\s*[\'"]?(?:https?:)?/)\s*([\'"])?#';
        $replace = "url($1{$path}";

        $style = preg_replace($search, $replace, $style);

        return parent::prepareCss($style, $url);
    }

}
