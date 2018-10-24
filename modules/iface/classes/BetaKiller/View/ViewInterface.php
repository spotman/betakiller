<?php
namespace BetaKiller\View;

interface ViewInterface
{
    /**
     * Assigns a variable by name. Assigned values will be available as a
     * variable within the view file:
     *
     *     // This value can be accessed as $foo within the view
     *     $view->set('foo', 'my value');
     *
     * You can also use an array to set several values at once:
     *
     *     // Create the values $food and $beverage in the view
     *     $view->set(array('food' => 'bread', 'beverage' => 'water'));
     *
     * @param   string $key   variable name or an array of variables
     * @param   mixed  $value value
     *
     * @return  \BetaKiller\View\ViewInterface
     */
    public function set(string $key, $value): ViewInterface;

    /**
     * Renders the view object to a string. Global and local data are merged
     * and extracted to create local variables within the view file.
     *
     *     $output = $view->render();
     *
     * @return  string
     */
    public function render(): string;
}
