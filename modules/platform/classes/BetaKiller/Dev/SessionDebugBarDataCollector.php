<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Expressive\Session\SessionIdentifierAwareInterface;
use Zend\Expressive\Session\SessionInterface;

class SessionDebugBarDataCollector extends DataCollector implements Renderable, AssetProvider
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * SessionDebugBarDataCollector constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(ServerRequestInterface $request)
    {
        $this->request = $request;
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
        $session = ServerRequestHelper::getSession($this->request);

        return $this->getSessionData($session);
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
            $data['id'] = $id;
        }

        foreach ($session->toArray() as $key => $value) {
            $data[$key] = $this->getVarDumper()->renderVar($value);
        }

        return $data;
    }

    /**
     * @return array
     */
    public function getAssets(): array
    {
        return $this->getVarDumper()->getAssets();
    }

    /**
     * Returns a hash where keys are control names and their values
     * an array of options as defined in {@see DebugBar\JavascriptRenderer::addControl()}
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
