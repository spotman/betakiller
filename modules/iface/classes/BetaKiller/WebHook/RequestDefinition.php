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
     * @param string $method
     * @param array  $fields
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
     * @param string $method
     * @param array  $fields
     */
    public function __construct(string $method, array $fields)
    {
        $this->setMethod($method);
        $this->addFields($fields);
    }

    /**
     * @param string $method HTTP method
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     * @throws \BetaKiller\WebHook\WebHookException
     */
    public function setMethod(string $method): RequestDefinitionInterface
    {
        $method = mb_strtoupper(trim($method));
        if ($method === '') {
            throw new WebHookException('Invalid method. Method is empty');
        }
        $this->method = $method;

        return $this;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @param string               $name  field name
     * @param string|int|bool|null $value field value
     *
     * @return \BetaKiller\WebHook\RequestDefinitionInterface
     * @throws \BetaKiller\WebHook\WebHookException
     */
    public function addField(string $name, $value): RequestDefinitionInterface
    {
        $name = trim($name);
        if ($name === '') {
            throw new WebHookException('Invalid name of field. Name can not me empty');
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
    public function addFields(array $fields): RequestDefinitionInterface
    {
        if (!\is_array($fields) || !$fields) {
            return $this;
        }
        foreach ($fields as $key => $value) {
            $this->addField($key, $value);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }
}
