<?php
namespace BetaKiller\Content;

use Database_Result;
use Kohana_Exception;

interface ContentElementInterface extends ContentRelatedInterface
{
    /**
     * @return Database_Result|ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_all_files();
}
