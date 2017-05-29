<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Суть контроллера: иметь возможность создания компактных модулей, в которых бы
 * можно было хранить и css, и js, и картинки выше DOCUMENT_ROOT, чтобы
 * при развертывании проекта не забывать копировать их куда надо
 * Просто бросаем модуль в modules, прописываем его в bootstrap
 *
 * @package Kohana-static-files
 * @author Berdnikov Alexey <aberdnikov@gmail.com>
 */
class Controller_StaticFiles extends Controller {

    protected $config;

	/**
	 * Развертывание статики по мере необходимости
	 */
	public function action_index()
	{
		// $this->auto_render  = FALSE;
        $file = $this->request->param("file");
        $this->config = self::get_config();

        $info               = pathinfo($file);
		$dir                = ('.' != $info['dirname']) ? $info['dirname'] . '/' : '';

		if ( ($orig = self::static_original($file)) )
		{
            // Читаем содержимое оригинала
            $str = file_get_contents($orig);

            // Заменяем строки если соответствующий тип файла
            if ( in_array($info['extension'], $this->config['replace_url_exts']) )
            {
                $str = $this->replace_url($str);
            }

            $is_enabled = ($this->config['enabled'] === TRUE);

            // Сохраняем в кеш
            if ( $is_enabled )
            {
                // Производим deploy статического файла,
                // В следующий раз его будет отдавать сразу nginx без запуска PHP
                $deploy = self::static_deploy($file);

                if ( @ file_put_contents($deploy, $str) AND $this->config['chmod'] )
                {
                    chmod($deploy, $this->config['chmod']);
                }
            }

            // Получаем время модификации оригинала
            $mtime = filemtime($orig);

            if (!$is_enabled)
            {
                $this->response->headers('Pragma', 'no-cache');
                $this->response->headers('Expires', gmdate("D, d M Y H:i:s \G\M\T", time() - 3600));
            }

			// А пока отдадим файл руками
//			$this->check_cache(sha1($this->request->uri()) . $mtime, $this->request);
			$this->response->body( $str );
			$this->response->headers('Content-Type', File::mime_by_ext($info['extension']) );
			$this->response->headers('Content-Length', strlen($str) );
			$this->response->headers('Last-Modified', gmdate("D, d M Y H:i:s \G\M\T", $mtime) );
		}
		else
		{
			// Return a 404 status
            throw new HTTP_Exception_404("File [:file] not found", array(':file' => $file));
		}
	}

	/**
	 * Поиск по проекту статичного файла
	 * (полный путь к файлу)
	 * @param string $file
	 * @return string
	 */
	public static function static_original($file)
	{
		$info = pathinfo($file);
		$dir  = ('.' != $info['dirname']) ? $info['dirname'] . '/' : '';

        return Kohana::find_file('static-files', $dir . $info['filename'], $info['extension']);
	}

	public static function static_deploy($file)
	{
        $config = self::get_config();

		$info   = pathinfo($file);
		$dir    = ('.' != $info['dirname']) ? $info['dirname'] . '/' : '';
		$deploy = rtrim($config['path'], '/')
                . '/'
		        . ltrim($config['url'], '/') . $dir
		        . $info['filename'] . '.'
		        . $info['extension'];

		if ( ! file_exists(dirname($deploy)) )
        {
            @ mkdir(dirname($deploy), $config['chmod'], true);
        }

		return $deploy;
	}

    protected function replace_url($text)
    {
        return StaticFile::instance()->replace_keys($text);
    }

    static public function get_config()
    {
        return Kohana::$config->load("staticfiles");
    }

    public function action_clear()
    {
        $user = Env::user(TRUE);

        if ( ! $user OR ! $user->isDeveloper() )
            throw new HTTP_Exception_403();

        $config = self::get_config();

        // Убиваем кеш статики
        $dir = $config['path'] . $config['url'];
        if( file_exists($dir))
        {
            self::rrmdir($dir);
        }

        // Убиваем кеш сборок
        $dir = $config['path'] . $config['cache'];
        if ( file_exists($dir) )
        {
            self::rrmdir($dir);
        }

        echo '<h1>static cache was successfully dropped</h1>';
    }

    static public function rrmdir($dir)
    {
        foreach ( glob($dir . '/*') as $file )
        {
            if ( is_dir($file) )
            {
                self::rrmdir($file);
            }
            else
            {
                unlink($file);
            }
        }
        @ rmdir($dir);
    }

    public function action_missing()
    {
        throw new HTTP_Exception_404;
    }

}
