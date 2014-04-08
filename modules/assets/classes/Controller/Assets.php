<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Assets extends Controller {

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

        $provider = $this->provider_factory();

        // Getting first uploaded file
        $_file = array_shift($_FILES);

        // Uploading via provider
        $model = $provider->upload($_file);

        // Returns
        $this->send_json(self::JSON_SUCCESS, $model->as_array());
    }

    public function action_public()
    {
        $provider = $this->provider_factory();

        $model = $this->by_hash($provider);

        // Get file content
        $content = $provider->get_content($model);

        // Deploy if needed
        if ( Kohana::in_production() ) // TODO Move to config
        {
            $this->_deploy($content);
        }

        // Send file content + headers
        $this->send_file($content, $model->get_mime());
    }

    protected function _deploy($content)
    {
        $full_path = DOCROOT . ltrim($this->request()->url(), '/');
        $path = pathinfo($full_path, PATHINFO_DIRNAME);

        $mask = 0644; // TODO Move to config

        if ( ! file_exists($path) )
        {
            mkdir($path, $mask, true);
        }

        file_put_contents($full_path, $content);
    }

    protected function provider_factory()
    {
        $provider_codename = $this->param('provider');

        if ( ! $provider_codename )
            throw new Assets_Exception('You must specify provider codename');

        /** @var Assets_Provider $provider */
        return Assets_Provider::factory($provider_codename);
    }

    protected function by_hash(Assets_Provider $provider)
    {
        $hash = $this->param('hash');

        if ( ! $hash )
            throw new Assets_Exception('You must specify hash');

        try
        {
            // Find asset model by hash
            $model = $provider->get_model_by_hash($hash);
        }
        catch ( Assets_Exception $e )
        {
            // File not found
            throw new HTTP_Exception_404;
        }

        return $model;
    }

    public function action_delete()
    {
        // This method responds via JSON (all exceptions will be caught automatically)
        $this->content_type_json();

        $provider = $this->provider_factory();

        // Get file model by hash value
        $model = $this->by_hash($provider);

        // Delete file through provider
        $provider->delete($model);

        $this->send_json(self::JSON_SUCCESS);
    }

}