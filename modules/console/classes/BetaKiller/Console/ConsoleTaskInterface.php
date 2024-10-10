<?php

declare(strict_types=1);

namespace BetaKiller\Console;

interface ConsoleTaskInterface
{
    /**
     * Put cli arguments with their default values here
     * Format: "optionName" => "defaultValue"
     *
     * @param \BetaKiller\Console\ConsoleOptionBuilderInterface $builder *
     *
     * @return \BetaKiller\Console\ConsoleOptionInterface[]
     */
    public function defineOptions(ConsoleOptionBuilderInterface $builder): array;

    public function run(ConsoleInputInterface $params): void;
}
