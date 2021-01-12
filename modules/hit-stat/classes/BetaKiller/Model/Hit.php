<?php
namespace BetaKiller\Model;

use Psr\Http\Message\UriInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Worknector\Model\AbstractCreatedByAt;

class Hit extends AbstractCreatedByAt implements HitInterface
{
    public const TABLE_NAME = 'stat_hits';

    public const COL_IS_PROCESSED  = 'is_processed';
    public const COL_IS_PROTECTED  = 'is_protected';
    public const COL_UUID          = 'uuid';
    public const COL_SESSION_TOKEN = 'session_token';
    public const COL_SOURCE_ID     = 'source_id';

    private const COL_TARGET_ID  = 'target_id';
    private const COL_MARKER_ID  = 'marker_id';
    private const COL_USER_ID    = 'user_id';
    private const COL_IP_ADDRESS = 'ip';

    public const REL_TARGET_PAGE = 'target';

    private const REL_SOURCE_PAGE   = 'source';
    private const REL_TARGET_MARKER = 'marker';

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this->_table_name = self::TABLE_NAME;

        $this->belongs_to([
            self::REL_SOURCE_PAGE => [
                'model'       => HitPage::getModelName(),
                'foreign_key' => self::COL_SOURCE_ID,
            ],

            self::REL_TARGET_PAGE => [
                'model'       => HitPage::getModelName(),
                'foreign_key' => self::COL_TARGET_ID,
            ],

            self::REL_TARGET_MARKER => [
                'model'       => HitMarker::getModelName(),
                'foreign_key' => self::COL_MARKER_ID,
            ],
        ]);

        $this->load_with([
            self::REL_SOURCE_PAGE,
            self::REL_TARGET_PAGE,
            self::REL_TARGET_MARKER,
        ]);

        parent::configure();
    }

    protected function isCreatedByRequired(): bool
    {
        return false;
    }

    public static function getCreatedByColumnName(): string
    {
        return self::COL_USER_ID;
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
     * @param \BetaKiller\Model\HitPageInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setSourcePage(HitPageInterface $value): HitInterface
    {
        $this->set(self::REL_SOURCE_PAGE, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\HitPageInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetPage(HitPageInterface $value): HitInterface
    {
        $this->set(self::REL_TARGET_PAGE, $value);

        return $this;
    }

    /**
     * @param \BetaKiller\Model\HitMarkerInterface $value
     *
     * @return \BetaKiller\Model\HitInterface
     */
    public function setTargetMarker(HitMarkerInterface $value): HitInterface
    {
        $this->set(self::REL_TARGET_MARKER, $value);

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
        return $this->getRelatedEntity(self::REL_SOURCE_PAGE);
    }

    /**
     * @return \BetaKiller\Model\HitPage
     */
    public function getTargetPage(): HitPageInterface
    {
        return $this->getRelatedEntity(self::REL_TARGET_PAGE);
    }

    /**
     * @return \BetaKiller\Model\HitMarkerInterface
     */
    public function getTargetMarker(): HitMarkerInterface
    {
        return $this->getRelatedEntity(self::REL_TARGET_MARKER);
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
