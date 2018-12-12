<?php

class Mofluid_Mynotes_Model_Mysql4_Mynotes_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract {

	public function _construct() {
		parent::_construct();
		$this->_init('mofluid_mynotes/mynotes');
		//~ /**** this is done to load the collection with the help of the descending order of the `created_at` ****/
		//~ $this->setDefaultSort('created_at');
		//~ $this->setDefaultDir('desc');
	}

}
