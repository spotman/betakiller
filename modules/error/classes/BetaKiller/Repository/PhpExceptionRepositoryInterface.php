<?php
namespace BetaKiller\Repository;

use BetaKiller\Model\PhpExceptionModelInterface;

/**
 * Class PhpExceptionRepositoryInterface
 *
 * @package BetaKiller\Error
 * @method PhpExceptionModelInterface getById(int $id)
 */
interface PhpExceptionRepositoryInterface extends DispatchableRepositoryInterface
{
    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getUnresolvedPhpExceptions(): array;

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getResolvedPhpExceptions(): array;

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getIgnoredPhpExceptions(): array;

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getRequiredNotification(): array;

    /**
     * @param string $hash
     *
     * @return PhpExceptionModelInterface|null
     */
    public function findByHash(string $hash): ?PhpExceptionModelInterface;
}
