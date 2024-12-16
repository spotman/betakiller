<?php

namespace BetaKiller\IFace;

use BetaKiller\Exception\NotAvailableHttpException;
use DateInterval;
use DateTimeImmutable;
use Psr\Http\Message\ServerRequestInterface;

readonly class HttpError503IFace extends AbstractHttpErrorIFace
{
    /**
     * Returns data for View
     * Override this method in child classes
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return array
     */
    public function getData(ServerRequestInterface $request): array
    {
        $now             = new DateTimeImmutable();
        $defaultDuration = new DateInterval('PT30S');

        $exception = self::fetchException($request);

        $exception ??= new NotAvailableHttpException($now->add($defaultDuration));

        if (!$exception instanceof NotAvailableHttpException) {
            throw new \LogicException('Exception must be instance of NotAvailableHttpException');
        }

        $endTime = $exception->getEndsAt();

        if ($endTime < $now) {
            $endTime = $now->add($defaultDuration);
        }

        $duration = $endTime->getTimestamp() - $now->getTimestamp();

        return array_merge(parent::getData($request), [
            'duration' => $duration,
        ]);
    }
}
