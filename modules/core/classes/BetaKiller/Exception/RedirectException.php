<?php
declare(strict_types=1);

namespace BetaKiller\Exception;

abstract class RedirectException extends HttpException implements HttpExceptionExpectedInterface
{
    /**
     * @var string
     */
    private $location;

    public function __construct(int $code, string $location)
    {
        parent::__construct($code);

        $this->location = $location;
    }

    public function getLocation(): string
    {
        return $this->location;
    }
}
