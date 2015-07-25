<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * базовый класс для импорта по проекту
 *
 * @author Spotman i.am@spotman.ru
 *
 */

abstract class Controller_ExcelImport_Abstract extends Controller_Basic {

    /** название поля для отправки файла */
    const FORM_FILENAME = 'excel-file';

    /** устанавливаем свой шаблон */
    public $template = 'templates/frontend_window';

    /** @var CRM_Import_Abstract_Doc объект, управляющий разбором документа */
    protected $doc;

    /** @var CRM_Import_Abstract_Errors */
    protected $doc_errors;


    /**
     * метод, настраивающий документ перед обработкой, создавая по одному объекту на каждый лист
     *
     * @return null
     */
    abstract public function prepare_doc();


    /**
     * создаёт объект. управляющий разбором документа
     * может быть переопределён в дочерних классах, если вдруг потребуется кастомный разбор

     * @param $filename
     * @return CRM_Import_Abstract_Doc
     */
    abstract public function doc_factory($filename);


    /** хелпер для создания url в пределах текущего контроллера */
    protected function url($action = 'index')
    {
        return '/'.Route::get('excel-import-universal')->uri(array(
            'controller'    => Request::initial()->controller(),
            'action'        => $action
        ));
    }


    public function before()
    {
        parent::before();
        $this->template->title = 'Импорт из Excel';
    }


    /** показываем форму с полем для отправки файла */
    public function action_index()
    {
        // если юзер неавторизован, выходим
        if( !Env::get('user') )
        {
            throw new HTTP_Exception_403("Для продолжения работы необходимо войти в систему");
        }

        /** @var $view stdClass */
        $view = View::factory('index');

        $view->projects         = $this->get_active_projects();
        $view->product_id       = Env::get('product', 'crm')->id;
        $view->form_action      = $this->url('upload');
        $view->form_filename    = static::FORM_FILENAME;

        $this->template->content = $view;
    }

    protected function get_active_projects()
    {
        /** @var $product Model_Product */
        $product = Env::get('product', 'crm');
        return $product->get_active_projects();
    }


    /** принимаем файл по http и обрабатываем его */
    public function action_upload()
    {
        if( empty($_FILES) OR !isset($_FILES[static::FORM_FILENAME]['tmp_name']) )
        {
            throw new ErrorException("Файл не получен, обработка остановлена...");
        }

        $file = $_FILES[static::FORM_FILENAME];
        $filename = $file['tmp_name'];

        // такое случается, если файл тяжелее post_max_size и иже с ними
        if( $file['size'] == 0 )
        {
            throw new ErrorException("Файл слишком большой и не может быть обработан");
        }

        if ( !is_uploaded_file($filename) )
        {
            throw new ErrorException("Файл не прошёл проверку на безопасность, удачи!");
        }

        // добавим себе времени
        set_time_limit(1200);

        // инициализируем документ
        $this->doc = $this->doc_factory($filename);

        // подготавливаем его (настраиваем каждый лист в дочернем классе)
        $this->prepare_doc();

        // запускаем обработку документа
        $this->doc->process();

        $this->doc->finalize();

        // посчитаем количество обработанных заявок
        $total_counter = $this->doc->get_total_row_counter();

        // и посмотрим есть ли ошибки
        $failed_numbers = $this->doc->get_formatted_errors();

        // если всё окей, перенаправляем на страницу с уведомлением
        if( $total_counter && !$failed_numbers )
        {
            $url = $this->url('ok') .'?counter='. $total_counter;
            $this->request->redirect($url);
        }

        // если есть ошибки, отображаем их
        $view = View::factory('error');

        $view->order_counter = $total_counter;
        $view->failed_numbers = $failed_numbers;
        $view->memory_usage = memory_get_peak_usage();

        $this->template->content = $view;
    }


    /**
     * страница с уведомлением о том, что всё хорошо
     */
    public function action_ok()
    {
        $view = View::factory('ok');

        // получаем кол-во обработанных заявок
        $view->order_counter = $this->request->query('counter');

        $this->template->content = $view;
    }
}
