<?php
namespace BetaKiller\Error;

use BetaKiller\Model\UserInterface;

interface PhpExceptionStorageInterface
{
    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getUnresolvedPhpExceptions();

    /**
     * @return PhpExceptionModelInterface[]
     */
    public function getResolvedPhpExceptions();

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     *
     * @return string
     */
    public function getTraceFor(PhpExceptionModelInterface $model);

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     * @param string                                       $traceResponse
     */
    public function setTraceFor(PhpExceptionModelInterface $model, $traceResponse);

    /**
     * @param \Exception $exception
     *
     * @return PhpExceptionModelInterface
     */
    public function storeException(\Exception $exception);

    /**
     * @param string $hash
     *
     * @return \BetaKiller\Error\PhpExceptionModelInterface|null
     */
    public function findByHash($hash);

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     * @param \BetaKiller\Model\UserInterface              $user
     */
    public function resolve(PhpExceptionModelInterface $model, UserInterface $user);

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     * @param \BetaKiller\Model\UserInterface              $user
     */
    public function ignore(PhpExceptionModelInterface $model, UserInterface $user);

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $model
     */
    public function delete(PhpExceptionModelInterface $model);
}
