<?php

declare(strict_types=1);

namespace BetaKiller\IFace;

use BetaKiller\Helper\InertiaPropsBuilder;
use Psr\Http\Message\ServerRequestInterface;

abstract readonly class AbstractInertiaIFace extends AbstractIFace implements InertiaIFaceInterface
{
    final public function getData(ServerRequestInterface $request): array
    {
        $builder = new InertiaPropsBuilder();

        $this->defineProps($request, $builder);

        return $builder->getAll();
    }

    abstract protected function defineProps(ServerRequestInterface $request, InertiaPropsBuilder $builder): void;
}
