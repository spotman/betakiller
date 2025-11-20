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
     * @inheritDoc
     */
    public function alwaysShowNiceMessage(): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function isNotificationEnabled(): bool
    {
        return false;
    }
}
