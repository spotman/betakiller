<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Assets extends Controller {

    public function action_upload()
    {
        $provider_codename = $this->param('provider');

        /** @var Assets_Provider $provider_instance */
        $provider_instance = Assets_Provider::factory($provider_codename);

        foreach ( $_FILES as $uploaded_file )
        {
            var_dump($uploaded_file);
        }
    }

    protected function check_uploaded_file($file)
    {

    }

}