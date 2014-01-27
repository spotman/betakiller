<?php defined('SYSPATH') OR die('No direct script access.');

class File extends Kohana_File {

    /**
     * Загружат в хранилище первый отправленный по HTTP файл
     * @param array $file Элемент из массива $_FILES
     * @return Model_File
     * @throws HTTP_Exception_500
     */
    public static function upload($file = NULL)
    {
        // Получаем первый переданный файл, если в аргументе пусто
        $file = $file ?: array_shift($_FILES);

        if ( ! $file )
            throw new HTTP_Exception_500('Файл не отправлен, загрузка отменена');

        /** @var Model_File $model */
        $model = ORM::factory('File');

        return $model->upload($file)->save();

//        foreach ( $_FILES as $file_array )
//        {
//            $file = ORM::factory('file');
//            $file->values(array("file" => $file_array));
//            try
//            {
//                $file->save();
//            }
//            catch ( ORM_Validation_Exception $e )
//            {
//                // @TODO исправить
//                return FALSE;
//            }
//            $result[] = $file;
//        }
    }

    /**
     * Переносит произвольный файл в хранилище и регистрирует его
     * @param $full_file_path
     * @param null $alias
     * @return Model_File
     */
    public static function store($full_file_path, $alias = NULL)
    {
        /** @var Model_File $file */
        $file = ORM::factory('File');
        return $file->store($full_file_path, $alias)->save();
    }
}