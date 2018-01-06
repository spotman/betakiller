<?php
namespace BetaKiller\Exception;

interface HttpExceptionExpectedInterface extends HttpExceptionInterface
{
    /**
     * Generate a Response for the current Exception
     *
     * @return \Response
     */
    public function get_response();
}
