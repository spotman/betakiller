<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Model\UserInterface;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;

class DebugBarUserDataCollector extends DataCollector implements Renderable
{
    /**
     * @var \BetaKiller\Model\UserInterface
     */
    private $user;

    /**
     * DebugBarSessionDataCollector constructor.
     *
     * @param \BetaKiller\Model\UserInterface $user
     */
    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Returns the unique name of the collector
     *
     * @return string
     */
    public function getName(): string
    {
        return 'user';
    }

    /**
     * Called by the DebugBar when data needs to be collected
     *
     * @return array Collected data
     */
    public function collect(): array
    {
        $data = [];

        foreach ($this->getData($this->user) as $key => $value) {
            $data[$key] = $this->getDataFormatter()->formatVar($value);
        }

        return $data;
    }

    private function getData(UserInterface $user): array
    {
        return [
            'id'       => $user->getID(),
            'username' => $user->getUsername(),
            'email'    => $user->getEmail(),
            'roles'    => implode('", "', $this->getAssignedRoles()),
        ];
    }

    private function getAssignedRoles(): array
    {
        return $this->user->getAllRolesNames();
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
                'icon'    => 'user',
                'widget'  => 'PhpDebugBar.Widgets.HtmlVariableListWidget',
                'map'     => 'user',
                'default' => '{}',
            ],
        ];
    }
}
