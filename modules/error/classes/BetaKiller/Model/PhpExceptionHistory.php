<?php

namespace BetaKiller\Model;

use DateTimeImmutable;

class PhpExceptionHistory extends \ORM implements PhpExceptionHistoryModelInterface
{
    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function configure(): void
    {
        $this->_db_group   = 'errors';
        $this->_table_name = 'error_history';

        $this->belongs_to([
            'error' => [
                'model'       => 'PhpException',
                'foreign_key' => 'error_id',
            ],
        ]);
    }

    /**
     * @return \BetaKiller\Model\PhpExceptionModelInterface
     */
    public function getPhpException(): PhpExceptionModelInterface
    {
        return $this->get('error');
    }

    /**
     * @param \BetaKiller\Model\PhpExceptionModelInterface $phpException
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setPhpException(PhpExceptionModelInterface $phpException): PhpExceptionHistoryModelInterface
    {
        $this->set('error', $phpException);

        return $this;
    }

    /**
     * @return null|string
     * @throws \Kohana_Exception
     */
    public function getUserID(): ?string
    {
        $id = $this->get('user');

        return $id ? (int)$id : null;
    }

    /**
     * @param \BetaKiller\Model\UserInterface|null $user
     *
     * @return PhpExceptionHistoryModelInterface
     * @throws \Kohana_Exception
     */
    public function setUser(?UserInterface $user = null): PhpExceptionHistoryModelInterface
    {
        $this->set('user', $user ? $user->getID() : null);

        return $this;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getTimestamp(): DateTimeImmutable
    {
        return $this->get_datetime_column_value('ts');
    }

    /**
     * @param \DateTimeImmutable $time
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setTimestamp(\DateTimeImmutable $time): PhpExceptionHistoryModelInterface
    {
        $this->set_datetime_column_value('ts', $time);

        return $this;
    }

    /**
     * @return string
     */
    public function getStatus(): string
    {
        return (string)$this->get('status');
    }

    /**
     * @param string $status
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setStatus(string $status): PhpExceptionHistoryModelInterface
    {
        $this->set('status', $status);

        return $this;
    }
}
