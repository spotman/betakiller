<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\CookieHelper;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarCookiesDataCollector extends DataCollector implements Renderable
{
    /**
     * @var \BetaKiller\Helper\CookieHelper
     */
    private $helper;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * DebugBarSessionDataCollector constructor.
     *
     * @param \BetaKiller\Helper\CookieHelper          $helper
     * @param \Psr\Http\Message\ServerRequestInterface $request
     */
    public function __construct(CookieHelper $helper, ServerRequestInterface $request)
    {
        $this->helper  = $helper;
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

        foreach ($this->helper->getAll($this->request) as $key => $value) {
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
