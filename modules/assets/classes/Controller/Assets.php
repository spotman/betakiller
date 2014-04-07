<?php defined('SYSPATH') OR die('No direct script access.');

class Controller_Assets extends Controller {

    public function action_upload()
    {
        $provider_codename = $this->param('provider');

        /** @var Assets_Provider $provider */
        $provider = Assets_Provider::factory($provider_codename);

        foreach ( $_FILES as $_file )
        {
            if ( $this->check_uploaded_file($_file) )
            {
                $provider->upload($_file);
            }

            var_dump($_file);
        }
    }

    protected function check_uploaded_file($_file)
    {
        // TODO Security checks
        return TRUE;
    }

}