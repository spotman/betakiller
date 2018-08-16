<?php
declare(strict_types=1);

namespace BetaKiller\Model;

use BetaKiller\WebHook\WebHookException;

class WebHookLogRequestDataAggregator implements WebHookLogRequestDataInterface
{
    private $items = [];

    /**
     * @param array $items [optional]
     */
    public function __construct(array $items = null)
    {
        if (\is_array($items)) {
            $this->items = $items;
        }
    }

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return \BetaKiller\Model\WebHookLogRequestDataInterface
     */
    public function add(string $name, $value): WebHookLogRequestDataInterface
    {
        $name = trim($name);
        if ($name === '') {
            throw new WebHookException('Name cant not be empty');
        }
        $this->items[$name] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        return $this->items;
    }
}
