<?php

class Profiler extends Kohana_Profiler
{
    /**
     * @return string
     * @throws \View_Exception
     */
    public static function render(): string
    {
        return View::factory('profiler/stats')->render();
    }
}
