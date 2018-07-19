<?php
class Mofluid_Chat_Adminhtml_AdminchatController
    extends Mage_Adminhtml_Controller_Action
{
    public function indexAction()
    {  
        // Let's call our initAction method which will set some basic params for each action
        $this->_initAction()
            ->renderLayout();
    }  
     
    public function newAction()
    {  
        // We just forward the new action to a blank edit form
        $this->_forward('edit');
    }  
     
    
    //~ public function editAction()
    //~ {  
        //~ $this->_initAction();
     
        //~ // Get id if available
        //~ $id  = $this->getRequest()->getParam('id');
        //~ $model = Mage::getModel('foo_bar/baz');
     
        //~ if ($id) {
            //~ // Load record
            //~ $model->load($id);
     
            //~ // Check if record is loaded
            //~ if (!$model->getId()) {
                //~ Mage::getSingleton('adminhtml/session')->addError($this->__('This baz no longer exists.'));
                //~ $this->_redirect('*/*/');
     
                //~ return;
            //~ }  
        //~ }  
     
        //~ $this->_title($model->getId() ? $model->getName() : $this->__('New Baz'));
     
        //~ $data = Mage::getSingleton('adminhtml/session')->getBazData(true);
        //~ if (!empty($data)) {
            //~ $model->setData($data);
        //~ }  
     
        //~ Mage::register('foo_bar', $model);
     
        //~ $this->_initAction()
            //~ ->_addBreadcrumb($id ? $this->__('Edit Baz') : $this->__('New Baz'), $id ? $this->__('Edit Baz') : $this->__('New Baz'))
            //~ ->_addContent($this->getLayout()->createBlock('foo_bar/adminhtml_baz_edit')->setData('action', $this->getUrl('*/*/save')))
            //~ ->renderLayout();
    //~ }
     
    //~ public function saveAction()
    //~ {
        //~ if ($postData = $this->getRequest()->getPost()) {
            //~ $model = Mage::getSingleton('foo_bar/baz');
            //~ $model->setData($postData);
 
            //~ try {
                //~ $model->save();
 
                //~ Mage::getSingleton('adminhtml/session')->addSuccess($this->__('The baz has been saved.'));
                //~ $this->_redirect('*/*/');
 
                //~ return;
            //~ }  
            //~ catch (Mage_Core_Exception $e) {
                //~ Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            //~ }
            //~ catch (Exception $e) {
                //~ Mage::getSingleton('adminhtml/session')->addError($this->__('An error occurred while saving this baz.'));
            //~ }
 
            //~ Mage::getSingleton('adminhtml/session')->setBazData($postData);
            //~ $this->_redirectReferer();
        //~ }
    //~ }
     
    public function messageAction()
    {
        $data = Mage::getModel('mofluid_chat/adminchat')->load($this->getRequest()->getParam('id'));
        echo $data->getContent();
    }
     
    /**
     * Initialize action
     *
     * Here, we set the breadcrumbs and the active menu
     *
     * @return Mage_Adminhtml_Controller_Action
     */
    protected function _initAction()
    {
        $this->loadLayout()
            // Make the active menu match the menu config nodes (without 'children' inbetween)
            ->_setActiveMenu('mofluid/mofluid_chat_adminchat')
            ->_title($this->__('Mofluid'))->_title($this->__('Adminchat'))
            ->_addBreadcrumb($this->__('Mofluid'), $this->__('Mofluid'))
            ->_addBreadcrumb($this->__('Adminchat'), $this->__('Adminchat'));
         
        return $this;
    }
     
    /**
     * Check currently called action by permissions for current user
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('mofluid/mofluid_chat_adminchat');
    }
}
