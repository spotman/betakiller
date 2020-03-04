<?php
namespace BetaKiller\Model;

use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

class Hit extends \ORM implements HitInterface
{
    public const COL_IS_PROCESSED  = 'is_processed';
    public const COL_IS_PROTECTED  = 'is_protected';
    public const COL_CREATED_AT    = 'created_at';
    public const COL_UUID          = 'uuid';
    public const COL_SESSION_TOKEN = 'session_token';

    private const COL_SOURCE_ID  = 'source_id';
    private const COL_TARGET_ID  = 'target_id';
    private const COL_MARKER_ID  = 'marker_id';
    private const COL_USER_ID    = 'user_id';
    private const COL_IP_ADDRESS = 'ip';

    public const REL_TARGET = 'target';

    private const REL_SOURCE = 'source';
    private const REL_MARKER = 'marker';
    private const REL_USER   = 'user';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = 'stat_hits';

        $this->belongs_to([
            self::REL_SOURCE => [
                'model'       => HitPage::getModelName(),
                'foreign_key' => self::COL_SOURCE_ID,
            ],

            self::REL_TARGET => [
                'model'       => HitPage::getModelName(),
                'foreign_key' => self::COL_TARGET_ID,
            ],

            self::REL_MARKER => [
                'model'       => HitMarker::getModelName(),
                'foreign_key' => self::COL_MARKER_ID,
            ],

            self::REL_USER => [
                'model'       => User::getModelName(),
                'foreign_key' => self::COL_USER_ID,
            ],
        ]);

        $this->load_with([
            self::REL_SOURCE,
            self::REL_TARGET,
            self::REL_MARKER,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function setUuid(UuidInterface $uuid): HitInterface
    {
        $this->set(self::COL_UUID, $uuid->toString());

        return $this;
    }

    /**
     * @return UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        $value = (string)$this->get(self::COL_UUID);

        return Uuid::fromString($value);
    }

    /**
     * @param string $token
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setSessionToken(string $token): HitInterface
    {
        $this->set(self::COL_SESSION_TOKEN, $token);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSessionToken(): bool
    {
        return (bool)$this->get(self::COL_SESSION_TOKEN);
    }

    /**
     * @return string
     */
    public function getSessionToken(): string
    {
        return (string)$this->get(self::COL_SESSION_TOKEN);
    }

    /**
     * @param \BetaKiller\Model\UserInterface $user
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function bindToUser(UserInterface $user): HitInterface
    {
        $this->set(self::REL_USER, $user);

        return $this;
    }

    /**
     * @return bool
     */
    public function isBoundToUser(): bool
    {
        return (bool)$this->get(self::COL_USER_ID);
    }

    /**
     * @param \BetaKiller\Model\HitPageInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setSourcePage(HitPageInterface $value): HitInterface
    {
        $this->set(self::REL_SOURCE, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\HitPageInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetPage(HitPageInterface $value): HitInterface
    {
        $this->set(self::REL_TARGET, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\HitMarkerInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetMarker(HitMarkerInterface $value): HitInterface
    {
        $this->set(self::REL_MARKER, $value);

        return $this;
    }

    /**
     * @return bool
     */
    public function hasSourcePage(): bool
    {
        return (bool)$this->get(self::COL_SOURCE_ID);
    }

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getSourcePage(): HitPageInterface
    {
        return $this->getRelatedEntity(self::REL_SOURCE);
    }

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getTargetPage(): HitPageInterface
    {
        return $this->getRelatedEntity(self::REL_TARGET);
    }

    /**
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function getTargetMarker(): HitMarkerInterface
    {
        return $this->getRelatedEntity(self::REL_MARKER);
    }

    /**
     * @return bool
     */
    public function hasTargetMarker(): bool
    {
        return (bool)$this->get(self::COL_MARKER_ID);
    }

    public function setIP(string $ip): HitInterface
    {
        $this->set(self::COL_IP_ADDRESS, $ip);

        return $this;
    }

    public function setTimestamp(\DateTimeImmutable $dateTime): HitInterface
    {
        $this->set_datetime_column_value(self::COL_CREATED_AT, $dateTime);

        return $this;
    }

    /**
     * @return bool
     */
    public function isProcessed(): bool
    {
        return (bool)$this->get(self::COL_IS_PROCESSED);
    }

    /**
     * @return \BetaKiller\Model\HitInterface
     */
    public function markAsProcessed(): HitInterface
    {
        $this->set(self::COL_IS_PROCESSED, true);

        return $this;
    }

    /**
     * @return bool
     */
    public function isProtected(): bool
    {
        return (bool)$this->get(self::COL_IS_PROTECTED);
    }

    /**
     * @inheritDoc
     */
    public function markAsProtected(): HitInterface
    {
        $this->set(self::COL_IS_PROTECTED, true);

        return $this;
    }

    public function getIP(): string
    {
        return $this->get(self::COL_IP_ADDRESS);
    }

    public function getTimestamp(): \DateTimeImmutable
    {
        return $this->get_datetime_column_value(self::COL_CREATED_AT);
    }

    /**
     * @return \Psr\Http\Message\UriInterface
     */
    public function getFullTargetUrl(): UriInterface
    {
        $uri = $this->getTargetPage()->getFullUrl();

        if ($this->hasTargetMarker()) {
            $marker = $this->getTargetMarker();

            // Parse stored query string
            \parse_str($uri->getQuery(), $queryArr);

            // Recreate full query string with UTM markers
            $queryString = \http_build_query($queryArr + $marker->asQueryArray());

            // Update URI
            $uri = $uri->withQuery($queryString);
        }

        return $uri;
    }
}
