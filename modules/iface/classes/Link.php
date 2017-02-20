<?php

/**
 * @todo deal with class name, namespace and maybe move it to another class
 */
class Link
{
    use BetaKiller\Utils\Instance\SingletonTrait;

    protected $links = [];

    public function add($rel, $href, array $attributes = [])
    {
        $attributes['rel'] = $rel;
        $attributes['href'] = $href;

        $this->links[] = $attributes;

        return $this;
    }

    public function canonical($href)
    {
        return $this->add('canonical', $href);
    }

    public function render()
    {
        $output = [];

        foreach ($this->links as $item)
        {
            $output[] = '<link'.HTML::attributes($item).' />';
        }

        return implode("\r\n", $output);
    }
}
