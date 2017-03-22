<?php

use BetaKiller\Error\PhpExceptionModelInterface;
use BetaKiller\Error\PhpExceptionHistoryModelInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Helper\UserModelFactoryTrait;

class Model_PhpExceptionHistory extends \ORM implements PhpExceptionHistoryModelInterface
{
    use UserModelFactoryTrait;

    /**
     * Prepares the model database connection, determines the table name,
     * and loads column information.
     *
     * @throws \Exception
     * @return void
     */
    protected function _initialize()
    {
        $this->_db_group = 'filesystem';
        $this->_table_name = 'error_history';

        $this->belongs_to([
            'error' =>  [
                'model'         =>  'PhpException',
                'foreign_key'   =>  'error_id',
            ]
        ]);

        parent::_initialize();
    }

    /**
     * @return \BetaKiller\Error\PhpExceptionModelInterface
     */
    public function getPhpException()
    {
        return $this->get('error');
    }

    /**
     * @param \BetaKiller\Error\PhpExceptionModelInterface $phpException
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setPhpException(PhpExceptionModelInterface $phpException)
    {
        $this->set('error', $phpException);
        return $this;
    }

    /**
     * @return UserInterface|null
     */
    public function getUser()
    {
        $id = $this->get('user');
        return $id ? $this->model_factory_user($id) : null;
    }

    /**
     * @param \BetaKiller\Model\UserInterface|null $user
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setUser(UserInterface $user = null)
    {
        $this->set('user', $user ? $user->get_id() : null);
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getTimestamp()
    {
        return $this->get_datetime_column_value('ts');
    }

    /**
     * @param \DateTime $time
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setTimestamp(\DateTime $time)
    {
        $this->set_datetime_column_value('ts', $time);
        return $this;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->get('status');
    }

    /**
     * @param string $status
     *
     * @return PhpExceptionHistoryModelInterface
     */
    public function setStatus($status)
    {
        $this->set('status', (string) $status);
        return $this;
    }
}
