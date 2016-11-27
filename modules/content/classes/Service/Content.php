<?php

class Service_Content extends \BetaKiller\Service
{
    use BetaKiller\Helper\ContentTrait;

    public function assets_provider_factory_from_mime($mime)
    {
        /** @var Assets_Provider[] $mime_providers */
        $mime_providers = [
            $this->assets_provider_content_image(),
        ];

        foreach ($mime_providers as $provider)
        {
            $allowed_mimes = $provider->get_allowed_mime_types();

            if ($allowed_mimes AND is_array($allowed_mimes) AND in_array($mime, $allowed_mimes))
                return $provider;
        }

        // Default way
        return $this->assets_provider_content_attachment();
    }
}
