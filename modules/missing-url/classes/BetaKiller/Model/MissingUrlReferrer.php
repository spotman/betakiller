<?php
declare(strict_types=1);

namespace BetaKiller\Model;


class MissingUrlReferrer extends \ORM implements MissingUrlReferrerModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_table_name = 'missing_url_referrers';

        $this->belongs_to([
            'target' => [
                'model'       => 'MissingUrlRedirectTarget',
                'foreign_key' => 'redirect_to',
            ],
        ]);

        parent::_initialize();
    }

    public function getHttpReferer(): string
    {
        return $this->get('http_referer');
    }

    public function setHttpReferer(string $value): MissingUrlReferrerModelInterface
    {
        $this->set('http_referer', $value);

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLastSeenAt(): \DateTimeInterface
    {
        return $this->get_datetime_column_value('last_seen_at');
    }

    /**
     * @param \DateTimeInterface $value
     *
     * @return \BetaKiller\Model\MissingUrlReferrerModelInterface
     */
    public function setLastSeenAt(\DateTimeInterface $value): MissingUrlReferrerModelInterface
    {
        $this->set_datetime_column_value('last_seen_at', $value);

        return $this;
    }

    /**
     * @return string
     */
    public function getIpAddress(): string
    {
        return $this->get('ip');
    }

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\MissingUrlReferrerModelInterface
     */
    public function setIpAddress(string $value): MissingUrlReferrerModelInterface
    {
        $this->set('ip', $value);

        return $this;
    }
}
