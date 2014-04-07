<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Assets extends Controller {

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

    public function action_download()
    {
        $provider = $this->provider_factory();

        // TODO
    }

    protected function provider_factory()
    {
        $provider_codename = $this->param('provider');

        if ( ! $provider_codename )
            throw new Assets_Exception('You must specify provider codename');

        /** @var Assets_Provider $provider */
        return Assets_Provider::factory($provider_codename);
    }

}