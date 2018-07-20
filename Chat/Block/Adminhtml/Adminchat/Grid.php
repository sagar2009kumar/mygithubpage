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
                'align' =>'center',
                'width' => '25px',
                'index' => 'id'
            )
        );
         
        $this->addColumn('customer_id',
            array(
                'header'=> $this->__('Customer Id'),
                'align' =>'center',
                'width' => '20px',
                'index' => 'customer_id'
            )
        );
        
        $this->addColumn('request_id',
			array(
				'header' =>  $this->__('Request Id'),
				'align' =>'center',
                'width' => '10px',
                'index' => 'request_id',
                'sortable'  => false,
                'renderer' => 'Mofluid_Chat_Block_Adminhtml_Adminchat_Requestrender'
                )
         );
        
        $this->addColumn('customer_name',
			array(
				'header' =>  $this->__('Customer Name'),
				'align' =>'left',
                'width' => '50px',
                'index' => 'customer_name'
                )
         );
         
         $this->addColumn('created_at',
			array(
				'header' =>  $this->__('Created At'),
				'align' =>'center',
				'type'  => 'datetime',
                'width' => '100',
                'index' => 'created_at'
                )
         );
         
         $this->addColumn('updated_at',
			array(
				'header' =>  $this->__('Updated At'),
				'align' =>'center',
				'type'   => 'datetime',
                'width' => '100px',
                'index' => 'updated_at'
                )
         );
         /*
         
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
         
         */
         
         //~ $this->addColumn('Action',
			//~ array(
				//~ 'header' =>  $this->__('Action'),
				//~ 'align' =>'right',
                //~ 'width' => '10px',
                //~ 'sortable'  => false,
                //~ 'index' => 'message_count',
                //~ 'actions'   => array(
				//~ array(
					//~ 'caption' => $this->__('Chat'),
					//~ 'url'     => array(
						//~ 'base'=>'sokochat',
						//~ 'params'=>array('id'=>$this->getRequest(),'service'=>'chat')
					//~ ),
					//~ 'field'   => 'id'
					//~ )
				//~ )
                //~ )
         //~ );
         
         
         $this->addColumn('message_count',
            array(
                'header'    => $this->__('Action'),
                'type'      => 'action',
                'getter'     => 'getId',
                'align' =>'center',
                'filter'    => false,
                'sortable'  => false,
                'width' => '50px',
                'actions'   => array(
				array(
					'caption' => $this->__('Chat'),
					'url'     => array(
						'base'=>'sokochat',
						'params'=>array('id'=>$this->getRequest(),'service'=>'chat')
					),
					'field'   => 'id'
					)
				)
			));
         
        return parent::_prepareColumns();
    }
     
    public function getRowUrl($row)
    {
        // This is where our row data will link to
        return $this->getUrl('sokochat/index/index', array('id' => $row->getId(), 'service'=>'chat'));
    }
    
    
} 
