<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\Widget\MenuWidget;

class MainWidget extends MenuWidget
{
    public function getData(): array
    {
        $items = parent::getData();

        return [
            'items' => $items,
        ];
    }
}
