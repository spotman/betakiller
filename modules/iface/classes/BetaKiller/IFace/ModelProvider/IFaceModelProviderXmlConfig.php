<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use BetaKiller\Model\DispatchableEntityInterface;
use Kohana;
use SimpleXMLElement;

class IFaceModelProviderXmlConfig extends IFaceModelProviderAbstract
{
    /**
     * @var IFaceModelProviderXmlConfigModel[]
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

    protected function loadXmlConfig(string $file): void
    {
        $content = file_get_contents($file);
        $sxo = simplexml_load_string($content);
        $this->parseXmlBranch($sxo);
    }

    protected function parseXmlBranch(SimpleXMLElement $branch, IFaceModelInterface $parentModel = null): void
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

    protected function parseXmlItem(SimpleXMLElement $branch, IFaceModelInterface $parentModel = null): IFaceModelProviderXmlConfigModel
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
     * @return IFaceModelProviderXmlConfigModel
     */
    protected function createModelFromConfig(array $config): IFaceModelProviderXmlConfigModel
    {
        return IFaceModelProviderXmlConfigModel::factory($config, $this);
    }

    protected function storeInCache(IFaceModelInterface $model)
    {
        $codename                = $model->getCodename();
        $this->models[$codename] = $model;
    }

    protected function getFromCache($codename)
    {
        if (!$this->hasInCache($codename)) {
            throw new IFaceException('Unknown codename :codename', [':codename' => $codename]);
        }

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
    public function getRoot(): array
    {
        return $this->getChilds();
    }

    /**
     * Returns default iface model in current provider
     *
     * @return IFaceModelInterface
     */
    public function getDefault(): ?IFaceModelInterface
    {
        // Admin IFaces can not be marked as "default"
        return null;
    }

    /**
     * Returns iface model by codename or throws exception if none was found
     *
     * @param string $codename
     *
     * @return IFaceModelInterface
     */
    public function getByCodename(string $codename): IFaceModelInterface
    {
        return $this->getFromCache($codename);
    }

    /**
     * Returns list of child nodes of $parent_model (or root nodes if none provided)
     *
     * @param IFaceModelInterface $parentModel
     *
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getChildren(IFaceModelInterface $parentModel): array
    {
        if (!($parentModel instanceof IFaceModelProviderXmlConfigModel)) {
            throw new IFaceException(__CLASS__.' accept only instances of :must', [
                'must:' => IFaceModelProviderXmlConfigModel::class,
            ]);
        }

        return $this->getChilds($parentModel);
    }

    /**
     * @param \BetaKiller\IFace\ModelProvider\IFaceModelProviderXmlConfigModel|null $parentModel
     *
     * @return IFaceModelInterface[]
     */
    protected function getChilds(IFaceModelProviderXmlConfigModel $parentModel = null): array
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

    /**
     * Search for IFace linked to provided entity, entity action and zone
     *
     * @param \BetaKiller\Model\DispatchableEntityInterface $entity
     * @param string                                        $entityAction
     * @param string                                        $zone
     *
     * @return IFaceModelInterface|null
     */
    public function getByEntityActionAndZone(DispatchableEntityInterface $entity, string $entityAction, string $zone): ?IFaceModelInterface
    {
        foreach ($this->models as $model) {
            if ($model->getEntityModelName() !== $entity->getModelName()) {
                continue;
            }

            if ($model->getEntityActionName() !== $entityAction) {
                continue;
            }

            if ($model->getZoneName() !== $zone) {
                continue;
            }

            return $model;
        }

        return null;
    }

    /**
     * @param string $action
     * @param string $zone
     *
     * @return IFaceModelInterface[]
     */
    public function getByActionAndZone(string $action, string $zone): array
    {
        $output = [];

        foreach ($this->models as $model) {
            if ($model->getEntityActionName() !== $action) {
                continue;
            }

            if ($model->getZoneName() !== $zone) {
                continue;
            }

            $output[] = $model;
        }

        return $output;
    }
}
