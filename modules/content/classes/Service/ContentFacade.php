<?php

use BetaKiller\Assets\Provider\AbstractAssetsProvider;

class Service_ContentFacade extends \BetaKiller\Service
{
    use BetaKiller\Helper\ContentTrait;

    public function assets_provider_factory_from_mime($mime)
    {
        /** @var AbstractAssetsProvider[] $mime_providers */
        $mime_providers = [
            $this->assets_provider_content_image(),
        ];

        foreach ($mime_providers as $provider)
        {
            $allowed_mimes = $provider->getAllowedMimeTypes();

            if ($allowed_mimes && is_array($allowed_mimes) && in_array($mime, $allowed_mimes))
                return $provider;
        }

        // Default way
        return $this->assets_provider_content_attachment();
    }
}
