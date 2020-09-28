<?php
declare(strict_types=1);

namespace BetaKiller\Model;

interface CronCommandInterface extends AbstractEntityInterface
{
    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronCommandInterface
     */
    public function setName(string $value): CronCommandInterface;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @param array $value
     *
     * @return \BetaKiller\Model\CronCommandInterface
     */
    public function setParams(array $value): CronCommandInterface;

    /**
     * @return array
     */
    public function getParams(): array;

    /**
     * @param string $value
     *
     * @return \BetaKiller\Model\CronCommandInterface
     */
    public function setCmd(string $value): CronCommandInterface;

    /**
     * @return string
     */
    public function getCmd(): string;
}
