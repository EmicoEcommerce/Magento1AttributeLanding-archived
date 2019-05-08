<?php

/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Block_Adminhtml_Page_Grid extends Mage_Adminhtml_Block_Widget_Grid
{

    public function __construct()
    {
        parent::__construct();
        $this->setId('grid_id');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }

    /**
     * @return mixed
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('emico_attributelanding/page')->getCollection();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * @return mixed
     */
    protected function _prepareColumns()
    {
        $this->addColumn('page_id',
            [
                'header' => $this->__('Page ID'),
                'width' => '20px',
                'index' => 'page_id'
            ]
        );

        $this->addColumn('active',
            [
                'header' => $this->__('Active'),
                'width' => '20px',
                'index' => 'active'
            ]
        );

        $this->addColumn('url_path',
            [
                'header' => $this->__('URL Path'),
                'width' => '200px',
                'index' => 'url_path'
            ]
        );

        $this->addColumn('type',
            [
                'header' => $this->__('Page type'),
                'width' => '200px',
                'index' => 'type'
            ]
        );

        $this->addColumn('title',
            [
                'header' => $this->__('Title'),
                'width' => '250px',
                'index' => 'title'
            ]
        );

        $this->addColumn('search_attributes',
            [
                'header' => $this->__('Search attributes'),
                'index' => 'search_attributes',
                'renderer' => 'Emico_AttributeLanding_Block_Adminhtml_Page_Renderer_SearchAttributes'
            ]
        );

        $this->addExportType('*/*/exportCsv', $this->__('CSV'));

        $this->addExportType('*/*/exportExcel', $this->__('Excel XML'));

        return parent::_prepareColumns();
    }

    /**
     * @param $row
     * @return mixed
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/edit', array('page_id' => $row->getId()));
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $modelPk = Mage::getModel('emico_attributelanding/page')->getResource()->getIdFieldName();
        $this->setMassactionIdField($modelPk);
        $this->getMassactionBlock()->setFormFieldName('ids');
        // $this->getMassactionBlock()->setUseSelectAll(false);
        $this->getMassactionBlock()->addItem('delete', array(
            'label' => $this->__('Delete'),
            'url' => $this->getUrl('*/*/massDelete'),
        ));
        return $this;
    }
}
