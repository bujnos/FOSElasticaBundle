<?php

namespace FOQ\ElasticaBundle;

/**
 * Deletes and recreates indexes
 */
class Resetter
{
    protected $indexConfigsByName;

    /**
     * Constructor.
     *
     * @param array $indexConfigsByName
     */
    public function __construct(array $indexConfigsByName)
    {
        $this->indexConfigsByName = $indexConfigsByName;
    }

    /**
     * Deletes and recreates all indexes
     */
    public function resetAllIndexes()
    {
        foreach ($this->indexConfigsByName as $indexConfig) {
            $indexConfig['index']->create($indexConfig['config'], true);
        }
    }

    /**
     * Deletes and recreates the named index
     *
     * @param string $indexName
     * @throws InvalidArgumentException if no index exists for the given name
     */
    public function resetIndex($indexName)
    {
        $indexConfig = $this->getIndexConfig($indexName);
        $indexConfig['index']->create($indexConfig['config'], true);
    }

    /**
     * Deletes and recreates a mapping type for the named index
     *
     * @param string $indexName
     * @param string $typeName
     * @throws InvalidArgumentException if no index or type mapping exists for the given names
     */
    public function resetIndexType($indexName, $typeName)
    {
        $indexConfig = $this->getIndexConfig($indexName);

        if (!isset($indexConfig['config']['mappings'][$typeName]['properties'])) {
            throw new \InvalidArgumentException(sprintf('The mapping for index "%s" and type "%s" does not exist.', $indexName, $typeName));
        }

        $type = $indexConfig['index']->getType($typeName);
        $type->delete();
        $type->setMapping($indexConfig['config']['mappings'][$typeName]['properties']);
    }

    /**
     * Gets an index config by its name
     *
     * @param string $index Index name
     * @return array
     * @throws InvalidArgumentException if no index config exists for the given name
     */
    protected function getIndexConfig($indexName)
    {
        if (!isset($this->indexConfigsByName[$indexName])) {
            throw new \InvalidArgumentException(sprintf('The configuration for index "%s" does not exist.', $indexName));
        }

        return $this->indexConfigsByName[$indexName];
    }
}
