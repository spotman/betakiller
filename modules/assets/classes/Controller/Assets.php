<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Assets extends Controller {

    /**
     * @var Assets_Provider
     */
    protected $_provider;

    /**
     * Common action for uploading files through provider
     *
     * @throws Assets_Exception
     */
    public function action_upload()
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        // Restrict multiple files at once
        if (count($_FILES) > 1)
            throw new Assets_Exception('Only one file can be uploaded at once');

        $this->provider_factory();

        // Getting first uploaded file
        $_file = array_shift($_FILES);

        // Getting additional POST data
        $_post_data = $this->request->post();

        // Uploading via provider
        $model = $this->_provider->upload($_file, $_post_data);

        // Returns
        $this->send_json(self::JSON_SUCCESS, $model->to_json());
    }

    public function action_original()
    {
        $this->provider_factory();

        $model = $this->from_item_deploy_url();

        // Get file content
        $content = $this->_provider->get_content($model);

        // Deploy to cache
        $this->deploy($model, $content);

        // Send file content + headers
        $this->send_file($content, $model->get_mime());
    }

    public function action_preview()
    {
        $this->provider_factory();

        if ( !($this->_provider instanceof Assets_Provider_Image) )
            throw new Assets_Exception('Preview can be served only by instances of Assets_Provider_Image');

        $size = $this->param('size');
        $model = $this->from_item_deploy_url();

        $preview_content = $this->_provider->prepare_preview($model, $size);

        // Deploy to cache
        $this->deploy($model, $preview_content);

        // Send file content + headers
        $this->send_file($preview_content, $model->get_mime());
    }

    public function action_crop()
    {
        $this->provider_factory();

        if ( !($this->_provider instanceof Assets_Provider_Image) )
            throw new Assets_Exception('Cropping can be processed only by instances of Assets_Provider_Image');

        $size = $this->param('size');
        $model = $this->from_item_deploy_url();

        $cropped_content = $this->_provider->crop($model, $size);

        // Deploy to cache
        $this->deploy($model, $cropped_content);

        // Send file content + headers
        $this->send_file($cropped_content, $model->get_mime());
    }

    public function action_delete()
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        $this->provider_factory();

        // Get file model by hash value
        $model = $this->from_item_deploy_url();

        // Delete file through provider
        $model->delete();

        $this->send_json(self::JSON_SUCCESS);
    }

    protected function provider_factory()
    {
        $provider_key = $this->param('provider');

        if ( ! $provider_key )
            throw new Assets_Exception('You must specify provider codename');

        $this->_provider = Assets_Provider::factory($provider_key);
    }

    protected function from_item_deploy_url()
    {
        $url = $this->param('item_url');

        if ( ! $url )
            throw new Assets_Exception('You must specify item url');

        try
        {
            // Find asset model by url
            $model = $this->_provider->get_model_by_deploy_url($url);
        }
        catch ( Assets_Exception $e )
        {
            // File not found
            throw new HTTP_Exception_404;
        }

        return $model;
    }

    protected function deploy(Assets_Model $model, $content)
    {
        $this->_provider->deploy($this->request, $model, $content);
    }

}
