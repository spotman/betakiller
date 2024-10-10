<?php

/**
 * Kohana exception class. Translates exceptions using the [I18n] class.
 *
 * @package        Kohana
 * @category       Exceptions
 * @author         Kohana Team
 * @copyright  (c) 2008-2012 Kohana Team
 * @license        http://kohanaframework.org/license
 */
class Kohana_Kohana_Exception extends Exception
{
    /**
     * Creates a new translated exception.
     *
     *     throw new Kohana_Exception('Something went terrible wrong, :user',
     *         array(':user' => $user));
     *
     * @param   string         $message   error message
     * @param   array          $variables translation variables
     * @param   integer|string $code      the exception code
     * @param   \Throwable     $previous  Previous exception
     */
    public function __construct($message = '', array $variables = null, $code = 0, Throwable $previous = null)
    {
        // Set the message
        $message = empty($variables) ? $message : strtr($message, $variables);

        // Pass the message and integer code to the parent
        parent::__construct($message, (int)$code, $previous);

        // Save the unmodified code
        // @link http://bugs.php.net/39615
        $this->code = $code;
    }

    /**
     * Magic object-to-string method.
     *
     *     echo $exception;
     *
     * @return  string
     */
    public function __toString()
    {
        return \BetaKiller\Exception::oneLiner($this);
    }
} // End Kohana_Exception
