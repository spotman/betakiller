<?php

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Utils\Kohana\TreeModel;

/**
 * Class Model_IFace
 * @category   Models
 * @author     Spotman
 * @package    Betakiller
 */
class Model_IFace extends TreeModel implements IFaceModelInterface
{
    protected function _initialize()
    {
        $this->belongs_to(array(
            'layout'            =>  array(
                'model'         =>  'Layout',
                'foreign_key'   =>  'layout_id'
            ),
        ));

        $this->load_with(array(
            'layout',
        ));

        parent::_initialize();
    }

    /**
     * Returns list of child iface models
     *
     * @return IFaceModelInterface[]|\BetaKiller\Utils\Kohana\TreeModel[]
     */
    function get_children()
    {
        return parent::get_children();
    }

    /**
     * Return parent iface model or NULL
     *
     * @return IFaceModelInterface|\BetaKiller\Utils\Kohana\TreeModel
     */
    public function get_parent()
    {
        return parent::get_parent();
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function is_default()
    {
        return (bool) $this->get('is_default');
    }

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function get_codename()
    {
        return $this->get('codename');
    }

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function get_label()
    {
        return $this->get('label');
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function get_title()
    {
        return $this->get('title');
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function get_description()
    {
        return $this->get('description');
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function get_uri()
    {
        return $this->get('uri');
    }

    /**
     * Returns layout model
     *
     * @return Model_Layout
     */
    public function get_layout()
    {
        return $this->get('layout');
    }

    /**
     * Returns layout codename
     *
     * @return string
     */
    public function get_layout_codename()
    {
        $layout = $this->get_layout();

        if ( ! $layout->loaded() )
        {
            $layout = $layout->get_default();
        }

        return $layout->get_codename();
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function has_dynamic_url()
    {
        return (bool) $this->get('is_dynamic');
    }

    /**
     * Returns TRUE if iface has multi-level tree-behavior url mapping
     *
     * @return bool
     */
    public function has_tree_behaviour()
    {
        return (bool) $this->get('is_tree');
    }

    /**
     * @return bool
     */
    public function hide_in_site_map()
    {
        return (bool) $this->get('hide_in_site_map');
    }
}
