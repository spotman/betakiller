<?php
declare(strict_types=1);

namespace BetaKiller\Url;

use LogicException;

class DummyInstance extends AbstractUrlElementInstance
{
    /**
     * @var \BetaKiller\Url\DummyModelInterface
     */
    private $model;

    /**
     * DummyInstance constructor.
     *
     * @param \BetaKiller\Url\DummyModelInterface $model
     */
    public function __construct(DummyModelInterface $model)
    {
        $this->model = $model;
    }

    /**
     * @return string
     */
    public static function getSuffix(): string
    {
        throw new LogicException('Dummy instance have no suffix and must not be used here');
    }

    /**
     * @return \BetaKiller\Url\DummyModelInterface
     */
    public function getModel(): DummyModelInterface
    {
        return $this->model;
    }
}
