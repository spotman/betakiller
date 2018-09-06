<?php
declare(strict_types=1);

namespace BetaKiller\Helper;

use BetaKiller\Exception\ValidationException;

class ExceptionTranslator
{
    public function fromOrmValidationException(\ORM_Validation_Exception $e): ValidationException
    {
        $container = new ValidationException($e);

        foreach ($e->errors('orm') as $name => $message) {
            $container->add($name, $message);
        }

        return $container;
    }
}
