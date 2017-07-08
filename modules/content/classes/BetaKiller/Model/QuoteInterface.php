<?php
namespace BetaKiller\Model;

use DateTimeImmutable;
use DateTimeInterface;
use ORM;

interface QuoteInterface
{
    /**
     * @param string $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function setText(string $value);

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getText(): string;

    /**
     * @param string $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function setAuthor(string $value);

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getAuthor(): string;

    /**
     * @param \DateTimeInterface $time
     *
     * @return $this
     */
    public function setCreatedAt(DateTimeInterface $time);

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable;
}
