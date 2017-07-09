<?php
namespace BetaKiller\Model;

interface PhpExceptionHistoryModelInterface
{
    /**
     * @return PhpExceptionModelInterface
     */
    public function getPhpException(): PhpExceptionModelInterface;

    /**
     * @param \BetaKiller\Model\PhpExceptionModelInterface $phpException
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setPhpException(PhpExceptionModelInterface $phpException): PhpExceptionHistoryModelInterface;

    /**
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface;

    /**
     * @param \BetaKiller\Model\UserInterface|null $user
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setUser(UserInterface $user = null): PhpExceptionHistoryModelInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getTimestamp(): \DateTimeImmutable;

    /**
     * @param \DateTimeInterface $time
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setTimestamp(\DateTimeInterface $time): PhpExceptionHistoryModelInterface;

    /**
     * @return string
     */
    public function getStatus(): string;

    /**
     * @param string $status
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setStatus(string $status): PhpExceptionHistoryModelInterface;

    public function save();

    public function delete();
}
