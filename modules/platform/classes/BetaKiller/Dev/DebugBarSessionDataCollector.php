<?php

declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Mezzio\Session\SessionIdentifierAwareInterface;
use Mezzio\Session\SessionInterface;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarSessionDataCollector extends DataCollector implements Renderable
{
    /**
     * @var \Mezzio\Session\SessionInterface
     */
    private SessionInterface $session;

    /**
     * DebugBarSessionDataCollector constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->session = ServerRequestHelper::getSession($request);
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    public function getName(): string
    {
        return 'session';
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        return $this->getSessionData($this->session);
    }

    private function getSessionID(SessionInterface $session): ?string
    {
        if (!$session instanceof SessionIdentifierAwareInterface) {
            return null;
        }

        return $session->getId();
    }

    private function getSessionData(SessionInterface $session): array
    {
        $data = [];

        $id = $this->getSessionID($session);

        if ($id) {
            $data['id'] = $this->getDataFormatter()->formatVar($id);
        }

        foreach ($session->toArray() as $key => $value) {
            $data[$key] = $this->getDataFormatter()->formatVar($value);
        }

        ksort($data);

        return $data;
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see \DebugBar\JavascriptRenderer::addControl()}
     *
     * @return array
     */
    public function getWidgets(): array
    {
        return [
            $this->getName() => [
                'icon'    => 'tags',
                'widget'  => 'PhpDebugBar.Widgets.HtmlVariableListWidget',
                'map'     => 'session',
                'default' => '{}',
            ],
        ];
    }
}
