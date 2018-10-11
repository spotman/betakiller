<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\Widget\MenuWidget;
use Psr\Http\Message\ServerRequestInterface;

class MainWidget extends MenuWidget
{
    public function getData(ServerRequestInterface $request, array $context): array
    {
        $items = parent::getData($request, $context);

        return [
            'items' => $items,
        ];
    }
}
