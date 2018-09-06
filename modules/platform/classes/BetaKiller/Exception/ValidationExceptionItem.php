<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

class ValidationExceptionItem
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $message;

    /**
     * ValidationExceptionItem constructor.
     *
     * @param string $name
     * @param string $message
     */
    public function __construct(string $name, string $message)
    {
        $this->name    = $name;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}
