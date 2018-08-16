<?php
namespace BetaKiller\Model;

interface WebHookLogRequestDataInterface
{
    /**
     * @param string $name
     * @param mixed $value
     *
     * @return \BetaKiller\Model\WebHookLogRequestDataInterface
     */
    public function add(string $name, $value): WebHookLogRequestDataInterface;

    /**
     * @return array
     */
    public function get(): array;
}
