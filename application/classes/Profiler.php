<?php defined('SYSPATH') OR die('No direct script access.');

class Profiler extends Kohana_Profiler
{
    /**
     * Перманентно включает профайлер для всех экшнов / контроллеров
     */
    public static function enable()
    {
        Session::instance()->set("profiler_enabled", TRUE);
    }

    /**
     * Отменяет действие enable()
     */
    public static function disable()
    {
        Session::instance()->delete("profiler_enabled");
    }

    /**
     * Возвращает TRUE, если профайлер включён
     * @return bool
     */
    public static function is_enabled()
    {
        return Session::instance()->get("profiler_enabled");
    }

    public static function render()
    {
        return View::factory("profiler/stats")->render();
    }
}
