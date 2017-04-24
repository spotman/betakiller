<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use Kohana;
use SimpleXMLElement;

class IFaceModelProviderAdmin extends IFaceModelProviderAbstract
{
    /**
     * @var IFaceModelProviderAdminModel[]
     */
    protected $models;

    public function __construct()
    {
        $config_files = Kohana::find_file('config', 'ifaces', 'xml');

        if (!$config_files) {
            throw new IFaceException('Missing admin config file');
        }

        foreach ($config_files as $file) {
            $this->loadXmlConfig($file);
        }
    }

    protected function loadXmlConfig($file)
    {
        $sxo = simplexml_load_file($file);
        $this->parseXmlBranch($sxo);
    }

    protected function parseXmlBranch(SimpleXMLElement $branch, IFaceModelInterface $parentModel = null)
    {
        // Parse branch childs
        foreach ($branch->children() as $childNode) {
            // Parse itself
            $childNodeModel = $this->parseXmlItem($childNode, $parentModel);

            // Store model
            $this->storeInCache($childNodeModel);

            // Iterate through childs
            $this->parseXmlBranch($childNode, $childNodeModel);
        }
    }

    protected function parseXmlItem(SimpleXMLElement $branch, IFaceModelInterface $parentModel = null)
    {
        $attr   = (array)$branch->attributes();
        $config = $attr['@attributes'];

        if ($parentModel && (!isset($config['parentCodename']) || !$config['parentCodename'])) {
            $config['parentCodename'] = $parentModel->getCodename();
        }

        return $this->createModelFromConfig($config);
    }

    /**
     * @param array $config
     *
     * @return IFaceModelProviderAdminModel
     */
    protected function createModelFromConfig(array $config)
    {
        return IFaceModelProviderAdminModel::factory($config, $this);
    }

    protected function storeInCache(IFaceModelInterface $model)
    {
        $codename                = $model->getCodename();
        $this->models[$codename] = $model;
    }

    protected function getFromCache($codename)
    {
        if (!$this->hasInCache($codename))
            throw new IFaceException('Unknown codename :codename', [':codename' => $codename]);

        return $this->models[$codename];
    }

    protected function hasInCache($codename)
    {
        return isset($this->models[$codename]);
    }

    /**
     * Returns list of root elements
     *
     * @return IFaceModelInterface[]
     */
    public function getRoot()
    {
        return $this->getChilds();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface
     */
    public function getDefault()
    {
        // Admin IFaces can not be marked as "default"
        return null;
    }

    /**
     * Returns iface model by codename or NULL if none was found
     *
     * @param $codename
     *
     * @return IFaceModelInterface
     */
    public function getByCodename($codename)
    {
        try {
            return $this->getFromCache($codename);
        } catch (IFaceException $e) {
            return null;
        }
    }

    /**
     * Returns list of child nodes of $parent_model (or root nodes if none provided)
     *
     * @param IFaceModelInterface $parentModel
     *
     * @return array
     */
    public function getChildren(IFaceModelInterface $parentModel)
    {
        if (!($parentModel instanceof IFaceModelProviderAdminModel)) {
            throw new IFaceException(__CLASS__.' accept only instances of :must', [
                'must:' => IFaceModelProviderAdminModel::class,
            ]);
        }

        return $this->getChilds($parentModel);
    }

    protected function getChilds(IFaceModelProviderAdminModel $parentModel = null)
    {
        $parent_codename = $parentModel ? $parentModel->getCodename() : null;

        $models = [];

        foreach ($this->models as $model) {
            if ($model->getParentCodename() !== $parent_codename) {
                continue;
            }

            $models[] = $model;
        }

        return $models;
    }
}
