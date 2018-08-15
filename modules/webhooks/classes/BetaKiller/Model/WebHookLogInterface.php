<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface WebHookLogInterface
{
    public const TABLE_NAME               = 'webhook_log';
    public const TABLE_FIELD_CODENAME     = 'codename';
    public const TABLE_FIELD_CREATED_AT   = 'created_at';
    public const TABLE_FIELD_STATUS       = 'status';
    public const TABLE_FIELD_MESSAGE      = 'message';
    public const TABLE_FIELD_REQUEST_DATA = 'request_data';

    /**
     * @return string
     */
    public function getCodename(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setCodename(string $value): WebHookLogInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable;

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setCreatedAt(\DateTimeImmutable $value): WebHookLogInterface;

    /**
     * @return bool
     */
    public function isStatusSucceeded(): bool;

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setStatus(bool $value): WebHookLogInterface;

    /**
     * @return string
     */
    public function getMessage(): string;

    /**
     * @param null|string $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setMessage(?string $value): WebHookLogInterface;

    /**
     * @return array
     */
    public function getRequestData(): array;

    /**
     * @param array|null $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setRequestData(?array $value): WebHookLogInterface;
}
