<?php
declare(strict_types=1);

namespace BetaKiller\Wamp;

use Thruway\Peer\Router;
use Thruway\Transport\TransportInterface;

class WampRouter extends Router
{
    /**
     * Handle close transport
     *
     * @param \Thruway\Transport\TransportInterface $transport
     */
    public function onClose(TransportInterface $transport)
    {
        parent::onClose($transport);

        // @see https://github.com/ratchetphp/Ratchet/issues/662#issuecomment-454886034
        gc_collect_cycles();
    }
}
