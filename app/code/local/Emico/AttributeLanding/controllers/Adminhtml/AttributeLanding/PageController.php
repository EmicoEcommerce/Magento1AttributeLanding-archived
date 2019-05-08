<?php
/**
 * @author Bram Gerritsen <bgerritsen@emico.nl>
 * @copyright (c) Emico B.V. 2017
 */
class Emico_AttributeLanding_Adminhtml_AttributeLanding_PageController extends Mage_Adminhtml_Controller_Action
{
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('admin/cms/emico_attributelanding');
    }

    public function indexAction()
    {
        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('emico_attributelanding/adminhtml_page'));
        $this->renderLayout();
    }

    public function exportCsvAction()
    {
        $fileName = 'Page_export.csv';
        $content = $this->getLayout()->createBlock('emico_attributelanding/adminhtml_page_grid')->getCsv();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function exportExcelAction()
    {
        $fileName = 'Page_export.xml';
        $content = $this->getLayout()->createBlock('emico_attributelanding/adminhtml_page_grid')->getExcelFile();
        $this->_prepareDownloadResponse($fileName, $content);
    }

    public function massDeleteAction()
    {
        $ids = $this->getRequest()->getParam('ids');
        if (!is_array($ids)) {
            $this->_getSession()->addError($this->__('Please select Page(s).'));
        } else {
            try {
                foreach ($ids as $id) {
                    $model = Mage::getSingleton('emico_attributelanding/page')->load($id);
                    $model->delete();
                }

                $this->_getSession()->addSuccess(
                    $this->__('Total of %d record(s) have been deleted.', count($ids))
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('emico_attributelanding')->__('An error occurred while mass deleting items. Please review log and try again.')
                );
                Mage::logException($e);
                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    public function editAction()
    {
        $id = $this->getRequest()->getParam('page_id');
        $model = Mage::getModel('emico_attributelanding/page');

        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->_getSession()->addError(
                    Mage::helper('emico_attributelanding')->__('This Page no longer exists.')
                );
                $this->_redirect('*/*/');
                return;
            }
        }

        Mage::register('current_attributelanding_page', $model);

        $this->loadLayout();
        $this->_addContent($this->getLayout()->createBlock('emico_attributelanding/adminhtml_page_edit'));
        $this->renderLayout();
    }

    /**
     *
     */
    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     *
     */
    public function saveAction()
    {
        $redirectBack = $this->getRequest()->getParam('back', false);
        if ($data = $this->getRequest()->getPost()) {

            $id = $this->getRequest()->getParam('page_id');
            $model = Mage::getModel('emico_attributelanding/page');
            if ($id) {
                $model->load($id);
                if (!$model->getId()) {
                    $this->_getSession()->addError(
                        Mage::helper('emico_attributelanding')->__('This Page no longer exists.')
                    );
                    $this->_redirect('*/*/index');
                    return;
                }
            }

            //check for file upload
            $uploadFields = [
                'header_image', 'overview_image'
            ];

            foreach ($uploadFields as $uploadField) {

                if(isset($data[$uploadField]['delete']) && (int)$data[$uploadField]['delete'] === 1) {
                    $data[$uploadField] = '';
                }

                else if(isset($_FILES[$uploadField]['name']) && file_exists($_FILES[$uploadField]['tmp_name'])) {
                    $filePath = $this->saveUploadedFile($uploadField);
                    $data[$uploadField] = $filePath;
                }
                else{
                    unset($data[$uploadField]);
                }
            }

            // save model
            try {
                $model->addData($this->sanitizeData($data));
                $model->setHideSelectedFilterGroup(!empty($data['hide_selected_filter_group']));
                $model->setActive(!empty($data['active']));
                $model->save();
                $this->_getSession()->addSuccess(
                    Mage::helper('emico_attributelanding')->__('The Page has been saved.')
                );
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
                $redirectBack = true;
            } catch (Exception $e) {
                $this->_getSession()->addError(Mage::helper('emico_attributelanding')->__('Unable to save the Page.'));
                $redirectBack = true;
                Mage::logException($e);
            }

            if ($redirectBack) {
                $this->_redirect('*/*/edit', array('page_id' => $model->getId()));
                return;
            }
        }
        $this->_redirect('*/*/index');
    }

    /**
     * @param array $data
     * @return array
     */
    protected function sanitizeData(array $data)
    {
        if (empty($data['category_id'])) {
            // When we leave category_id an empty string Magento will cast it to int before persisting to MySQL
            // This will cause problems because category_id is an optional association
            $data['category_id'] = null;
        }

        if (!empty($data['search_attributes']) && isset($data['search_attributes']['__empty'])) {
            unset($data['search_attributes']['__empty']);
        }

        return $data;
    }

    public function deleteAction()
    {
        if ($id = $this->getRequest()->getParam('page_id')) {
            try {
                // init model and delete
                $model = Mage::getModel('emico_attributelanding/page');
                $model->load($id);
                if (!$model->getId()) {
                    Mage::throwException(Mage::helper('emico_attributelanding')->__('Unable to find a Page to delete.'));
                }
                $model->delete();
                // display success message
                $this->_getSession()->addSuccess(
                    Mage::helper('emico_attributelanding')->__('The Page has been deleted.')
                );
                // go to grid
                $this->_redirect('*/*/index');
                return;
            } catch (Mage_Core_Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getSession()->addError(
                    Mage::helper('emico_attributelanding')->__('An error occurred while deleting Page data. Please review log and try again.')
                );
                Mage::logException($e);
            }
            // redirect to edit form
            $this->_redirect('*/*/edit', array('page_id' => $id));
            return;
        }

        $this->_getSession()->addError(
            Mage::helper('emico_attributelanding')->__('Unable to find a Page to delete.')
        );

        $this->_redirect('*/*/index');
    }

    /**
     * save uploaded file
     */
    protected function saveUploadedFile($fileId)
    {
        try {
            $uploader = new Varien_File_Uploader($fileId);
            $uploader->setAllowedExtensions(array('jpg', 'jpeg', 'gif', 'png'));
            $uploader->setAllowRenameFiles(false);

            $path = Mage::getBaseDir('media') . DS . 'emico_attributelanding' . DS;
            $fileName = $_FILES[$fileId]['name'];
            $fileExtension = substr($fileName, -4);
            $fileBaseName = substr($fileName, 0, -4);
            $targetFileName = md5($fileBaseName) . $fileExtension;

            $uploader->save($path, $targetFileName);

            return 'emico_attributelanding' . DS . $targetFileName;

        } catch (Exception $e) {
            $this->_getSession()->addError(
                Mage::helper('emico_attributelanding')->__('Could not upload image.')
            );
            return false;
        }
    }
}
