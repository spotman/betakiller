<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Exception;

class NoTmpDirUploadException extends AssetsUploadException
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
        return 'assets.upload.no_tmp_dir';
    }
}
