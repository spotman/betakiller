<?php

/**
 * A patch for the Internationalization (i18n) class.
 *
 * @package    I18n
 * @author     Mikito Takada
 */
class I18n extends Kohana_I18n
{
    public const PLACEHOLDER_PREFIX = ':';

    /**
     * @var  string  source language: en-us, es-es, zh-cn, etc
     */
    public static $source = 'en';

    /**
     * @var array Cache of missing strings
     */
    private static $missingKeys = [];

    /**
     * @param string      $string
     * @param string|null $lang
     *
     * @return bool
     */
    public static function has($string, $lang = null): bool
    {
        if (!$lang) {
            // Use the global target language
            $lang = static::$lang;
        }

        // Load the translation table for this language
        $table = static::load($lang);

        return isset($table[$string]);
    }

    /**
     * Returns translation of a string. If no translation exists, the original
     * string will be returned. No parameters are replaced.
     *
     *     $hello = I18n::get('Hello friends, my name is :name');
     *
     * @param   $string string  text to translate
     * @param   $lang   string   target language
     *
     * @return  string
     */
    public static function get($string, $lang = null): string
    {
        if (!$string) {
            return '';
        }

        if (!$lang) {
            // Use the global target language
            $lang = static::$lang;
        }

        // Load the translation table for this language
        $table = static::load($lang);

        // Return the translated string if it exists
        if (isset($table[$string])) {
            return $table[$string];
        }

        // Detect current module if exists
        $module = Request::current() ? Request::current()->module() : null;

        $key = $module ?: 'application';

        // Translated string does not exist
        // Store the original string as missing
        // Still makes sense to store the original string so that loading the untranslated file will work.
        static::$missingKeys[$key][$lang][$string] = $string;

        return $string;
    }

    public static function addPlaceholderPrefixToKeys(array $data)
    {
        $output = [];

        foreach ($data as $key => $value) {
            // Add prefix if it does not exist
            if (strpos($key, self::PLACEHOLDER_PREFIX) !== 0) {
                $key = self::PLACEHOLDER_PREFIX.$key;
            }

            $output[$key] = $value;
        }

        return $output;
    }

    public static function saveMissingKeys(): void
    {
        register_shutdown_function([__CLASS__, 'writeMissingKeys']);
    }

    public static function writeMissingKeys()
    {
        // something new must be added for anything to happen
        if (empty(static::$missingKeys)) {
            return;
        }

        foreach (static::$missingKeys as $module => $data) {
            try {
                self::putData($module, $data[static::$lang]);
            } catch (Throwable $e) {
                $container = \BetaKiller\DI\Container::getInstance();
                $logger    = $container->get(\Psr\Log\LoggerInterface::class);
                $logger->alert($e->getMessage(), ['exception' => $e]);
            }
        }
    }

    /**
     * @param string $module
     * @param array  $data
     *
     * @throws \RuntimeException
     */
    protected static function putData(string $module, array $data)
    {
        // Skip empty records
        if (!$data) {
            return;
        }

        // Use module-related i18n file if module is defined or app-related instead
        $basePath = ($module !== 'application')
            ? MODPATH.$module.'/'
            : Kohana::include_paths()[0];

        $savePath = $basePath.'i18n/';

        // Check that the path exists
        if (!file_exists($savePath) && !mkdir($savePath) && !is_dir($savePath)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $savePath));
        }

        // Define filename
        $filename     = static::$lang.'.php';
        $fullFilePath = $savePath.$filename;

        // Get current file content if exists
        /** @noinspection PhpIncludeInspection */
        $currentAppLangData = file_exists($fullFilePath) ? include $fullFilePath : [];

        // Do nothing if i18n file is broken
        if (!is_array($currentAppLangData)) {
            return;
        }

        $content = static::makeFileContent(array_merge($data, $currentAppLangData));

        // Save the file
        file_put_contents($fullFilePath, $content, LOCK_EX);
    }

    protected static function makeFileContent(array $data)
    {
        return '<?php
/**
 * Translation file in language: '.static::$lang.'
 * Automatically generated from previous translation file.
 */
return '.var_export($data, true).';'.PHP_EOL;
    }
}
