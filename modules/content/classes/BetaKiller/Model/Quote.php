<?php
namespace BetaKiller\Model;

use DateTimeImmutable;
use DateTimeInterface;
use ORM;

class Quote extends ORM implements QuoteInterface
{
    /**
     * @param string $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function setText(string $value)
    {
        return $this->set('text', (string)$value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getText(): string
    {
        return $this->get('text');
    }

    /**
     * @param string $value
     *
     * @return $this
     * @throws \Kohana_Exception
     */
    public function setAuthor(string $value)
    {
        return $this->set('author', $value);
    }

    /**
     * @return string
     * @throws \Kohana_Exception
     */
    public function getAuthor(): string
    {
        return $this->get('author');
    }

    /**
     * @param \DateTimeInterface $time
     *
     * @return $this
     */
    public function setCreatedAt(DateTimeInterface $time)
    {
        return $this->set_datetime_column_value('created_at', $time);
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('created_at');
    }
}
