<?php
declare(strict_types=1);

namespace BetaKiller\Model;

/**
 * WebHookService
 *
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class WebHookService extends \ORM
{
    protected function configure(): void
    {
        $this->_table_name = 'webhook_services';

        parent::configure();
    }

    /**
     * Returns target service name (website domain or company name)
     *
     * @return string
     */
    public function getCodename(): string
    {
        return $this->codename;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
