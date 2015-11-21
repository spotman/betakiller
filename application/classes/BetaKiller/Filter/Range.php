<?php
namespace BetaKiller\Filter;

abstract class Range extends Base {

    /**
     * @var int|null
     */
    protected $_from = NULL;

    /**
     * @var int|null
     */
    protected $_to = NULL;

    /**
     * @param array $data
     * @return static
     */
    public function fromArray(array $data)
    {
        if ( isset($data['from']) )
        {
            $this->setFrom($data['from']);
        }

        if ( isset($data['to']) )
        {
            $this->setTo($data['to']);
        }

        return $this;
    }

    /**
     * @return array
     */
    public function asArray()
    {
        return array(
            'from'  =>  $this->getFrom(),
            'to'    =>  $this->getTo(),
        );
    }

    public function getFrom()
    {
        return $this->_from;
    }

    public function setFrom($value)
    {
        $this->_from = $value;
    }

    public function getTo()
    {
        return $this->_to;
    }

    public function setTo($value)
    {
        $this->_to = $value;
    }

    /**
     * Returns TRUE if filter was previously selected (optional filtering via key)
     *
     * @param string|int|null $value
     * @return bool
     */
    public function isSelected($value = null)
    {
        $from = $this->getFrom();
        $to   = $this->getTo();

        if ($value === null) {
            return !($to === null AND $from === null);
        } elseif ($from AND $value < $from) {
            return false;
        } elseif ($to AND $value > $to) {
            return false;
        } else {
            return true;
        }
    }

    public function setUrlQueryValues(array $values)
    {
        if (count($values) != 2)
            throw new Exception('Range filter accepts only two values: "from,to"');

        $from   = $values[0];
        $to     = $values[1];

        $this->setFrom($from);
        $this->setTo($to);
    }

    public function getUrlQueryValues()
    {
        return [
            $this->getFrom(),
            $this->getTo()
        ];
    }

}
