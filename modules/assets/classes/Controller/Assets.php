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

        // Uploading via provider
        $model = $this->_provider->upload($_file);

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

        $model = $this->from_item_deploy_url();

        // Creating temporary file
        $temp_file_name = tempnam('/tmp', 'image-resize');

        if ( ! $temp_file_name )
            throw new Assets_Provider_Exception('Can not create temporary file for image resizing');

        // Getting original file content
        $original_content = $this->_provider->get_content($model);

        // Putting content into it
        file_put_contents($temp_file_name, $original_content);

        // Creating resizing instance
        $image = Image::factory($temp_file_name);

        $resized_content = $image
            ->resize($this->_provider->get_preview_max_width(), $this->_provider->get_preview_max_height())
            ->render(NULL /* auto */, $this->_provider->get_preview_quality());

        // Deleting temp file
        unlink($temp_file_name);

        // Deploy to cache
        $this->deploy($model, $resized_content);

        // Send file content + headers
        $this->send_file($resized_content, $model->get_mime());
    }

    public function action_delete()
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        $this->provider_factory();

        // Get file model by hash value
        $model = $this->from_item_deploy_url();

        // Delete file through provider
        $this->_provider->delete($model);

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
        $this->_provider->deploy($model, $this->request->action(), $content);
    }

}