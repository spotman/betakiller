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
     * @return null|string
     */
    public function getUserID(): ?string;

    /**
     * @param \BetaKiller\Model\UserInterface|null $user
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setUser(?UserInterface $user = null): PhpExceptionHistoryModelInterface;

    /**
     * @return \DateTimeImmutable
     */
    public function getTimestamp(): \DateTimeImmutable;

    /**
     * @param \DateTimeImmutable $time
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setTimestamp(\DateTimeImmutable $time): PhpExceptionHistoryModelInterface;

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
