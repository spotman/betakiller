<?php defined('SYSPATH') OR die('No direct script access.');

class Model_FileOld extends ORM
{
    public function uploads_dir()
    {
        return DOCROOT.'uploads'.DIRECTORY_SEPARATOR;
    }

    public function get_file_path()
    {
        return $this->uploads_dir().$this->get_filename();
    }

    /**
     * Возвращает имя файла в файловой системе
     * @return string
     */
    public function get_filename()
    {
        return $this->file;
    }

    /**
     * Возвращает имя файла, которое было указано пользователем при загрузке
     * @return string
     */
    public function get_alias()
    {
        return $this->alias;
    }

//    public function rules()
//    {
//        return array(
//            'file' => array(
//                array('Upload::valid'),
//                array('Upload::not_empty'),
//                array('Upload::type', array(':value', array('jpg', 'jpeg', 'png', 'gif', 'zip', 'pdf', 'doc', 'docx', 'xls', 'txt', 'xlsx','odt','csv'))),
//                array(array($this, 'file_save'), array(':value'))
//            ),
//        );
//    }
//
//    public function file_save($file)
//    {
//        $alias = $file['name'];
//
//        $name = $this->make_filename($file['name']);
//
//        $uploaded = Upload::save($file, $name, $this->uploads_dir());
//
//        if ( $uploaded )
//        {
//            $this->set('file', $name);
//            $this->set('alias', $alias);
//            $this->set('type', strtolower(pathinfo($file['name'], PATHINFO_EXTENSION)));
//            $this->set('size', $file['size']);
//        }
//
//        // return result
//        return $uploaded;
//    }

    /**
     * Помещает в хранилище файл, загруженный по HTTP
     * @param array $file_array Массив-описатель файла, взятый из $_FILES
     * @return $this
     * @throws HTTP_Exception_500
     */
    public function upload(array $file_array)
    {
        $original_filename = $file_array['name'];
        $full_filename = $file_array['tmp_name'];

        // Определяем тип файла
        $type = $this->get_file_type($full_filename) ?: pathinfo($original_filename, PATHINFO_EXTENSION);

        // Формируем хешированное имя файла
        $dst_filename = $this->make_filename($original_filename);

        $this->set('file', $dst_filename);
        $this->set('alias', $original_filename);
        $this->set('type', strtolower($type));
        $this->set('size', filesize($full_filename));

        // Перемещаем оригинальный файл в хранилище
        $uploaded = Upload::save($file_array, $dst_filename, $this->uploads_dir());

        if ( ! $uploaded )
            throw new HTTP_Exception_500("Файл не прошёл проверку, загрузка отменена");

        return $this;
    }

    /**
     * Формирует уникальный хеш, используемый в качестве имени файла в фс
     * @param $original_filename
     * @return string
     */
    protected function make_filename($original_filename)
    {
        return md5(microtime() . $original_filename);
    }

    /**
     * Возвращает тип (расширение) файла, определяя его по MIME-type
     * @param $filename
     * @return string|null
     */
    protected function get_file_type($filename)
    {
        try
        {
            $mime_type = File::mime($filename);
            return File::ext_by_mime($mime_type);
        }
        catch ( Exception $e )
        {
            return NULL;
        }
    }

    /**
     * Перемещает файл в хранилище и регистрирует его
     * @param string $full_filename Абсолютный путь до файла
     * @param string $alias Имя, под которым файл будет загружен пользователю
     * @return $this
     * @throws HTTP_Exception_500
     */
    public function store($full_filename, $alias = NULL)
    {
        if ( ! $alias )
        {
            $alias = basename($full_filename);
        }

        // Определяем тип файла
        $type = $this->get_file_type($full_filename) ?: pathinfo($alias, PATHINFO_EXTENSION);

        // Формируем хешированное имя файла и путь до него в хранилище
        $dst_filename = $this->make_filename($alias);
        $dst_path = $this->uploads_dir() . $dst_filename;

        // Перемещаем оригинальный файл в хранилище
        if ( ! rename($full_filename, $dst_path) )
            throw new HTTP_Exception_500("Невозможно переместить файл из :src в :dst",
                array(':src' => $full_filename, ':dst' => $dst_path));

        $this->set('file', $dst_filename);
        $this->set('alias', $alias);
        $this->set('type', strtolower($type));
        $this->set('size', filesize($dst_path));

        return $this;
    }

    public function get_content()
    {
        $path = $this->get_file_path();

        if ( ! file_exists($path) )
            throw new HTTP_Exception_500('Файл :file зарегистрирован в хранилище, но физически отсутствует',
            array(':file' => $path));

        return file_get_contents($path);
    }

    public function get_size()
    {
        return $this->size;
    }

    public function get_id()
    {
        return $this->id;
    }

    /**
     * Геттер / сеттер для отметки файла
     * Файл будет автоматически удалён после первой загрузки
     * @param null $set
     * @return bool|$this
     */
    public function remove_after_download($set = NULL)
    {
        if ( $set === NULL )
            return (bool) $this->remove_after_download;

        $this->remove_after_download = (bool) $set;

        return $this;
    }

    public function unlink()
    {
        $path = $this->get_file_path();

        if ( ! file_exists($path) )
            throw new HTTP_Exception_500('Файл :file зарегистрирован в хранилище, но физически отсутствует',
                array(':file' => $path));

        unlink($path);

        return $this;
    }

    public function get_download_link()
    {
        return Route::url('file', array('action' => 'download', 'id' => $this->get_id()));
    }

}