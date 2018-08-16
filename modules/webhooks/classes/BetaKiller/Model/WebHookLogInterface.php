<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface WebHookLogInterface
{
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
     * @return WebHookLogRequestDataInterface
     */
    public function getRequestData(): WebHookLogRequestDataInterface;

    /**
     * @param WebHookLogRequestDataInterface $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setRequestData(WebHookLogRequestDataInterface $value): WebHookLogInterface;
}
