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
        $message = __($message, $variables);

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
     * @uses    Kohana_Exception::text
     * @return  string
     */
    public function __toString()
    {
        return Kohana_Exception::text($this);
    }

    /**
     * Get a single line of text representing the exception:
     *
     * Error [ Code ]: Message ~ File [ Line ]
     *
     * @param   \Throwable $e
     *
     * @return  string
     */
    public static function text(Throwable $e)
    {
        return sprintf('%s [ %s ]: %s ~ %s [ %d ]',
            get_class($e), $e->getCode(), strip_tags($e->getMessage()), Debug::path($e->getFile()), $e->getLine());
    }
} // End Kohana_Exception
