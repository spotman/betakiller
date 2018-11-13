<?php
namespace BetaKiller\IFace\Admin\Error;

use BetaKiller\Exception;
use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\PhpExceptionModelInterface;
use Psr\Http\Message\ServerRequestInterface;

class PhpExceptionStackTraceIFace extends AbstractErrorAdminIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     * @throws \BetaKiller\Exception
     */
    public function getData(ServerRequestInterface $request): array
    {
        /** @var PhpExceptionModelInterface $model */
        $model = ServerRequestHelper::getEntity($request, PhpExceptionModelInterface::class);

        if (!$model) {
            throw new Exception('Incorrect php exception hash');
        }

        return [
            'trace' => $model->getTrace(),
        ];
    }
}
