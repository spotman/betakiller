<?php
namespace BetaKiller;


interface ExceptionInterface extends \Throwable
{
    public const DEFAULT_EXCEPTION_CODE = 500;

    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with *false* return if notification about exceptions of concrete class is not needed
     *
     * @return bool
     */
    public function isNotificationEnabled(): bool;

    /**
     * If returns true, then original exception message will be shown to end-user in JSON and error pages
     * Override this method with *true* return if it's domain exception
     *
     * @return bool
     */
    public function showOriginalMessageToUser(): bool;

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     * Override this method with *true* return if this exception type has dedicated error page like 404
     *
     * @return bool
     */
    public function alwaysShowNiceMessage(): bool;

    /**
     * Returns default message for current exception
     * Allows throwing concrete exception without message
     * Useful for custom exception types
     * Return null if no default message allowed
     *
     * @return string
     */
    public function getDefaultMessageI18nKey(): ?string;
}
