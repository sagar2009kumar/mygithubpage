<?php
class Mofluid_Chat_Block_Adminhtml_Adminchat_Grid
    extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
         
        // Set some defaults for our grid
        $this->setDefaultSort('id');
        $this->setId('mofluid_chat_adminchat_grid');
        $this->setDefaultDir('asc');
        $this->setSaveParametersInSession(true);
    }
     
    protected function _getCollectionClass()
    {
        // This is the model we are using for the grid
        return 'mofluid_chat/adminchat_collection';
    }
     
    protected function _prepareCollection()
    {
        // Get and set our collection for the grid
        $collection = Mage::getResourceModel($this->_getCollectionClass());
        $this->setCollection($collection);
         
        return parent::_prepareCollection();
    }
     
    protected function _prepareColumns()
    {
        // Add the columns that should appear in the grid
        $this->addColumn('id',
            array(
                'header'=> $this->__('ID'),
                'align' =>'right',
                'width' => '25px',
                'index' => 'id'
            )
        );
         
        $this->addColumn('customer_id',
            array(
                'header'=> $this->__('Customer Id'),
                'align' =>'right',
                'width' => '25px',
                'index' => 'customer_id'
            )
        );
        
        $this->addColumn('customer_name',
			array(
				'header' =>  $this->__('Customer Name'),
				'align' =>'right',
                'width' => '50px',
                'index' => 'customer_name'
                )
         );
         
         $this->addColumn('request_id',
			array(
				'header' =>  $this->__('Request Id'),
				'align' =>'right',
                'width' => '10px',
                'index' => 'request_id'
                )
         );
         
         $this->addColumn('created_at',
			array(
				'header' =>  $this->__('Created At'),
				'align' =>'right',
				'type'  => 'datetime',
				'align' =>'left',
                'width' => '100',
                'index' => 'created_at'
                )
         );
         
         $this->addColumn('updated_at',
			array(
				'header' =>  $this->__('Updated At'),
				'align' =>'left',
				'type'   => 'datetime',
                'width' => '100px',
                'index' => 'updated_at'
                )
         );
         
         $this->addColumn('message_count',
			array(
				'header' =>  $this->__('Message Count'),
				'align' =>'right',
                'width' => '10px',
                'sortable'  => false,
                'index' => 'message_count'
                )
         );
         
         $this->addColumn('message',
			array(
				'header' =>  $this->__('Message'),
				'align' =>'right',
                'width' => '50px',
                'index' => 'message',
                'sortable'  => false,
                )
         );
         
         $this->addColumn('last_message',
			array(
				'header' =>  $this->__('Last Message'),
				'align' =>'right',
                'width' => '50px',
                'index' => 'last_message',
                'sortable'  => false,
                )
         );
         
         /*
         $this->addColumn('action',
			array('header'=> $this->__('Action'),
				'align' =>'right',
                'width' => '50px',
                'index' => 'action'
                )
         );*/
         
         $url = "https://www.google.com?";
         
         $this->addColumn('action',
            array(
                'header'    => $this->__('Action'),
                'type'      => 'action',
                'getter'     => 'getId',
                'align' =>'center',
                'filter'    => false,
                'sortable'  => false,
                'width' => '50px',
                /*
                'actions'   => array(
                    array(
                        'caption' => $this->__('View'),
                        'url'     => $this->getEditParamsForAssociated(),
                        'field'   => 'id',
                        'onclick'  => 'window.location.href = "https://www.ibm.com?";return false;',
                        'href' => 'http://google.com'
                    )
                )*/
                'actions'   => array(
				array(
					'caption' => $this->__('Chat'),
					'url'     => array(
						'base'=>'sokochat',
						'params'=>array('id'=>$this->getRequest(),'service'=>'chat')
					),
					'field'   => 'id'
				)
        ),
        ));

		// superProduct.createPopup(this.href)
         
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('*/*/edit', array('id' => $row->getId()));
    }
    
    
} 
