<?php
namespace BetaKiller\IFace\ModelProvider;

use BetaKiller\IFace\Exception\IFaceException;
use BetaKiller\IFace\IFaceModelInterface;
use Kohana;
use SimpleXMLElement;

class IFaceModelProviderXmlConfig implements IFaceModelProviderInterface
{
    /**
     * @var IFaceModelProviderXmlConfigModel[]
     */
    private $models;

    /**
     * @return IFaceModelInterface[]
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    public function getAll(): array
    {
        if (!$this->models) {
            $this->loadAll();
        }

        return $this->models;
    }

    /**
     * @throws \BetaKiller\IFace\Exception\IFaceException
     */
    private function loadAll(): void
    {
        $configFiles = Kohana::find_file('config', 'ifaces', 'xml');

        if (!$configFiles) {
            throw new IFaceException('Missing IFace config files');
        }

        foreach ($configFiles as $file) {
            $this->loadXmlConfig($file);
        }
    }

    private function loadXmlConfig(string $file): void
    {
        $content = file_get_contents($file);
        $sxo     = simplexml_load_string($content);
        $this->parseXmlBranch($sxo);
    }

    private function parseXmlBranch(SimpleXMLElement $branch, IFaceModelInterface $parentModel = null): void
    {
        // Parse branch childs
        foreach ($branch->children() as $childNode) {
            // Parse itself
            $childNodeModel = $this->parseXmlItem($childNode, $parentModel);

            // Store model
            $this->models[$childNodeModel->getCodename()] = $childNodeModel;

            // Iterate through childs
            $this->parseXmlBranch($childNode, $childNodeModel);
        }
    }

    private function parseXmlItem(SimpleXMLElement $branch, IFaceModelInterface $parent = null): IFaceModelInterface
    {
        $attr   = (array)$branch->attributes();
        $config = $attr['@attributes'];

        if ($parent && (!isset($config['parentCodename']) || !$config['parentCodename'])) {
            $config['parentCodename'] = $parent->getCodename();
        }

        return $this->createModelFromConfig($config);
    }

    /**
     * @param array $config
     *
     * @return IFaceModelInterface
     */
    private function createModelFromConfig(array $config): IFaceModelInterface
    {
        return IFaceModelProviderXmlConfigModel::factory($config);
    }
}
