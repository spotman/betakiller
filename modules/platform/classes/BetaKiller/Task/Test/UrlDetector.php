<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Auth\UserUrlDetectorInterface;
use BetaKiller\Model\UserInterface;
use BetaKiller\Task\AbstractTask;

class UrlDetector extends AbstractTask
{
    /**
     * @var \BetaKiller\Auth\UserUrlDetectorInterface
     */
    private $urlDetector;

    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * UrlDetector constructor.
     *
     * @param \BetaKiller\Model\UserInterface           $user
     * @param \BetaKiller\Auth\UserUrlDetectorInterface $urlDetector
     */
    public function __construct(UserInterface $user, UserUrlDetectorInterface $urlDetector)
    {
        parent::__construct();

        $this->urlDetector = $urlDetector;
        $this->user = $user;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        echo $this->urlDetector->detect($this->user).PHP_EOL;
    }
}
