<?php
class Mofluid_Chat_Block_Adminhtml_Adminchat_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Init class
     */
    public function __construct()
    {  
        parent::__construct();
     
        $this->setId('mofluid_chat_adminchat_form');
        $this->setTitle($this->__('AdminChat Information'));
    }  
     
    /**
     * Setup form fields for inserts/updates
     *
     * return Mage_Adminhtml_Block_Widget_Form
     */
    protected function _prepareForm()
    {  
        //~ $model = Mage::registry('mofluid_chat');
        
        
     
        //~ $form = new Varien_Data_Form(array(
            //~ 'id'        => 'edit_form',
            //~ 'action'    => $this->getUrl('*/*/save', array('id' => $this->getRequest()->getParam('id'))),
            //~ 'method'    => 'post'
        //~ ));
     
        //~ $fieldset = $form->addFieldset('base_fieldset', array(
            //~ 'legend'    => Mage::helper('checkout')->__('Baz Information'),
            //~ 'class'     => 'fieldset-wide',
        //~ ));
     
        //~ if ($model->getId()) {
            //~ $fieldset->addField('id', 'hidden', array(
                //~ 'name' => 'id',
            //~ ));
        //~ }  
     
        //~ $fieldset->addField('name', 'text', array(
            //~ 'name'      => 'name',
            //~ 'label'     => Mage::helper('checkout')->__('Name'),
            //~ 'title'     => Mage::helper('checkout')->__('Name'),
            //~ 'required'  => true,
        //~ ));
     
        //~ $form->setValues($model->getData());
        //~ $form->setUseContainer(true);
        //~ $this->setForm($form);
        
     
        return parent::_prepareForm();
    }  
}
