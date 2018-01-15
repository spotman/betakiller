<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\PhpExceptionModelInterface;
use BetaKiller\Utils\Kohana\ORM\OrmInterface;

/**
 * Class PhpExceptionRepository
 *
 * @package BetaKiller\Error
 * @method PhpExceptionModelInterface getById(int $id)
 * @method PhpExceptionModelInterface create()
 */
class PhpExceptionRepository extends AbstractOrmBasedDispatchableRepository
{
    /**
     * @return string
     */
    public function getUrlKeyName(): string
    {
        return 'hash';
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getUnresolvedPhpExceptions(): array
    {
        $orm = $this->getOrmInstance();

        $this->filterUnresolved($orm)->orderByLastSeenAt($orm);

        return $orm->get_all();
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getResolvedPhpExceptions(): array
    {
        $orm = $this->getOrmInstance();

        $this->filterResolved($orm)->orderByLastSeenAt($orm);

        return $orm->get_all();
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getRequiredNotification(): array
    {
        $orm = $this->getOrmInstance();

        $this->filterNotificationRequired($orm)
            ->filterNew($orm)
            ->orderByLastSeenAt($orm);

        return $orm->get_all();
    }

    /**
     * @param string $hash
     *
     * @return PhpExceptionModelInterface|null
     */
    public function findByHash(string $hash): ?PhpExceptionModelInterface
    {
        $orm = $this->getOrmInstance();

        /** @var \BetaKiller\Model\PhpException $model */
        $model = $orm->where('hash', '=', $hash)->find();

        return $model->loaded() ? $model : null;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterUnresolved(OrmInterface $orm): PhpExceptionRepository
    {
        $orm->where('resolved_by', 'IS', null);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterResolved(OrmInterface $orm): PhpExceptionRepository
    {
        $orm->where('resolved_by', 'IS NOT', null);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterNotificationRequired(OrmInterface $orm): PhpExceptionRepository
    {
        $orm->where('notification_required', '=', true);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterNew(OrmInterface $orm): PhpExceptionRepository
    {
        return $this->filterStatus($orm, PhpExceptionModelInterface::STATE_NEW);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param string                                    $status
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function filterStatus(OrmInterface $orm, string $status): PhpExceptionRepository
    {
        $orm->where('status', '=', $status);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param bool|null                                 $asc
     *
     * @return \BetaKiller\Repository\PhpExceptionRepository
     */
    private function orderByLastSeenAt(OrmInterface $orm, ?bool $asc = null): PhpExceptionRepository
    {
        $orm->order_by('last_seen_at', $asc ? 'asc' : 'desc');

        return $this;
    }
}
