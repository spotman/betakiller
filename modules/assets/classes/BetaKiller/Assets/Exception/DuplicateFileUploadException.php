<?php
declare(strict_types=1);

namespace BetaKiller\Assets\Exception;

class DuplicateFileUploadException extends AssetsUploadException
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
        return 'assets.upload.duplicate';
    }

    /**
     * If returns true, then original exception message will be shown to end-user in JSON and error pages
     * Override this method with *true* return if it's domain exception
     *
     * @return bool
     */
    public function showOriginalMessageToUser(): bool
    {
        return true;
    }
}
