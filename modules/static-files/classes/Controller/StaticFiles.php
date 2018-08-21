<?php use BetaKiller\Exception\NotFoundHttpException;

defined('SYSPATH') or die('No direct access allowed.');

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

        if ($orig = $this->static_original($file)) {
            // Получаем время модификации оригинала
            $mtime = filemtime($orig);
            $lastModified = (new DateTime())->setTimestamp($mtime);

            $this->response->last_modified($lastModified);
//            $this->response->headers('last-modified', gmdate("D, d M Y H:i:s \G\M\T", $mtime));

            // Check for not modified header
            if ($this->response->check_if_not_modified_since()) {
                return;
            }

//            // Читаем содержимое оригинала
//            $str = file_get_contents($orig);

//            // Заменяем строки если соответствующий тип файла
//            if (in_array($info['extension'], $this->config['replace_url_exts'], true)) {
//                $str = $this->replace_url($str);
//            }

            $is_enabled = ($this->config['enabled'] === true);

            // Сохраняем в кеш
            if ($is_enabled) {
                // Производим deploy статического файла,
                // В следующий раз его будет отдавать сразу nginx без запуска PHP
                $deploy = $this->static_deploy($file);

                symlink($orig, $deploy);

//                if ($this->config['chmod']) {
//                    chmod($orig, $this->config['chmod']);
//                }
            }

//            if (!$is_enabled) {
//                $this->response->headers('Pragma', 'no-cache');
//                $this->response->headers('Expires', gmdate("D, d M Y H:i:s \G\M\T", time() - 3600));
//            }

            // А пока отдадим файл руками
//			$this->check_cache(sha1($this->request->uri()) . $mtime, $this->request);
            $this->response->body(file_get_contents($orig));
            $this->response->headers('Content-Type', File::mime_by_ext($info['extension']));
            $this->response->headers('Content-Length', filesize($orig));
        } else {
            // Return a 404 status
            throw new NotFoundHttpException("File [:file] not found", [':file' => $file]);
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

    /**
     * @param $file
     *
     * @return string
     * @throws \RuntimeException
     */
    private function static_deploy($file)
    {
        $config = $this->get_config();

        $info   = pathinfo($file);
        $dir    = ($info['dirname'] !== '.') ? $info['dirname'].'/' : '';
        $deploy = rtrim($config['path'], '/')
            .'/'
            .ltrim($config['url'], '/').$dir
            .$info['filename'].'.'
            .$info['extension'];

        $dir = dirname($deploy);

        if (!file_exists($dir) && !mkdir($dir, 0777, true) && !is_dir($dir)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dir));
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
        throw new NotFoundHttpException();
    }
}
