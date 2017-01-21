<?php
namespace BetaKiller\DI;

use DI\FactoryInterface;
use DI\InvokerInterface;

interface ContainerInterface extends \Interop\Container\ContainerInterface, FactoryInterface, InvokerInterface {}
