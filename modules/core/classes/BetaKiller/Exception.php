<?php
namespace BetaKiller;

use BetaKiller\Exception\DefaultExceptionBehaviourTrait;

class Exception extends \Exception implements ExceptionInterface
{
    use DefaultExceptionBehaviourTrait;

    /**
     * @param \Throwable $e
     *
     * @return static
     */
    public static function wrap(\Throwable $e)
    {
        return new static(':error', [':error' => $e->getMessage()], $e->getCode(), $e);
    }

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
    public function __construct($message = '', array $variables = null, $code = 0, \Throwable $previous = null)
    {
        // Set the message
        $message = empty($variables) ? $message : strtr($message, $variables);

        // Pass the message and integer code to the parent
        parent::__construct($message, (int)$code, $previous);

        // Save the unmodified code
        // @link http://bugs.php.net/39615
        $this->code = $code;
    }

    public function oneLiner(): string
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            \get_class($this), $this->getCode(), \strip_tags($this->getMessage()), $this->getFile(), $this->getLine()
        );
    }
}
