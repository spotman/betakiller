<?php
declare(strict_types=1);

namespace BetaKiller\Dev;

use BetaKiller\Helper\ServerRequestHelper;
use BetaKiller\Model\RoleInterface;
use BetaKiller\Model\UserInterface;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Psr\Http\Message\ServerRequestInterface;

class DebugBarUserDataCollector extends DataCollector implements Renderable
{
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private ServerRequestInterface $request;

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

        $user = ServerRequestHelper::getUser($this->request);

        foreach ($this->getData($user) as $key => $value) {
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
            'roles'    => implode('", "', $this->getAssignedRoles($user)),
        ];
    }

    private function getAssignedRoles(UserInterface $user): array
    {
        return array_map(function (RoleInterface $role) {
            return $role->getName();
        }, $user->getRoles());
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
