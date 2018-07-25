<?php
namespace BetaKiller\Url\ModelProvider;

use BetaKiller\Model\DummyModelTrait;
use BetaKiller\Url\DummyModelInterface;

class DummyPlainModel extends AbstractPlainUrlElementModel implements DummyModelInterface
{
    use DummyModelTrait;
}
