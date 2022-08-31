<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use DateTimeImmutable;

interface CreatedAtInterface
{
    public const API_KEY_CREATED_AT_ISO = 'created_at_iso'; // DateTime in ISO-8601 format

    /**
     * @param \DateTimeImmutable|null $value [optional]
     *
     * @return \BetaKiller\Model\CreatedAtInterface
     */
    public function setCreatedAt(DateTimeImmutable $value = null): CreatedAtInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable;

    /**
     * @return string
     */
    public static function getCreatedAtColumnName(): string;
}
