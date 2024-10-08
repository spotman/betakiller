<?php
namespace BetaKiller\IFace;

use BetaKiller\Exception\NotAvailableHttpException;
use Psr\Http\Message\ServerRequestInterface;

class HttpError503IFace extends AbstractHttpErrorIFace
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
        $now             = new \DateTimeImmutable;
        $defaultDuration = new \DateInterval('PT30S');

        if (!$this->exception) {
            $endTime         = $now->add($defaultDuration);
            $this->exception = new NotAvailableHttpException($endTime);
        }

        if (!$this->exception instanceof NotAvailableHttpException) {
            throw new \LogicException('Exception must be instance of NotAvailableHttpException');
        }

        $endTime = $this->exception->getEndsAt();

        if ($endTime < $now) {
            $endTime = $now->add($defaultDuration);
        }

        $duration = $endTime->getTimestamp() - $now->getTimestamp();

        return array_merge(parent::getData($request), [
            'duration' => $duration,
        ]);
    }
}
