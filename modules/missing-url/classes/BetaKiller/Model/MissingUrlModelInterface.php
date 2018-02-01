<?php
declare(strict_types=1);

namespace BetaKiller\Model;


interface MissingUrlModelInterface extends DispatchableEntityInterface
{
    public const URL_KEY = 'id';

    /**
     * @return string
     */
    public function getMissedUrl(): string;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface
     */
    public function setMissedUrl(string $value): MissingUrlModelInterface;

    /**
     * @return \DateTimeInterface
     */
    public function getLastSeenAt(): \DateTimeInterface;

    /**
     * @param \DateTimeInterface $value
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface
     */
    public function setLastSeenAt(\DateTimeInterface $value): MissingUrlModelInterface;

    /**
     * @return \BetaKiller\Model\MissingUrlRedirectTargetModelInterface
     */
    public function getRedirectTarget(): ?MissingUrlRedirectTargetModelInterface;

    /**
     * @param \BetaKiller\Model\MissingUrlRedirectTargetModelInterface $target
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface
     */
    public function setRedirectTarget(MissingUrlRedirectTargetModelInterface $target): MissingUrlModelInterface;

    /**
     * @param \BetaKiller\Model\MissingUrlReferrerModelInterface $model
     *
     * @return \BetaKiller\Model\MissingUrlModelInterface
     */
    public function addReferrer(MissingUrlReferrerModelInterface $model): MissingUrlModelInterface;

    public function hasReferrer(MissingUrlReferrerModelInterface $model): bool;

    /**
     * @return MissingUrlReferrerModelInterface[]
     */
    public function getReferrerList(): array;
}
