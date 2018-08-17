<?php
declare(strict_types=1);

namespace BetaKiller\WebHook;

interface RequestDefinitionInterface
{
    /**
     * @param string $method HTTP method
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     */
    public function setMethod(string $method): RequestDefinitionInterface;

    /**
     * @return string
     */
    public function getMethod(): string;

    /**
     * @param string               $name  field name
     * @param string|int|bool|null $value field value
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     */
    public function addField(string $name, $value): RequestDefinitionInterface;

    /**
     * @param array $fields [[string name, string value],..]
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     */
    public function addFields(array $fields): RequestDefinitionInterface;

    /**
     * @return array
     */
    public function getFields(): array;
}
