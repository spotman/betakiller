<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarCookiesDataCollector extends DataCollector implements Renderable
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * DebugBarSessionDataCollector constructor.
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
        return 'cookies';
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        $data = [];

        foreach ($this->request->getCookieParams() as $key => $value) {
            $data[$key] = $this->getDataFormatter()->formatVar($value);
        }

        return $data;
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
                'icon'    => 'cookie',
                'widget'  => 'PhpDebugBar.Widgets.HtmlVariableListWidget',
                'map'     => 'cookies',
                'default' => '{}',
            ],
        ];
    }
}
