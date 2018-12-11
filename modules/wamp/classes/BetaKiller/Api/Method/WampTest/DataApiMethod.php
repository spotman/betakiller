<?php
declare(strict_types=1);

namespace BetaKiller\Api\Method\WampTest;

use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiMethodException;
use Spotman\Api\ApiMethodResponse;
use Spotman\Api\Method\AbstractApiMethod;
use Spotman\Defence\ArgumentsInterface;
use Spotman\Defence\DefinitionBuilderInterface;

class DataApiMethod extends AbstractApiMethod
{
    public const ARG_CASE = 'case';

    public const CASE_TRUE         = 'true';
    public const CASE_FALSE        = 'false';
    public const CASE_ZERO         = 'zero';
    public const CASE_INT          = 'int';
    public const CASE_ARRAY        = 'array';
    public const CASE_EMPTY_ARRAY  = 'empty_array';
    public const CASE_STRING       = 'string';
    public const CASE_EMPTY_STRING = 'empty_string';

    public const AVAILABLE_CASES = [
        self::CASE_TRUE,
        self::CASE_FALSE,
        self::CASE_ZERO,
        self::CASE_INT,
        self::CASE_ARRAY,
        self::CASE_EMPTY_ARRAY,
        self::CASE_STRING,
        self::CASE_EMPTY_STRING,
    ];

    /**
     * @return \Spotman\Defence\DefinitionBuilderInterface
     */
    public function getArgumentsDefinition(): DefinitionBuilderInterface
    {
        return $this->definition()
            ->string(self::ARG_CASE)
            ->whitelist(self::AVAILABLE_CASES);
    }

    /**
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \Spotman\Api\ApiMethodResponse|null
     */
    public function execute(ArgumentsInterface $arguments, UserInterface $user): ?ApiMethodResponse
    {
        $case = $arguments->getString(self::ARG_CASE);

        return $this->response(self::makeTestResponse($case));
    }

    public static function makeTestResponse(string $case)
    {
        switch ($case) {
            case self::CASE_TRUE:
                return true;

            case self::CASE_FALSE:
                return false;

            case self::CASE_ZERO:
                return 0;

            case self::CASE_INT:
                return 100;

            case self::CASE_ARRAY:
                return [
                    'asd',
                    'qwe',
                    123,
                    456,
                ];

            case self::CASE_EMPTY_ARRAY:
                return [];

            case self::CASE_STRING:
                return 'asd';

            case self::CASE_EMPTY_STRING:
                return '';

            default:
                throw new ApiMethodException('Unknown case ":value"', [':value' => $case]);
        }
    }
}
