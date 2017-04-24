<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentInterface;
use BetaKiller\Utils\Kohana\TreeModelSingleParentTrait;
use HTTP_Exception_501;

class IFaceModelProviderAdminModel implements IFaceModelInterface
{
    use TreeModelSingleParentTrait;

    /**
     * @var IFaceModelProviderAdmin
     */
    protected $provider;

    /**
     * @var string
     */
    protected $codename;

    /**
     * @var string
     */
    protected $parentCodename;

    /**
     * @var string
     */
    protected $uri;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $title;

    /**
     * Admin IFaces have "admin" layout by default
     *
     * @var string
     */
    protected $layoutCodename = 'admin';

    /**
     * @var bool
     */
    protected $hasDynamicUrl;

    /**
     * @var bool
     */
    protected $hasTreeBehaviour;

    /**
     * @var bool
     */
    protected $hideInSiteMap;

    public static function factory($data, IFaceModelProviderAdmin $provider)
    {
        /** @var self $instance */
        $instance = new static;
        $instance->fromArray($data);
        $instance->setProvider($provider);

        return $instance;
    }

    /**
     * Returns TRUE if iface is marked as "default"
     *
     * @return bool
     */
    public function isDefault()
    {
        // Admin IFaces can not have "is_default" marker
        return false;
    }

    /**
     * @return int
     * @throws HTTP_Exception_501
     */
    public function get_id()
    {
        throw new HTTP_Exception_501('Admin IFace model have no ID');
    }

    /**
     * Returns iface url part
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Return parent iface model or NULL
     *
     * @return IFaceModelInterface|null
     */
    public function getParent()
    {
        if (!$this->parentCodename) {
            return null;
        }

        return $this->getProvider()->getByCodename($this->parentCodename);
    }

    /**
     * Returns codename of parent IFace or NULL
     *
     * @return string
     */
    public function getParentCodename()
    {
        return $this->parentCodename;
    }

    /**
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getRoot()
    {
        return $this->getProvider()->getRoot();
    }

    /**
     * Returns list of child iface models
     *
     * @return \BetaKiller\IFace\IFaceModelInterface[]
     */
    public function getChildren()
    {
        return $this->getProvider()->getChildren($this);
    }

    /**
     * @param string|null $column
     *
     * @return int[]
     * @throws HTTP_Exception_501
     */
    public function getAllChildren($column = null)
    {
        throw new HTTP_Exception_501(':method not implemented yet', [':method' => __METHOD__]);
    }

    /**
     * @param \BetaKiller\Utils\Kohana\TreeModelSingleParentInterface|null $parent
     *
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function setParent(TreeModelSingleParentInterface $parent = null)
    {
        throw new HTTP_Exception_501('Admin model can not change parent');
    }

    /**
     * Sets title for using in <title> tag
     *
     * @param string $value
     *
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function setTitle($value)
    {
        throw new HTTP_Exception_501('Admin model can not change title');
    }

    /**
     * Sets description for using in <meta> tag
     *
     * @param string $value
     *
     * @return $this
     * @throws HTTP_Exception_501
     */
    public function setDescription($value)
    {
        throw new HTTP_Exception_501('Admin model can not change description');
    }

    /**
     * Returns iface codename
     *
     * @return string
     */
    public function getCodename()
    {
        return $this->codename;
    }

    /**
     * Returns label for using in breadcrumbs and etc
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Returns title for using in page <title> tag
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns description for using in <meta> tag
     *
     * @return string
     */
    public function getDescription()
    {
        // Admin IFace does not need description
        return null;
    }

    /**
     * Returns array representation of the model data
     *
     * @return array
     */
    public function asArray()
    {
        return [
            'codename'       => $this->getCodename(),
            'uri'            => $this->getUri(),
            'parentCodename' => $this->getParentCodename(),
            'label'          => $this->getLabel(),
            'title'          => $this->getTitle(),
            'hasDynamicUrl'  => $this->hasDynamicUrl(),
            'hideInSiteMap'  => $this->hideInSiteMap(),
            'layoutCodename' => $this->getLayoutCodename(),
        ];
    }

    /**
     * Returns layout codename
     *
     * @return string
     */
    public function getLayoutCodename()
    {
        return $this->layoutCodename;
    }

    public function fromArray(array $data)
    {
        $this->codename = $data['codename'];
        $this->uri      = $data['uri'];

        $this->label = isset($data['label']) ? $data['label'] : null;
        $this->title = isset($data['title']) ? $data['title'] : null;

        if (isset($data['parentCodename'])) {
            $this->parentCodename = $data['parentCodename'];
        }

        if (isset($data['hasDynamicUrl'])) {
            $this->hasDynamicUrl = true;
        }

        if (isset($data['hasTreeBehaviour'])) {
            $this->hasTreeBehaviour = true;
        }

        if (isset($data['hideInSiteMap'])) {
            $this->hideInSiteMap = true;
        }

        if (isset($data['layoutCodename'])) {
            $this->layoutCodename = $data['layoutCodename'];
        }
    }

    /**
     * Returns TRUE if iface provides dynamic url mapping
     *
     * @return bool
     */
    public function hasDynamicUrl()
    {
        return (bool)$this->hasDynamicUrl;
    }

    /**
     * Returns TRUE if iface provides tree-like url mapping
     *
     * @return bool
     */
    public function hasTreeBehaviour()
    {
        return (bool)$this->hasTreeBehaviour;
    }

    /**
     * @return bool
     */
    public function hideInSiteMap()
    {
        return (bool)$this->hideInSiteMap;
    }

    /**
     * @return IFaceModelProviderAdmin
     */
    protected function getProvider()
    {
        return $this->provider;
    }

    public function setProvider(IFaceModelProviderAdmin $provider)
    {
        $this->provider = $provider;

        return $this;
    }
}
