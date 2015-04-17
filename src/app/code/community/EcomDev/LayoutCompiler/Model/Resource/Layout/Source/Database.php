<?php

class EcomDev_LayoutCompiler_Model_Resource_Layout_Source_Database
    extends Mage_Core_Model_Resource_Db_Abstract
{


    protected function _construct()
    {
        $this->_init('core/layout_update', 'layout_update_id');
    }

    /**
     * Returns a checksum for all database update stings in the specified theme, package, area and store
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param int $storeId
     * @return string
     */
    public function getLayoutUpdateChecksum($area, $package, $theme, $storeId)
    {
        $select = $this->getLayoutUpdateSelect($area, $package, $theme, $storeId);
        $select->columns(array('checksum' => new Zend_Db_Expr('MD5(CONCAT(update.handle, update.xml))')));
        $result = $this->_getReadAdapter()->fetchCol($select);

        return md5(implode('', $result));
    }

    /**
     * Returns layout update handles for a current theme and current store,
     * that should be compiled as layout instructions
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param int $storeId
     * @return string[]
     */
    public function getLayoutUpdateHandles($area, $package, $theme, $storeId)
    {
        $select = $this->getLayoutUpdateSelect($area, $package, $theme, $storeId);
        $select->columns(array('handle', 'xml'), 'update');

        $result = array();

        foreach ($this->_getReadAdapter()->fetchAll($select) as $row) {
            if (isset($result[$row['handle']])) {
                $result[$row['handle']] .= PHP_EOL . $row['xml'];
            } else {
                $result[$row['handle']] = $row['xml'];
            }
        }

        return $result;
    }

    /**
     * Returns a select object, that can be used for retrieving all related layout updates
     *
     * @param string $area
     * @param string $package
     * @param string $theme
     * @param int $storeId
     * @return Varien_Db_Select
     */
    private function getLayoutUpdateSelect($area, $package, $theme, $storeId)
    {
        $select = $this->_getReadAdapter()->select();
        $select->from(array('update' => $this->getMainTable()), array())
            ->join(
                array('link' => $this->getTable('core/layout_link')),
                'link.layout_update_id = update.layout_update_id',
                array()
            );

        $select->where('link.area = ?', $area)
            ->where('link.package = ?', $package)
            ->where('link.theme = ?', $theme)
            ->where('link.store_id IN(?)', array(0, $storeId))
            ->order('update.sort_order ASC')
            ->group('update.layout_update_id');

        return $select;
    }
}
