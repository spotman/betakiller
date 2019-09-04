<?php
declare(strict_types=1);

namespace BetaKiller\Api;

use BetaKiller\Model\LanguageInterface;
use BetaKiller\Model\UserInterface;
use Spotman\Api\ApiLanguageDetectorInterface;
use Spotman\Api\ApiMethodInterface;
use Spotman\Api\ApiMethodWithLangDefinitionInterface;
use Spotman\Defence\ArgumentsInterface;

class ApiLanguageDetector implements ApiLanguageDetectorInterface
{
    /**
     * @param \Spotman\Api\ApiMethodInterface     $instance
     * @param \Spotman\Defence\ArgumentsInterface $arguments
     * @param \BetaKiller\Model\UserInterface     $user
     *
     * @return \BetaKiller\Model\LanguageInterface
     */
    public function detect(
        ApiMethodInterface $instance,
        ArgumentsInterface $arguments,
        UserInterface $user
    ): LanguageInterface {
        $lang = $instance instanceof ApiMethodWithLangDefinitionInterface
            ? $instance->detectLanguage($arguments)
            : null;

        if (!$lang) {
            $lang = $user->getLanguage();
        }

        return $lang;
    }
}
