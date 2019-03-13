<?php
namespace BetaKiller\Twig;

use BetaKiller\View\ViewInterface;

final class TwigView implements ViewInterface
{
    /**
     * Twig environment
     *
     * @var \Twig\Environment
     */
    private $env;

    /**
     * @var string
     */
    private $file;

    /**
     * @var array
     */
    private $data;

    /**
     * TwigView constructor.
     *
     * @param \Twig\Environment $env
     * @param string            $file
     */
    public function __construct(\Twig\Environment $env, string $file)
    {
        $this->env  = $env;
        $this->file = $file;
    }

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
    public function set(string $key, $value): ViewInterface
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Render Twig template as string
     *
     * @return  string  Rendered Twig template
     * @throws \Twig\Error\LoaderError
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\SyntaxError
     */
    public function render(): string
    {
        return $this->env->render($this->file, $this->data);
    }
}
