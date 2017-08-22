<?php
namespace BetaKiller\Filter\Model;

class ValuesGroup
{
    /**
     * @var string
     */
    protected $_codename;

    /**
     * @var string
     */
    protected $_label;

    /**
     * @var Value[]
     */
    protected $_values;

    /**
     * @param string $_label
     * @param array  $_values
     *
     * @return static
     */
    public static function factory(string $_label, array $_values)
    {
        return new static($_label, $_values);
    }

    /**
     * @param string  $_label
     * @param Value[] $_values
     */
    public function __construct(string $_label, array $_values)
    {
        $this->_label  = $_label;
        $this->_values = $_values;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->_label;
    }

    /**
     * @return Value[]
     */
    public function getValues(): array
    {
        return $this->_values;
    }

    /**
     * @param \BetaKiller\Filter\Model\Value $value
     */
    public function addValue(Value $value): void
    {
        $this->_values[] = $value;
    }

    /**
     * @param string|null $nsSeparator
     * @param bool|null   $selectedOnly
     *
     * @return string[]
     */
    public function asValuesArray(?string $nsSeparator = null, ?bool $selectedOnly = null): array
    {
        $nsSeparator  = $nsSeparator ?? '-';
        $selectedOnly = $selectedOnly ?? false;
        $output       = [];

        foreach ($this->getValues() as $value) {
            if ($selectedOnly && !$value->isSelected()) {
                continue;
            }

            $keyPrefix = $value->getKeyNamespace();
            $key       = $keyPrefix
                ? $keyPrefix.$nsSeparator.$value->getKey()
                : $value->getKey();

            $output[$key] = $value->getLabel();
        }

        return $output;
    }

    /**
     * @return string
     */
    public function getCodename(): string
    {
        return $this->_codename;
    }

    /**
     * @param string $codename
     */
    public function setCodename(string $codename): void
    {
        $this->_codename = $codename;
    }
}
