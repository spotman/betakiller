<?php
namespace BetaKiller\Widget\Admin\Menu;

use BetaKiller\Widget\MenuWidget;

class MainWidget extends MenuWidget
{
    public function getData(): array
    {
        $items = parent::getData();
        $data = [
            'items' => [
                $items,
            ],
        ];

        return $data;
    }
}
