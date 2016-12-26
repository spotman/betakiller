<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Image extends Kohana_Image
{
    private static $_tmp_files = [];

    public static function from_content($content, $driver = null)
    {
        if ( !$content )
            throw new Kohana_Exception('No content for image info detection');

        // Creating temporary file
        $tmp_file = tempnam(sys_get_temp_dir(), 'image-resize-temp');

        if (!$tmp_file)
            throw new Kohana_Exception('Can not create temporary file for image info detecting');

        // Putting content into it
        file_put_contents($tmp_file, $content);

        self::$_tmp_files[] = $tmp_file;
        return static::factory($tmp_file, $driver);
    }

    public function __destruct()
    {
        if (self::$_tmp_files) {
            foreach (self::$_tmp_files as $file) {
                unlink($file);
            }
        }
    }
}
