<?php
declare(strict_types=1);

namespace BetaKiller\WebHook;

use BetaKiller\Url\WebHookModelInterface;

abstract class AbstractWebHook implements WebHookInterface
{
    /**
     * @var \BetaKiller\Url\WebHookModelInterface
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
