<?php defined('SYSPATH') or die('No direct access allowed.');

/**
 * Суть контроллера: иметь возможность создания компактных модулей, в которых бы
 * можно было хранить и css, и js, и картинки выше DOCUMENT_ROOT, чтобы
 * при развертывании проекта не забывать копировать их куда надо
 * Просто бросаем модуль в modules, прописываем его в bootstrap
 *
 * @package Kohana-static-files
 * @author  Berdnikov Alexey <aberdnikov@gmail.com>
 */
class Controller_StaticFiles extends Controller
{
    protected $config;

    /**
     * Развертывание статики по мере необходимости
     */
    public function action_index()
    {
        // $this->auto_render  = FALSE;
        $file         = $this->request->param("file");
        $this->config = $this->get_config();

        $info = pathinfo($file);
        $dir  = ('.' !== $info['dirname']) ? $info['dirname'].'/' : '';

        if ($orig = $this->static_original($file)) {
            // Читаем содержимое оригинала
            $str = file_get_contents($orig);

            // Заменяем строки если соответствующий тип файла
            if (in_array($info['extension'], $this->config['replace_url_exts'], true)) {
                $str = $this->replace_url($str);
            }

            $is_enabled = ($this->config['enabled'] === true);

            // Сохраняем в кеш
            if ($is_enabled) {
                // Производим deploy статического файла,
                // В следующий раз его будет отдавать сразу nginx без запуска PHP
                $deploy = $this->static_deploy($file);

                if (@ file_put_contents($deploy, $str) AND $this->config['chmod']) {
                    chmod($deploy, $this->config['chmod']);
                }
            }

            // Получаем время модификации оригинала
            $mtime = filemtime($orig);

            if (!$is_enabled) {
                $this->response->headers('Pragma', 'no-cache');
                $this->response->headers('Expires', gmdate("D, d M Y H:i:s \G\M\T", time() - 3600));
            }

            // А пока отдадим файл руками
//			$this->check_cache(sha1($this->request->uri()) . $mtime, $this->request);
            $this->response->body($str);
            $this->response->headers('Content-Type', File::mime_by_ext($info['extension']));
            $this->response->headers('Content-Length', strlen($str));
            $this->response->headers('Last-Modified', gmdate("D, d M Y H:i:s \G\M\T", $mtime));
        } else {
            // Return a 404 status
            throw new HTTP_Exception_404("File [:file] not found", [':file' => $file]);
        }
    }

    /**
     * Поиск по проекту статичного файла
     * (полный путь к файлу)
     *
     * @param string $file
     *
     * @return string
     */
    public static function static_original($file)
    {
        $info = pathinfo($file);
        $dir  = ('.' !== $info['dirname']) ? $info['dirname'].'/' : '';

        return Kohana::find_file('static-files', $dir.$info['filename'], $info['extension']);
    }

    private function static_deploy($file)
    {
        $config = $this->get_config();

        $info   = pathinfo($file);
        $dir    = ('.' !== $info['dirname']) ? $info['dirname'].'/' : '';
        $deploy = rtrim($config['path'], '/')
            .'/'
            .ltrim($config['url'], '/').$dir
            .$info['filename'].'.'
            .$info['extension'];

        if (!file_exists(dirname($deploy))) {
            @ mkdir(dirname($deploy), $config['chmod'], true);
        }

        return $deploy;
    }

    private function replace_url($text)
    {
        return StaticFile::instance()->replace_keys($text);
    }

    private function get_config()
    {
        return Kohana::$config->load("staticfiles");
    }

    public function action_missing()
    {
        throw new HTTP_Exception_404;
    }
}
