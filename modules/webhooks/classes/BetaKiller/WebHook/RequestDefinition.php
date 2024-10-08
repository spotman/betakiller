<?php
declare(strict_types=1);

namespace BetaKiller\WebHook;

class RequestDefinition implements RequestDefinitionInterface
{
    /**
     * @var string HTTP method
     */
    private $method = '';

    /**
     * @var array fields [[string name, string value],..]
     */
    private $fields = [];

    /**
     * @param string $method HTTP method
     * @param array  $fields [string "name" => string "value", ...]
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     */
    public static function create(string $method, array $fields): RequestDefinitionInterface
    {
        return new self($method, $fields);
    }

    /**
     * RequestDefinition constructor.
     *
     * @param string $method HTTP method
     * @param array  $fields [string "name" => string "value", ...]
     */
    public function __construct(string $method, array $fields)
    {
        $this->setMethod($method);
        $this->addFields($fields);
    }

    /**
     * Returns HTTP method
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array [string "name" => string "value", ...]
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @param string $method HTTP method
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     * @throws \BetaKiller\WebHook\WebHookException
     */
    private function setMethod(string $method): RequestDefinitionInterface
    {
        $method = mb_strtoupper(trim($method));
        if ($method === '') {
            throw new WebHookException('Invalid method. Method is empty');
        }
        $this->method = $method;

        return $this;
    }

    /**
     * @param string               $name  field name
     * @param string|int|bool|null $value field value
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     * @throws \BetaKiller\WebHook\WebHookException
     */
    private function addField(string $name, $value): RequestDefinitionInterface
    {
        $name = trim($name);
        if (!$name) {
            throw new WebHookException('Invalid name of field. Name can not be empty');
        }

        if ($value === null || \is_bool($value)) {
            $value = (int)$value;
        }
        if (!\is_string($value) && !\is_numeric($value)) {
            $value = '';
        }

        $this->fields[$name] = $value;

        return $this;
    }

    /**
     * @param array $fields [[string name, string value],..]
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     */
    private function addFields(array $fields): RequestDefinitionInterface
    {
        foreach ($fields as $key => $value) {
            $this->addField($key, $value);
        }

        return $this;
    }
}
