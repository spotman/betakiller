<?php
namespace BetaKiller\Filter;

abstract class Range extends AbstractFilter
{
    /**
     * @var int|null
     */
    protected $_from = null;

    /**
     * @var int|null
     */
    protected $_to = null;

    /**
     * @param array $data
     *
     * @return static
     */
    public function fromArray(array $data)
    {
        if (isset($data['from'])) {
            $this->setFrom($data['from']);
        }

        if (isset($data['to'])) {
            $this->setTo($data['to']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function asArray(): array
    {
        return [
            'from' => $this->getFrom(),
            'to'   => $this->getTo(),
        ];
    }

    public function getFrom(): ?int
    {
        return $this->_from;
    }

    public function setFrom(?int $value)
    {
        $this->_from = $value;
    }

    public function getTo(): ?int
    {
        return $this->_to;
    }

    public function setTo(?int $value)
    {
        $this->_to = $value;
    }

    /**
     * Returns TRUE if filter was previously selected (optional filtering via key)
     *
     * @param string|int|null $value
     *
     * @return bool
     */
    public function isSelected($value = null): bool
    {
        $from = $this->getFrom();
        $to   = $this->getTo();

        if ($value === null) {
            return !($to === null && $from === null);
        }

        if ($from && $value < $from) {
            return false;
        }

        if ($to && $value > $to) {
            return false;
        }

        // Value is in range
        return true;
    }

    public function setUrlQueryValues(array $values): void
    {
        if (count($values) !== 2) {
            throw new FilterException('Range filter accepts only two values: "from,to"');
        }

        list($from, $to) = $values;

        $this->setFrom((int)$from);
        $this->setTo((int)$to);
    }

    public function getUrlQueryValues(): array
    {
        return [
            $this->getFrom(),
            $this->getTo(),
        ];
    }
}
