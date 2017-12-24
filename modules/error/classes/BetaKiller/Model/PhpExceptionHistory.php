<?php

namespace BetaKiller\Model;

use BetaKiller\Helper\UserModelFactoryTrait;
use DateTimeImmutable;

class PhpExceptionHistory extends \ORM implements PhpExceptionHistoryModelInterface
{
    use UserModelFactoryTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize(): void
    {
        $this->_db_group   = 'filesystem';
        $this->_table_name = 'error_history';

        $this->belongs_to([
            'error' => [
                'model'       => 'PhpException',
                'foreign_key' => 'error_id',
            ],
        ]);

        parent::_initialize();
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
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        $id = $this->get('user');

        if (!$id) {
            return null;
        }

        $user = $this->model_factory_user($id);

        return $user->loaded() ? $user : null;
    }

    /**
     * @param \BetaKiller\Model\UserInterface|null $user
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setUser(UserInterface $user = null): PhpExceptionHistoryModelInterface
    {
        $this->set('user', $user ? $user->get_id() : null);

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
     * @param \DateTimeInterface $time
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setTimestamp(\DateTimeInterface $time): PhpExceptionHistoryModelInterface
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
        $this->set('status', (string)$status);

        return $this;
    }
}
