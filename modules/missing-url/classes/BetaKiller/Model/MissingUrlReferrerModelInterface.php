<?php
declare(strict_types=1);

namespace BetaKiller\Model;


interface MissingUrlReferrerModelInterface extends AbstractEntityInterface
{
    /**
     * @return string
     */
    public function getHttpReferer(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\MissingUrlReferrerModelInterface
     */
    public function setHttpReferer(string $value): MissingUrlReferrerModelInterface;

    /**
     * @return \DateTimeInterface
     */
    public function getLastSeenAt(): \DateTimeInterface;

    /**
     * @param \DateTimeInterface $value
     *
     * @return \BetaKiller\Model\MissingUrlReferrerModelInterface
     */
    public function setLastSeenAt(\DateTimeInterface $value): MissingUrlReferrerModelInterface;

    /**
     * @return string
     */
    public function getIpAddress(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\MissingUrlReferrerModelInterface
     */
    public function setIpAddress(string $value): MissingUrlReferrerModelInterface;
}
