<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Exception;

class CantWriteUploadException extends AssetsUploadException
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
        return 'assets.upload.cant_write';
    }
}
