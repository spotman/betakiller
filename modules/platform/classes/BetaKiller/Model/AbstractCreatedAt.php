<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\Exception\DomainException;
use DateTimeImmutable;
use ORM;

abstract class AbstractCreatedAt extends ORM implements CreatedAtInterface
{
    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            static::getCreatedAtColumnName() => [
                ['not_empty'],
                ['date'],
            ],
        ];
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt(DateTimeImmutable $value = null): CreatedAtInterface
    {
        $this->set_datetime_column_value(static::getCreatedAtColumnName(), $value ?? new DateTimeImmutable);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        $createdAt = $this->get_datetime_column_value(static::getCreatedAtColumnName());

        if (!$createdAt) {
            throw new DomainException('CreatedAtInterface::createdAt can not be empty');
        }

        return $createdAt;
    }

    public static function getCreatedAtColumnName(): string
    {
        return 'created_at';
    }
}