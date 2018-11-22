<?php
declare(strict_types=1);

namespace BetaKiller\Action;

use BetaKiller\Exception;
use BetaKiller\Url\AbstractUrlElement;
use Spotman\Defence\ArgumentsFacade;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilder;
use Spotman\Defence\DefinitionBuilderInterface;

abstract class AbstractAction extends AbstractUrlElement implements ActionInterface
{
    /**
     * @var \Spotman\Defence\ArgumentsFacade
     */
    private $argumentsFacade;

    /**
     * @param \Spotman\Defence\ArgumentsFacade $argumentsFacade
     */
    public function __construct(ArgumentsFacade $argumentsFacade)
    {
        $this->argumentsFacade = $argumentsFacade;
    }

    /**
     * @return string
     */
    public static function getSuffix(): string
    {
        return self::SUFFIX;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    abstract protected function getArgumentsDefinition(): DefinitionBuilderInterface;

    /**
     * @param array $requestArguments
     *
     * @return \Spotman\Defence\ArgumentsInterface
     */
    protected function validateArguments(array $requestArguments): ArgumentsInterface
    {
        $definition = $this->getArgumentsDefinition();

        try {
            $arguments = $this->argumentsFacade->prepareArguments($requestArguments, $definition);
        } catch (\InvalidArgumentException $e) {
            throw new Exception('Validation error in action ":action": :error', [
                ':error'  => $e->getMessage(),
                ':action' => get_class($this),
            ], 0, $e);
        }

        return $arguments;
    }

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    protected function definition(): DefinitionBuilderInterface
    {
        return new DefinitionBuilder;
    }
}
