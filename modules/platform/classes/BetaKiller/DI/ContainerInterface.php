<?php
namespace BetaKiller\DI;

use DI\FactoryInterface;
use Invoker\InvokerInterface;

interface ContainerInterface extends \Psr\Container\ContainerInterface, FactoryInterface, InvokerInterface
{
}
