<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\WebHook\WebHookException;

class WebHookLog extends \ORM implements WebHookLogInterface
{
    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        parent::configure();
    }

    /**
     * @return string
     */
    public function getID(): string
    {
        return $this->get_id();
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getCodename(): string
    {
        return $this->get(self::TABLE_FIELD_CODENAME);
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     * @throws \BetaKiller\WebHook\WebHookException
     * @throws \Kohana_Exception
     */
    public function setCodename(string $value): WebHookLogInterface
    {
        $value = trim($value);
        if ($value === '') {
            throw new WebHookException('Codename cant not be empty');
        }
        $this->set(self::TABLE_FIELD_CODENAME, $value);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::TABLE_FIELD_CREATED_AT);
    }

    /**
     * @param \DateTimeImmutable $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     */
    public function setCreatedAt(\DateTimeImmutable $value): WebHookLogInterface
    {
        $this->set_datetime_column_value(self::TABLE_FIELD_CREATED_AT, $value);

        return $this;
    }

    /**
     * @return bool
     * @throws \Kohana_Exception
     */
    public function isStatusSucceeded(): bool
    {
        return (bool)$this->get(self::TABLE_FIELD_STATUS);
    }

    /**
     * @param bool $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     * @throws \Kohana_Exception
     */
    public function setStatus(bool $value): WebHookLogInterface
    {
        $this->set(self::TABLE_FIELD_STATUS, $value);

        return $this;
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getMessage(): string
    {
        return (string)$this->get(self::TABLE_FIELD_MESSAGE);
    }

    /**
     * @param null|string $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     * @throws \Kohana_Exception
     */
    public function setMessage(?string $value): WebHookLogInterface
    {
        if (\is_string($value)) {
            $value = trim($value);
        }
        $this->set(self::TABLE_FIELD_MESSAGE, $value);

        return $this;
    }

    /**
     * @return array
     */
    public function getRequestData(): array
    {
        $data = (string)$this->get(self::TABLE_FIELD_REQUEST_DATA);
        if ($data === '') {
            $data = [];
        } else {
            $data = \json_decode($data, true);
            if (!\is_array($data)) $data = [];
        }

        return $data;
    }

    /**
     * @param array|null $value
     *
     * @return \BetaKiller\Model\WebHookLogInterface
     * @throws \Kohana_Exception
     */
    public function setRequestData(?array $value): WebHookLogInterface
    {
        if (\is_array($value)) {
            $value = \json_encode($value);
        }
        $this->set(self::TABLE_FIELD_REQUEST_DATA, $value);

        return $this;
    }
}
