<?php
namespace BetaKiller\Model;

use BetaKiller\Uri;
use Psr\Http\Message\UriInterface;
use function mb_strimwidth;

final class HitPage extends \ORM implements HitPageInterface
{
    public const TABLE_NAME = 'stat_hit_pages';

    public const REL_DOMAIN   = 'domain';
    public const REL_REDIRECT = 'redirect';

    public const COL_DOMAIN_ID   = 'domain_id';
    public const COL_IS_MISSING  = 'is_missing';
    public const COL_IS_IGNORED  = 'is_ignored';
    public const COL_REDIRECT_ID = 'redirect_id';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     * @throws \Exception
     */
    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            self::REL_DOMAIN => [
                'model'       => 'HitDomain',
                'foreign_key' => self::COL_DOMAIN_ID,
            ],
        ]);

        $this->belongs_to([
            self::REL_REDIRECT => [
                'model'       => 'HitPageRedirect',
                'foreign_key' => self::COL_REDIRECT_ID,
            ],
        ]);

        $this->load_with([
            self::REL_DOMAIN,
            self::REL_REDIRECT,
        ]);
    }

    public function setDomain(HitDomain $domain): HitPageInterface
    {
        $this->set(self::REL_DOMAIN, $domain);

        return $this;
    }

    public function setUri(string $url): HitPageInterface
    {
        // Truncate URI to 512 symbols
        $url = mb_strimwidth($url, 0, 512, '...');

        $this->set('uri', $url);

        return $this;
    }

    public function incrementHits(): HitPageInterface
    {
        $this->setHits($this->getHits() + 1);

        return $this;
    }

    public function setHits(int $value): HitPageInterface
    {
        $this->set('hits', $value);

        return $this;
    }

    public function getUri(): string
    {
        return $this->get('uri');
    }

    public function getHits(): int
    {
        return (int)$this->get('hits');
    }

    public function isIgnored(): bool
    {
        return $this->getDomain()->isIgnored() || (bool)$this->get(self::COL_IS_IGNORED);
    }

    public function markAsIgnored(): HitPageInterface
    {
        $this->set(self::COL_IS_IGNORED, true);

        return $this;
    }

    public function markAsMissing(): HitPageInterface
    {
        $this->setIsMissing(true);

        return $this;
    }

    public function markAsOk(): HitPageInterface
    {
        $this->setIsMissing(false);

        return $this;
    }

    public function isMissing(): bool
    {
        return (bool)$this->get(self::COL_IS_MISSING);
    }

    public function setRedirect(HitPageRedirectInterface $redirect): HitPageInterface
    {
        $this->set(self::REL_REDIRECT, $redirect);

        return $this;
    }

    public function getRedirect(): ?HitPageRedirectInterface
    {
        return $this->getRelatedEntity(self::REL_REDIRECT, true);
    }

    public function setLastSeenAt(\DateTimeImmutable $dateTime): HitPageInterface
    {
        $this->set_datetime_column_value('last_seen_at', $dateTime);

        return $this;
    }

    public function setFirstSeenAt(\DateTimeImmutable $dateTime): HitPageInterface
    {
        $this->set_datetime_column_value('first_seen_at', $dateTime);

        return $this;
    }

    public function getLastSeenAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value('last_seen_at');
    }

    public function getFirstSeenAt(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value('first_seen_at');
    }

    public function getFullUrl(): UriInterface
    {
        $domain = $this->getDomain();

        return (new Uri($this->getUri()))
            ->withScheme('https')
            ->withHost($domain->getName());
    }

    /**
     * @return \BetaKiller\Model\HitDomain
     */
    private function getDomain(): HitDomain
    {
        return $this->getRelatedEntity(self::REL_DOMAIN);
    }

    private function setIsMissing(bool $value): HitPage
    {
        $this->set(self::COL_IS_MISSING, $value);

        return $this;
    }
}
