<?php
namespace BetaKiller\Helper;

/**
 * Trait LogTrait
 *
 * @package BetaKiller\Helper
 * @deprecated Use \Psr\Log\LoggerInterface instead
 */
trait LogTrait
{
    /**
     * @param string $message
     * @param array $variables
     * @return $this
     * @deprecated Use \Psr\Log\LoggerInterface instead
     */
    protected function debug($message, array $variables = NULL)
    {
        \Log::debug($message, $variables, NULL, 1);
        return $this;
    }

    /**
     * @param string $message
     * @param array $variables
     * @return $this
     * @deprecated Use \Psr\Log\LoggerInterface instead
     */
    protected function info($message, array $variables = NULL)
    {
        \Log::info($message, $variables, NULL, 1);
        return $this;
    }

    /**
     * @param string $message
     * @param array $variables
     * @return $this
     * @deprecated Use \Psr\Log\LoggerInterface instead
     */
    protected function notice($message, array $variables = NULL)
    {
        \Log::notice($message, $variables, NULL, 1);
        return $this;
    }

    /**
     * @param string $message
     * @param array $variables
     * @return $this
     * @deprecated Use \Psr\Log\LoggerInterface instead
     */
    protected function warning($message, array $variables = NULL)
    {
        \Log::warning($message, $variables, NULL, 1);
        return $this;
    }

    /**
     * @param \Throwable $e
     * @return $this
     * @deprecated Use \Psr\Log\LoggerInterface instead
     */
    protected function exception(\Throwable $e)
    {
        \Log::exception($e);
        return $this;
    }
}
