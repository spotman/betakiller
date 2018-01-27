<?php
declare(strict_types=1);

namespace Spotman\DotEnv;


class DotEnvException extends \Exception
{
    /**
     * Construct the exception. Note: The message is NOT binary safe.
     *
     * @link  http://php.net/manual/en/exception.construct.php
     *
     * @param string     $message The Exception message to throw.
     * @param array|null $values Key-value pairs for replacing in the exception
     *
     * @since 5.1.0
     */
    public function __construct(string $message, array $values = null)
    {
        if ($values) {
            $message = strtr($message, $values);
        }

        parent::__construct($message);
    }
}
