<?php
namespace BetaKiller\Content;

use Database_Result;
use Kohana_Exception;

interface ContentElementInterface extends ContentRelatedInterface
{
    /**
     * @param int
     *
     * @return ContentElementInterface|NULL
     */
    public function get_by_id($id);

    /**
     * @return int
     */
    public function get_id();

    /**
     * @return Database_Result|ContentElementInterface[]
     * @throws Kohana_Exception
     */
    public function get_all_files();
}
