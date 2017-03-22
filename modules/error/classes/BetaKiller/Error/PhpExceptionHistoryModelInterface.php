<?php
namespace BetaKiller\Error;

use BetaKiller\Model\UserInterface;

interface PhpExceptionHistoryModelInterface
{
    /**
     * @return PhpExceptionModelInterface
     */
    public function getPhpException();

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $phpException
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setPhpException(PhpExceptionModelInterface $phpException);

    /**
     * @return UserInterface|null
     */
    public function getUser();

    /**
     * @param \BetaKiller\Model\UserInterface|null $user
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setUser(UserInterface $user = null);

    /**
     * @return \DateTime
     */
    public function getTimestamp();

    /**
     * @param \DateTime $time
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setTimestamp(\DateTime $time);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param string $status
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setStatus($status);

    public function save();

    public function delete();
}
