<?php
declare(strict_types=1);

namespace BetaKiller\WebHook;

use BetaKiller\Model\WebHookModelInterface;
use BetaKiller\Url\AbstractUrlElementInstance;

abstract class AbstractWebHook extends AbstractUrlElementInstance implements WebHookInterface
{
    /**
     * @return string
     */
    public static function getSuffix(): string
    {
        return self::SUFFIX;
    }

    /**
     * @var \BetaKiller\Model\WebHookModelInterface
     */
    private $model;

    public function getModel(): WebHookModelInterface
    {
        return $this->model;
    }

    public function setModel(WebHookModelInterface $model): WebHookInterface
    {
        $this->model = $model;

        return $this;
    }
}
