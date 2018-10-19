<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

use DateTimeImmutable;

/**
 * Class NotAvailableHttpException
 * Use it for maintenance mode and for server fault graceful response
 *
 * @package BetaKiller\Exception
 */
class NotAvailableHttpException extends HttpException
{
    /**
     * @var \DateTimeImmutable
     */
    private $endsAt;

    public function __construct(DateTimeImmutable $endsAt)
    {
        parent::__construct(503);

        $this->endsAt = $endsAt;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getEndsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    /**
     * Overwrite this method with "return TRUE" to show custom message in all cases
     * Override this method with *true* return if this exception type has dedicated error page like 404
     *
     * @return bool
     */
    public function alwaysShowNiceMessage(): bool
    {
        return true;
    }

    /**
     * Returns TRUE if someone must be notified about current exception type
     * Override this method with *false* return if notification about exceptions of concrete class is not needed
     *
     * @return bool
     */
    public function isNotificationEnabled(): bool
    {
        return false;
    }
}
