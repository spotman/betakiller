<?php

use BetaKiller\Exception;

abstract class Image extends Kohana_Image
{
    private static $tmpFiles = [];

    private static $shutdownHandlerRegistered = false;

    /**
     * @param      $content
     * @param string|null $driver
     *
     * @return \Image
     * @throws \BetaKiller\Exception
     */
    public static function fromContent(string $content, string $driver = null): \Image
    {
        if ( !$content ) {
            throw new Exception('No content for image info detection');
        }

        // Creating temporary file
        $tmp_file = tempnam(sys_get_temp_dir(), 'image-resize-temp-');

        if (!$tmp_file) {
            throw new Exception('Can not create temporary file for image info detecting');
        }

        self::registerShutdownFunction();

        // Putting content into it
        file_put_contents($tmp_file, $content);

        self::$tmpFiles[] = $tmp_file;
        return static::factory($tmp_file, $driver);
    }

    private static function registerShutdownFunction(): void
    {
        if (self::$shutdownHandlerRegistered) {
            return;
        }

        register_shutdown_function([self::class, 'clearTempFiles']);

        self::$shutdownHandlerRegistered = true;
    }

    public static function clearTempFiles(): void
    {
        if (self::$tmpFiles) {
            foreach (self::$tmpFiles as $file) {
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
