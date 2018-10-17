<?php
namespace BetaKiller\Assets\Exception;

class AssetsUploadException extends AssetsProviderException
{
    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     * Return null if no default message allowed
     *
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string
    {
        return 'assets.upload.error';
    }
}
