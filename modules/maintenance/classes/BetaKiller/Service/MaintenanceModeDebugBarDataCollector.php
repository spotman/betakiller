<?php
declare(strict_types=1);

namespace BetaKiller\Service;

use BetaKiller\Model\MaintenanceMode;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class MaintenanceModeDebugBarDataCollector extends DataCollector implements Renderable, AssetProvider
{
    /**
     * @var \BetaKiller\Model\MaintenanceMode
     */
    private $model;

    /**
     * MaintenanceModeDebugBarDataCollector constructor.
     *
     * @param \BetaKiller\Model\MaintenanceMode $model
     */
    public function __construct(?MaintenanceMode $model)
    {
        $this->model = $model;
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        if (!$this->model) {
            return [];
        }

        $startsAt = $this->model->getStartsAt()->format('H:i:s T');

        return [
            'label' => 'Maintenance mode at '.$startsAt,
        ];
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    public function getName(): string
    {
        return 'maintenance';
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
            'maintenance' => [
                'icon'    => 'code-fork',
                'tooltip' => 'Maintenance mode',
                'map'     => 'maintenance.label',
                'default' => '"Regular"',
            ],
        ];
    }

    /**
     * Returns an array with the following keys:
     *  - base_path
     *  - base_url
     *  - css: an array of filenames
     *  - js: an array of filenames
     *  - inline_css: an array map of content ID to inline CSS content (not including <style> tag)
     *  - inline_js: an array map of content ID to inline JS content (not including <script> tag)
     *  - inline_head: an array map of content ID to arbitrary inline HTML content (typically
     *        <style>/<script> tags); it must be embedded within the <head> element
     *
     * All keys are optional.
     *
     * Ideally, you should store static assets in filenames that are returned via the normal css/js
     * keys.  However, the inline asset elements are useful when integrating with 3rd-party
     * libraries that require static assets that are only available in an inline format.
     *
     * The inline content arrays require special string array keys:  the caller of this function
     * will use them to deduplicate content.  This is particularly useful if multiple instances of
     * the same asset provider are used.  Inline assets from all collectors are merged together into
     * the same array, so these content IDs effectively deduplicate the inline assets.
     *
     * @return array
     */
    public function getAssets(): array
    {
        if (!$this->model) {
            return [];
        }

        return [
            'inline_css' => [
                'mode_widget_css' => '.phpdebugbar-fa-code-fork, .phpdebugbar-fa-code-fork ~ .phpdebugbar-text { color: #F00 !important; font-weight: bold; }',
            ],
        ];
    }
}
