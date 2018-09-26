<?php
declare(strict_types=1);

namespace BetaKiller\Model;


class MissingUrl extends \ORM implements MissingUrlModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'missing_urls';

        $this->belongs_to([
            'target' => [
                'model'       => 'MissingUrlRedirectTarget',
                'foreign_key' => 'redirect_to',
            ],
        ]);

        $this->has_many([
            'referrers' => [
                'model'       => 'MissingUrlReferrer',
                'foreign_key' => 'url_id',
                'far_key'     => 'referrer_id',
                'through'     => 'missing_urls_missing_url_referrers',
            ],
        ]);
    }

    public function getMissedUrl(): string
    {
        return $this->get('url');
    }

    public function setMissedUrl(string $value): MissingUrlModelInterface
    {
        $this->set('url', $value);

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
     * @return \BetaKiller\Model\MissingUrlModelInterface
     */
    public function setLastSeenAt(\DateTimeInterface $value): MissingUrlModelInterface
    {
        $this->set_datetime_column_value('last_seen_at', $value);

        return $this;
    }

    public function getRedirectTarget(): ?MissingUrlRedirectTargetModelInterface
    {
        /** @var \BetaKiller\Model\MissingUrlRedirectTarget $target */
        $target = $this->get('target');

        return $target->loaded() ? $target : null;
    }

    public function setRedirectTarget(MissingUrlRedirectTargetModelInterface $target): MissingUrlModelInterface
    {
        $this->set('target', $target);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\MissingUrlReferrerModelInterface $model
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface
     */
    public function addReferrer(MissingUrlReferrerModelInterface $model): MissingUrlModelInterface
    {
        $this->add('referrers', $model);

        return $this;
    }

    /**
     * @return MissingUrlReferrerModelInterface[]
     */
    public function getReferrerList(): array
    {
        return $this->getReferrersRelation()->find_all()->as_array();
    }

    private function getReferrersRelation(): MissingUrlReferrer
    {
        return $this->get('referrers');
    }

    public function hasReferrer(MissingUrlReferrerModelInterface $model): bool
    {
        return $this->has('referrers', $model);
    }
}
