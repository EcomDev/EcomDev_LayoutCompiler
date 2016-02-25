<?php

use EcomDev_LayoutCompiler_Contract_Layout_ProcessorInterface as ProcessorInterface;
use EcomDev_LayoutCompiler_Contract_Layout_LoaderInterface as LoaderInterface;
use EcomDev_LayoutCompiler_Contract_CompilerInterface as CompilerInterface;
use EcomDev_LayoutCompiler_Contract_Layout_UpdateInterface as UpdateInterface;
use EcomDev_LayoutCompiler_Contract_Layout_ItemInterface as ItemInterface;

class EcomDev_LayoutCompiler_Model_Layout
    extends Mage_Core_Model_Layout
    implements EcomDev_LayoutCompiler_Contract_LayoutInterface
{
    use EcomDev_LayoutCompiler_PathAwareTrait;
    use EcomDev_LayoutCompiler_FactoryAwareTrait;

    /**
     * Instance of compiler
     *
     * @var CompilerInterface
     */
    private $compiler;

    /**
     * Instance of processor
     *
     * @var ProcessorInterface
     */
    private $processor;

    /**
     * Instance of loader
     *
     * @var LoaderInterface
     */
    private $loader;

    /**
     * Instance of layout update
     *
     * @var UpdateInterface
     */
    private $update;

    /**
     * Returns an instance of layout wrapper
     *
     * Should be created via factory
     *
     * @return ProcessorInterface
     */
    public function getProcessor()
    {
        if ($this->processor === null) {
            $this->processor = $this->getObjectFactory()
                ->createInstance('layout_processor');
        }

        return $this->processor;
    }

    /**
     * Returns an instance of loader object
     *
     * Should be created via factory
     *
     * @return LoaderInterface
     */
    public function getLoader()
    {
        if ($this->loader === null) {
            $this->loader = $this->getObjectFactory()
                ->createInstance('layout_loader');
        }

        return $this->loader;
    }

    /**
     * Returns an instance of loader object
     *
     * Should be created via factory
     *
     * @return CompilerInterface
     */
    public function getCompiler()
    {
        if ($this->compiler === null) {
            $this->compiler = $this->getObjectFactory()
                ->createInstance('compiler');
        }

        return $this->compiler;
    }

    /**
     * Returns update interface instance
     *
     * @return UpdateInterface
     */
    public function getUpdate()
    {
        if ($this->update === null) {
            $this->update = $this->getObjectFactory()
                ->createInstance('layout_update');
        }

        return $this->update;
    }

    /**
     * Finds a block by its identifier
     *
     * @param string $identifier
     * @return Mage_Core_Block_Abstract|bool
     */
    public function findBlockById($identifier)
    {
        return $this->getBlock($identifier);
    }

    /**
     * Makes it possible to specify special options, after the block is created
     *
     * @param string $block
     * @param array $attributes
     * @return Mage_Core_Block_Abstract
     */
    protected function _getBlockInstance($block, array $attributes = array())
    {
        $block = parent::_getBlockInstance($block, $attributes);

        if ($block && isset($attributes['_ecomdev_system_option'])) {
            if (isset($attributes['_ecomdev_system_option']['is_anonymous'])) {
                $block->setIsAnonymous($attributes['_ecomdev_system_option']['is_anonymous']);

                if (isset($attributes['_ecomdev_system_option']['anon_suffix'])) {
                    $block->setAnonSuffix($attributes['_ecomdev_system_option']['anon_suffix']);
                }
            }
        }

        return $block;
    }


    /**
     * Returns an instance of a block
     *
     * @param string $classAlias block class alias
     * @param string $identifier block identifier
     * @param string[] $options block options (before, after, template, etc)
     * @return Mage_Core_Block_Abstract
     */
    public function newBlock(
        $classAlias, $identifier, array $options = array()
    )
    {
        $attributes = array();

        if (isset($options['_ecomdev_system_option'])) {
            $attributes['_ecomdev_system_option'] = $options['_ecomdev_system_option'];
        }

        $block = $this->createBlock($classAlias, $identifier, $attributes);

        if ($block !== false) {
            if (!empty($options['parent'])
                && $parentBlock = $this->findBlockById($options['parent'])) {
                $alias = '';
                if (!empty($options['as'])) {
                    $alias = $options['as'];
                }
                if (isset($options['before']) || isset($options['after'])) {
                    $siblingName = isset($options['before']) ? $options['before'] : $options['after'];
                    $after = !isset($options['before']);
                    if ($siblingName === '-') {
                        $siblingName = '';
                    }
                    $parentBlock->insert($block, $siblingName, $after, $alias);
                } else {
                    $parentBlock->append($block, $alias);
                }
            }

            if (!empty($options['template'])) {
                $block->setTemplate($options['template']);
            }

            if (!empty($options['output'])) {
                $this->addOutputBlock($block->getNameInLayout(), $options['output']);
            }
        }

        return $block;
    }

    /**
     * Returns a list of output block names with their callback methods
     *
     * @return string[][]
     */
    public function getOutputBlockList()
    {
        return $this->_output;
    }

    /**
     * Fix issue with PHP7.0
     *
     * @return string
     */
    public function getOutput()
    {
        $out = '';
        if (!empty($this->_output)) {
            foreach ($this->_output as $callback) {
                $out .= $this->getBlock($callback[0])->{$callback[1]}();
            }
        }
        return $out;
    }

    /**
     * Does post initialization process for Magento layout, like it is done for Magento.
     *
     * @return $this
     */
    public function generateXml()
    {
        $this->getUpdate()->loadRuntime();
        $this->getProcessor()->execute(ItemInterface::TYPE_POST_INITIALIZE);
        return $this;
    }

    /**
     * Generates block from layout instructions
     *
     * @param Mage_Core_Model_Layout_Element|null $parent
     * @return void
     */
    public function generateBlocks($parent = null)
    {
        if ($parent !== null) {
            parent::generateBlocks($parent);
            return;
        }

        $this->getProcessor()->execute(ItemInterface::TYPE_LOAD);
        $this->getProcessor()->reset();
    }

}
