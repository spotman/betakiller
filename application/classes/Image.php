<?php defined('SYSPATH') OR die('No direct script access.');

abstract class Image extends Kohana_Image
{
    private static $tmp_files = [];

    private static $shutdownHandlerRegistered = false;

    public static function from_content($content, $driver = null)
    {
        if ( !$content ) {
            throw new Kohana_Exception('No content for image info detection');
        }

        // Creating temporary file
        $tmp_file = tempnam(sys_get_temp_dir(), 'image-resize-temp-');

        if (!$tmp_file) {
            throw new Kohana_Exception('Can not create temporary file for image info detecting');
        }

        self::registerShutdownFunction();

        // Putting content into it
        file_put_contents($tmp_file, $content);

        self::$tmp_files[] = $tmp_file;
        return static::factory($tmp_file, $driver);
    }

    private static function registerShutdownFunction()
    {
        if (self::$shutdownHandlerRegistered) {
            return;
        }

        register_shutdown_function([Image::class, 'clearTempFiles']);

        self::$shutdownHandlerRegistered = true;
    }

    public static function clearTempFiles()
    {
        if (self::$tmp_files) {
            foreach (self::$tmp_files as $file) {
                unlink($file);
            }
        }
    }

    public function asDataURI(): string
    {
        // Read image path, convert to base64 encoding
        $imageData = base64_encode($this->render(null, 75));

        // Format the image SRC:  data:{mime};base64,{data};
        return 'data:'.$this->mime.';base64,'.$imageData;
    }
}
