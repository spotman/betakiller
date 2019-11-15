<?php
declare(strict_types=1);

namespace BetaKiller\Task\Test;

use BetaKiller\Exception;
use BetaKiller\Task\AbstractTask;
use BetaKiller\Url\ActionModelInterface;
use BetaKiller\Url\DummyModelInterface;
use BetaKiller\Url\IFaceModelInterface;
use BetaKiller\Url\UrlElementInterface;
use BetaKiller\Url\UrlElementTreeInterface;
use PhpSchool\CliMenu\Action\ExitAction;
use PhpSchool\CliMenu\Builder\CliMenuBuilder;
use PhpSchool\CliMenu\CliMenu;
use PhpSchool\CliMenu\MenuItem\LineBreakItem;
use PhpSchool\CliMenu\MenuItem\MenuItemInterface;
use PhpSchool\CliMenu\MenuItem\SelectableItem;
use PhpSchool\CliMenu\MenuItem\StaticItem;

final class CheckElementTree extends AbstractTask
{
    /**
     * @var \BetaKiller\Url\UrlElementTreeInterface
     */
    private $tree;

    /**
     * @var UrlElementInterface|null
     */
    private $currentElement;

    /**
     * CheckElementTree constructor.
     *
     * @param \BetaKiller\Url\UrlElementTreeInterface $tree
     */
    public function __construct(UrlElementTreeInterface $tree)
    {
        parent::__construct();

        $this->tree = $tree;
    }

    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @return array
     */
    public function defineOptions(): array
    {
        return [];
    }

    public function run(): void
    {
        // Validate first
        $this->tree->validate();

        /** @see https://stackoverflow.com/a/2204201 */
        $menu = (new CliMenuBuilder)
            ->enableAutoShortcuts()
            ->setWidth((int)exec('tput cols') - 4)
            ->build();

        $this->drawCurrentElement($menu);

        $menu->open();
    }

    private function onSelect(CliMenu $menu): void
    {
        $currentLabel    = $menu->getSelectedItem()->getText();
        $currentCodename = trim(explode(']', $currentLabel, 2)[1]);

        $this->currentElement = $this->tree->getByCodename($currentCodename);

        $this->drawCurrentElement($menu);

        $menu->redraw();
    }

    private function drawCurrentElement(CliMenu $menu): void
    {
        // Cleanup
        foreach ($menu->getItems() as $item) {
            $menu->removeItem($item);
        }

        // Breadcrumbs
        $menu->setTitle($this->getBreadcrumbs());

        // Link to parent and root
        if ($this->currentElement) {
            $menu->addItem(new SelectableItem('Root', function (CliMenu $menu) {
                $this->currentElement = null;

                $this->drawCurrentElement($menu);
                $menu->redraw();
            }));

            $menu->addItem(new SelectableItem('Return to parent', function (CliMenu $menu) {
                $this->currentElement = $this->tree->getParent($this->currentElement);

                $this->drawCurrentElement($menu);
                $menu->redraw();
            }));

            $menu->addItem(new LineBreakItem());
        }

        // Listing of childs
        $children = $this->currentElement
            ? $this->tree->getChildren($this->currentElement)
            : $this->tree->getRoot();

        if ($children) {
            foreach ($children as $child) {
                $menu->addItem($this->createMenuItem($child));
            }

            $menu->addItem(new LineBreakItem());
        }

        // Search
        $menu->addItem(new SelectableItem('Search', function (CliMenu $menu) {
            $result = $menu->askText()
                ->setPromptText('Enter codename')
//                ->setPlaceholderText('Jane Doe')
                ->setValidationFailedText('Please enter codename')
                ->ask();

            $codename = $result->fetch();

            try {
                $this->currentElement = $this->tree->getByCodename($codename);
                $this->drawCurrentElement($menu);
                $menu->redraw();
            } catch (\Throwable $e) {
                $menu->confirm($e->getMessage())->display('OK');
            }
        }));

        // Exit
        $menu->addItem(new SelectableItem('Exit', new ExitAction));

        // Add static info
        if ($this->currentElement) {
            $menu->addItem(new LineBreakItem('-'));

            $info = print_r(json_encode($this->currentElement, \JSON_PRETTY_PRINT), true);

            $menu->addItem(new StaticItem($info));
        }
    }

    private function createMenuItem(UrlElementInterface $element): MenuItemInterface
    {
        $type = $this->getType($element);

        $label = sprintf('%s %s', str_pad('['.$type.']', 8), $element->getCodename());

        return new SelectableItem($label, function (CliMenu $menu) {
            $this->onSelect($menu);
        });
    }

    private function getType(UrlElementInterface $element): string
    {
        switch (true) {
            case $element instanceof IFaceModelInterface:
                return 'IFace';

            case $element instanceof ActionModelInterface:
                return 'Action';

            case $element instanceof DummyModelInterface:
                return 'Dummy';

            default:
                throw new Exception('Unknown UrlElement type in ":name"', [
                    ':name' => $element->getCodename(),
                ]);
        }
    }

    private function getBreadcrumbs(): string
    {
        $items = [];

        if ($this->currentElement) {
            foreach ($this->tree->getBranchIterator($this->currentElement) as $element) {
                $items[] = $element->getUri();
            }
        }

        return '/'.implode('/', $items);
    }
}
