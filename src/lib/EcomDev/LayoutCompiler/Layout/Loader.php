<?php

use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;
use EcomDev_LayoutCompiler_Contract_IndexInterface as IndexInterface;

class EcomDev_LayoutCompiler_Layout_Loader
    implements EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface
{
    /**
     * Loaded handles hash of flags
     * 
     * @var array
     */
    private $loaded = array();

    /**
     * List of items to be loaded
     *
     * @var ItemInterface
     */
    private $items = array();

    /**
     * Current processor
     *
     * @var ProcessorInterface
     */
    private $currentProcessor;
    
    /**
     * Loads handle items
     *
     * @param string|string[] $handleName
     * @param IndexInterface $index
     * @return ItemInterface[]
     */
    public function load($handleName, IndexInterface $index)
    {
        $files = $index->getHandleIncludes($handleName);

        $this->items = array();
        foreach ($files as $file) {
            @include $file;
        }

        $this->loaded[$handleName] = true;
        $items = $this->items;
        $this->items = array();
        return $items;
    }

    /**
     * Check if handle has been already loaded
     *
     * @param string $handle
     * @return bool
     */
    public function isLoaded($handle)
    {
        return isset($this->loaded[$handle]);
    }

    /**
     * Loads a handles into layout processor
     *
     * @param string|string[] $handleName
     * @param ProcessorInterface $processor
     * @param IndexInterface $index
     * @return $this
     */
    public function loadIntoProcessor($handleName, ProcessorInterface $processor, IndexInterface $index)
    {
        if ($this->isLoaded($handleName)) {
            return $this;
        }

        $files = $index->getHandleIncludes($handleName);

        $previousProcessor = $this->currentProcessor;
        $this->currentProcessor = $processor;
        foreach ($files as $file) {
            @include $file;
        }

        $this->currentProcessor = $previousProcessor;
        $this->loaded[$handleName] = true;
        return $this;
    }

    /**
     * Resets state of loader
     *
     * @return $this
     */
    public function reset()
    {
        $this->loaded = array();
        return $this;
    }

    /**
     * And item adder
     *
     * @param ItemInterface $item
     * @return $this
     */
    public function addItem(ItemInterface $item)
    {
        if ($this->currentProcessor) {
            $this->currentProcessor->addItem($item);
            return $this;
        }

        $this->items[] = $item;
        return $this;
    }

    /**
     * And item relation
     *
     * @param ItemInterface $item
     * @param string $relatedBlockIdentifier
     * @return $this
     */
    public function addItemRelation(ItemInterface $item, $relatedBlockIdentifier)
    {
        if ($this->currentProcessor) {
            $this->currentProcessor->addItemRelation($item, $relatedBlockIdentifier);
        }
        
        return $this;
    }
}
