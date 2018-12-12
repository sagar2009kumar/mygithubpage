<?php

class Mofluid_Mynotes_Model_Mynotes extends Mage_Core_Model_Abstract {

	public function _construct() {

		parent::_construct();
		$this->_init('mofluid_mynotes/mynotes');
	}

	public function getNotesList($customerId, $currentPage = 1, $pageSize = 10) {

		try {

			$noteCollection = Mage::getModel('mofluid_mynotes/mynotes')->getCollection()->addFieldToSelect('*')->addFieldToFilter('customer_id', $customerId)->setPageSize($pageSize)->setCurPage($currentPage)->setOrder('created_at', 'desc');

			$lastPageNumber = $noteCollection->getLastPageNumber();
			$tempRes = array();

			foreach($noteCollection as $note) {
				
				$productId = $note->getProductId();
    	  		$noteDescription = $note->getNoteDescription();
    	    	$noteId = $note->getId();
    	    	$_product = Mage::getModel('catalog/product')->load($productId);
				$productUrl = $_product->getProductUrl();
    	    	$productImageUrl = (string)Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
				$productName = $_product->getName();

				array_push($tempRes, array('noteId'=>$noteId, 'noteDescription'=> $noteDescription, 'productId'=>$productId, 'productName'=>$productName, 'productImage'=>$productImageUrl, 'productUrl'=>$productUrl));
			}
			$res = array('status'=>1, 'lastPageNumber'=>$lastPageNumber, 'noteList'=>$tempRes);
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
		}

		return $res;
	}

}
