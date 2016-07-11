<?php
namespace BetaKiller\Helper;

trait Log
{
    /**
     * @param string $message
     * @param array $variables
     * @return $this
     */
    protected function debug($message, array $variables = NULL)
    {
        Log::debug($message, $variables);
        return $this;
    }

    /**
     * @param string $message
     * @param array $variables
     * @return $this
     */
    protected function info($message, array $variables = NULL)
    {
        Log::info($message, $variables);
        return $this;
    }

    /**
     * @param string $message
     * @param array $variables
     * @return $this
     */
    protected function notice($message, array $variables = NULL)
    {
        Log::notice($message, $variables);
        return $this;
    }

    /**
     * @param string $message
     * @param array $variables
     * @return $this
     */
    protected function warning($message, array $variables = NULL)
    {
        Log::warning($message, $variables);
        return $this;
    }

    /**
     * @param \Exception $e
     * @return $this
     */
    protected function exception(\Exception $e)
    {
        \Log::exception($e);
        return $this;
    }
}
