<?php

class Mofluid_Mynotes_Model_Mysql4_Mynotes extends Mage_Core_Model_Mysql4_Abstract {

	public function _construct() {

		/**** this is the name of the modelconfig/table_entity_name and the id field ****/
	
		$this->_init('mofluid_mynotes/mynotes', 'id');

	}

}
