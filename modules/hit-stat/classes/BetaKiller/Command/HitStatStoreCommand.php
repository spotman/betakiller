<?php
declare(strict_types=1);

namespace BetaKiller\Command;

use BetaKiller\MessageBus\CommandMessageInterface;
use BetaKiller\Model\HitMarkerInterface;
use BetaKiller\Model\HitPageInterface;
use BetaKiller\Model\UserInterface;
use Ramsey\Uuid\UuidInterface;

final class HitStatStoreCommand implements CommandMessageInterface
{
    /**
     * @var \BetaKiller\Model\HitPageInterface|null
     */
    private $source;

    /**
     * @var \BetaKiller\Model\HitPageInterface
     */
    private $target;

    /**
     * @var \BetaKiller\Model\HitMarkerInterface|null
     */
    private $marker;

    /**
     * @var \DateTimeImmutable
     */
    private $moment;

    /**
     * @var string
     */
    private $ip;

    /**
     * @var \Ramsey\Uuid\UuidInterface
     */
    private $uuid;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * HitStatStoreCommand constructor.
     *
     * @param \Ramsey\Uuid\UuidInterface                $uuid
     * @param \BetaKiller\Model\UserInterface      $user
     * @param string                                    $ip
     * @param \BetaKiller\Model\HitPageInterface        $source
     * @param \BetaKiller\Model\HitPageInterface        $target
     * @param \BetaKiller\Model\HitMarkerInterface|null $marker
     *
     * @throws \Exception
     */
    public function __construct(
        UuidInterface $uuid,
        UserInterface $user,
        string $ip,
        ?HitPageInterface $source,
        HitPageInterface $target,
        ?HitMarkerInterface $marker
    ) {
        $this->uuid = $uuid;
        $this->user = $user;
        $this->ip        = $ip;
        $this->source    = $source;
        $this->target    = $target;
        $this->marker    = $marker;
        $this->moment    = new \DateTimeImmutable();
    }

    /**
     * @return \Ramsey\Uuid\UuidInterface
     */
    public function getUuid(): UuidInterface
    {
        return $this->uuid;
    }

    /**
     * @return \BetaKiller\Model\UserInterface
     */
    public function getUser(): UserInterface
    {
        return $this->user;
    }

    /**
     * @return \BetaKiller\Model\HitPageInterface|null
     */
    public function getSource(): ?HitPageInterface
    {
        return $this->source;
    }

    /**
     * @return \BetaKiller\Model\HitPageInterface
     */
    public function getTarget(): HitPageInterface
    {
        return $this->target;
    }

    /**
     * @return \BetaKiller\Model\HitMarkerInterface|null
     */
    public function getMarker(): ?HitMarkerInterface
    {
        return $this->marker;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getMoment(): \DateTimeImmutable
    {
        return $this->moment;
    }

    /**
     * @return string
     */
    public function getIp(): string
    {
        return $this->ip;
    }
}
