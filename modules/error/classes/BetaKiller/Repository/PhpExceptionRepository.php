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

        $this->filterUnresolved($orm)->orderByCreatedAt($orm);

        return $orm->get_all();
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getResolvedPhpExceptions(): array
    {
        $orm = $this->getOrmInstance();

        $this->filterResolved($orm)->orderByCreatedAt($orm);

        return $orm->get_all();
    }

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getRequiredNotification(): array
    {
        $orm = $this->getOrmInstance();

        $this->filterNotificationRequired($orm)->orderByCreatedAt($orm);

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
     * @return $this
     */
    private function filterUnresolved(OrmInterface $orm)
    {
        $orm->where('resolved_by', 'IS', null);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     *
     * @return $this
     */
    private function filterResolved(OrmInterface $orm)
    {
        $orm->where('resolved_by', 'IS NOT', null);

        return $this;
    }

    private function filterNotificationRequired(OrmInterface $orm)
    {
        $orm->where('notification_required', '=', true);

        return $this;
    }

    /**
     * @param \BetaKiller\Utils\Kohana\ORM\OrmInterface $orm
     * @param bool|null                                 $asc
     *
     * @return $this
     */
    private function orderByCreatedAt(OrmInterface $orm, ?bool $asc = null)
    {
        $orm->order_by('created_at', $asc ? 'asc' : 'desc');

        return $this;
    }
}
