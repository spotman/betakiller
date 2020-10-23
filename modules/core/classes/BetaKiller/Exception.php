<?php
namespace BetaKiller;

use BetaKiller\Exception\DefaultExceptionBehaviourTrait;

class Exception extends \Exception implements ExceptionInterface
{
    use DefaultExceptionBehaviourTrait;

    public const PLACEHOLDER_PREFIX = ':';

    /**
     * @var array
     */
    private $variables = [];

    /**
     * @param \Throwable  $e
     * @param string|null $message
     *
     * @return static
     */
    public static function wrap(\Throwable $e, string $message = null)
    {
        /** @phpstan-ignore-next-line */
        return new static(':error', [':error' => $message ?? $e->getMessage()], $e->getCode(), $e);
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
        if (!empty($variables)) {
            $this->variables = $variables;
            $message         = strtr($message, array_filter($variables, 'is_scalar'));
        }

        // Pass the message and integer code to the parent
        parent::__construct($message, (int)$code, $previous);

        // Save the unmodified code
        // @link http://bugs.php.net/39615
        $this->code = $code;
    }

    public static function oneLiner(\Throwable $e): string
    {
        return sprintf(
            '%s [ %s ]: %s ~ %s [ %d ]',
            \get_class($e), $e->getCode(), \strip_tags($e->getMessage()), $e->getFile(), $e->getLine()
        );
    }

    public static function addPlaceholderPrefixToKeys(array $data): array
    {
        $output = [];

        foreach ($data as $key => $value) {
            // Add prefix if it does not exist
            if (strpos($key, self::PLACEHOLDER_PREFIX) !== 0) {
                $key = self::PLACEHOLDER_PREFIX.$key;
            }

            $output[$key] = $value;
        }

        return $output;
    }

    /**
     * Returns "key => value" pairs provided by caller in constructor parameters for placeholders` replacement
     *
     * @return array
     */
    public function getMessageVariables(): array
    {
        return $this->variables;
    }
}
