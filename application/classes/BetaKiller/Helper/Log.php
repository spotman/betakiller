<?php
namespace BetaKiller\Helper;

trait Log
{
    /**
     * @param $message
     * @return $this
     */
    protected function debug($message)
    {
        Log::debug($message);
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    protected function info($message)
    {
        Log::info($message);
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    protected function notice($message)
    {
        Log::notice($message);
        return $this;
    }

    /**
     * @param $message
     * @return $this
     */
    protected function warning($message)
    {
        Log::warning($message);
        return $this;
    }

    /**
     * @param \Exception $e
     * @return $this
     */
    protected function exception(\Exception $e)
    {
        Log::exception($e);
        return $this;
    }
}
