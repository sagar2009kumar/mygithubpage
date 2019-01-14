
<?php

/*
Mofluidapi119_Catalog_Products v0.0.1
(c) 2016-2017 by Mofluid. All rights reserved.
Kaleshwar Jaiswal
*/
include_once('Catalog/Products.php');

class Service
{
	
	protected $_errorFlag = false;
	protected $_errorMessage = "";
	protected static $_requiredError = false;
	protected static $_baseCurrencyCode = '';
	

    /** Function : ws_category
     * Service Name : Category
     * @param $store : Store Id for Magento Stores
     * @param $service : Name of the Webservice
     * @return JSON Array
     * Description : Service to fetch all category
     * */
    public $CACHE_EXPIRY; //= 300; //in Seconds
    public function __construct(){
			$expireTime = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
     		$cacheExpireTime = $expireTime->getData('mofluid_cs_accountid');
     		self::$_baseCurrencyCode = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
			$this->CACHE_EXPIRY = $cacheExpireTime ;
	}
    public function ws_sidecategory($store, $service)
    {

    }
    //fix for market place issue
    function is_empty($var)
    {
     return empty($var);
    }
   /*
    function getChildCategories($id){
		$cat = Mage::getModel('catalog/category')->load($id);
		$subcats = $cat->getChildren();
		$all_child = array();
		$counter = 0;
		foreach(explode(',',$subcats) as $subCatid)
		{
		 $_category = Mage::getModel('catalog/category')->load($subCatid);
		 if($_category->getIsActive()) {
			$sub_cat = Mage::getModel('catalog/category')->load($_category->getId());
			$all_child[$counter]["id"]   = $sub_cat->getId();
            $all_child[$counter]["name"] = $sub_cat->getName();
			$sub_subcats = $sub_cat->getChildren();
			$setcount = 0;
			foreach(explode(',',$sub_subcats) as $sub_subCatid)
			{
				 $_sub_category = Mage::getModel('catalog/category')->load($sub_subCatid);
				 if($_sub_category->getIsActive()) {
					 $all_child[$counter]["children"][$setcount]["id"] = $_sub_category->getId();
					 $all_child[$counter]["children"][$setcount]["name"] = $_sub_category->getName();
					// $echo= '<li class="sub_cat"><a href="'.$_sub_category->getURL().'" title="View the products for the "'.$_sub_category->getName().'" category">'.$_sub_category->getName().'</a></li>';
				 }
				 $setcount++;
			}
		 }
		 $counter++;
		}

		return $all_child;
	}
    */

    function getChildCategories($id){


		$cat = Mage::getModel('catalog/category')->load($id);
		$subcats = $cat->getChildren();
		$all_child = array();
		$counter = 0;



		foreach(explode(',',$subcats) as $subCatid)
		{
		 $_category = Mage::getModel('catalog/category')->load($subCatid);
		 if($_category->getIsActive()) {

			$sub_cat = Mage::getModel('catalog/category')->load($_category->getId());
			$all_child[$counter]["id"]  = $sub_cat->getId();
            $all_child[$counter]["name"] = $sub_cat->getName();
            if($sub_cat->getIsAnchor()){
				$all_child[$counter]["products"] = $sub_cat->getProductCollection()->count();
            }else{
				$all_child[$counter]["products"] = $sub_cat->getProductCount();
			}
			$sub_subcats = $sub_cat->getChildren();
			$setcount = 0;

			foreach(explode(',',$sub_subcats) as $sub_subCatid)
			{
				 $_sub_category = Mage::getModel('catalog/category')->load($sub_subCatid);
				 if($_sub_category->getIsActive()) {

					 $sub_sub_cat = Mage::getModel('catalog/category')->load($_sub_category->getId());

					 $all_child[$counter]["children"][$setcount]["id"] = $_sub_category->getId();
					 $all_child[$counter]["children"][$setcount]["name"] = $_sub_category->getName();
					 $all_child[$counter]["children"][$setcount]["products"] = $_sub_category->getProductCount();
					 //$parentcount += $_sub_category->getProductCount();


					 $sub_sub_subcats = $sub_sub_cat->getChildren();

					 $setsubcount = 0;

					 /*foreach(explode(',',$sub_sub_subcats) as $sub_sub_subCatid)
					 {
					 	 $_sub_sub_category = Mage::getModel('catalog/category')->load($sub_sub_subCatid);
					 	if($_sub_sub_category->getIsActive()) {
							if($_sub_sub_category->getId()){
								$sub_sub_sub_cat = Mage::getModel('catalog/category')->load($_sub_sub_category->getId());
								$all_child[$counter]["children"][$setcount]['children'][$setsubcount]["id"] = $_sub_sub_category->getId();
								$all_child[$counter]["children"][$setcount]['children'][$setsubcount]["name"] = $_sub_sub_category->getName();
								$all_child[$counter]["children"][$setcount]['children'][$setsubcount][""] = $_sub_sub_category->getProductCount();
								//$parentcount += $_sub_sub_category->getProductCount();
								$sub_sub_sub_subcats = $sub_sub_sub_cat->getChildren();
								$setsubsubcount = 0;

								foreach(explode(',',$sub_sub_sub_subcats) as $sub_sub_sub_subCatid)
								{
									$_sub_sub_sub_category = Mage::getModel('catalog/category')->load($sub_sub_sub_subCatid);
									if($_sub_sub_sub_category->getIsActive()) {
									$all_child[$counter]["children"][$setcount]['children'][$setsubcount]['children'][$setsubsubcount]["id"] = $_sub_sub_sub_category->getId();
									$all_child[$counter]["children"][$setcount]['children'][$setsubcount]['children'][$setsubsubcount]["name"] = $_sub_sub_sub_category->getName();
									$all_child[$counter]["children"][$setcount]['children'][$setsubcount]['children'][$setsubsubcount]["products"] = $_sub_sub_sub_category->getProductCount();
									//$parentcount += $_sub_sub_sub_category->getProductCount();
									}
									$setsubsubcount++;
								}
							}
						}
					 	 $setsubcount++;

					 }*/

					// $echo= '<li class="sub_cat"><a href="'.$_sub_category->getURL().'" title="View the products for the "'.$_sub_category->getName().'" category">'.$_sub_category->getName().'</a></li>';



				 }
				 $setcount++;

			}
			//$all_child[$counter]["products"] = $sub_cat->getProductCount();
		 }
		 $counter++;

		}

		return $all_child;
	}
	
	/**** getting all the categories for the store ****/
	public function getCategories($storeId) {
	
		Mage::app()->setCurrentStore($storeId); 
	
		$res = array();
		/**** Getting the root category id ****/
		try { 
			$rootCategoryId = Mage::app()->getStore()->getRootCategoryId();
			$tempRes = $this->getChildrenCategories($rootCategoryId, 1, 2);
			
			if($this->_errorFlag) {
				/**** if error has occurred during the recursion process ****/
				return array("status"=>"0","errorMessage"=>"Error in getting the categories. Error Message : ".$this->_errorMessage);
			}else {
				/**** return the getting the categories ****/
				$res = array("status"=>"1","categories"=> $tempRes);
			}
		}catch(Exception $e) {
			$res = array("status"=>"0", "errorMessage"=>$e->getMessage());
		}
		return $res;
	}
	
	/**** getting the children categories for the 2 level by recursion ****/
	
	public function getChildrenCategories($categoryId, $currLevel = 1, $totalLevel) {
		
		try { 
			
			/**** getting the children categories ****/
			$category = Mage::getModel('catalog/category')->load($categoryId);
			$subCategories = $category->getChildren();
			$tempRes = array();
			
			foreach(explode(',',$subCategories) as $subCategoryId) {
				
				$subCategory = Mage::getModel('catalog/category')->load($subCategoryId);
				
				/**** if the category is active and included in the navigation menu ****/
				if($subCategory->getIsActive() && $subCategory->getIncludeInMenu()) {
					
					if($currLevel < $totalLevel) {
						/**** push the recursed result alongwith the current result ****/
						$var = $this->getChildrenCategories($subCategory->getId(), $currLevel+1, $totalLevel);
						array_push($tempRes, array("categoryId"=>$subCategory->getId(),"name"=>$subCategory->getName(), "productCount"=> (string)$subCategory->getProductCount(),"children"=> $var));
					}else {
						/**** If the recursion has reaced to its final level ****/
						array_push($tempRes, array("categoryId"=>$subCategory->getId(),"name"=>$subCategory->getName(), "productCount"=> (string)$subCategory->getProductCount()));
					}
				}
			}
		}catch(Exception $e) {
			/**** If exception occurs during the recursion set the error flag and set the error message ****/
			$this->_errorFlag = true;
			$this->_errorMessage = $e->getMessage();
		}
		return $tempRes;
	}
	
    function getChildCategories_old($id)
    {
        $category  = Mage::getModel('catalog/category')->load($id);
        $all_child = array();
        if ($category->hasChildren()) {
            $children = Mage::getModel('catalog/category')->getCategories($category->getId());
            $counter  = 0;
            foreach ($children as $child) {
                $all_child[$counter]["id"]   = $child->getId();
                $all_child[$counter]["name"] = $child->getName();
                if ($child->hasChildren()) {
                    $all_child[$counter]["children"] = $this->getChildCategories($child->getId());
                }
                $counter++;
            }
        }

        return $all_child;
    }

    /*   * *fetch initial data** */

    public function fetchInitialData($store, $service, $currency)
    {
		Mage::app()->setCurrentStore($store);
		return $this->getCategories();
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" .$store;
			//print_r($cache->load($cache_key)); die;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        Mage::app()->setCurrentStore($store);
        $result    = array();
        $rootcatId = Mage::app()->getStore()->getRootCategoryId();
        $result["categories"] = $this->getChildCategories($rootcatId);
        // $result["cms"]=  $this->getCMSBlocks();
        //$result["theme"] = $this->getBannerSlider("elegant");
         if($enable){
			$cache->save(json_encode($result), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return $result;
    }

    public function ws_category($store, $service)
    {
        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        $res = array();
        try {
            $storecategoryid = Mage::app()->getStore($store)->getRootCategoryId();
            $total           = 0;
            $category        = Mage::getModel('catalog/category');
            $tree            = $category->getTreeModel();
            $tree->load();

            $ids = $tree->getCollection()->getAllIds();
            $arr = array();

            $storecategoryid = Mage::app()->getStore($store)->getRootCategoryId();
            $cat             = Mage::getModel('catalog/category');
            $cat->load($storecategoryid);
            $categories = $cat->getCollection()->addAttributeToSelect(array(
                'name',
                'thumbnail',
                'image',
                'description',
                'store'
            ))->addIdFilter($cat->getChildren());
            try {
                foreach ($categories as $tmp) {
                    $res[] = array(
                        "id" => $tmp->getId(),
                        "name" => $tmp->getName(),
                        "image" => Mage::getModel('catalog/category')->load($tmp->getId())->getImageUrl(),
                        "thumbnail" => Mage::getBaseUrl('media') . 'catalog/category/' . Mage::getModel('catalog/category')->load($tmp->getId())->getThumbnail()
                    );
                    $total = $total + 1;
                }
            }
            catch (Exception $ex) {
                $res = $this->ws_subcategory($store, 'subcategory', $storecategoryid);
            }
            array_push($arr, $cat);
            //  $res = $res + '<br><br><br><center><b>Total Category : '.$total.'</b></center><br>';
        }
        catch (Exception $ex) {
            //$res = $res + 'Exception Problem : '.$ex;
        }
	   if($enable){
				$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
			}

        return ($res);
    }


    public function ws_getorderid($store, $service)
    {
		//~ $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		//~ $enable = $cacheEnable->getData('mofluid_cs_status');
		//~ if($enable){
			//~ $cache     = Mage::app()->getCache();
			//~ $cache_key = "mofluid_" . $service . "_store" . $store;
			//~ if($cache->load($cache_key))
			//~ return json_decode($cache->load($cache_key));
		//~ }
		Mage::app()->setCurrentStore($store);
        $result    = array();
        $incrementId= "1";
        $orders = Mage::getModel('sales/order')->getCollection()
        ->setOrder('created_at','DESC')
        ->setPageSize(1)
        ->setCurPage(1);

		//print_r($orders->getFirstItem());


	    $orderId = $orders->getFirstItem()->getIncrementId();
		$result["orderid"] = $orderId;
	    //~ if($enable){
			//~ $cache->save(json_encode($result), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		//~ }
        return $result;

    }
  public function getSearchFilter($store, $categoryids)
  {
		try
		{
			Mage::app()->setCurrentStore($store);
			$getfullfilter = array();
			$filter = array();
			$init = 0;
			$hash=array();
			foreach($categoryids as $categoryid){
				$layer = Mage::getModel("catalog/layer");
				$category = Mage::getModel("catalog/category")->load($categoryid);
				$layer->setCurrentCategory($category);
				$attributes = $layer->getFilterableAttributes();
				foreach ($attributes as $attribute)
				{		
						if(array_key_exists($attribute->attribute_code."",$hash))
						{	
							if($hash[$attribute->attribute_code.""]==1)
							continue;
							//~ $hash[$attribute->attribute_code.""]=1;
						}
						$hash[$attribute->attribute_code.""]=1;
							
						if ($attribute->getAttributeCode() == 'price') {
							$filterBlockName = 'catalog/layer_filter_price';
						} 
						elseif ($attribute->getBackendType() == 'decimal') {
							$filterBlockName = 'catalog/layer_filter_decimal';
						} 
						else {
							$filterBlockName = 'catalog/layer_filter_attribute';
						}
						$filter[$init]["code"] = $attribute->attribute_code;
						$filter[$init]["type"] =  $attribute->frontend_input;
						$filter[$init]["label"] =  $attribute->frontend_label;

						$result = Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
						$innercount = 0;
						$filter_data = array();
						foreach($result->getItems() as $option) {	
							$filter_data[$innercount]["count"] =  $option->getCount();
							$filter_data[$innercount]["label"] =  strip_tags($option->getLabel());
							$filter_data[$innercount]["id"] =  $option->getValue();
							$innercount++;
						}
						$filter[$init]["values"] = $filter_data;
						$init++;
				}
			}
			return $filter;
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
}

    public function ws_getcategoryfilter($store,$categoryid){
	Mage::app()->setCurrentStore($store);
	$layer = Mage::getModel("catalog/layer");
	$category = Mage::getModel("catalog/category")->load($categoryid);
	$layer->setCurrentCategory($category);
	$attributes = $layer->getFilterableAttributes();
	$getfullfilter = array();
	$filter = array();
	$init = 0;
	foreach ($attributes as $attribute)
	{
		
			if ($attribute->getAttributeCode() == 'price') {

				$filterBlockName = 'catalog/layer_filter_price';

			} elseif ($attribute->getBackendType() == 'decimal') {
				$filterBlockName = 'catalog/layer_filter_decimal';
			} else {
				$filterBlockName = 'catalog/layer_filter_attribute';
			}
			$filter[$init]["code"] = $attribute->attribute_code;
			$filter[$init]["type"] =  $attribute->frontend_input;
			$filter[$init]["label"] =  $attribute->frontend_label;

			$result = Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
			$innercount = 0;
			$filter_data = array();
			foreach($result->getItems() as $option) {
				$filter_data[$innercount]["count"] =  $option->getCount();
				$filter_data[$innercount]["label"] =  strip_tags($option->getLabel());

				$filter_data[$innercount]["id"] =  $option->getValue();

				$innercount++;

			}
			$filter[$init]["values"] = $filter_data;
		$init++;
	}
	//die($ans);
	return $filter;

 }
    /** Function : ws_subcategory
     * Service Name : SubCategory
     * @param $store : Store Id for Magento Stores
     * @param $service : Name of the Webservice
     * @param $categoryid : Category Id for the App
     * @return JSON Array
     * Description : Service to fetch all category
     * */
    public function ws_subcategory($store_id, $service, $categoryid)
    {
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store_id. "_category" . $categoryid;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        Mage::app()->setCurrentStore($store_id);
        $res      = array();
        $children = Mage::getModel('catalog/category')->getCategories($categoryid);
        foreach ($children as $current_category) {
            $category = Mage::getModel('catalog/category')->load($current_category->getId());
            $res[]    = array(
                "id" => $category->getId(),
                "name" => $category->getName(),
                "image" => $category->getImageUrl(),
                "thumbnail" => Mage::getBaseUrl('media') . 'catalog/category/' . $category->getThumbnail()
            );
        }
        $result["id"]         = $categoryid;
        $result["title"]      = Mage::getModel('catalog/category')->load($categoryid)->getName();
        $result["images"]     = Mage::getModel('catalog/category')->load($categoryid)->getImageUrl();
        $result["thumbnail"]  = Mage::getBaseUrl('media') . 'catalog/category/' . Mage::getModel('catalog/category')->load($categoryid)->getThumbnail();
        $result["categories"] = $res;

		 if($enable){
				$cache->save(json_encode($result), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
			}
        return ($result);
    }

    public function ws_products($store_id, $service, $categoryid, $curr_page, $page_size, $sortType, $sortOrder, $currentcurrencycode, $price)
    {	try
		{
			$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
			$enable = $cacheEnable->getData('mofluid_cs_status');
			if($enable){
				$cache     = Mage::app()->getCache();
				$cache_key = "mofluid_" . $service . "_store" . $store;
				if($cache->load($cache_key))
				return json_decode($cache->load($cache_key));
			}// die;
			Mage::app()->setCurrentStore($store_id);
			$res                = array();
			$show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
			$is_in_stock_option = $show_out_of_stock ? 0 : 1;
			$basecurrencycode   = Mage::app()->getStore($store_id)->getBaseCurrencyCode();
			Mage::app()->setCurrentStore($store_id);
			$c_id     = $categoryid;
			$category = new Mage_Catalog_Model_Category();
			$category->load($c_id);
			$children1 = $category->getProductCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store_id)->addAttributeToSelect('*')->addAttributeToFilter('type_id', array(
				'in' => array(
					Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
					Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
					Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
					Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
				)
			))->addAttributeToFilter('visibility', 4)->addAttributeToFilter('is_in_stock', array(
				'in' => array(
					$is_in_stock_option,
					1
				)
			))->addAttributeToFilter('status', array('eq' => 1));
			$collection = $category->getProductCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store_id)->addAttributeToSelect('*')->addAttributeToFilter('type_id', array(
				'in' => array(
					Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
					Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
					Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
					Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
				)
			))->addAttributeToFilter('visibility', 4)->addAttributeToFilter('is_in_stock', array(
				'in' => array(
					$is_in_stock_option,
					1
				)
			))->addAttributeToFilter('status', array('eq' => 1));
			$collection->addAttributeToSort($sortType, $sortOrder);
			$res["total"] = $collection->getSize();
			$collection->setPage($curr_page, $page_size);
			foreach ($collection as $_product) {
				$gflag=1;
				$productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
				$defaultprice  = str_replace(",", "", number_format($_product->getPrice(), 2));
				$defaultsprice = str_replace(",", "", number_format($_product->getSpecialprice(), 2));
				;

				try {
					$custom_option_product = Mage::getModel('catalog/product')->load($_product->getId());
					$custom_options        = $custom_option_product->getOptions();
					$has_custom_option     = 0;
					foreach ($custom_options as $optionKey => $optionVal) {
						$has_custom_option = 1;
					}
				}
				catch (Exception $ee) {
					echo $ee->getMessage();
					$has_custom_option = 0;
				}

				//print_r($defaultprice); $echo= "<br>";
				// Get the Special Price
				$specialprice         = $_product->getSpecialPrice();

				// Get the Special Price FROM date
				$specialPriceFromDate = $_product->getSpecialFromDate();
				// Get the Special Price TO date
				$specialPriceToDate   = $_product->getSpecialToDate();
				// Get Current date
				$today                = time();

				if ($specialprice) {
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {

						$specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
					} else {
						$specialprice = 0;
					}
				} else {
					$specialprice = 0;
				}
				 if ($_product->getTypeID() == 'grouped') {

					$defaultprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
					$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
				  // 	$mofluid_all_product_images[0] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();
				 $associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
					if(count($associatedProducts))
					{
						$gflag=1;
					}
					else
					{
						$gflag=0;
					}
				}
				else
				{
					 $defaultprice =  number_format($_product->getPrice(), 2, '.', '');
					 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
				}
				if($defaultprice == $specialprice)
					$specialprice = number_format(0, 2, '.', '');
					if($specialprice==0.0)
					$specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());
			   if($gflag)
			   {
				$res["data"][] = array(
					"id" => $_product->getId(),
					"name" => $_product->getName(),
					"imageurl" => (string)$productImage,
					"sku" => $_product->getSku(),
					"type" => $_product->getTypeID(),
					"spclprice" => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
					"currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
					"price" => number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
					"created_date" => $_product->getCreatedAt(),
					"is_in_stock" => $_product->getStockItem()->getIsInStock(),
					"hasoptions" => $has_custom_option,
					"stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty()
				);
				}

			}
			if($enable){
				$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
			}
			return ($res);
		}
		catch(Exception $e)
		{
			echo $e->getMessage();
		}
    }
    /*** Related  Products ****/
    public function ws_getRelatedProducts($product_id,$currentcurrencycode, $service, $store)
    {
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
		Mage::app()->setCurrentStore($store);
        $basecurrencycode   = Mage::app()->getStore()->getBaseCurrencyCode();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;
        $store_id  = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->getCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store)->setOrder('created_at', 'desc')->addAttributeToFilter('visibility', 4);
        $_products->addAttributeToSelect('*');
        $_products->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
            )
        ))->setPage(1,20)->addAttributeToFilter('status', array('eq' => 1))->addAttributeToFilter('is_in_stock', array(
            'in' => array(
                $is_in_stock_option,
                1
            )
        ));

       $Products = Mage::getModel('catalog/product')->load($product_id);
      // $productImage = Mage::helper('catalog/image')->init($Products,'small_image')->resize(200,200);
       $allRelatedProductIds = $Products->getRelatedProductIds();

	   $featuredProducts = array();
	   $featuredProducts["total"] = count($allRelatedProductIds);
       $i                = 0;
       if ($_products->getSize()) {
           $count = 0;
           foreach ($allRelatedProductIds as $id) {
				$_product = Mage::getModel('catalog/product')->load($id);
                $count++;
                $product_id   = $_product->getId();
                $productName  = $_product->getName();
                $productPrice         = number_format($_product->getPrice(), 3);
                $productImage  =$_product->getImageUrl();
              // $echo= 'New Price '.$productPrice; die;
                $specialPriceFromDate = $_product->getSpecialFromDate();
                $specialPriceToDate   = $_product->getSpecialToDate();
                //$echo= 'New Price '.$specialPriceToDate; die;
                $today                = time();
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
                    $productSprice = number_format($_product->getSpecialprice(), 3);
                else
                $productSprice = "0.00";
                $productStatus  = $_product->getStockItem()->getIsInStock();
                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                if ($productStatus == 1 && $stock_quantity < 0)
                    $productStatus == 1;
                else
                    $productStatus == 0;
                //convert price from base currency to current currency
                $currencysymbol = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                //$echo= PHP_EOL.$productPrice;
                $tax_type       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
                $_product       = Mage::getModel('catalog/product')->load($_product->getId());
                $taxClassId     = $_product->getData("tax_class_id");
                $taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $taxRate        = $taxClasses["value_" . $taxClassId];
                //$tax_price = (($taxRate)/100) *  ($_product->getPrice());
                $tax_price      = str_replace(",", "", number_format(((($taxRate) / 100) * ($_product->getPrice())), 3));

                if ($tax_type == 0) {
                    $defaultprice = str_replace(",", "", $productPrice);
                } else {
                    $defaultprice = str_replace(",", "", $productPrice) - $tax_price;
                    //$defaultprice = str_replace(",","",number_format(($_product->getPrice()-$tax_price),2));
                }
                //$defaultprice = str_replace(",","",$productPrice);
                $actualprice   = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 3));
                $defaultsprice = str_replace(",", "", $productSprice);
                $splsprice     = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 3));
                // Get the Special Price
                $specialprice         = $_product->getSpecialPrice();
                // Get the Special Price FROM date
                $specialPriceFromDate =$_product->getSpecialFromDate();
                // Get the Special Price TO date
                $specialPriceToDate   = $_product->getSpecialToDate();
                // Get Current date
                $today                = time();

                if ($specialprice) {
                    if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                        $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 3));
                    } else {
                        $specialprice = 0;
                    }
                } else {
                    $specialprice = 0;
                }

                $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
                if ($tax_type == 0) {
                    $specialprice = $specialprice;
                } else {
                    $specialprice = $specialprice - $tax_price_for_special;
                }
                /*Added by Mofluid team to resolve spcl price issue in 1.17*/

                if ($_product->getTypeID() == 'grouped') {
                $actualprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 3, '.', '');
                $specialprice =  number_format($_product->getFinalPrice(), 3, '.', '');
              // 	$mofluid_all_product_images[0] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

            }
            else
            {
            	 $actualprice =  number_format($_product->getPrice(), 3, '.', '');
           		 $specialprice =  number_format($_product->getFinalPrice(), 3, '.', '');
            }

                if($actualprice == $specialprice)
                    $specialprice = number_format(0, 3, '.', '');
                    if($specialprice==0.0)
                    $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());
					$featuredProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "sku" => $_product->getSku(),
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 3, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 3, '.', ''),
                    'currency_symbol' => $currencysymbol,
                     "created_date" => $_product->getCreatedAt(),
                    'is_stock_status' => $productStatus,
                     "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty()

                );

            }
            $featuredProducts["products_list"] = array_reverse($featuredProducts["products_list"]);
            $featuredProducts["status"][0]     = array(
                'Show_Status' => "1"
            );
        } else
            $featuredProducts["status"][0] = array(
                'Show_Status' => "0"
            );


        $featuredProducts["products_list"] = array_reverse($featuredProducts["products_list"]);
        if($enable){
			$cache->save(json_encode($featuredProducts), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($featuredProducts);
    }

     /***  End Related  Products ****/
    /*   * *Convert Currency** */

    public function convert_currency($price, $from, $to)
    {
        $newPrice = Mage::helper('directory')->currencyConvert($price, $from, $to);
        return $newPrice;
    }

    /*   * **********************get featured products*************** */


    public function ws_getFeaturedProducts($currentcurrencycode, $service, $store)
    {
       	$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
	    if($enable){
			$cache     = Mage::app()->getCache();
			 $cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
	   }
        //get base currency from magento
        Mage::app()->setCurrentStore($store);
        $basecurrencycode   = Mage::app()->getStore()->getBaseCurrencyCode();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;

        $store_id  = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->getCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store)->setOrder('created_at', 'desc');
        $_products->addAttributeToSelect('*');

        $_products->addFieldToFilter(array(
            array(
                'attribute' => 'featured',
                'eq' => true
            )
        ));
        $_products->setPage(1,10);
        $_products->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
            )
        ))->addAttributeToFilter('status', array('eq' => 1))->addAttributeToFilter('is_in_stock', array(
            'in' => array(
                $is_in_stock_option,
                1
            )
        ));

        $featuredProducts = array();
        $i                = 0;
        if ($_products->getSize()) {
            foreach ($_products->getItems() as $_product) {
                $product_id   = $_product->getId();
                $productName  = $_product->getName();
				$productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
                $productPrice         = number_format($_product->getPrice(), 2);
                $specialPriceFromDate = $_product->getSpecialFromDate();
                $specialPriceToDate   = $_product->getSpecialToDate();
                $today                = time();
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
                    $productSprice = number_format($_product->getSpecialprice(), 2);
                else
                    $productSprice = "0.00";
                $productStatus  = $_product->getStockItem()->getIsInStock();
                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                if ($productStatus == 1 && $stock_quantity < 0)
                    $productStatus == 1;
                else
                    $productStatus == 0;

                //convert price from base currency to current currency
                $currencysymbol = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                //$echo= PHP_EOL.$productPrice;
                $tax_type       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
                //$_product       = Mage::getModel('catalog/product')->load($_product->getId());
                $taxClassId     = $_product->getData("tax_class_id");
                $taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $taxRate        = $taxClasses["value_" . $taxClassId];
                //$tax_price = (($taxRate)/100) *  ($_product->getPrice());
                $tax_price      = str_replace(",", "", number_format(((($taxRate) / 100) * ($_product->getPrice())), 2));

                if ($tax_type == 0) {
                    $defaultprice = str_replace(",", "", $productPrice);
                } else {
                    $defaultprice = str_replace(",", "", $productPrice) - $tax_price;
                    //$defaultprice = str_replace(",","",number_format(($_product->getPrice()-$tax_price),2));
                }


                //$defaultprice = str_replace(",","",$productPrice);
                $actualprice   = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
                $defaultsprice = str_replace(",", "", $productSprice);
                $splsprice     = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));

                // Get the Special Price
                $specialprice         = $_product->getSpecialPrice();
                // Get the Special Price FROM date
                $specialPriceFromDate = $_product->getSpecialFromDate();
                // Get the Special Price TO date
                $specialPriceToDate   = $_product->getSpecialToDate();
                // Get Current date
                $today                = time();

                if ($specialprice) {
                    if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                        $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                    } else {
                        $specialprice = 0;
                    }
                } else {
                    $specialprice = 0;
                }

                $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
                if ($tax_type == 0) {
                    $specialprice = $specialprice;
                } else {
                    $specialprice = $specialprice - $tax_price_for_special;
                }

                /*Added by Mofluid team to resolve spcl price issue in 1.17*/
                if ($_product->getTypeID() == 'grouped') {
                $actualprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
                $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
              // 	$mofluid_all_product_images[0] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

            }
            else
            {
            	 $actualprice =  number_format($_product->getPrice(), 2, '.', '');
           		 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }

              //  $actualprice =  number_format($_product->getPrice(), 2, '.', '');
               // $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
                if($actualprice == $specialprice)
                    $specialprice = number_format(0, 2, '.', '');
              if($specialprice==0.0)
              $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());
                $featuredProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'currency_symbol' => $currencysymbol,
                    'is_stock_status' => $productStatus
                );
            }
            $featuredProducts["status"][0] = array(
                'Show_Status' => "1"
            );
        } else
            $featuredProducts["status"][0] = array(
                'Show_Status' => "0"
            );

        if($enable){
			$cache->save(json_encode($featuredProducts), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($featuredProducts);
    }



/*************************** Best Seller*****************/
/*
       public function ws_getBestsellerProducts($currentcurrencycode, $service, $store)
    {
       $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        //get base currency from magento
        Mage::app()->setCurrentStore($store);
        $basecurrencycode   = Mage::app()->getStore()->getBaseCurrencyCode();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;

        $store_id  = Mage::app()->getStore()->getId();
		//~ $_products = Mage::getResourceModel('reports/product_collection')
            //~ ->addOrderedQty()
            //~ ->addAttributeToSelect(array('name', 'price', 'small_image')) //edit to suit tastes
            //~ ->setStoreId($store_id)
            //~ ->addStoreFilter($store_id)
            //~ ->setOrder('ordered_qty', 'desc'); //best sellers on top
			//~ Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($_products);
			//~ Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($_products);

        $_products = Mage::getResourceModel('reports/product_collection')
                ->addAttributeToSelect('*')
                ->addAttributeToFilter("status", Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
                ->addPriceData()
                ->addOrderedQty()
                ->setOrder("ordered_qty", "desc")
                ->setStore($store_id)
                ->addStoreFilter($store_id)
				->setPageSize(20)->setCurPage(1);
$_products = $_products->getColumnValues('entity_id');

$_products = Mage::getResourceModel('catalog/product_collection')
                        ->addAttributeToSelect(array('name','description','price','small_image'))
                        ->addAttributeToFilter('entity_id',array('in' => $_products));

//~ foreach($_products as $_products){
    //~ $echo= $product->getName();
    //~ $echo= $product->getDescription();
    //~ $echo= $product->getPrice();
    //~ $echo= Mage::helper('catalog/image')->init($product,'small_image');
//~ }
       // die("ed best seller ");
        $getBestsellerProducts = array();
        $i                = 0;
        if ($_products->getSize()) {
			//$count = 0;
            foreach ($_products->getItems() as $_product) {

				 //~ if ($count == 20)
                    //~ break;
                $count++;
                $product_id   = $_product->getId();
                $productName  = $_product->getName();
				$productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
                $productPrice         = number_format($_product->getPrice(), 2);
                $specialPriceFromDate = $_product->getSpecialFromDate();
                $specialPriceToDate   = $_product->getSpecialToDate();
                $today                = time();
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
                    $productSprice = number_format($_product->getSpecialprice(), 2);
                else
                    $productSprice = "0.00";
                $productStatus  = $_product->getStockItem()->getIsInStock();
                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                if ($productStatus == 1 && $stock_quantity < 0)
                    $productStatus == 1;
                else
                    $productStatus == 0;
                //convert price from base currency to current currency
                $currencysymbol = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                //$echo= PHP_EOL.$productPrice;
                $tax_type       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
                //$_product       = Mage::getModel('catalog/product')->load($_product->getId());
                $taxClassId     = $_product->getData("tax_class_id");
                $taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $taxRate        = $taxClasses["value_" . $taxClassId];
                //$tax_price = (($taxRate)/100) *  ($_product->getPrice());
                $tax_price      = str_replace(",", "", number_format(((($taxRate) / 100) * ($_product->getPrice())), 2));
                if ($tax_type == 0) {
                    $defaultprice = str_replace(",", "", $productPrice);
                } else {
                    $defaultprice = str_replace(",", "", $productPrice) - $tax_price;
                    //$defaultprice = str_replace(",","",number_format(($_product->getPrice()-$tax_price),2));
                }
                //$defaultprice = str_replace(",","",$productPrice);
                $actualprice   = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
                $defaultsprice = str_replace(",", "", $productSprice);
                $splsprice     = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                // Get the Special Price
                $specialprice         = $_product->getSpecialPrice();
                // Get the Special Price FROM date
                $specialPriceFromDate = $_product->getSpecialFromDate();
                // Get the Special Price TO date
                $specialPriceToDate   = $_product->getSpecialToDate();
                // Get Current date
                $today                = time();

                if ($specialprice) {
                    if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                        $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                    } else {
                        $specialprice = 0;
                    }
                } else {
                    $specialprice = 0;
                }

                $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
                if ($tax_type == 0) {
                    $specialprice = $specialprice;
                } else {
                    $specialprice = $specialprice - $tax_price_for_special;
                }

                if ($_product->getTypeID() == 'grouped') {
                $actualprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
                $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }
            else
            {
            	$actualprice =  number_format($_product->getPrice(), 2, '.', '');
           		$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }
                if($actualprice == $specialprice)
                    $specialprice = number_format(0, 2, '.', '');
                    if($specialprice==0.0)
                    $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());
                $getBestsellerProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'currency_symbol' => $currencysymbol,
                    'is_stock_status' => $productStatus
                );
            }
            $getBestsellerProducts["status"][0] = array(
                'Show_Status' => "1"
            );
        } else
            $getBestsellerProducts["status"][0] = array(
                'Show_Status' => "0"
            );
        return ($getBestsellerProducts);
    }
    * */
        /***************************End  Best Seller *****************/

    public function ws_getNewProducts($currentcurrencycode, $service, $store)
    {
        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        //get base currency from magento
        Mage::app()->setCurrentStore($store);
        $basecurrencycode   = Mage::app()->getStore()->getBaseCurrencyCode();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;

        $store_id  = Mage::app()->getStore()->getId();
        $_products = Mage::getModel('catalog/product')->getCollection()
					->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')
					->addStoreFilter($store)->setOrder('created_at', 'desc')
					->addAttributeToFilter('visibility', 4);
        $_products->addAttributeToSelect('*');

        $_products->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE
            )
        ))->addAttributeToFilter('status', array('eq' => 1))->addAttributeToFilter('is_in_stock', array(
            'in' => array(
                $is_in_stock_option,

            )
        ));
         $_products->setPage(1,10);


        $featuredProducts = array();
        $i                = 0;
        if ($_products->getSize()) {
            $count = 0;
            foreach ($_products->getItems() as $_product) {
                if ($count == 10)
                    break;
                $count++;

                $product_id   = $_product->getId();
                $productName  = $_product->getName();
                $productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
                $productPrice         = number_format($_product->getPrice(), 2);
                $specialPriceFromDate = $_product->getSpecialFromDate();
                $specialPriceToDate   = $_product->getSpecialToDate();
                $today                = time();
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
                    $productSprice = number_format($_product->getSpecialprice(), 2);
                else
                    $productSprice = "0.00";

                $productStatus  = $_product->getStockItem()->getIsInStock();
                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                if ($productStatus == 1 && $stock_quantity < 0)
                    $productStatus == 1;
                else
                    $productStatus == 0;

                //convert price from base currency to current currency
                $currencysymbol = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                $tax_type       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
                //$_product       = Mage::getModel('catalog/product')->load($_product->getId());
                $taxClassId     = $_product->getData("tax_class_id");
                $taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $taxRate        = $taxClasses["value_" . $taxClassId];
                $tax_price      = str_replace(",", "", number_format(((($taxRate) / 100) * ($_product->getPrice())), 2));

                if ($tax_type == 0) {
                    $defaultprice = str_replace(",", "", $productPrice);
                } else {
                    $defaultprice = str_replace(",", "", $productPrice) - $tax_price;
                }

                $actualprice   = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
                $defaultsprice = str_replace(",", "", $productSprice);
                $splsprice     = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));

                // Get the Special Price
                $specialprice         = $_product->getSpecialPrice();
                // Get the Special Price FROM date
                $specialPriceFromDate = $_product->load($product_id)->getSpecialFromDate();
                // Get the Special Price TO date
                $specialPriceToDate   = $_product->getSpecialToDate();
                // Get Current date
                $today                = time();

                if ($specialprice) {
                    if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                        $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                    } else {
                        $specialprice = 0;
                    }
                } else {
                    $specialprice = 0;
                }

                $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
                if ($tax_type == 0) {
                    $specialprice = $specialprice;
                } else {
                    $specialprice = $specialprice - $tax_price_for_special;
                }
                /*Added by Mofluid team to resolve spcl price issue in 1.17*/

                if ($_product->getTypeID() == 'grouped') {
                $actualprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
                $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
              // 	$mofluid_all_product_images[0] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

            }
            else
            {
            	 $actualprice =  number_format($_product->getPrice(), 2, '.', '');
           		 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }


               // $actualprice =  number_format($_product->getPrice(), 2, '.', '');
                //$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
                if($actualprice == $specialprice)
                    $specialprice = number_format(0, 2, '.', '');
                    if($specialprice==0.0)
                    $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());
                $featuredProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'currency_symbol' => $currencysymbol,
                    'is_stock_status' => $productStatus
                );

            }
            $featuredProducts["products_list"] = array_reverse($featuredProducts["products_list"]);
            $featuredProducts["status"][0]     = array(
                'Show_Status' => "1"
            );
        } else
            $featuredProducts["status"][0] = array(
                'Show_Status' => "0"
            );

        $featuredProducts["products_list"] = array_reverse($featuredProducts["products_list"]);
        if($enable){
			$cache->save(json_encode($featuredProducts), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($featuredProducts);
    }

    public function ws_getCustomerId($store, $service, $email)
    {
        $customer  = Mage::getModel('customer/customer');
        $websiteId = Mage::getModel('core/store')->load($store)->getWebsiteId();
        if ($store) {
            $customer->setCurrentStore($store);
            $customer->website_id = $websiteId;
        }
        $customer->loadByEmail($email);
        if ($customer->getId()) {
            $res       = array();
            $res["id"] = $customer->getId();
            return $res;
        }
        return -1;
    }

    public function getGroupedProductPrice($product_id, $currency)
    {
        $group            = Mage::getModel('catalog/product_type_grouped')->setProduct(Mage::getModel('catalog/product')->load($product_id));
        $base_currency    = Mage::app()->getStore()->getBaseCurrencyCode();
        $group_collection = $group->getAssociatedProductCollection();
        $prices           = array();
        foreach ($group_collection as $group_product) {
            $_product = Mage::getModel('catalog/product')->load($group_product->getId());
            $prices[] = round(floatval(Mage::helper('directory')->currencyConvert($_product->getFinalPrice(), $base_currency, $currency)), 2);
        }
        sort( $prices);
        $prices = array_shift($prices);
        return $prices;
    }

    public function ws_verifyLogin($store, $service, $username, $password)
    {
        $websiteId       = Mage::getModel('core/store')->load($store)->getWebsiteId();
        $res             = array();
        $res["username"] = $username;
        $res["password"] = base64_decode($password);
        $login_status    = 1;
        try {
            $login_customer_result = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->authenticate($username, base64_decode($password));
            $login_customer        = Mage::getModel('customer/customer')->setWebsiteId($websiteId);
            $login_customer->loadByEmail($username);
            $res["firstname"] = $login_customer->firstname;
            $res["lastname"]  = $login_customer->lastname;
            $res["id"]        = $login_customer->getId();
            //$res["password"]
            //$res["password"]
        }
        catch (Exception $e) {
            $login_status = 0;
        }
        $res["login_status"] = $login_status;
        return $res;
    }

    public function ws_createuser($store, $service, $firstname, $lastname, $email, $password)
    {
        // Website and Store details
        $res                  = array();
        $websiteId            = Mage::getModel('core/store')->load($store)->getWebsiteId();
        $customer             = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setCurrentStore($store);
        //  $echo= 'Phase 2';
        try {
            // If new, save customer information
            $customer->firstname     = $firstname;
            $customer->lastname      = $lastname;
            $customer->email         = $email;
            $customer->password_hash = md5(base64_decode($password));
            $res["email"]            = $email;
            $res["firstname"]        = $firstname;
            $res["lastname"]         = $lastname;
            $res["password"]         = $password;
            $res["status"]           = 0;
            $res["id"]               = 0;
            $cust                    = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

            //check exists email address of users
            if ($cust->getId()) {
                $res["id"]     = $cust->getId();
                $res["status"] = 0;
            } else {
                //$echo= 'Phase 2.5';
                if ($customer->save()) {
                    $customer->sendNewAccountEmail('confirmed');
                    $this->send_Password_Mail_to_NewUser($firstname, base64_decode($password), $email);
                    $res["id"]     = $customer->getId();
                    $res["status"] = 1;
                } else {
                    //$echo= "Already Exist";
                    $exist_customer = Mage::getModel("customer/customer");
                    $exist_customer->setWebsiteId($websiteId);
                    $exist_customer->setCurrentStore($store);
                    $exist_customer->loadByEmail($email);
                    $res["id"]     = $exist_customer->getId();
                    $res["status"] = 1;

                    //$echo= "An error occured while saving customer";
                }
            }
            //$echo= 'Phase 3';
        }
        catch (Exception $e) {

            //$echo= "Already Exist Exception";
            try {
                $exist_customer = Mage::getModel("customer/customer");
                $exist_customer->setWebsiteId($websiteId);

                $exist_customer->setCurrentStore($store);
                $exist_customer->loadByEmail($email);

                $res["id"]     = $exist_customer->getId();
                $res["status"] = 1;
            }
            catch (Exception $ex) {
                $res["id"]     = -1;
                $res["status"] = 0;
            }
        }
        return $res;
    }

    //Older API to get Product detail
     public function ws_productdetail($store_id, $service, $productid, $currentcurrencycode)
    {
        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" .$store_id."_productid".$productid."_currency".$currentcurrencycode;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        Mage::app()->setCurrentStore($store_id);
        $custom_attr       = array();
        $product           = Mage::getModel('catalog/product')->load($productid);
        $attributes        = $product->getAttributes();
	$stock = $product->getStockItem();
      //print_r($stock->getData()); die;
        //$echo= count($attributes);
        $custom_attr_count = 0;
        foreach ($attributes as $attribute) {
            if ($attribute->is_user_defined && $attribute->is_visible) {
                $attribute_value = $attribute->getFrontend()->getValue($product);
                if ($attribute_value == null || $attribute_value == "") {
                    continue;
                } else {
                    $custom_attr["data"][$custom_attr_count]["attr_code"]  = $attribute->getAttributeCode();
                    $custom_attr["data"][$custom_attr_count]["attr_label"] = $attribute->getStoreLabel($product);
                    $custom_attr["data"][$custom_attr_count]["attr_value"] = $attribute_value;
                    ++$custom_attr_count;
                }
            }
        }
        $custom_attr["total"] = $custom_attr_count;
        $res                  = array();
        $productsCollection   = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('entity_id', array(
            'in' => $productid
        ))->addAttributeToSelect('*');


        $mofluid_all_product_images = array();
        $mofluid_non_def_images     = array();
        $mofluid_product            = Mage::getModel('catalog/product')->load($productid);
        $mofluid_baseimage          = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

        foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
            $mofluid_imagecame = $mofluid_image->getUrl();
            if ($mofluid_baseimage == $mofluid_imagecame) {
                $mofluid_all_product_images[] = $mofluid_image->getUrl();
            } else {
                $mofluid_non_def_images[] = $mofluid_image->getUrl();
            }
        } //print_r($mofluid_non_def_images); die;
        $mofluid_all_product_images = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
        //get base currency from magento
        $basecurrencycode           = Mage::app()->getStore()->getBaseCurrencyCode();
        foreach ($productsCollection as $product) {
            $a          = Mage::getModel('catalog/product')->load($product->getId());
            $taxClassId = $a->getData("tax_class_id");
            $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
            $taxRate    = $taxClasses["value_" . $taxClassId];
            $b          = (($taxRate) / 100) * ($product->getFinalPrice());
            $product    = Mage::getModel('catalog/product')->load($productid);

            $all_custom_option_array = array();
            $attVal                  = $product->getOptions();
            $optStr                  = "";
            $inc                     = 0;
            $has_custom_option       = 0;  //print_r($product->getOptions()); die;
            foreach ($attVal as $optionKey => $optionVal) {//print_r($optionVal->getTitle()); die;

                $has_custom_option                                          = 1;
                $all_custom_option_array[$inc]['custom_option_name']        = $optionVal->getTitle();
                $all_custom_option_array[$inc]['custom_option_id']          = $optionVal->getId();
                $all_custom_option_array[$inc]['custom_option_is_required'] = $optionVal->getIsRequire();
                $all_custom_option_array[$inc]['custom_option_type']        = $optionVal->getType();
                $all_custom_option_array[$inc]['sort_order']                = $optionVal->getSortOrder();
                $all_custom_option_array[$inc]['all']                       = $optionVal->getData();
                if ($all_custom_option_array[$inc]['all']['default_price_type'] == "percent") {
                    $all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice() * round($all_custom_option_array[$inc]['all']['price'] * 10, 2) / 10) / 100), 2);
                    //$all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice()*$all_custom_option_array[$inc]['all']['price'])/100),2);
                } else {
                    $all_custom_option_array[$inc]['all']['price'] = number_format($all_custom_option_array[$inc]['all']['price'], 2);
                }

                $all_custom_option_array[$inc]['all']['price'] = str_replace(",", "", $all_custom_option_array[$inc]['all']['price']);
                $all_custom_option_array[$inc]['all']['price'] = strval(round($this->convert_currency($all_custom_option_array[$inc]['all']['price'], $basecurrencycode, $currentcurrencycode), 2));

                $all_custom_option_array[$inc]['custom_option_value_array'];
                $inner_inc = 0;
                foreach ($optionVal->getValues() as $valuesKey => $valuesVal) {
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['id']    = $valuesVal->getId();
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['title'] = $valuesVal->getTitle();

                    $defaultcustomprice              = str_replace(",", "", ($valuesVal->getPrice()));
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = strval(round($this->convert_currency($defaultcustomprice, $basecurrencycode, $currentcurrencycode), 2));

                    //$all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = number_format($valuesVal->getPrice(),2);
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price_type'] = $valuesVal->getPriceType();
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sku']        = $valuesVal->getSku();
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sort_order'] = $valuesVal->getSortOrder();
                    if ($valuesVal->getPriceType() == "percent") {

                        $defaultcustomprice                                                              = str_replace(",", "", ($product->getFinalPrice()));
                        $customproductprice                                                              = strval(round($this->convert_currency($defaultcustomprice, $basecurrencycode, $currentcurrencycode), 2));
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = str_replace(",", "", round((floatval($customproductprice) * floatval(round($valuesVal->getPrice(), 1)) / 100), 2));
                        //$all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = number_format((($product->getPrice()*$valuesVal->getPrice())/100),2);
                    }
                    $inner_inc++;
                }
                $inc++;
            }
           $_associatedProductsArray[] = array();

        if ($product->getTypeId() == 'grouped') {
            $i = 0;
            $_associatedProducts = $product->getTypeInstance(true)->getAssociatedProducts($product);
            foreach ($_associatedProducts as $_associatedProduct) {//print_r($_associatedProduct['thumbnail']);die;
		$thumbnail_image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' .$_associatedProduct['thumbnail'];
//print_r($thumbnail_image); die;
//print_r($_associatedProduct['stock_item']['qty']); die;
                $group_prices[] = $_associatedProduct->getPrice();
                $_associatedProductsArray[$i]["product_Id"] = $_associatedProduct->getEntityId();
                $_associatedProductsArray[$i]["sku"] = $_associatedProduct->getSku();
                $_associatedProductsArray[$i]["product_name"] = $_associatedProduct->getName();
                $_associatedProductsArray[$i]["category"] = $_associatedProduct->getCategoryIds(); //'category';
                $_associatedProductsArray[$i]["image"] = $thumbnail_image;
                $_associatedProductsArray[$i]["url"] = $_associatedProduct->getProductUrl();
                $_associatedProductsArray[$i]["description"] = $_associatedProduct->getDescription();
                $_associatedProductsArray[$i]["shortdes"] = $_associatedProduct->getShortDescription();
                  //$_associatedProductsArray[$i]["max_allowed_quantity"]  = $this->scopeConfig->getValue("cataloginventory/item_options/max_sale_qty",\Magento\Store\Model\ScopeInterface::SCOPE_STORE); //die('hwllo');
                $_associatedProductsArray[$i]["quantity"] = $_associatedProduct['stock_item']['qty'];//$stock->getQty();
                $_associatedProductsArray[$i]["visibility"] = $_associatedProduct->isVisibleInSiteVisibility(); //getVisibility();
                $_associatedProductsArray[$i]["type"] = $_associatedProduct->getTypeID();
                $_associatedProductsArray[$i]["weight"] = $_associatedProduct->getWeight();
                $_associatedProductsArray[$i]["status"] = $_associatedProduct->getStatus();
                $_associatedProductsArray[$i]["isInStock"] = $_associatedProduct['stock_item']['is_in_stock'];//$stock->getIsInStock();
                $_associatedProductsArray[$i]["maxsQty"] = $stock->getMaxSaleQty();
                $_associatedProductsArray[$i]["currency"] = Mage::app()->getLocale()->currency(Mage::app()->getStore($store_id)->getBaseCurrencyCode())->getSymbol();

                $defaultprice = str_replace(",", "", ($_associatedProduct->getPrice()));
                $discountprice = str_replace(",", "", number_format($_associatedProduct->getFinalPrice(), 2));
                //  $discountprice = str_replace(",","",($product->getFinalPrice()));

                $_associatedProductsArray[$i]["discount"] = strval(round($this->convert_currency($discountprice, $basecurrencycode, $currentcurrencycode), 2));


                //$defaultshipping = $scopeConfig->getValue('carriers/flatrate/price', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                //$_associatedProductsArray[$i]["shipping"] = strval(round($this->convert_currency($defaultshipping, $basecurrencycode, $currentcurrencycode), 2));

                $defaultsprice = str_replace(",", "", ($_associatedProduct->getSpecialprice()));


                // Get the Special Price
                $specialprice = $_associatedProduct->getSpecialPrice();
                // Get the Special Price FROM date
                $specialPriceFromDate = $_associatedProduct->getSpecialFromDate();
                // Get the Special Price TO date
                $specialPriceToDate = $_associatedProduct->getSpecialToDate();
                // Get Current date
                $today = time();

                if ($specialprice) {
                    if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                        $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                    } else {
                        $specialprice = 0;
                    }
                } else {
                    $specialprice = 0;
                }



                if (floatval($discountprice)) {
                    if (floatval($discountprice) < floatval($defaultprice)) {
                        $defaultprice = floatval($discountprice);
                    }
                }

                /* Added by Mofluid team to resolve spcl price issue in 1.17 */
                $defaultprice = number_format($_associatedProduct->getPrice(), 2, '.', '');
                $specialprice = number_format($_associatedProduct->getFinalPrice(), 2, '.', '');
                if ($defaultprice == $specialprice)
                    $specialprice = number_format(0, 2, '.', '');


                $_associatedProductsArray[$i]["price"] = number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');

                $_associatedProductsArray[$i]["sprice"] = number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
                $_associatedProductsArray[$i]["tax"] = number_format($b, 2);
                $_associatedProductsArray[$i]["finalPrice"] = $_associatedProduct->getFinalPrice();
                //$min[] = number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
                // $_associatedProductsArray[$i]["minprice"]  = $_associatedProduct->getMinimalPrice();
                //$_associatedProductsArray[$i]["price"]    =  number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
                //$_associatedProductsArray[$i]["sprice"]   = number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
                // $_associatedProductsArray[$i]["tax"]      = number_format($b, 2);
                $i++;
            }
        }
        sort($group_prices);
        $min_price = $group_prices[0];
            $res["id"]          = $product->getId();
            $res["sku"]         = $product->getSku();
            $res["name"]        = $product->getName();
            $res["category"]    = $product->getCategoryIds(); //'category';
            $res["image"]       = $mofluid_all_product_images; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/catalog/product'.$product->getImage();
            $res["url"]         = $product->getProductUrl();
            $res["description"] = $product->getDescription();
            $res["shortdes"]    = $product->getShortDescription();
            $res["quantity"]    = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productid)->getQty(); //$product->getQty();
            $res["visibility"]  = $product->isVisibleInSiteVisibility(); //getVisibility();
            $res["type"]        = $product->getTypeID();
	    $res["groupdata"] = $_associatedProductsArray;
            $res["weight"]      = $product->getWeight();
            $res["status"]      = $product->getStatus();
            $res["minprice"] = $min_price;
            //convert price from base currency to current currency
            $res["currencysymbol"] = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();

            //$defaultprice = str_replace(",","",($product->getPrice()));
            /*    $tax_type = Mage::getStoreConfig('tax/calculation/price_includes_tax');
            $product = Mage::getModel('catalog/product')->load($product->getId());
            $taxClassId = $product->getData("tax_class_id");
            $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
            $taxRate = $taxClasses["value_" . $taxClassId];
            //$tax_price = (($taxRate)/100) *  ($_product->getPrice());
            $tax_price = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
            */

            /*
            if ($tax_type == 0) {
            $defaultprice = str_replace(",", "", number_format($product->getPrice(), 2));
            //$discountprice = str_replace(",","",number_format($product->getFinalPrice(),2));
            } else {
            // $discountprice = str_replace(",","",number_format(($product->getFinalPrice()-$tax_price),2));
            $defaultprice = str_replace(",", "", number_format(($product->getPrice() - $tax_price), 2));
            }

            */
            $defaultprice  = str_replace(",", "", ($product->getPrice()));
            $discountprice = str_replace(",", "", number_format($product->getFinalPrice(), 2));
            //  $discountprice = str_replace(",","",($product->getFinalPrice()));

            $res["discount"] = strval(round($this->convert_currency($discountprice, $basecurrencycode, $currentcurrencycode), 2));


            $defaultshipping = Mage::getStoreConfig('carriers/flatrate/price');
            $res["shipping"] = strval(round($this->convert_currency($defaultshipping, $basecurrencycode, $currentcurrencycode), 2));

            $defaultsprice = str_replace(",", "", ($product->getSpecialprice()));


            // Get the Special Price
            $specialprice         = Mage::getModel('catalog/product')->load($product->getId())->getSpecialPrice();
            // Get the Special Price FROM date
            $specialPriceFromDate = Mage::getModel('catalog/product')->load($product->getId())->getSpecialFromDate();
            // Get the Special Price TO date
            $specialPriceToDate   = Mage::getModel('catalog/product')->load($product->getId())->getSpecialToDate();
            // Get Current date
            $today                = time();

            if ($specialprice) {
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                    $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                } else {
                    $specialprice = 0;
                }
            } else {
                $specialprice = 0;
            }
            //tax price for special price
            /*  $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
            if ($tax_type == 0) {
            $specialprice = $specialprice;
            } else {
            $specialprice = $specialprice - $tax_price_for_special;
            } */


            if (floatval($discountprice)) {
                if (floatval($discountprice) < floatval($defaultprice)) {
                    $defaultprice = floatval($discountprice);
                }
            }

            /*Added by Mofluid team to resolve spcl price issue in 1.17*/
           // $defaultprice =  number_format($_product->getPrice(), 2, '.', '');
           // $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
        //    if($defaultprice == $specialprice)
          //      $specialprice = number_format(0, 2, '.', '');


            $res["price"]    = "0";// number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
            $res["sprice"]   = "0";//number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
            $res["tax"]      = number_format($b, 2);
            $res["tax_type"] = $tax_type;

            $res["has_custom_option"] = $has_custom_option;
            if ($has_custom_option) {
                $res["custom_option"] = $all_custom_option_array;
            }
        }
        $res["custom_attribute"] = $custom_attr;
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($res);
    }


    //implemented by saddam
    public function getProductDetails($storeId,$currencyCode,$productId){
		$storeId=(int)$storeId;
		$productId=(int)$productId;
		Mage::app()->setCurrentStore($storeId);
		$product=Mage::getModel('catalog/product')->load($productId);
		$res=array();
		$id=$product->getId();
		if(!$id)
		return $res;
		$sku=$product->getSku();
		$name=$product->getName();
		$url=(string)$product->getProductUrl();
		$description=$product->getDescription();
		$short_description=$product->getShortDescription();
		$type=$product->getTypeID();
		$currency_symbol=Mage::app()->getLocale()->currency($currencyCode)->getSymbol();
		$price=(float)number_format($product->getPrice(), 2, '.', '');
		$finalPrice=(float)number_format($product->getFinalPrice(), 2, '.', '');
		$minPrice=$maxPrice=$finalPrice;
		$image= (string)Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200);
		$quantity=(int)$product->getStockItem()->getQty();
		$isInStock=(bool)$product->getStockItem()->getIsInStock();
		$hasCustomOptions=(bool)$product->getData('has_options');
		$res['prod_id']=$productId;
		$res['sku']=$sku;
		$res['name']=$name;
		$res['description']=$description;
		$res['shortdes']=$short_description;
		$res['type']=$type;
		$res['img']=(string)$image;
                $res['url']=$url;
		$res['currencysymbol']=$currency_symbol;
		$res['has_custom_option']=(bool)$hasCustomOptions;
		$res['quantity']=$quantity;
		$attributes=$product->getAttributes();
		$basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();
		$attribute_res=array();
		$i=0;
		foreach ($attributes as $attribute){
			 if ($attribute->is_user_defined && $attribute->is_visible) {
				 $attribute_value = $attribute->getFrontend()->getValue($product);
				 if(!$this->is_empty($attribute_value)){
					 $attribute_res[$i]['attr_code']=$attribute->getAttributeCode();
					 $attribute_res[$i]['attr_label']=$attribute->getStoreLabel($product);
					 $attribute_res[$i]['attr_value']= $attribute_value;
					 $i=$i+1;
					 }
				 }
			}
		$attribute_res['size']=(int)$i;
		$res['custom_attribute']=$attribute_res;
		//if($type=='simple'){
			$res['is_in_stock']=$isInStock;
			$res['price']=$price;
			$res['sprice']=$finalPrice;
			//}

		if($type=='configurable'){
			$config_option=array();
			$productAttributeOptions = $product->getTypeInstance(true)->getConfigurableAttributesAsArray($product);
						foreach ($productAttributeOptions as $productAttribute) {
						$config_option[] = $productAttribute['label'];

					 }
			 $defaultsprice = str_replace(",", "", ($product->getSpecialprice()));
			$res1 =$this->configurable($product,$currencycode,$defaultsprice,$basecurrencycode,$storeId);
			$res = array_merge($res, $res1);
			$res['config_option'] = $config_option;
			}

	        if($type== 'downloadable'){
            $down_data = array();
            $down_data["links_title"] = $product->getLinksTitle();
            $down_data["links_purchased_separately"] = $product->getLinksPurchasedSeparately();
            $down_data["links_exist"] =$product->getLinksExist();
            $links = Mage::getModel('downloadable/link')
                        ->getCollection()->addTitleToResult()
                        ->addFieldToFilter('product_id',array('eq'=>$product->getId()));
            $link_data = array();
            $count = 0;
            foreach($links as $link){
                $link_data[$count] = $link->getData();
                $count++;
            }
            $down_data['links_data'] = $link_data;
            $res["downloadable_pro_data"] = $down_data;
        }
		return $res;
		}



    /*   * ******************************************************************************************************************************** */
      public function ws_productdetailDescription($store_id, $service, $productid, $currentcurrencycode)
    {
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store_id;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
         Mage::app()->setCurrentStore($store_id);
        $custom_attr       = array();
        $product           = Mage::getModel('catalog/product')->load($productid);
        $attributes        = $product->getAttributes();
        $custom_attr_count = 0;
        foreach ($attributes as $attribute) {

            if ($attribute->is_user_defined && $attribute->is_visible) {
			//	$echo= "<pre>";print_r("hello"); die;
                $attribute_value = $attribute->getFrontend()->getValue($product);
                if ($attribute_value == null || $attribute_value == "") {
                    continue;
                } else {
                    $custom_attr["data"][$custom_attr_count]["attr_code"]  = $attribute->getAttributeCode();
                    $custom_attr["data"][$custom_attr_count]["attr_label"] = $attribute->getStoreLabel($product);
                    $custom_attr["data"][$custom_attr_count]["attr_value"] = $attribute_value;
                    ++$custom_attr_count;
                }
            }
        }
        $custom_attr["total"] = $custom_attr_count;
        $res                  = array();
        $productsCollection   = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('entity_id', array(
            'in' => $productid
        ))->addAttributeToSelect('*');
        $basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();
        foreach ($productsCollection as $product) {
            $a          = Mage::getModel('catalog/product')->load($product->getId());
            $taxClassId = $a->getData("tax_class_id");
            $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
            $taxRate    = $taxClasses["value_" . $taxClassId];
            $b          = (($taxRate) / 100) * ($product->getPrice());
            $product    = Mage::getModel('catalog/product')->load($productid);
            $all_custom_option_array = array();
            $attVal                  = $product->getOptions();
            $optStr                  = "";
            $inc                     = 0;
            $has_custom_option       = 0;
            foreach ($attVal as $optionKey => $optionVal) {
                $has_custom_option                                          = 1;
                $all_custom_option_array[$inc]['custom_option_name']        = $optionVal->getTitle();
                $all_custom_option_array[$inc]['custom_option_id']          = $optionVal->getId();
                $all_custom_option_array[$inc]['custom_option_is_required'] = $optionVal->getIsRequire();
                $all_custom_option_array[$inc]['custom_option_type']        = $optionVal->getType();
                $all_custom_option_array[$inc]['sort_order']                = $optionVal->getSortOrder();
                $all_custom_option_array[$inc]['all']                       = $optionVal->getData();
                if ($all_custom_option_array[$inc]['all']['default_price_type'] == "percent") {
                    $all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice() * round($all_custom_option_array[$inc]['all']['price'] * 10, 2) / 10) / 100), 2);
                    //$all_custom_option_array[$inc]['all']['price'] = number_format((($product->getFinalPrice()*$all_custom_option_array[$inc]['all']['price'])/100),2);
                } else {
                    $all_custom_option_array[$inc]['all']['price'] = number_format($all_custom_option_array[$inc]['all']['price'], 2);
                }
                $all_custom_option_array[$inc]['all']['price'] = str_replace(",", "", $all_custom_option_array[$inc]['all']['price']);
                $all_custom_option_array[$inc]['all']['price'] = strval(round($this->convert_currency($all_custom_option_array[$inc]['all']['price'], $basecurrencycode, $currentcurrencycode), 2));
                $all_custom_option_array[$inc]['custom_option_value_array'];
                $inner_inc = 0;
                foreach ($optionVal->getValues() as $valuesKey => $valuesVal) {
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['id']    = $valuesVal->getId();
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['title'] = $valuesVal->getTitle();
                    $defaultcustomprice                                                              = str_replace(",", "", ($valuesVal->getPrice()));
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = strval(round($this->convert_currency($defaultcustomprice, $basecurrencycode, $currentcurrencycode), 2));

                    //$all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = number_format($valuesVal->getPrice(),2);
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price_type'] = $valuesVal->getPriceType();
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sku']        = $valuesVal->getSku();
                    $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['sort_order'] = $valuesVal->getSortOrder();
                    if ($valuesVal->getPriceType() == "percent") {
                        $defaultcustomprice                                                              = str_replace(",", "", ($product->getFinalPrice()));
                        $customproductprice                                                              = strval(round($this->convert_currency($defaultcustomprice, $basecurrencycode, $currentcurrencycode), 2));
                        $all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = str_replace(",", "", round((floatval($customproductprice) * floatval(round($valuesVal->getPrice(), 1)) / 100), 2));
                        //$all_custom_option_array[$inc]['custom_option_value_array'][$inner_inc]['price'] = number_format((($product->getPrice()*$valuesVal->getPrice())/100),2);
                    }
                    $inner_inc++;
                }
                $inc++;
            }
            $productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200);
            if ($product->getStockItem()->getIsInStock()) {

   					$stock = 1;
   			}else{
				$res["is_in_stock"] = 0;

				$stock = 0;
			}
            //convert price from base currency to current currency
           // $res["currencysymbol"] = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
            $currencysymbol = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
            //$defaultprice = str_replace(",","",($product->getPrice()));
            $tax_type   = Mage::getStoreConfig('tax/calculation/price_includes_tax');
            $product    = Mage::getModel('catalog/product')->load($product->getId());
            $taxClassId = $product->getData("tax_class_id");
            $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
            $taxRate    = $taxClasses["value_" . $taxClassId];
            //$tax_price = (($taxRate)/100) *  ($_product->getPrice());
            $tax_price  = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
            $discountprice = str_replace(",","",number_format($product->getFinalPrice(),2));
            if ($tax_type == 0) {
                $defaultprice = str_replace(",", "", number_format($product->getPrice(), 2));
                //$discountprice = str_replace(",","",number_format($product->getFinalPrice(),2));
            } else {
                // $discountprice = str_replace(",","",number_format(($product->getFinalPrice()-$tax_price),2));
                $defaultprice = str_replace(",", "", number_format(($product->getPrice() - $tax_price), 2));
            }
            $discount = strval(round($this->convert_currency($discountprice, $basecurrencycode, $currentcurrencycode), 2));
            $defaultshipping = Mage::getStoreConfig('carriers/flatrate/price');
            //$res["shipping"] = strval(round($this->convert_currency($defaultshipping, $basecurrencycode, $currentcurrencycode), 2));
            $shipping = strval(round($this->convert_currency($defaultshipping, $basecurrencycode, $currentcurrencycode), 2));
            $defaultsprice = str_replace(",", "", ($product->getSpecialprice()));
            // Get the Special Price
            $specialprice         = $product->getSpecialPrice();
            // Get the Special Price FROM date
            $specialPriceFromDate =$product->getSpecialFromDate();
            // Get the Special Price TO date
            $specialPriceToDate   = $product->getSpecialToDate();
            // Get Current date
            $today                = time();
            if ($specialprice) {
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                    $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                } else {
                    $specialprice = 0;
                }
            } else {
                $specialprice = 0;
            }
            //tax price for special price
            $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
            if ($tax_type == 0) {
                $specialprice = $specialprice;
            } else {
                $specialprice = $specialprice - $tax_price_for_special;
            }
            if ($specialprice == 0) {
                if (floatval($discountprice)) {
                    if (floatval($discountprice) < floatval($defaultprice)) {
                        $defaultprice = floatval($discountprice);
                    }
                }
            }
            $defaultprice =  number_format($product->getPrice(), 2, '.', '');
            $specialprice =  number_format($product->getFinalPrice(), 2, '.', '');
            if($defaultprice == $specialprice)
              $specialprice = number_format(0, 2, '.', '');
            $price   =  number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
            $sprice  = number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', '');
            $tax      = number_format($b, 2);
            $tax_type = $tax_type;
           //$res["has_custom_option"] = $has_custom_option;
            $has_custom_option = $has_custom_option;
            $custom_option = $all_custom_option_array;
            if ($has_custom_option) {
                //$res["custom_option"] = $all_custom_option_array;
                 $custom_option = $all_custom_option_array;
            }    $product_data=$product;
            $config_option = array();

				$qty=(int)$product->getStockItem()->getQty();
				if($qty == 0){
					$qty =strval(round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($productid)->getQty(), 2));
					}
			// Code wriiten to check manage stock status
			 $product_stock= $this->getProductStock($productid);

			if ($product_stock["manage_stock"]==0){
			$qty=5000;
			}
			$price=number_format($price, 3, '.', '');
			$sprice=number_format($sprice, 3, '.', '');
			if($sprice==0.0 || $price==$sprice)
			{
				$sprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($product,$product->getPrice());
				if(is_null($sprice))
				$sprice = 0.0;
				$sprice=number_format($sprice, 3, '.', '');
			}
			$res = array(
				  'sku'       => $product->getSku(),

				  'name'      => $product->getName(),
				   'category'	=> $product->getCategoryIds(),
				  'url'        => $product->getProductUrl(),
				   'description'=> $product->getDescription(),
				   //~ //print_r($res);die;
				   'shortdes'   => $product->getShortDescription(),
				   'quantity'   => (string)$qty,
				   'visibility' => $product->getProductUrl(),
				   'type'       => $product->getTypeID(),
				   'weight'     => $product->getWeight(),
				   'status'   	=> $product->getStatus(),
				   'img'  		=> (string)$productImage,
				    'manage_stock'=>$product_stock["manage_stock"],
				   'is_in_stock'=> $stock,
				   'currencysymbol' => $currencysymbol,
				   'discount'	=> $discount,
				   'price'      => $price,
				   'sprice'     => $sprice,
				   'tax'        => $tax,
				   'tax_type'   => $tax_type,
				   'shipping'   => $shipping,
				   'has_custom_option' => $has_custom_option,
				   'custom_option' => $custom_option,
				   'custom_attribute' => $custom_attr,
				   'config_option' =>$config_option
				  // 'config_attributes'=>$configurable_array
            );
           if (($product_data->getTypeID() == "configurable")){
					    $productAttributeOptions = $product_data->getTypeInstance(true)->getConfigurableAttributesAsArray($product_data);
						foreach ($productAttributeOptions as $productAttribute) {
						$config_option[] = $productAttribute['label'];

					 }
					$res1 =$this->configurable($product,$currentcurrencycode,$defaultsprice,$basecurrencycode,$store_id);
					$res = array_merge($res, $res1);
					$product_data = Mage::getModel('catalog/product')->load($product->getId());
					$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product_data);
					$simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
					$qty =0;
					foreach ($simple_collection as $product1) {
					$qty = $qty+(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product1->getId())->getQty());
					}
			     }

			  else {
					$configurable_array=array();
				} //print_r($res3); die;
        }
         //~ $res[][] =$res;
 $res['quantity'] = (string)$qty;
 $res['config_option'] = $config_option;
        if($product->getTypeID() == 'downloadable'){
            $down_data = array();
            $down_data["links_title"] = $product->getLinksTitle();
            $down_data["links_purchased_separately"] = $product->getLinksPurchasedSeparately();
            $down_data["links_exist"] =$product->getLinksExist();
            $links = Mage::getModel('downloadable/link')
                        ->getCollection()->addTitleToResult()
                        ->addFieldToFilter('product_id',array('eq'=>$product->getId()));
            $link_data = array();
            $count = 0;
            foreach($links as $link){
                $link_data[$count] = $link->getData();
                $count++;
            }
            $down_data['links_data'] = $link_data;
            $res["downloadable_pro_data"] = $down_data;
        }
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($res);
    }

    /**********************************configurable  **********/
 	public function configurable($_product,$currentcurrencycode,$defaultsprice,$basecurrencycode,$store)
 	{
		$product_data = Mage::getModel('catalog/product')->load($_product->getId());
					$configurable_count = 0;
					$configurable_array= array();
					$productAttributeOptions = $product_data->getTypeInstance(true)->getConfigurableAttributes($product_data);

					$conf = Mage::getModel('catalog/product_type_configurable')->setProduct($product_data);
					$simple_collection = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
					$relation_count               = 0;
				foreach ($simple_collection as $product) {
					$data = array();
					 $configurable_count = 0;
					foreach ($productAttributeOptions as $attribute) {

						$currentcurrencycode;
						$productAttribute                                              = $attribute->getProductAttribute();
                        $productAttributeId                                            = $productAttribute->getId();
                        $attributeValue                                                = $product->getData($productAttribute->getAttributeCode());
                        $attributeLabel                                                = $product->getData($productAttribute->getValue());
							$total = Count($productAttributeOptions);
						$config_option_attribute =	$this->ws_get_configurable_option_attributes($attributeValue, $attribute->getLabel(), $_product->getId(), $currentcurrencycode,$store);
						$data[$attribute->getLabel()]= $config_option_attribute ;
						$configurable_array1[$configurable_count]["data"]  = $config_option_attribute ;
                       $configurable_relation=array();
						try {
                            $configurable_curr_arr = (array) $configurable_array1[$configurable_count]["data"];
                            if (array_key_exists("label",$configurable_curr_arr) && !empty($configurable_relation) && count($configurable_relation)>=$relation_count && $configurable_relation[$relation_count]) {
                                $configurable_relation[$relation_count] = $configurable_relation[$relation_count] . ',' . str_replace(',', '', str_replace(' ','', $configurable_curr_arr["label"]));
                            } else { if(array_key_exists("label",$configurable_curr_arr))
                                $configurable_relation[$relation_count] = str_replace(',', '', str_replace(' ','', $configurable_curr_arr["label"]));
                            }
                        }
                        catch (Exception $err) {
							//die("Hello".$err->getMessage());
                            echo 'Error : ' . $err->getMessage();
                            //$this->getResponse()->setBody($echo);
                        }
                        $configurable_count++;
				}   $relation_count++;
				$res = array('config_relation'=>$configurable_relation);
			$specialprice= $_product->getSpecialPrice();	
        if($specialprice==0.0)
        $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());
				//print_r($configurable_relation); die;
				$configurable_array[] = array(
							"prod_id"=>$product->getId(),
							"sku"    =>$product->getSku(),
							"name"          => $product->getName(),
							"spclprice"     => number_format($specialprice, 3),
							"price"        =>number_format($product->getPrice(), 3),
							"is_in_stock" =>(bool) $product->getStockItem()->getIsInStock(),
							"stock_quantity" => strval(round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty(), 2)),
							"type"			=>  $product->getTypeID(),
				                        "img"=>(string) Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200),
							"data"          => $data

						);
			}//print_r($configurable_array); die;
			  //die;
			  $res['config_attributes']=$configurable_array;
			 return ($res);
	}

    /*   * ************************************************************************************************************************** */

    //Older API to get Product detail
    public function ws_productdetailImage($store_id, $service, $productid, $currentcurrencycode)
    {
        $cache     = Mage::app()->getCache();
        $cache_key = "mofluid_" . $service . "_store" . $store_id . "_productid_img" . $productid . "_currency" . $currentcurrencycode;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));

        $custom_attr       = array();
        $product           = Mage::getModel('catalog/product')->load($productid);
        $attributes        = $product->getAttributes();
        //$echo= count($attributes);
        $custom_attr_count = 0;

        $res                = array();
        $productsCollection = Mage::getModel('catalog/product')->getCollection()->addAttributeToFilter('entity_id', array(
            'in' => $productid
        ))->addAttributeToSelect('*');


        $mofluid_all_product_images = array();
        $mofluid_non_def_images     = array();
        $mofluid_product            = Mage::getModel('catalog/product')->load($productid);
        $mofluid_baseimage          = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

        foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
            $mofluid_imagecame = $mofluid_image->getUrl();
            if ($mofluid_baseimage == $mofluid_imagecame) {
                $mofluid_all_product_images[] = $mofluid_image->getUrl();
            } else {
                $mofluid_non_def_images[] = $mofluid_image->getUrl();
            }
        }
        $mofluid_all_product_images = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
        //get base currency from magento
        $basecurrencycode           = Mage::app()->getStore()->getBaseCurrencyCode();
        foreach ($productsCollection as $product) {
            $res["id"]     = $product->getId();
            $res["image"]  = $mofluid_all_product_images; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/catalog/product'.$product->getImage();
            $res["status"] = $product->getStatus();
        }
        $cache->save(json_encode($res), $cache_key, array(
            "mofluid"
        ), $this->CACHE_EXPIRY);
        return ($res);
    }

    /*   * ************************************************************************************************************************** */

    //Latest Method to get product detail
    public function ws_productinfo($store_id, $service, $productid, $currentcurrencycode)
    {
        $product = new Mofluidapi118_Products($store_id, $service, $productid, $currentcurrencycode);
       $product->getCompleteProductInfo();
	//  $echo= "<pre>"; print_r($product->getCompleteProductInfo()); die;
        return $product->getCompleteProductInfo();
    }

    public function ws_currency($store_id, $service)
    {
        $cache     = Mage::app()->getCache();
        $cache_key = "mofluid_currency_store" . $store_id;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));
        $res                    = array();
        $res["currentcurrency"] = Mage::app()->getStore($storeID)->getCurrentCurrencyCode();
        $res["basecurrency"]    = Mage::app()->getStore($storeID)->getBaseCurrencyCode();
        $res["currentsymbol"]   = Mage::app()->getLocale()->currency($res["currentcurrency"])->getSymbol();
        $res["basesymbol"]      = Mage::app()->getLocale()->currency($res["basecurrency"])->getSymbol();
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($res);
    }

    public function ws_setaddress($store, $service, $customerId, $Jaddress, $user_mail, $saveaction)
    {
        //----------------------------------------------------------------------
        if ($customerId == "notlogin") {
            $result                 = array();
            $result['billaddress']  = 1;
            $result['shippaddress'] = 1;
        } else {

            $customer               = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($user_mail);
            $Jaddress               = str_replace(" ", "+", $Jaddress);
            $address                = json_decode(base64_decode($Jaddress));
            $billAdd                = $address->billing;
            $shippAdd               = $address->shipping;
            $result                 = array();
            $result['billaddress']  = 0;
            $result['shippaddress'] = 0;
            $_bill_address          = array(
                'firstname' => $billAdd->firstname,
                'lastname' => $billAdd->lastname,
                'street' => array(
                    '0' => $billAdd->street
                ),
                'city' => $billAdd->city,
                'region_id' => '',
                'region' => $billAdd->region,
                'postcode' => $billAdd->postcode,
                'country_id' => $billAdd->country,
                'telephone' => $billAdd->phone
            );
            $_shipp_address         = array(
                'firstname' => $shippAdd->firstname,
                'lastname' => $shippAdd->lastname,
                'street' => array(
                    '0' => $shippAdd->street
                ),
                'city' => $shippAdd->city,
                'region_id' => '',
                'region' => $shippAdd->region,
                'postcode' => $shippAdd->postcode,
                'country_id' => $shippAdd->country,
                'telephone' => $shippAdd->phone
            );
            if ($saveaction == 1 || $saveaction == "1") {
                $billAddress = Mage::getModel('customer/address');
                $billAddress->setData($_bill_address)->setCustomerId($customerId)->setIsDefaultBilling('1')->setSaveInAddressBook('1');

                $shippAddress = Mage::getModel('customer/address');
                $shippAddress->setData($_shipp_address)->setCustomerId($customerId)->setIsDefaultShipping('1')->setSaveInAddressBook('1');
            } else {
                $billAddress  = Mage::getModel('customer/address');
                $shippAddress = Mage::getModel('customer/address');
                if ($defaultBillingId = $customer->getDefaultBilling()) {
                    $billAddress->load($defaultBillingId);
                    $billAddress->addData($_bill_address);
                } else {
                    $billAddress->setData($_bill_address)->setCustomerId($customerId)->setIsDefaultBilling('1')->setSaveInAddressBook('1');
                }
                if ($defaultShippingId = $customer->getDefaultShipping()) {
                    $shippAddress->load($defaultShippingId);
                    $shippAddress->addData($_shipp_address);
                } else {
                    $shippAddress->setData($_shipp_address)->setCustomerId($customerId)->setIsDefaultShipping('1')->setSaveInAddressBook('1');
                }
            }

            try {

                if (count($billAdd) > 0) {
                    if ($billAddress->save())
                        $result['billaddress'] = 1;
                }
                if (count($shippAdd) > 0) {
                    if ($shippAddress->save())
                        $result['shippaddress'] = 1;
                }
            }
            catch (Exception $ex) {
                //Zend_Debug::dump($ex->getMessage());
            }
        }
        return $result;

        //---------------------------------------------------------------------
    }

    public function rootCategoryData($store, $service)
    {
        $res               = array();
        $res["categories"] = $this->ws_category($store, "category");
        return $res;
    }

    public function getStore($store, $service, $currentcurrencycode)
    {
        //Cache data for app
        try {

            $cache_data = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);

            if ($cache_data['mofluid_cs_accountid'] == '') {

                $cache_array = array(
                    'status' => $cache_data['mofluid_cs_status'],
                    'cache_time' => 15
                );
            } else {
                $cache_array = array(
                    'status' => $cache_data['mofluid_cs_status'],
                    'cache_time' => $cache_data['mofluid_cs_accountid']
                );
            }
        }
        catch (Exception $ex) {

        }
    }

    public function ws_checkout($store, $service, $theme, $currentcurrencycode)
    {

        $res             = array();
        $checkout_type   = Mage::getStoreConfig('checkout/options/guest_checkout');
        $res['checkout'] = $checkout_type;
        return $res;

    }
    
    

    public function ws_storedetails($store, $service, $theme, $currentcurrencycode)
    {
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        $res       = array();


        $date        = Mage::app()->getLocale()->date();
        $timezone    = $date->getTimezone();
        $offset      = $date->getGmtOffset($date->getTimezone());
        $offset_hour = (int) ($date->getGmtOffset($date->getTimezone()) / 3600);
        $offset_min  = ($date->getGmtOffset($date->getTimezone()) % 3600) / 60;

        // $modern_theme_data = Mage::getModel('mofluid_thememofluidmodern/mofluid_themes_core')->load(11);


        //get data from mofluid_themes table

        $resource       = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query = 'SELECT * FROM '.$resource->getTableName('mofluid_themes');
        $results        = $readConnection->fetchAll($query);

        foreach ($results as $gdata) {
            if ($gdata['mofluid_theme_code'] == 'modern') {
                $google_client_id = $gdata['google_ios_clientid'];
                $google_login     = $gdata['google_login'];

                $cms_pages = $gdata['cms_pages'];
                $about_us = $gdata['about_us'];
                $term_condition = $gdata['term_condition'];
                $privacy_policy = $gdata['privacy_policy'];
                $return_privacy_policy = $gdata['return_privacy_policy'];
                $tax_flag = $gdata['tax_flag'];



            }
        }

        /**
         * Print out the results
         */


        //Getting data from mofluid cache
        try {

            $cache_data = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);

            if ($cache_data['mofluid_cs_accountid'] == '') {

                $cache_array = array(
                    'status' => $cache_data['mofluid_cs_status'],
                    'cache_time' => 15
                );
            } else {
                $cache_array = array(
                    'status' => $cache_data['mofluid_cs_status'],
                    'cache_time' => $cache_data['mofluid_cs_accountid']
                );
            }
        }
        catch (Exception $ex) {

        }



        /* Get Guest Checkout status */
        //$checkout_type = Mage::getStoreConfig('checkout/options/guest_checkout');


        // $echo= "<pre>"; print_r($cache_array); exit;
        $mofluid_theme_data = array();
        Mage::app()->setCurrentStore($store);
        try {
            $res["store"]                        = array();
            $res["store"]                        = Mage::app()->getStore($store)->getData();
            $res["store"]["frontname"]           = Mage::app()->getStore($store)->getFrontendName(); //getLogoSrc()
            $res["store"]["cache_setting"]       = $cache_array;
            $res["store"]["logo"]                = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/default/default/' . Mage::getStoreConfig('design/header/logo_src');
            $res["store"]["banner"]              = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/default/default/images/banner.png';
            $res["store"]["adminname"]           = Mage::getStoreConfig('trans_email/ident_sales/name');
            $res["store"]["email"]               = Mage::getStoreConfig('trans_email/ident_sales/email');
            $res["store"]["checkout"]            = Mage::getStoreConfig('trans_email/ident_sales/email');
            $res["store"]["google_ios_clientid"] = $google_client_id;
            $res["store"]["google_login_flag"]   = $google_login;

            $res["store"]["cms_pages"]   = $cms_pages;
            $res["store"]["about_us"]   = $about_us;
            $res["store"]["term_condition"]   = $term_condition;
            $res["store"]["privacy_policy"]   = $privacy_policy;
            $res["store"]["return_privacy_policy"]   = $return_privacy_policy;
            $res["store"]["tax_flag"]   = $tax_flag;

            $res["timezone"]                    = array();
            $res["timezone"]["name"]            = $timezone;
            $res["timezone"]["offset"]          = array();
            $res["timezone"]["offset"]["value"] = $offset;
            $res["timezone"]["offset"]["hour"]  = $offset_hour;
            $res["timezone"]["offset"]["min"]   = $offset_min;

            $res["url"]            = array();
            $res["url"]["current"] = Mage::helper('core/url')->getCurrentUrl();
            $res["url"]["media"]   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
            $res["url"]["skin"]    = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
            $res["url"]["js"]      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
            $res["url"]["root"]    = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
            $res["url"]["store"]   = Mage::helper('core/url')->getHomeUrl();

            $res["currency"]                   = array();
            $res["currency"]["base"]["code"]   = Mage::app()->getStore($store)->getBaseCurrencyCode();
            $res["currency"]["base"]["name"]   = Mage::app()->getLocale()->currency(Mage::app()->getStore($store)->getBaseCurrencyCode())->getName();
            $res["currency"]["base"]["symbol"] = Mage::app()->getLocale()->currency(Mage::app()->getStore($store)->getBaseCurrencyCode())->getSymbol();

            $res["currency"]["current"]["code"]        = Mage::app()->getStore($store)->getCurrentCurrencyCode();
            $res["currency"]["current"]["name"]        = Mage::app()->getLocale()->currency(Mage::app()->getStore($store)->getCurrentCurrencyCode())->getName();
            $res["currency"]["current"]["symbol"]      = Mage::app()->getLocale()->currency(Mage::app()->getStore($store)->getCurrentCurrencyCode())->getSymbol();
            $res["currency"]["allow"]                  = Mage::getStoreConfig('currency/options/allow');
            $res["configuration"]                      = array();
            $res["configuration"]["show_out_of_stock"] = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
            //  $res["categories"] = $this->ws_category($store, "category");
            $mofluid_theme_id                          = "1";
            if ($theme == null || $theme == "") {
                $theme = 'elegant';
            }
            $mofluid_elegant_config_model_settings = Mage::getModel('mofluid_thememofluidelegant/thememofluidelegant')->getCollection()->addFieldToFilter('mofluid_theme_code', $theme)->getData();
            $mofluid_theme_id                      = $mofluid_elegant_config_model_settings[0]['mofluid_theme_id'];
            $mofluid_theme_elegant_model           = Mage::getModel('mofluid_thememofluidelegant/images');
            $mofluid_theme_elegant_banner          = $mofluid_theme_elegant_model->getCollection()->addFieldToFilter('mofluid_theme_id', $mofluid_theme_id)->addFieldToFilter('mofluid_image_type', 'banner');
            $mofluid_theme_elegant_banner_all_data = $mofluid_theme_elegant_banner->setOrder('mofluid_image_sort_order', 'ASC')->getData();
            $mofluid_theme_banner_image_type       = $mofluid_elegant_config_model_settings[0]['mofluid_theme_banner_image_type'];
            if ($mofluid_theme_banner_image_type == "1") {
                foreach ($mofluid_theme_elegant_banner_all_data as $banner_key => $banner_value) {
                    try {
                        $mofluid_image_action = json_decode(base64_decode($banner_value['mofluid_image_action']));
                        if ($mofluid_image_action->base == 'product') {
                            $_products = Mage::getModel('catalog/product')->getCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store)->addAttributeToFilter('entity_id', $mofluid_image_action->id);
                            foreach ($_products as $_product) {
                                $productStatus  = $_product->getStockItem()->getIsInStock();
                                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                                if ($productStatus == 1 && $stock_quantity < 0)
                                    $productStatus == 1;
                                else
                                    $productStatus == 0;
                                break;
                            }
                            $mofluid_image_action->status         = $productStatus;
                            $banner_value['mofluid_image_action'] = base64_encode(json_encode($mofluid_image_action));
                        }
                    }
                    catch (Exception $ex) {
                        $ex->getMessage();
                        //$this->getResponse()->setBody($echo);
                    }
                    if ($banner_value['mofluid_store_id'] == $store) {
                        $mofluid_theme_elegant_banner_data[] = $banner_value;
                    } else if ($banner_value['mofluid_store_id'] == 0) {
                        $mofluid_theme_elegant_banner_data[] = $banner_value;
                    } else {
                        continue;
                    }
                }
            } else {
                foreach ($mofluid_theme_elegant_banner_all_data as $banner_key => $banner_value) {
                    try {
                        $mofluid_image_action = json_decode(base64_decode($banner_value['mofluid_image_action']));
                        if ($mofluid_image_action->base == 'product') {
                            $_products = Mage::getModel('catalog/product')->getCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store)->addAttributeToFilter('entity_id', $mofluid_image_action->id);
                            foreach ($_products as $_product) {
                                $productStatus  = $_product->getStockItem()->getIsInStock();
                                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                                if ($productStatus == 1 && $stock_quantity < 0)
                                    $productStatus == 1;
                                else
                                    $productStatus == 0;
                                break;
                            }
                            $mofluid_image_action->status         = $productStatus;
                            $banner_value['mofluid_image_action'] = base64_encode(json_encode($mofluid_image_action));
                        }
                    }
                    catch (Exception $ex) {

                    }
                    if ($banner_value['mofluid_image_isdefault'] == '1' && $banner_value['mofluid_store_id'] == $store) {
                        $mofluid_theme_elegant_banner_data[] = $banner_value;
                        break;
                    } else if ($banner_value['mofluid_image_isdefault'] == '1' && $banner_value['mofluid_store_id'] == 0) {
                        $mofluid_theme_elegant_banner_data[] = $banner_value;
                        break;
                    } else {
                        continue;
                    }
                }
                if (count($mofluid_theme_elegant_banner_data) <= 0) {
                    $mofluid_theme_elegant_banner_data[] = $mofluid_theme_elegant_banner_all_data[0]; //$banner_value;
                }
            }

            $mofluid_theme_elegant_logo      = $mofluid_theme_elegant_model->getCollection()->addFieldToFilter('mofluid_image_type', 'logo')->addFieldToFilter('mofluid_theme_id', $mofluid_theme_id);
            $mofluid_theme_elegant_logo_data = $mofluid_theme_elegant_logo->getData();

            $mofluid_theme_data["code"]            = $theme;
            $mofluid_theme_data["logo"]["image"]   = $mofluid_theme_elegant_logo_data;
            $mofluid_theme_data["logo"]["alt"]     = Mage::getStoreConfig('design/header/logo_alt');
            $mofluid_theme_data["banner"]["image"] = $mofluid_theme_elegant_banner_data;
            $res["theme"]                          = $mofluid_theme_data;




            //get google analytics
            $modules      = Mage::getConfig()->getNode('modules')->children();
            $modulesArray = (array) $modules;

            if (!empty($modulesArray['Mofluid_Ganalyticsm'])) {
                $google_analytics              = array();
                $mofluid_google_analytics      = Mage::getModel('mofluid_ganalyticsm/ganalyticsm')->load(23);
                $google_analytics["accountid"] = $mofluid_google_analytics->getData('mofluid_ga_accountid');
                $google_analytics["status"]    = $mofluid_google_analytics->getData('mofluid_ga_status');
                if (!$google_analytics["status"]) {
                    $google_analytics["status"] = 0;
                }
                $res["analytics"] = $google_analytics;
            }



            //  $cache_time=array('status'=>1 , 'cache_time'=>12);
        }
        catch (Exception $ex) {
			$ex->getMessage();	
            //$echo= $ex;
            //$this->getResponse()->setBody($echo);
        }
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return $res;
    }
   public function ws_searchFilter($store,$search_data){
		Mage::app()->setCurrentStore($store);
			   try
			   {	
					$search_condition           = array();	
					$search_condition[]['like'] = '%' . $search_data . '%';
					$show_out_of_stock          = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
					$is_in_stock_option         = $show_out_of_stock ? 0 : 1;
					$product_collection       = Mage::getResourceModel('catalog/product_collection')->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addAttributeToSelect('*')->addAttributeToFilter('name', $search_condition)->addStoreFilter($store)->addFieldToFilter('status', 1)->addAttributeToFilter('visibility', 4)->addAttributeToFilter('type_id', array(
							'in' => array(
								'configurable',
								'grouped',
								'simple',
								'downloadable'
							)
						))->addAttributeToFilter('status', array('eq' => 1))->addAttributeToFilter('is_in_stock', array(
							'in' => array(
								$is_in_stock_option,
								1
							)
						));
					$cids=array();
					$hash=array();
					$categoryIds = array();
					foreach ($product_collection as $_product) {
						$categoryIds = $_product->getCategoryIds();
						//print_r($categoryIds); echo "\n";
						foreach($categoryIds as $id) {
							if(array_key_exists($id,$hash))
							{
								if($hash[$id]==1)
								continue;
							}
							else $hash[$id] = 1;
							//~ echo '<pre>'.print_r($categoryIds); die("HERE!");
							//~ if(array_key_exists($id."",$hash))
							//~ {
							//~ if($hash[$id]!=1)
								//~ $cids[] = $id;
							//~ }
							//~ $hash[$id]=1;
						}
					}
					//foreach($hash as $key=>$value)
					//$cids[] = $key;

					return $this->getSearchFilter($store,$categoryIds);
				}
				catch(Exception $e)
				{
					echo $e->getMessage();
				}
	   }

public function ws_search($store, $service, $search_data, $curr_page, $page_size, $sortType, $sortOrder, $currentcurrencycode,$filterdata)
    {
        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store."_searchfor".$search_data;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        Mage::app()->setCurrentStore($store);
        $basecurrencycode           = Mage::app()->getStore()->getBaseCurrencyCode();
        $res                        = array();
        $show_out_of_stock          = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option         = $show_out_of_stock ? 0 : 1;
        $search_condition           = array();
        /* $all_search_word = explode(' ',$search_data);
        foreach($all_search_word as $key=>$value) {
        $search_condition[]['like'] = '%'.$value.'%';
        } */
        $search_condition[]['like'] = '%' . $search_data . '%';

        try {
            //Code to Search Product by $searchstring and get Product IDs
            $total_product_collection = Mage::getResourceModel('catalog/product_collection')->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store)->addAttributeToSelect('*')->addAttributeToFilter('name', $search_condition)->addStoreFilter($store)->addAttributeToFilter('visibility', 4)->addAttributeToFilter('type_id', array(
                'in' => array(
                    'configurable',
                    'grouped',
                    'simple',
                    'downloadable'
                )
            ))->addAttributeToFilter('status', array('eq' => 1))->addAttributeToFilter('is_in_stock', array(
                'in' => array(
                    $is_in_stock_option,
                    1
                )
            ))->load();
            $res["total"]             = count($total_product_collection);

            $product_collection       = Mage::getResourceModel('catalog/product_collection')->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addAttributeToSelect('*')->addAttributeToFilter('name', $search_condition)->addStoreFilter($store)->addFieldToFilter('status', 1)->addAttributeToFilter('visibility', 4)->addAttributeToFilter('type_id', array(
                'in' => array(
                    'configurable',
                    'grouped',
                    'simple',
                    'downloadable'
                )
            ))->addAttributeToFilter('status', array('eq' => 1))->addAttributeToSort($sortType, $sortOrder)->addAttributeToFilter('is_in_stock', array(
                'in' => array(
                    $is_in_stock_option,
                    1
                )
            ));
            //Apply filter on search
            $data=json_decode($filterdata);
			if(!$this->is_empty($data)){
			$size=count($data);
			$color_val=array();
			$size_val=array();
			$price_val=array();
			$from=9999999;
			$to=-9999999;
			for($i=0;$i<$size;$i++)
			{
				$code=$data[$i]->code;
				switch($code){
					case "color":
					   $idValues =explode(",",$data[$i]->id);
					   $id_size=count($idValues);
					for($j=0;$j<$id_size;$j++)
						 $color_val[]=(int)$idValues[$j];
					   break;
					case "size":
						 $sizeValues=explode(",",$data[$i]->id);
						 $size_val_len=count($sizeValues);
						 for($j=0;$j<$size_val_len;$j++)
							 $size_val[]=(int)$sizeValues[$j];
							 break;
					case "price":
						 $pricestr=$data[$i]->id;
						 $priceStrLen=strlen($pricestr);
						 if($pricestr[0]=='-')
							$pricestr="0".$pricestr;
						 if($pricestr[$priceStrLen-1]=='-')
						 $pricestr=$pricestr."9999999";
						 $priceValues=explode(",",$pricestr);
						 $priceValues_len=count($priceValues);
						 for($j=0;$j<$priceValues_len;$j++){
							$cur=$priceValues[$j];
							$cur_arr=explode("-",$cur);
							$cur_len=count($cur_arr);
							for($k=0;$k<$cur_len;$k++){
								$cur_val=(int)$cur_arr[$k];
								if($cur_val<$from)
								   $from=$cur_val;
								if($cur_val>$to)
								   $to=$cur_val;
								}
							 }
						 break;
				}

			}
			//apply color filter
			if(count($color_val)>0){
			$product_collection->addAttributeToFilter('color', array('in' => $color_val));
			}
			//Apply size filter
			if(count($size_val)>0){
			$product_collection->addAttributeToFilter('size', array('in'=>$size_val));
			}
			//Apply price filter
			if($from!=9999999 || $to!=-9999999){
			$product_collection->addAttributeToFilter('price', array('from'=>$from,'to'=>$to));
			}
			}
			//In last step apply pagination
			if(!$this->is_empty($curr_page) && !$this->is_empty($page_size)){
			$product_collection->setPageSize((int)$page_size);
			$product_collection->setCurPage((int)$curr_page);
			}
			$product_collection->load();

            foreach ($product_collection as $_product) {
                if (in_array($store, $_product->getStoreIds())) {
                    /*$mofluid_all_product_images = array();
                    $mofluid_non_def_images     = array();
                    $mofluid_product            = Mage::getModel('catalog/product')->load($_product->getId());
                    $mofluid_baseimage          = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();
                    foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
                        $mofluid_imagecame = $mofluid_image->getUrl();
                        if ($mofluid_baseimage == $mofluid_imagecame) {
                            $mofluid_all_product_images[0] = $mofluid_image->getUrl();
                            break;
                        } else {
                            $mofluid_non_def_images[] = $mofluid_image->getUrl();
                        }
                    }
                    $mofluid_all_product_images = array_merge($mofluid_all_product_images, $mofluid_non_def_images);*/
                    $productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);




                    //"comment by sumit" $defaultprice = str_replace(",","",number_format($_product->getFinalPrice(),2));
                    /*     $tax_type = Mage::getStoreConfig('tax/calculation/price_includes_tax');
                    $_product = Mage::getModel('catalog/product')->load($_product->getId());
                    $taxClassId = $_product->getData("tax_class_id");
                    $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                    $taxRate = $taxClasses["value_" . $taxClassId];
                    //$tax_price = (($taxRate)/100) *  ($_product->getPrice());
                    $tax_price = str_replace(",", "", number_format(((($taxRate) / 100) * ($_product->getPrice())), 2));

                    if ($tax_type == 0) {
                    $defaultprice = str_replace(",", "", number_format($_product->getPrice(), 2));
                    } else {
                    $defaultprice = str_replace(",", "", number_format(($_product->getPrice() - $tax_price), 2));
                    }

                    */
                    $defaultprice  = str_replace(",", "", number_format($_product->getPrice(), 2));
                    $defaultsprice = str_replace(",", "", number_format($_product->getSpecialprice(), 2));


                    try {
                        $custom_option_product = Mage::getModel('catalog/product')->load($_product->getId());
                        $custom_options        = $custom_option_product->getOptions();
                        $has_custom_option     = 0;
                        foreach ($custom_options as $optionKey => $optionVal) {
                            $has_custom_option = 1;
                        }
                    }
                    catch (Exception $ee) {
                        $has_custom_option = 0;
                    }
                    // Get the Special Price
                    $specialprice         = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialPrice();
                    // Get the Special Price FROM date
                    $specialPriceFromDate = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialFromDate();
                    // Get the Special Price TO date
                    $specialPriceToDate   = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialToDate();
                    // Get Current date
                    $today                = time();

                    if ($specialprice) {
                        if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                            $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                        } else {
                            $specialprice = 0;
                        }
                    } else {
                        $specialprice = 0;
                    }
                    /*
                    $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
                    if ($tax_type == 0) {
                    $specialprice = $specialprice;
                    } else {
                    $specialprice = $specialprice - $tax_price_for_special;
                    }
                    */
                    $g_flag_group1=0;
                   	$g_flag_group2=0;
                    $original_price = 0;
                    if ($_product->getTypeID() == "grouped") {
                        $grouped_productIds = $_product->getTypeInstance()->getChildrenIds($_product->getId());
						$grouped_prices     = array();
                        foreach ($grouped_productIds as $grouped_ids) {
                            foreach ($grouped_ids as $grouped_id) {
                           		$grouped_product  = Mage::getModel('catalog/product')->load($grouped_id);
                                 if($grouped_product['status']==2)
                                 $g_flag_group2++;

                                 $g_flag_group1++;
								 $grouped_prices[] = $grouped_product->getPriceModel()->getPrice($grouped_product);
                            }
                        }
                        sort($grouped_prices);
                        $original_price = strval(round($this->convert_currency($grouped_prices[0], $basecurrencycode, $currentcurrencycode), 2));
                    } 
                    else
                    {
                        $original_price = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
                    }

                    /*Added by Mofluid team to resolve spcl price issue in 1.17*/


                               //Code added by sumit
					if ($_product->getTypeID() == 'grouped') {
						$original_price = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
						$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
					  // 	$mofluid_all_product_images[0] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

					}
					else
					{
						$original_price =  number_format($_product->getPrice(), 2, '.', '');
						 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
					}
					//   $original_price =  number_format($_product->getPrice(), 2, '.', '');
					// $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
                    if($original_price == $specialprice)
                        $specialprice = number_format(0, 2, '.', '');
                        if($specialprice==0.0)
                        $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());

					/*  SUMIT KUMAR
					If product type is grouped then it will check that all child product is disable or not if
					disable then product will not show in search page
					*/
					if ($_product->getTypeID() == 'grouped') {
						if($g_flag_group2!=$g_flag_group1)
						{
							$res["data"][] = array(
							"id" => $_product->getId(),
							"name" => $_product->getName(),
							"imageurl" => (string)$productImage,
							"sku" => $_product->getSku(),
							"type" => $_product->getTypeID(),
							"hasoptions" => $has_custom_option,
							"currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
							"price" => number_format($this->convert_currency($original_price, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
							"spclprice" =>number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
							"created_date" => $_product->getCreatedAt(),
							"is_in_stock" => $_product->getStockItem()->getIsInStock(),
							"stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty()
							);
						}
					}
					else {
						 $res["data"][] = array(
							"id" => $_product->getId(),
							"name" => $_product->getName(),
							"imageurl" => (string)$productImage,
							"sku" => $_product->getSku(),
							"type" => $_product->getTypeID(),
							"hasoptions" => $has_custom_option,
							"currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
							"price" => number_format($this->convert_currency($original_price, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
							"spclprice" =>number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
							"created_date" => $_product->getCreatedAt(),
							"is_in_stock" => $_product->getStockItem()->getIsInStock(),
							"stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty()
							);
					}
                }
            }
        }
        catch (Exception $ex) {
            echo $ex->getMessage();
        }
		if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return $res;    
    }
    public function send_Password_Mail_to_NewUser($user, $pswd, $email)
    {

        //load the custom template to the email
        $emailTemplate                      = Mage::getModel('core/email_template')->loadDefault('mofluid_password');
        // it depends on the template variables
        $emailTemplateVariables             = array();
        $emailTemplateVariables['user']     = $user;
        $emailTemplateVariables['password'] = $pswd;
        $emailTemplateVariables['email']    = $email;
        $websitename                        = Mage::app()->getWebsite()->getName();
        $emailTemplate->setSenderName($websitename);
        $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email'));
        $emailTemplate->setType('html');
        $emailTemplate->setTemplateSubject($websitename . ' New Account Password');
        $emailTemplate->send($email, $websitename, $emailTemplateVariables);
    }

    public function updateOrderStatus($cust_id, $orderid, $store, $currency)
    {
        $res = array();
        try {
            $this->ws_sendorderemail($orderid);
            $res["id"]     = $orderid;
            $res["status"] = "PROCESSING";
        }
        catch (Exception $err) {
            $res["id"]      = $orderid;
            $res["status"]  = "error";
            $res["message"] = $err->getMessage();
        }
        return $res;
    }

    public function orderInfo($cust_id, $orderid, $store, $currency)
    {
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_" . $service . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
        $res              = array();
        $order            = Mage::getModel('sales/order')->loadByIncrementId($orderid);
        $shippingAddress  = $order->getShippingAddress();
        $billingAddress   = $order->getBillingAddress();
        if (is_object($shippingAddress)) {
            $shippadd = array(
                "prefix" => $shippingAddress->getPrefix(),
                "firstname" => $shippingAddress->getFirstname(),
                "lastname" => $shippingAddress->getLastname(),
                "company" => $shippingAddress->getCompany(),
                "street" => $shippingAddress->getStreetFull(),
                "region" => $shippingAddress->getRegion(),
                "city" => $shippingAddress->getCity(),
                "postcode" => $shippingAddress->getPostcode(),
                "countryid" => $shippingAddress->getCountry_id(),
                "country" => $shippingAddress->getCountry(),
                "phone" => $shippingAddress->getTelephone(),
                "email" => $shippingAddress->getEmail(),
                "shipmyid" => $flag
            );
        }
        if (is_object($billingAddress)) {
            $billadd = array(
                "prefix" => $billingAddress->getPrefix(),
                "firstname" => $billingAddress->getFirstname(),
                "lastname" => $billingAddress->getLastname(),
                "company" => $billingAddress->getCompany(),
                "street" => $billingAddress->getStreetFull(),
                "region" => $billingAddress->getRegion(),
                "city" => $billingAddress->getCity(),
                "postcode" => $billingAddress->getPostcode(),
                "countryid" => $billingAddress->getCountry_id(),
                "country" => $billingAddress->getCountry(),
                "phone" => $billingAddress->getTelephone(),
                "email" => $billingAddress->getEmail()
            );
        }
        $payment        = array();
        $payment_result = array();
        $payment        = $order->getPayment();
        try {
            $payment_result = array(
                "title" => $payment->getMethodInstance()->getTitle(),
                "code" => $payment->getMethodInstance()->getCode()
            );
            if ($payment->getMethodInstance()->getCode() == "banktransfer") {
                $payment_result["description"] = $payment->getMethodInstance()->getInstructions();
            }
        }
        catch (Exception $ex2) {

        }
        $items       = $order->getAllItems();
        $itemcount   = count($items);
        $itemcounter = 0;
        $product     = array();
        foreach ($items as $itemId => $item) {
            $mofluid_all_product_images = array();
            $mofluid_non_def_images     = array();
            $mofluid_product            = Mage::getModel('catalog/product')->load($item->getProductId());
            if ($mofluid_product->getTypeID() == "simple") {
                $parentIds = Mage::getModel('catalog/product_type_grouped')->getParentIdsByChild($mofluid_product->getId()); // check for grouped product
                if (!$parentIds) {
                    $parentIds = Mage::getModel('catalog/product_type_configurable')->getParentIdsByChild($mofluid_product->getId()); //check for config product
                }
            }
            if ($parentIds[0]) {
                $mofluid_parent_product = Mage::getModel('catalog/product')->load($parentIds[0]);
                $mofluid_baseimage      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_parent_product->getImage();
            } else {
                $mofluid_baseimage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();
            }
            if (!$mofluid_baseimage) {
                foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {

                    $mofluid_imagecame = $mofluid_image->getUrl();
                    if ($mofluid_baseimage == $mofluid_imagecame) {
                        $mofluid_all_product_images[0] = $mofluid_image->getUrl();
                        break;
                    } else {
                        $mofluid_non_def_images[] = $mofluid_image->getUrl();
                    }
                }

                $mofluid_all_product_images = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
            } else {
                $mofluid_all_product_images[0] = $mofluid_baseimage;
            }
            $product[$itemcounter]["id"]    = $item->getId();
            $product[$itemcounter]["sku"]   = $item->getSku();
            $product[$itemcounter]["name"]  = $item->getName();
            $product[$itemcounter]["qty"]   = number_format($item->getQtyOrdered(), 2, '.', '');
            //$product[$itemcounter]["image"]  = (string)Mage::helper('catalog/image')->init(Mage::getModel('catalog/product')->load($item->getId()), 'thumbnail');
            $product[$itemcounter]["image"] = $mofluid_all_product_images[0];
            $product[$itemcounter]["price"] = number_format($item->getPrice(), 2, '.', '');
            $itemcounter++;
        }
        $coupon     = array();
        $couponCode = $order->getCouponCode();
        if ($couponCode != "") {
            $coupon["applied"] = 1;
            $coupon["code"]    = $couponCode;
            $coupon["amount"]  = number_format($order->getDiscountAmount() * -1, 2, '.', '');
        }
        $res = array(
            "id" => $order->getId(),
            "order_id" => $order->getRealOrderId(),
            "status" => $order->getStatus(),
            "state" => $order->getState(),
            "order_date" => $order->getCreatedAtStoreDate() . "",
            "payment" => $payment_result,
            "products" => $product,
            "currency" => array(
                "code" => $order->getOrderCurrencyCode(),
                "symbol" => Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol(),
                "current" => $currency
            ),
            "address" => array(
                "shipping" => $shippadd,
                "billing" => $billadd
            ),
            "amount" => array(
                "total" => number_format($order->getGrandTotal(), 2, '.', ''),
                "tax" => number_format($order->getTaxAmount(), 2, '.', '')
            ),
            "coupon" => $coupon,
            "shipping" => array(
                "method" => $order->getShippingDescription(),
                "amount" => number_format($order->getShippingAmount(), 2, '.', '')
            )
        );
        //$echo= "<pre>"; print_r($res); die;
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return $res;
    }

    public function ws_myOrder($cust_id, $curr_page, $page_size, $store, $currency)
    {

        $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
        $res              = array();
        $totorders        = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $cust_id);
        $res["total"]     = count($totorders);
        $orders           = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $cust_id)->setOrder('created_at', 'desc')->setPage($curr_page, $page_size);
        $orderData = array();
	//$this->setOrders($orders);
        foreach ($orders as $order) {

            $shippingAddress = $order->getShippingAddress();
            if (is_object($shippingAddress)) {
                $shippadd = array();
                $flag     = 0;
                if (count($orderData) > 0)
                    $flag = 1;
                $shippadd = array(
                    "firstname" => $shippingAddress->getFirstname(),
                    "lastname" => $shippingAddress->getLastname(),
                    "company" => $shippingAddress->getCompany(),
                    "street" => $shippingAddress->getStreetFull(),
                    "region" => $shippingAddress->getRegion(),
                    "city" => $shippingAddress->getCity(),
                    "pincode" => $shippingAddress->getPostcode(),
                    "countryid" => $shippingAddress->getCountry_id(),
                    "contactno" => $shippingAddress->getTelephone(),
                    "shipmyid" => $flag
                );
            }
            $billingAddress = $order->getBillingAddress();
            if (is_object($billingAddress)) {
                $billadd = array();
                $billadd = array(
                    "firstname" => $billingAddress->getFirstname(),
                    "lastname" => $billingAddress->getLastname(),
                    "company" => $billingAddress->getCompany(),
                    "street" => $billingAddress->getStreetFull(),
                    "region" => $billingAddress->getRegion(),
                    "city" => $billingAddress->getCity(),
                    "pincode" => $billingAddress->getPostcode(),
                    "countryid" => $billingAddress->getCountry_id(),
                    "contactno" => $billingAddress->getTelephone()
                );
            }
            $payment = array();
            $payment = $order->getPayment();



            try {
                $payment_result = array(
                    "payment_method_title" => $payment->getMethodInstance()->getTitle(),
                    "payment_method_code" => $payment->getMethodInstance()->getCode()
                );
                if ($payment->getMethodInstance()->getCode() == "banktransfer") {

                    $payment_result["payment_method_description"] = $payment->getMethodInstance()->getInstructions();
                }
            }
            catch (Exception $ex2) {

            }

            //$order = Mage::getModel('sales/order')->load($order_id);
            $items                       = $order->getAllItems();
            $itemcount                   = count($items);
            $total_products=array();
	    $test_p	= array(); 
            foreach ($items as $itemId => $item) {
                $name = $item->getName();
                //$echo= $item->getName();
                /**** no need to do this because this is basically doing the same thing ****/
                if ($item->getOriginalPrice() > 0) {
                    $unitPrice = number_format($this->convert_currency(floatval($item->getOriginalPrice()), $basecurrencycode, $currency), 2, '.', '');
                } else {
                    $unitPrice= number_format($this->convert_currency(floatval($item->getPrice()), $basecurrencycode, $currency), 2, '.', '');
                }

                $sku   = $item->getSku();
                $id    = $item->getProductId();
                $_prod = Mage::getModel('catalog/product')->load($item->getProductId());
                $psmallImg = Mage::helper('catalog/image')->init($_prod,'small_image')->resize(200,200);
                $smallimg = (string)$psmallImg;
                //$qty[]=$item->getQtyToInvoice();
                $qty    = $item->getQtyOrdered();
                $products = Mage::getModel('catalog/product')->load($item->getProductId());
                $image = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . '/media/catalog/product' . $products->getThumbnail();
                $product = array(
                "name" => $name,
                "sku" => $sku,
                "id" => $id,
                "quantity" => $qty,
                "unitprice" => $unitPrice,
                "image" => $image,
                "small_image" => $smallimg,
                "price_org" => $test_p,
                "price_based_curr" => 1
            );
             $total_products[]=$product;
            }
            $total_products["count"]=$itemcount;

            $order_date = $order->getCreatedAtStoreDate() . '';
            $orderData  = array(
                "id" => $order->getId(),
                "order_id" => $order->getRealOrderId(),
                "status" => $order->getStatus(),
                "order_date" => $order_date,
                "grand_total" => number_format($this->convert_currency(floatval($order->getGrandTotal()), $basecurrencycode, $currency), 2, '.', ''),
                "shipping_address" => $shippadd,
                "billing_address" => $billadd,
                "shipping_message" => $order->getShippingDescription(),
                "shipping_amount" => number_format($this->convert_currency(floatval($order->getShippingAmount()), $basecurrencycode, $currency), 2, '.', ''),
                "payment_method" => $payment_result,
                "tax_amount" => number_format($this->convert_currency(floatval($order->getTaxAmount()), $basecurrencycode, $currency), 2, '.', ''),
                "product" => $total_products,
                "order_currency" => $order->getOrderCurrencyCode(),
                "order_currency_symbol" => Mage::app()->getLocale()->currency($order->getOrderCurrencyCode())->getSymbol(),
                "currency" => $currency,
                "couponUsed" => 0
            );
            $couponCode = $order->getCouponCode();
            if ($couponCode != "") {
                $orderData["couponUsed"]      = 1;
                $orderData["couponCode"]      = $couponCode;
                $orderData["discount_amount"] = floatval(number_format($this->convert_currency(floatval($order->getDiscountAmount()), $basecurrencycode, $currency), 2, '.', '')) * -1;
            }

            $res["data"][] = $orderData;
        }
        return $res;
    }

    public function ws_myProfile($cust_id)
    {
        try {
            $customer                    = Mage::getModel('customer/customer')->load($cust_id);
            $customerData                = $customer->getData();
            $customerData['membersince'] = Mage::getModel('core/date')->date("Y-m-d h:i:s A", $customerData['created_at']);
            $shippingAddress             = $customer->getDefaultShippingAddress();
        }
        catch (Exception $ex2) {
            $echo= $ex2;
            $this->getResponse()->setBody($echo);
        }
        $shippadd = array();
        $billadd  = array();
        try {
            if ($shippingAddress != null) {
                $shippadd = array(
                    "firstname" => $shippingAddress->getFirstname(),
                    "lastname" => $shippingAddress->getLastname(),
                    "company" => $shippingAddress->getCompany(),
                    "street" => $shippingAddress->getStreetFull(),
                    "region" => $shippingAddress->getRegion(),
                    "city" => $shippingAddress->getCity(),
                    "pincode" => $shippingAddress->getPostcode(),
                    "countryid" => $shippingAddress->getCountry_id(),
                    "contactno" => $shippingAddress->getTelephone()
                );
            }
            $billingAddress = $customer->getDefaultBillingAddress();
            if ($billingAddress != null) {
                $billadd = array(
                    "firstname" => $billingAddress->getFirstname(),
                    "lastname" => $billingAddress->getLastname(),
                    "company" => $billingAddress->getCompany(),
                    "street" => $billingAddress->getStreetFull(),
                    "region" => $billingAddress->getRegion(),
                    "city" => $billingAddress->getCity(),
                    "pincode" => $billingAddress->getPostcode(),
                    "countryid" => $billingAddress->getCountry_id(),
                    "contactno" => $billingAddress->getTelephone()
                );
            }
        }
        catch (Exception $ex) {
            $echo= $ex;
            $this->getResponse()->setBody($echo);
        }
        $res = array();
        $res = array(
            "CustomerInfo" => $customerData,
            "BillingAddress" => $billadd,
            "ShippingAddress" => $shippadd
        );
        return $res;
    }

    public function ws_changeProfilePassword($custid, $username, $oldpassword, $newpassword, $store)
    {
        $res         = array();
        $oldpassword = base64_decode($oldpassword);
        $newpassword = base64_decode($newpassword);
        $validate    = 0;
        $websiteId   = Mage::getModel('core/store')->load($store)->getWebsiteId();
        try {
            $login_customer_result = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->authenticate($username, $oldpassword);
            $validate              = 1;
        }
        catch (Exception $ex) {
            $validate = 0;
        }
        if ($validate == 1) {
            try {
                $customer = Mage::getModel('customer/customer')->load($custid);
                $customer->setPassword($newpassword);
                $customer->save();
                $res = array(
                    "customerid" => $custid,
                    "oldpassword" => $oldpassword,
                    "newpassword" => $newpassword,
                    "change_status" => 1,
                    "message" => 'Your Password has been Changed Successfully'
                );
            }
            catch (Exception $ex) {
                $res = array(
                    "customerid" => $custid,
                    "oldpassword" => $oldpassword,
                    "newpassword" => $newpassword,
                    "change_status" => -1,
                    "message" => 'Error : ' . $ex->getMessage
                );
            }
        } else {
            $res = array(
                "customerid" => $custid,
                "oldpassword" => $oldpassword,
                "newpassword" => $newpassword,
                "change_status" => 0,
                "message" => 'Incorrect Old Password.'
            );
        }
        return $res;
    }

    public function ws_setprofile($store, $service, $customerId, $JbillAdd, $JshippAdd, $profile)
    {

        $billAdd  = json_decode($JbillAdd);
        $shippAdd = json_decode($JshippAdd);
        $profile  = json_decode($profile);

        $result                 = array();
        $result['billaddress']  = 0;
        $result['shippaddress'] = 0;
        $result['userprofile']  = 0;

        /* Update User Profile Data */

        $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($profile->email);

        //check exists email address of users
        if ($customer->getId() && $customer->getId() != $customerId) {
            return $result;
        } else {
            $_bill_address  = array(
                'firstname' => $billAdd->billfname,
                'lastname' => $billAdd->billlname,
                'street' => array(
                    '0' => $billAdd->billstreet1,
                    '1' => $billAdd->billstreet2
                ),
                'city' => $billAdd->billcity,
                'region_id' => '',
                'region' => $billAdd->billstate,
                'postcode' => $billAdd->billpostcode,
                'country_id' => $billAdd->billcountry,
                'telephone' => $billAdd->billphone
            );
            $_shipp_address = array(
                'firstname' => $shippAdd->shippfname,
                'lastname' => $shippAdd->shipplname,
                'street' => array(
                    '0' => $shippAdd->shippstreet1,
                    '1' => $shippAdd->shippstreet2
                ),
                'city' => $shippAdd->shippcity,
                'region_id' => '',
                'region' => $shippAdd->shippstate,
                'postcode' => $shippAdd->shipppostcode,
                'country_id' => $shippAdd->shippcountry,
                'telephone' => $shippAdd->shippphone
            );


            $billAddress = Mage::getModel('customer/address');
            if ($defaultBillingId = $customer->getDefaultBilling()) {
                $billAddress->load($defaultBillingId);
                $billAddress->addData($_bill_address);
            } else {
                $billAddress->setData($_bill_address)->setCustomerId($customerId)->setIsDefaultBilling('1')->setSaveInAddressBook('1');
            }
            $shippAddress = Mage::getModel('customer/address');
            if ($defaultShippingId = $customer->getDefaultShipping()) {
                $shippAddress->load($defaultShippingId);
                $shippAddress->addData($_shipp_address);
            } else {
                $shippAddress->setData($_shipp_address)->setCustomerId($customerId)->setIsDefaultShipping('1')->setSaveInAddressBook('1');
            }


            try {
                if ($billAddress->save())
                    $result['billaddress'] = 1;
                if ($shippAddress->save())
                    $result['shippaddress'] = 1;

                $tab_prefix      = Mage::getConfig()->getTablePrefix();
                $write           = Mage::getSingleton("core/resource")->getConnection("core_write");
                $sql1            = "update `" . $tab_prefix . "customer_entity` set `email` = '" . $profile->email . "' where`entity_id` =" . $customerId;
                $attributeModel1 = Mage::getModel('eav/entity_attribute')->loadByCode("customer", "firstname");
                $firstnameId     = $attributeModel1->getAttributeId();
                $attributeModel2 = Mage::getModel('eav/entity_attribute')->loadByCode("customer", "lastname");
                $lastnameId      = $attributeModel2->getAttributeId();
                $sql2            = "update `" . $tab_prefix . "customer_entity_varchar` set `value` = '" . $profile->fname . "' where `entity_type_id` =1 AND `attribute_id`=" . $firstnameId . " AND `entity_id`=" . $customerId;
                $sql3            = "update `" . $tab_prefix . "customer_entity_varchar` set `value` = '" . $profile->lname . "' where `entity_type_id` =1 AND `attribute_id`=" . $lastnameId . " AND `entity_id`=" . $customerId;

                if ($write->query($sql1) && $write->query($sql2) && $write->query($sql3)) {
                    $result['userprofile'] = 1;
                }
            }
            catch (Exception $ex) {
                Zend_Debug::dump($ex->getMessage());
            }
            return $result;
        }
        //---------------------------------------------------------------------
    }

    public function ws_forgotPassword($email = "")
    {
        $res             = array();
        $res["response"] = "error";

        if ($email) {
            /** @var $customer Mage_Customer_Model_Customer */
            $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

            if ($customer->getId()) {
                try {
                    $newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
                    $customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
                    $customer->sendPasswordResetConfirmationEmail();
                    $res["response"] = "success";
                }
                catch (Exception $exception) {
                    // $this->_getSession()->addError($exception->getMessage());
                }
            }
        }
        return ($res);
    }

    public function _getSession()
    {
        return Mage::getSingleton('customer/session');
    }

    /* Function call to login user from Email address */
/*
    public function ws_loginwithsocial($store, $username, $firstname, $lastname)
    {
        $websiteId       = Mage::getModel('core/store')->load($store)->getWebsiteId();
        $res             = array();
        $res["username"] = $username;
        $login_status    = 1;
        try {
            // $login_customer_result = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->authenticate($username);
            $login_customer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);
            $login_customer->loadByEmail($username);
            if ($login_customer->getId()) {
                $res["firstname"] = $login_customer->firstname;
                $res["lastname"]  = $login_customer->lastname;
                $res["id"]        = $login_customer->getId();
            } else {
                $login_status = 0;
                $res          = $this->ws_registerwithsocial($store, $username, $firstname, $lastname);
                if ($res["status"] == 1) {
                    $login_status = 1;
                }
            }
        }
        catch (Exception $e) {
            $login_status = 0;
            $res          = $this->ws_registerwithsocial($store, $username, $firstname, $lastname);
            if ($res["status"] == 1) {
                $login_status = 1;
            }
        }
        $res["login_status"] = $login_status;
        return $res;
    }
*/
    /* Function call to register user from its Email address */

    //~ public function ws_registerwithsocial($store, $email, $firstname, $lastname)
    //~ {
        //~ $res                  = array();
        //~ $websiteId            = Mage::getModel('core/store')->load($store)->getWebsiteId();
        //~ $customer             = Mage::getModel("customer/customer");
        //~ $customer->website_id = $websiteId;
        //~ $customer->setCurrentStore($store);
        //~ try {
            //~ // If new, save customer information
            //~ $customer->firstname     = $firstname;
            //~ $customer->lastname      = $lastname;
            //~ $customer->email         = $email;
            //~ $password                = base64_encode(rand(11111111, 99999999));
            //~ $customer->password_hash = md5(base64_decode($password));
            //~ $res["email"]            = $email;
            //~ $res["firstname"]        = $firstname;
            //~ $res["lastname"]         = $lastname;
            //~ $res["password"]         = $password;
            //~ $res["status"]           = 0;
            //~ $res["id"]               = 0;
            //~ $cust                    = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

            //~ //check exists email address of users
            //~ if ($cust->getId()) {
                //~ $res["id"]     = $cust->getId();
                //~ $res["status"] = 0;
            //~ } else {
                //~ if ($customer->save()) {
                    //~ $customer->sendNewAccountEmail('confirmed');
                    //~ $this->send_Password_Mail_to_NewUser($firstname, base64_decode($password), $email);
                    //~ $res["id"]     = $customer->getId();
                    //~ $res["status"] = 1;
                //~ } else {
                    //~ $exist_customer = Mage::getModel("customer/customer");
                    //~ $exist_customer->setWebsiteId($websiteId);
                    //~ $exist_customer->setCurrentStore($store);
                    //~ $exist_customer->loadByEmail($email);
                    //~ $res["id"]     = $exist_customer->getId();
                    //~ $res["status"] = 1;
                //~ }
            //~ }
        //~ }
        //~ catch (Exception $e) {
            //~ try {
                //~ $exist_customer = Mage::getModel("customer/customer");
                //~ $exist_customer->setWebsiteId($websiteId);
                //~ $exist_customer->setCurrentStore($store);
                //~ $exist_customer->loadByEmail($email);
                //~ $res["id"]     = $exist_customer->getId();
                //~ $res["status"] = 1;
            //~ }
            //~ catch (Exception $ex) {
                //~ $res["id"]     = -1;
                //~ $res["status"] = 0;
            //~ }
        //~ }
        //~ return $res;
    //~ }

    function mofluid_register_push($store, $deviceid, $pushtoken, $platform, $appname, $description)
    {
        $res        = array();
        $tab_prefix = Mage::getConfig()->getTablePrefix();
        try {
            $mofluid_push = Mage::getSingleton('core/resource')->getConnection('core_write');
            $readresult   = $mofluid_push->query("SELECT * FROM  " . $tab_prefix . "mofluidpush WHERE device_id = '" . $deviceid . "' AND app_name = '" . $appname . "' AND platform ='" . $platform . "'");
            $row          = $readresult->fetch();
            $readresult2  = $mofluid_push->query("SELECT * FROM  " . $tab_prefix . "mofluidpush WHERE push_token_id = '" . $pushtoken . "' AND app_name = '" . $appname . "' AND platform ='" . $platform . "'");
            $row          = $readresult->fetch();
            $row2         = $readresult2->fetch();

            if ($row["device_id"]) {
                $mofluid_push->query("DELETE FROM  " . $tab_prefix . "mofluidpush WHERE device_id = '" . $deviceid . "' AND app_name = '" . $appname . "' AND platform ='" . $platform . "'");
                $mofluid_push->query("insert into " . $tab_prefix . "mofluidpush (mofluidadmin_id, device_id, push_token_id, platform, app_name, description)
                                                                      values (1,'" . $deviceid . "','" . $pushtoken . "','" . $platform . "','" . $appname . "','" . $description . "')");
                $res = array(
                    "status" => "update",
                    "deviceid" => $deviceid,
                    "pushtoken" => $pushtoken,
                    "message" => "Update token for the existing device id."
                );
            } else if ($row2["push_token_id"]) {
                $mofluid_push->query("DELETE FROM  " . $tab_prefix . "mofluidpush WHERE push_token_id = '" . $pushtoken . "' AND app_name = '" . $appname . "' AND platform ='" . $platform . "'");
                $mofluid_push->query("insert into " . $tab_prefix . "mofluidpush (mofluidadmin_id, device_id, push_token_id, platform, app_name, description)
                                                                      values (1,'" . $deviceid . "','" . $pushtoken . "','" . $platform . "','" . $appname . "','" . $description . "')");
                $res = array(
                    "status" => "update",
                    "deviceid" => $deviceid,
                    "pushtoken" => $pushtoken,
                    "message" => "Update Device for the existing token id."
                );
            } else {
                $mofluid_push->query("insert into " . $tab_prefix . "mofluidpush (mofluidadmin_id, device_id, push_token_id, platform, app_name, description)
                                                                      values (1,'" . $deviceid . "','" . $pushtoken . "','" . $platform . "','" . $appname . "','" . $description . "')");
                $res = array(
                    "status" => "register",
                    "deviceid" => $deviceid,
                    "pushtoken" => $pushtoken,
                    "message" => "register device id with new token."
                );
            }
        }
        catch (Exception $ex) {
            $res = array(
                "status" => "error",
                "deviceid" => $deviceid,
                "pushtoken" => $pushtoken,
                "message" => $ex->getMessage()
            );
        }
        return $res;
    }

    public function ws_termCondition($store)
    {
        $flag = Mage::getStoreConfigFlag('checkout/options/enable_agreements');
        if ($flag) {
            $agreements = Mage::getModel('checkout/agreement')->getCollection()->addStoreFilter($store)->addFieldToFilter('is_active', 1);
            $data       = $agreements->getData('agreements');
            return $data;
        }
    }

    public function ws_productQuantity($product)
    {

        $pqty    = array();
        $config_manage_stock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
        $config_max_sale_qty=Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty');
        $product = json_decode($product);
        foreach ($product as $key => $val) {
            try {
                $model      = Mage::getModel('catalog/product');
                $_product   = $model->load($val);
                $stocklevel = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product)->getQty();
                $stock_product = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
         		$stock_data = $stock_product->getData();

                if($stock_data['use_config_manage_stock']==0)
                {
                 if($stock_data['manage_stock']==0)
                	{
                			if($stock_data['use_config_max_sale_qty']==0)
                			{
                				$pqty[$val] =$stock_data['max_sale_qty'];
                			 }
                			 else
                			 {
                			 $pqty[$val] = $config_max_sale_qty;

                			 }
                	}
                	else
                	{
                		        $pqty[$val] = $stocklevel;
                	}
                }
                else
                {

                	if($config_manage_stock==0){ $pqty[$val] = $config_max_sale_qty; } else { $pqty[$val] = $stocklevel; }

                }
                        }
            catch (Exception $ex) {

            }
        }
        return $pqty;
    }

    public function ws_countryList($store_id, $paymentgateway, $pmethod)
    {
        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
        $cache_key = "mofluid_country_store" . $store_id . "_paymentgateway" . $paymentgateway . "_pmethod" . $pmethod;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}

        $country = array();
        if ($paymentgateway == "paypal" && $pmethod == "standard") {
            $allowspecific = Mage::getConfig()->getNode('default/payment/paypal_standard/allowspecific');

            // get specific countries of standard paypal method from config table
            if ($allowspecific == 1) {
                $_countries = Mage::getConfig()->getNode('default/payment/paypal_standard/specificcountry');
                $data       = explode(",", $_countries[0]);
                if (count($data) > 0) {
                    foreach ($data as $key => $country_code) {
                        $country[$country_code] = Mage::app()->getLocale()->getCountryTranslation($country_code);
                    }
                }
            } else {
                $_countries = Mage::getResourceModel('directory/country_collection')->loadData()->toOptionArray(false);

                if (count($_countries) > 0) {
                    foreach ($_countries as $_country) {
                        $country[$_country['value']] = $_country['label'];
                    }
                }
            }
        } // end of outer if
        if($enable){
			$cache->save(json_encode($country), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($country);
    }

    //************ list all enable shipping method ********** //

    public function ws_listShipping()
    {


        $methods  = Mage::getSingleton('shipping/config')->getActiveCarriers();
        $shipping = array();
        foreach ($methods as $_ccode => $_carrier) {
            if ($_methods = $_carrier->getAllowedMethods()) {
                if (!$_title = Mage::getStoreConfig("carriers/$_ccode/title"))
                    $_title = $_ccode;
                foreach ($_methods as $_mcode => $_method) {
                    $_code            = $_ccode . '_' . $_mcode;
                    $shipping[$_code] = array(
                        'method' => $_method,
                        'title' => $_title
                    );
                }
            }
        }
        //~ $cache->save(json_encode($shipping), $cache_key, array(
            //~ "mofluid"
        //~ ), $this->CACHE_EXPIRY);
        return $shipping;
    }

    function ws_validatecurrency($store, $service, $currency, $paymentgateway)
    {
        $cache     = Mage::app()->getCache();
        $cache_key = "mofluid_service" . $service . "_store" . $store . "_currency" . $currency . "_paymentmethod" . $paymentgateway;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));
        if ($paymentgateway == 'secureebs_standard' || $paymentgateway == 'paypal_standard' || $paymentgateway == 'authorizenet' || $paymentgateway == 'authorize' || $paymentgateway == 'moto' || $paymentgateway == 'moneris' || $paymentgateway == 'banorte' || $paymentgateway == 'payucheckout_shared' || $paymentgateway == 'sisowde' || $paymentgateway == 'sisow_ideal') {
            $payment_types['paypal']              = array(
                "0" => 'AUD',
                "1" => 'BRL',
                "2" => 'CAD',
                "3" => 'CZK',
                "4" => 'DKK',
                "5" => 'EUR',
                "6" => 'HKD',
                "7" => 'HUF',
                "8" => 'ILS',
                "9" => 'JPY',
                "10" => 'MYR',
                "11" => 'MXN',
                "12" => 'NOK',
                "13" => 'NZD',
                "14" => 'PHP',
                "15" => 'PLN',
                "16" => 'GBP',
                "17" => 'RUB',
                "18" => 'SGD',
                "19" => 'SEK',
                "20" => 'CHF',
                "21" => 'TWD',
                "22" => 'TRY',
                "23" => 'THB',
                "24" => 'USD'
            );
            $payment_types['paypal_standard']     = $payment_types['paypal'];
            $payment_types['authorizenet']        = array(
                "0" => 'GBP',
                "1" => 'USD',
                "2" => 'EUR',
                "3" => 'AUD'
            );
            $payment_types['secureebs_standard']  = array(
                "0" => 'INR'
            );
            $payment_types['moto']                = array(
                "0" => 'INR'
            );
            $payment_types['moneris']             = array(
                "0" => 'USD'
            );
            $payment_types['banorte']             = array(
                "0" => 'MXN'
            );
            $payment_types['payucheckout_shared'] = array(
                "0" => 'INR'
            );
            $payment_types['sisowde']             = array(
                "0" => 'EUR'
            );
            $payment_types['sisow_ideal']         = array(
                "0" => 'EUR'
            );
            $size_of_array                        = sizeof($payment_types[$paymentgateway]);
            if ($size_of_array > 0) {
                if (in_array($currency, $payment_types[$paymentgateway]))
                    $status = "1";
                else {
                    $msg    = "Currency Code " . $currency . " is not supported with this Payment Type. Please Select different Payment Mode.";
                    $status = "0";
                }
            }
        } else
            $status = "1";
        $res["status"] = $status;
        $res["msg"]    = $msg;
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return $res;
    }

    /**
     * Method : prepareQuote
     * @param : $custid => Customer Id of the Logged In User
     * @param : $Jproduct => Cart Products data in json
     * @param : $store => Store Id of the Magento Store
     * @param : $address => Address for current request
     * @param : $couponCode  => Applied Coupon code
     */
    public function prepareQuote($custid, $Jproduct, $store, $address, $shipping_code, $couponCode, $currency, $is_create_quote, $find_shipping)
    {
        $Jproduct         = str_replace(" ", "+", $Jproduct);
        $shipping_amount  =	 0;
        $shipping_methods = array();
        $orderproduct     = json_decode(base64_decode($Jproduct));
        $address          = str_replace(" ", "+", $address);
        $address          = json_decode(base64_decode($address),true);
        $config_manage_stock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
        $config_max_sale_qty = Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty');
        $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
        try {
            $customerObj     = Mage::getModel('customer/customer')->load($custid);
            // get billing and shipping address of customer
            $shippingAddress = array(); $billingAddress = array();
	   if(array_key_exists("shipping",$address) && array_key_exists("billing",$address)){ 	
	   $shippingAddress = array(
                //'prefix' => $address->shipping->prefix,
                'firstname' => $address["shipping"]["firstname"],
                'lastname' => $address["shipping"]["lastname"],
                //'company' => $address["shipping"]["company"],
                'street' => $address["shipping"]["street"],
                'city' => $address["shipping"]["city"],
                'postcode' => $address["shipping"]["postcode"],
                'telephone' => $address["shipping"]["phone"],
                'country_id' => $address["shipping"]["country"],
                'region' => $address["shipping"]["region"]
            );
            $billingAddress  = array(
                //'prefix' => $address->billing->prefix,
                'firstname' => $address["billing"]["firstname"],
                'lastname' => $address["billing"]["lastname"],
                //'company' => $address["billing"]["company"],
                'street' => $address["billing"]["street"],
                'city' => $address["billing"]["city"],
                'postcode' => $address["billing"]["postcode"],
                'telephone' => $address["billing"]["phone"],
                'country_id' => $address["billing"]["country"],
                'region' => $address["billing"]["region"]
            );
            //Setting Region ID In case of Country is US
            if ($address["billing"]["country"] == "US" || $address["billing"]["country"] == "USA") {
                $regionModel                 = Mage::getModel('directory/region')->loadByCode($address["billing"]["region"], $address["billing"]["country"]);
                $regionId                    = $regionModel->getId();
                $billingAddress["region_id"] = $regionId;
            }
            if ($address["shipping"]["country"] == "US" || $address["shipping"]["country"] == "USA") {
                $regionModelShipping          = Mage::getModel('directory/region')->loadByCode($address["shipping"]["region"], $address["shipping"]["country"]);
                $regionIdShipp                = $regionModelShipping->getId();
                $shippingAddress["region_id"] = $regionIdShipp;
            }
	    }
            $quote    = Mage::getModel('sales/quote');
            $customer = Mage::getModel('customer/customer')->load($custid);
            $quote->assignCustomer($customer);
            Mage::app()->setCurrentStore($store);

            $quote->setStore(Mage::app()->getStore());
            $res           = array();
            $stock_counter = 0;
            $flag=0;
            foreach ($orderproduct as $key => $item) {
                $product_stock          = $this->getProductStock($item->id);
               try
               {
						if($product_stock['use_config_manage_stock']==0)
						{
						 if($product_stock['manage_stock']==0)
							{
									if($product_stock['use_config_max_sale_qty']==0)
									{
										$product_stock_quantity =$product_stock['max_sale_qty'];
										$flag=1;
									 }
									 else
									 {
										$product_stock_quantity = $config_max_sale_qty;
										$flag=1;

									 }
							}
							else
							{
										$product_stock_quantity = $product_stock['qty'];
										$flag=0;
							}
						}
						else
						{
							if($config_manage_stock==0){  $product_stock_quantity = $config_max_sale_qty; $flag=1; } else {  $product_stock_quantity = $product_stock['qty']; $flag=0; }

						}
                }
                catch(Exception $ex)
                {

                }
               // $product_stock_quantity = $product_stock['qty'];
                $manage_stock           = $product_stock['manage_stock'];
                $is_in_stock            = $product_stock['is_in_stock'];
                $res["qty_flag"]        = $flag;
                if ($item->quantity > $product_stock_quantity) {
                    $res["status"]                              = "error";
                    $res["type"]                                = "quantity";
                    $res["product"][$stock_counter]["id"]       = $item->id;
                    $res["product"][$stock_counter]["name"]     = Mage::getModel('catalog/product')->load($item->id)->getName();
                    $res["product"][$stock_counter]["sku"]      = Mage::getModel('catalog/product')->load($item->id)->getSku();
                    $res["product"][$stock_counter]["quantity"] = $product_stock_quantity;
                    $stock_counter++;
                }
                $product = Mage::getModel('catalog/product');
                $product->load($item->id);
                $productType = $product->getTypeID();
                $quoteItem   = Mage::getModel('sales/quote_item')->setProduct($product);
                $quoteItem->setQuote($quote);
                $quoteItem->setQty($item->quantity);
                if ($product->getTypeId() == 'downloadable') {
                    $params = array();
                   // $links = Mage::getModel('downloadable/product_type')->getLinks( $product );
                    //$linkId = 0;
                    $links = array();
                    foreach ($item->down_link_options as $link) {
                        array_push($links, $link->link_id);
                    }
                    $params['product'] = $item->id;
                    $params['qty'] = $item->quantity;
                    $params['links'] = $links;
                    $request = new Varien_Object();
                    $request->setData($params);
                    $quote->addProduct($product , $request);
                }
                else{
					if (array_key_exists("options",$item)) {
						foreach ($item->options as $ckey => $cvalue) {
							$custom_option_ids_arr[] = $ckey;
						}
						$option_ids = implode(",", $custom_option_ids_arr);
						$quoteItem->addOption(new Varien_Object(array(
							'product' => $quoteItem->getProduct(),
							'code' => 'option_ids',
							'value' => $option_ids
						)));
						foreach ($item->options as $ckey => $cvalue) {
							if (is_array($cvalue)) {
								$all_ids = implode(",", array_unique($cvalue));
							} else {
								$all_ids = $cvalue;
							}
							//Handle Custom Option Time depending upon Timezone
							if (preg_match('/(2[0-3]|[01][0-9]):[0-5][0-9]:[0-5][0-9]/', $all_ids)) {
								$currentTimestamp = Mage::getModel('core/date')->timestamp(time());
								$currentDate      = date('Y-m-d', $currentTimestamp);
								$test             = new DateTime($currentDate . ' ' . $all_ids);
								$all_ids          = $test->getTimeStamp();
							}
							try {
								$quoteItem->addOption(new Varien_Object(array(
									'product' => $quoteItem->getProduct(),
									'code' => 'option_' . $ckey,
									'value' => $all_ids
								)));
							}
							catch (Exception $eee) {
								$echo= 'Error ' . $eee->getMessage();
								$this->getResponse()->setBody($echo);
							}
						} //end inner foreach
						$quote->addItem($quoteItem);
					} //end if
					else {
						$quote->addItem($quoteItem);
						continue;
					}
				}
            } //end outer foreach
            if ($stock_counter > 0 && $is_create_quote == 1) {
                return $res;
            }
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')->setEntityType('customer_address');
            foreach ($addressForm->getAttributes() as $attribute) {
				//echo '<pre>'.print_r($attributes).'</pre>';
                if (array_key_exists($attribute->getAttributeCode(), $shippingAddress)) {
                    $quote->getShippingAddress()->setData($attribute->getAttributeCode(), $shippingAddress[$attribute->getAttributeCode()]);
                }
            }
            foreach ($addressForm->getAttributes() as $attribute) {
                if (array_key_exists($attribute->getAttributeCode(),$billingAddress)) {
                    $quote->getBillingAddress()->setData($attribute->getAttributeCode(), $billingAddress[$attribute->getAttributeCode()]);
                }
            }
            $quote->setBaseCurrencyCode($basecurrencycode);
            $quote->setQuoteCurrencyCode($currency);
            if ($find_shipping) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->save();
            } else {
                $quote->getShippingAddress()->setShippingMethod($shipping_code)->setCollectShippingRates(true);
            }
            //Check if applied for coupon
            if (!$this->is_empty($couponCode)) {
                $quote->setCouponCode($couponCode);
                $coupon_status = 1;
            } else {
                $coupon_status = 0;
            }
            //$quote->setTotalsCollectedFlag(false)->collectTotals();
            $quote->collectTotals()->save();
            $totals = $quote->getTotals();
            try {
                $test                = $quote->getShippingAddress();
                $shipping_tax_amount = number_format(Mage::helper('directory')->currencyConvert($test['shipping_tax_amount'], $basecurrencycode, $currency), 2, ".", "");
            }
            catch (Exception $ex) {
                $shipping_tax_amount = 0;
            }
            if ($find_shipping) {
                $shipping                 = $quote->getShippingAddress()->getGroupedAllShippingRates();
                $index                    = 0;
                $shipping_dropdown_option = '';
                foreach ($shipping as $shipping_method_id => $shipping_method) {
                    foreach ($shipping_method as $current_shipping_method) {
                        $shipping_methods[$index]["id"]            = $shipping_method_id;
                        $shipping_methods[$index]["code"]          = str_replace(" ", "%20", $current_shipping_method->getCode());
                        $shipping_methods[$index]["method_title"]  = $current_shipping_method->getMethodTitle();
                        $shipping_methods[$index]["carrier_title"] = $current_shipping_method->getCarrierTitle();
                        $shipping_methods[$index]["carrier"]       = $current_shipping_method->getCarrier();
                        $shipping_methods[$index]["price"]         = Mage::helper('directory')->currencyConvert($current_shipping_method->getPrice(), $basecurrencycode, $currency);
                        $shipping_methods[$index]["description"]   = $current_shipping_method->getMethodDescription();
                        $shipping_methods[$index]["error_message"] = $current_shipping_method->getErrorMessage();
                        $shipping_methods[$index]["address_id"]    = $current_shipping_method->getAddressId();
                        $shipping_methods[$index]["created_at"]    = $current_shipping_method->getCreatedAt();
                        $shipping_methods[$index]["updated_at"]    = $current_shipping_method->getUpdatedAt();
                        $shipping_option_title                     = $shipping_methods[$index]["carrier_title"];
                        if ($shipping_methods[$index]["method_title"]) {
                            $shipping_option_title .= ' (' . $shipping_methods[$index]["method_title"] . ')';
                        }
                        if ($shipping_methods[$index]["price"]) {
                            $shipping_option_title .= ' + ' . Mage::app()->getLocale()->currency($currency)->getSymbol() . number_format($shipping_methods[$index]["price"], 2);
                        }
                        $shipping_dropdown_option .= '<option id=' . $shipping_methods[$index]["id"] . ' value= ' . $shipping_methods[$index]["code"] . ' price =' . $shipping_methods[$index]["price"] . ' description=' . $shipping_method[0]->getMethodDescription() . '>' . $shipping_option_title . '</option>';
                        $index++;
                    }
                }
               // $res["available_shipping_method"] = base64_encode($shipping_dropdown_option);
            }
            $dis = 0;
            $isPriceTaxInclusive = 0;
            //Find Applied Tax
            if (array_key_exists("tax",$totals) && $totals['tax']->getValue()) {
				if($isPriceTaxInclusive==1)
				$tax_amount = 0;
				
				else $tax_amount = number_format(Mage::helper('directory')->currencyConvert($totals['tax']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            } else {
                $tax_amount = 0;
            }
            if (array_key_exists('shipping',$totals) && $totals['shipping']->getValue()) {
                $shipping_amount = number_format(Mage::helper('directory')->currencyConvert($totals['shipping']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            } else {
                $shipping_amount = 0;
            }
            if ($shipping_tax_amount) {
                $shipping_amount += $shipping_tax_amount;
            }
            //Find Applied Discount
            if (array_key_exists('discount',$totals) && $totals['discount']->getValue()) {
                $coupon_status   = 1;
                $coupon_discount = number_format(Mage::helper('directory')->currencyConvert($totals['discount']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            } else {
                $coupon_discount = 0;
                $coupon_status   = 0;
            }
            $quoteData              = $quote->getData();
            $dis                    = $quoteData['grand_total'];
            $grandTotal             = number_format(Mage::helper('directory')->currencyConvert($totals['grand_total']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            $res["coupon_discount"] = $coupon_discount;
            $res["coupon_status"]   = $coupon_status;
            $res["tax_amount"]      = $tax_amount;
            $res["total_amount"]    = $grandTotal;
            $res["currency"]        = $currency;
            $res["status"]          = "success";
            $res["shipping_amount"] = $shipping_amount;
            $res["shipping_method"] = $shipping_methods;
            if ($is_create_quote == 1) {
                $quote->save();
                $res["quote_id"] = $quote->getId();
            }
            return $res;
        }
        catch (Exception $ex) {
            $res["coupon_discount"] = 0;
            $res["coupon_status"]   = 0;
            $res["tax_amount"]      = 0;
            $res["total_amount"]    = 0;
            $res["currency"]        = $currency;
            $res["status"]          = "error";
            $res["type"]            = $ex->getMessage();
            $res["shipping_amount"] = $shipping_amount;
            $res["shipping_method"] = $shipping_methods;
            return $res;
        }
    }

    public function getProductStock($product_id)
    {
        $stock_data    = array();
        $stock_product = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
        $stock_data    = $stock_product->getData();
        return $stock_data;
    }
	    public function getProductStock1($store_id,$service,$product_id){
        $res = array();
        $i =0;
		$product=   explode(",",$product_id);
		foreach( $product as  $productkey => $productvalue){
			$_product = Mage::getModel('catalog/product')->load($productvalue);
			$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
			if ($stock["manage_stock"]==0){
			$res[$i] = array( "Product id" =>$productvalue,
			                  "Quantity" =>5000
			                           );
			$i++;
			}
			else{

			$res[$i] = array( "Product id" =>$productvalue,
			                  "Quantity" =>$stock->getQty()
			                           );
			$i++;
			}
		}
        return $res;
       }

    public function setQuoteGiftMessage($quote, $message, $custid)
    {
        $message_id = array();
        $message    = json_decode($message, true);
        //foreach ($message as $key => $value) {
            $giftMessage = Mage::getModel('giftmessage/message');
            $giftMessage->setCustomerId($custid);
            $giftMessage->setSender($message["sender"]);
            $giftMessage->setRecipient($message["receiver"]);
            $giftMessage->setMessage($message["message"]);
            $giftObj                 = $giftMessage->save();
            $message_id["msg_id"][]  = $giftObj->getId();
            $message_id["prod_id"][] = $message["product_id"];
            $quote->setGiftMessageId($giftObj->getId());
            $quote->save();
        //}
        return $quote;
    }

    public function setQuotePayment($quote, $pmethod, $transid)
    {
        $quotePayment = $quote->getPayment();
        $quotePayment->setMethod($pmethod)->setIsTransactionClosed(1)->setTransactionAdditionalInfo(Mage_Sales_Model_Order_Payment_Transaction::RAW_DETAILS, array(
            'TransactionID' => $transid,
            'key2' => 'value2'
        ));
        $quotePayment->setCustomerPaymentId($transid);
        $quote->setPayment($quotePayment);
        return $quote;
    }

    public function updateQuantityAfterOrder($Jproduct)
    {
        $error    = array();
        $Jproduct = str_replace(" ", "+", $Jproduct);

        $orderproduct = json_decode(base64_decode($Jproduct));
        try {
            foreach ($orderproduct as $key => $item) {
                $productId = $item->id;
                $orderQty  = $item->quantity;

                $manage_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId)->getManageStock();
		if($manage_stock==0)
		continue;

		//get total quantity
                $totalqty  = (int) Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId)->getQty();
		//calculate new quantity
                $newqty    = $totalqty - $orderQty;
                //update new quantity
                try {
                    $product = Mage::getModel('catalog/product')->load($productId);
                    $product->setStockData(array(
                        'is_in_stock' => $newqty ? 1 : 0, //Stock Availability
                        'qty' => $newqty //qty
                    ));
                    $product->save();
                }
                catch (Exception $ee) {
                    $error[] = $ee->getMessage();
                }
            }
        }
        catch (Exception $ex) {
            $error[] = $ex->getMessage();
        }
        return $error;
    }

    /* ====================      Service to create order with Coupon Code  ================= */

    /*public function placeorder($custid, $Jproduct, $store, $address, $couponCode, $is_create_quote, $transid, $payment_code, $shipping_code, $currency, $message)
    {
        $res            = array();
        $quantity_error = array();
        try {
            $quote_data = $this->prepareQuote($custid, $Jproduct, $store, $address, $shipping_code, $couponCode, $currency, 1, 0);
         //  $echo= "<pre>"; print_r($quote_data); die;
            if ($quote_data["status"] == "error") {
                return $quote_data;
            }
            $quote        = Mage::getModel('sales/quote')->load($quote_data['quote_id']);
            //$quote->setInventoryProcessed(true);
            $quote        = $this->setQuoteGiftMessage($quote, $message, $custid);
            $quote        = $this->setQuotePayment($quote, $payment_code, $transid);
            $convertQuote = Mage::getSingleton('sales/convert_quote');
            try {
                $order = $convertQuote->addressToOrder($quote->getShippingAddress());
            }
            catch (Exception $Exc) {
                $echo= $Exc->getMessage();
            }
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                $orderItem = $convertQuote->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $order->addItem($orderItem);
            }
            try {
                $decode_address = json_decode(base64_decode($address));
                $order->setCustomer_email($decode_address->billing->email);
                $order->setCustomerFirstname($decode_address->billing->firstname)->setCustomerLastname($decode_address->billing->lastname);
            }
            catch (Exception $e) {

            }
            $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));
            $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
            $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
            $order->save();
            if($quote_data['qty_flag']==1)
            {
            $quantity_error         = '';
            } else { $quantity_error         = $this->updateQuantityAfterOrder($Jproduct); }
            $res["status"]          = 1;
            $res["id"]              = $order->getId();
            $res["orderid"]         = $order->getIncrementId();
            $res["transid"]         = $order->getPayment()->getTransactionId();
            $res["shipping_method"] = $shipping_code;
            $res["payment_method"]  = $payment_code;
            $res["quantity_error"]  = $quantity_error;
            $order->addStatusHistoryComment("Order was placed using Mobile App")->setIsVisibleOnFront(false)->setIsCustomerNotified(false);
            if ($res["orderid"] > 0 && ($payment_code == "cashondelivery" || $payment_code == "banktransfer" || $payment_code == "free")) {
                $this->ws_sendorderemail($res["orderid"]);
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                $res["order_status"] = "PROCESSING";
            } else {
                $order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
                $res["order_status"] = "PENDING_PAYMENT";
            }
        }
        catch (Exception $except) {
            $res["status"]          = 0;
            $res["shipping_method"] = $shipping_code;
            $res["payment_method"]  = $payment_code;
        }

        return $res;
    }*/

    public function placeorder($custid, $Jproduct, $store, $address, $couponCode, $is_create_quote, $transid, $payment_code, $shipping_code, $currency, $message)
    {
        $res            = array();
        $quantity_error = array();
        try {
            $quote_data = $this->prepareQuote($custid, $Jproduct, $store, $address, $shipping_code, $couponCode, $currency, 1, 0);

            if ($quote_data["status"] == "error") {
                return $quote_data;
            }
            $quote        = Mage::getModel('sales/quote')->load($quote_data['quote_id']);
            $quote        = $this->setQuoteGiftMessage($quote, $message, $custid);
            $quote        = $this->setQuotePayment($quote, $payment_code, $transid);
            $convertQuote = Mage::getSingleton('sales/convert_quote');
            try {
                if ($quote->getIsVirtual()) {
                    $order = $convertQuote->addressToOrder($quote->getBillingAddress());
                } else {
                    $order = $convertQuote->addressToOrder($quote->getShippingAddress());
                }
                if (!$quote->getIsVirtual()) {
                    $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
                }
            }
            catch (Exception $Exc) {
                echo $Exc->getMessage();
            }
            $items = $quote->getAllItems();
            foreach ($items as $item) {
                $orderItem = $convertQuote->itemToOrderItem($item);
                if ($item->getParentItem()) {
                    $orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
                }
                $order->addItem($orderItem);

            }
            try {
                $decode_address = json_decode(base64_decode($address));
                $order->setCustomer_email($decode_address->billing->email);
                $order->setCustomerFirstname($decode_address->billing->firstname)->setCustomerLastname($decode_address->billing->lastname);
            }
            catch (Exception $e) {
		echo $e->getMessage();
            }
            $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));
            $order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
            if (!$quote->getIsVirtual()) {
                $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
            }
            $order->save();
            //Mage::dispatchEvent('checkout_type_onepage_save_order_after', array('order'=>$order, 'quote'=>$quote));
            //$quote->setIsActive(0);
            //$quote->save();
	    $quantity_error         = $this->updateQuantityAfterOrder($Jproduct);
	    $res["status"]          = 1;
            $res["id"]              = $order->getId();
            $res["orderid"]         = $order->getIncrementId();
            $res["transid"]         = $order->getPayment()->getTransactionId();
            $res["shipping_method"] = $shipping_code;
            $res["payment_method"]  = $payment_code;
            $res["quantity_error"]  = $quantity_error;
            $order->addStatusHistoryComment("Order was placed using Mobile App")->setIsVisibleOnFront(false)->setIsCustomerNotified(false);
            if ($res["orderid"] > 0 && ($payment_code == "cashondelivery" || $payment_code == "banktransfer" || $payment_code == "free" || $payment_code == "paypal")) {
                $this->ws_sendorderemail($res["orderid"]);
                $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                $res["order_status"] = "PROCESSING";
            } else {
                $order->setState(Mage_Sales_Model_Order::STATE_NEW, true)->save();
                $res["order_status"] = "PENDING_PAYMENT";
            }
        }
        catch (Exception $except) {
	    echo $except->getMessage();
            $res["status"]          = 0;
            $res["shipping_method"] = $shipping_code;
            $res["payment_method"]  = $payment_code;
        }

        return $res;
    }

    /* ====================      Service to check ship2myid module availability   ================= */

    public function ws_shipmyidenabled()
    {
        $res           = array();
        $res["result"] = 0;
        $modules       = array_keys((array) Mage::getConfig()->getNode('modules')->children());
        if (Mage::getStoreConfig('clm24core/shippings/enabled') && in_array("Mofluid_MofluidShipMyId", $modules)) {
            $res["result"] = 1;
        }
        return $res;
    }

    /* ====================      Service to send order email after successfull payment of paypal   ================= */

    public function ws_sendorderemail($orderid)
    {
        $result["result"] = 0;
        if ($orderid > 0) {
            try {
                $order = Mage::getModel('sales/order');
                $order->loadByIncrementId($orderid);
                if ($order->email_sent != 1) {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
                    $order->sendNewOrderEmail();
                    $order->setEmailSent(true);
                    $result["result"] = 1;
                } else {
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, 'Gateway has authorized the payment.');
                }
                $order->save();
            }
            catch (Exception $ex) {
                //$echo= $ex->getMessage();
            }
        }
        return $result;
    }

    /* ====================  Service to fetch all product of Store ================= */

    public function ws_productSearchHelp($store_id)
    {
        $cache     = Mage::app()->getCache();
        $cache_key = "mofluid_search_autosuggestion_store" . $store_id;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));
        $res                = array();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;
        try {
            $collection = Mage::getResourceModel('catalog/product_collection')->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addAttributeToSelect('name')->addAttributeToSelect('id')->addStoreFilter($store_id)->addAttributeToFilter('status', 1)->addAttributeToFilter('type_id', array(
                'in' => array(
                    Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                    Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE
                )
            ))->addAttributeToFilter('is_in_stock', array(
                'in' => array(
                    $is_in_stock_option,
                    1
                )
            ))->addAttributeToFilter('visibility', 4)->load();
            foreach ($collection as $_product) {
                $stock_status   = 1;
                $is_in_stock    = $_product->getStockItem()->getIsInStock();
                $stock_quantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
                //Uncomment if prevent uncategorized products
                /* if(!count($_product->getCategoryIds()) ){
                continue;
                } */


                if ($is_in_stock <= 0 || $stock_quantity <= 0) {
                    $stock_status = 0;
                }
                $res[] = array(
                    "id" => $_product->getId(),
                    "name" => $_product->getName(),
                    "stock_status" => $stock_status
                );
            }
        }
        catch (Exception $ex) {
            $res = $ex->getMessage();
        }
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($res);
    }

    public function ws_countryStateList($store_id, $country)
    {

        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
             $cache_key = "mofluid_country_statelist_store" . $store_id . "_country" . $country;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));

        $state = array();
        if ($country != "") {
            try {
                $_states = $us = Mage::getModel('directory/region_api')->items($country);

                if (count($_states) > 0) {
                    foreach ($_states as $_state) {
                        if ($_state['code'] != "")
                            $state[$_state['code']] = $_state['name'];
                    }
                } else {
                    $state["result"] = "0";
                }
            }
            catch (Exception $exception) {
                $state["result"] = "0";
            }
        }
        if($enable){
			$cache->save(json_encode($state), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($state);
    }

    /* =======================get all mofluid extensions===================== */

    public function ws_getmofluidextension()
    {
        $connection      = Mage::getSingleton('core/resource')->getConnection('core_read');
        $resource        = Mage::getSingleton('core/resource');
        $modules         = Mage::getConfig()->getNode('modules')->children();
        $modulesArray    = (array) $modules;
        $module_name_arr = array();
        foreach ($modulesArray as $key => $val) {
            if ($val->active) {
                try {
                    $module_name_arr[] = $key;
                }
                catch (Exception $ex) {

                }
            }
        }
        $selectresource              = $connection->select()->from(Mage::getSingleton('core/resource')->getTableName('mofluidadmin/mofluidresource'), array(
            '*'
        ));
        $MofluidResourcedata         = $connection->fetchAll($selectresource);
        $mofluid_available_resource  = array();
        $mofluid_final_resource_data = array();
        $mofluid_final_resource      = array();
        $found                       = 0;
        foreach ($module_name_arr as $mkey => $mval) {
            foreach ($MofluidResourcedata as $mrkey => $mrval) {
                if ($mrval['module'] == $mval && $mrval['sendbuildmode'] != 0) {
                    $mofluid_available_resource[] = $mrval['resource'];
                    $found                        = 1;
                }
            }
        }
        return ($mofluid_available_resource);
    }

    /* =====================get CMS Pages================== */

    public function getallCMSPages($store, $pageId)
    {
		$cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_cmspage_" . $pageId . "_store" . $store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        $page_data            = array();
        $page                 = Mage::getModel('cms/page')->load($pageId);
        //    	$page_data=$page->getData();
        $page_data["title"]   = $page->getTitle();
        //    		$page_data["content"] = $page->getContent();
        $page_data["content"] = Mage::helper('cms')->getPageTemplateProcessor()->filter($page->getContent());
        //	$page_data = $this->formatpage_data($page_data);
        if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return ($page_data);
        // $echo="<pre>"; print_r($page_data);
    }

    function deliver_timeslot($store, $timeslot, $custid)
    {



        $res        = array();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();

        $select    = $connection->select()->from('mofluid_delivery_time', array(
            '*'
        )) // select * from tablename or use array('id','title') selected values
            ->where('user_id=?', $custid); // where id =$custid
        $rowsArray = $connection->fetchAll($select); // return all rows
        //$rowArray =$connection->fetchRow($select);

        if (count($rowsArray) > 0) {
            $__fields                  = array();
            $__fields['user_id']       = $custid;
            $__fields['delivery_time'] = $timeslot;
            $__where                   = $connection->quoteInto('user_id =?', $custid);
            $dat                       = $connection->update('mofluid_delivery_time', $__fields, $__where);
            if ($dat) {
                $res['dstatus'] = 1;
            } else {
                $res['dstatus'] = 0;
            }
        } else {

            $__fields                  = array();
            $__fields['user_id']       = $custid;
            $__fields['delivery_time'] = $timeslot;
            $dat                       = $connection->insert('mofluid_delivery_time', $__fields);
            if ($dat) {
                $res['dstatus'] = 1;
            } else {
                $res['dstatus'] = 0;
            }
        }
        $connection->commit();
        return $res;
    }

    function getdeliver_timeslot($store, $custid)
    {
        $res        = array();
        $connection = Mage::getSingleton('core/resource')->getConnection('core_write');
        $connection->beginTransaction();

        $select    = $connection->select()->from('mofluid_delivery_time', array(
            '*'
        )) // select * from tablename or use array('id','title') selected values
            ->where('user_id=?', $custid); // where id =$custid
        $rowsArray = $connection->fetchAll($select); // return all rows
        $rowArray  = $connection->fetchRow($select);
        $abc       = explode(",", $rowArray['delivery_time']);

        foreach ($abc as $a) {
            array_push($res, trim($a));
        }

        $connection->commit();
        return $res;
    }

    /* =====================get payment method================== */

    public function ws_getpaymentmethod()
    {
        //Get all payment method values and status from mofluid payment module
        $mofluid_pay_connection     = Mage::getSingleton('core/resource')->getConnection('core_read');
        $mofluid_pay_selectresource = $mofluid_pay_connection->select()->from(Mage::getSingleton('core/resource')->getTableName('mofluid_paymentcod/payment'), array(
            '*'
        ));
        $mofluid_pay_data           = $mofluid_pay_connection->fetchAll($mofluid_pay_selectresource);
        $connection      = Mage::getSingleton('core/resource')->getConnection('core_read');
        $resource        = Mage::getSingleton('core/resource');
        $modules         = Mage::getConfig()->getNode('modules')->children();
        $modulesArray    = (array) $modules;
        $module_name_arr = array();
        foreach ($modulesArray as $key => $val) {
            if ($val->active) {
                if (strpos($key, "Mofluid_Payment") !== false || $key == "Mage_Secureebs" || strpos($key, "MofluidCustom_Payment") !== false || strpos($key, "MofluidExtra_Payment") !== false) {
                    try {
                        $payment_key       = str_replace("MofluidCustom", "", $key);
                        $payment_key       = str_replace("MofluidExtra", "", $payment_key);
                        $payment_key       = str_replace("Mofluid", "", $payment_key);
                        $payment_key       = str_replace("_Payment", "", $payment_key);
                        $module_name_arr[] = $payment_key;
                    }
                    catch (Exception $ex) {

                    }
                }
            }
        }
        //Verify all payment extensions exists on magento site
        foreach ($mofluid_pay_data as $paykey => $payvalue) {
            $mofluid_pg_code = $payvalue["payment_method_code"];
            if (!$this->check_pay_method_in_array($module_name_arr, $mofluid_pg_code)) {
                $mofluid_pay_data[$paykey]["payment_method_status"] = "0";
            }
            //Check Dependency of EBS Payment Method with Mage_Secureebs module
            if ($payvalue["payment_method_code"] == "ebs") {
                if (!$this->check_pay_method_in_array($module_name_arr, "Mage_Secureebs")) {
                    $mofluid_pay_data[$paykey]["payment_method_status"] = "0";
                }
            }
            //Get Title and Instructions for Bank Transfer payment Method and update the array
            if ($payvalue["payment_method_code"] == "banktransfer") {
                try {
                    $mofluid_pay_data[$paykey]["payment_method_title"]               = Mage::getModel("payment/method_banktransfer")->getTitle();
                    $mofluid_pay_data[$paykey]["payment_method_display_description"] = str_replace("\n", "<br>", Mage::getModel("payment/method_banktransfer")->getInstructions());
                }
                catch (Exception $ex) {
                    $echo= $ex->getMessage();
                    $this->getResponse()->setBody($echo);
                }
            }
        }
        return ($mofluid_pay_data);
    }

    private function check_pay_method_in_array($pay_method_all_arr, $pay_method_single)
    {
        foreach ($pay_method_all_arr as $key => $value) {
            if (strpos($pay_method_single, $value) !== false) {
                return 1;
            }
        }
        return 0;
    }

    /* =======================get all mofluid app countries===================== */

    public function ws_mofluidappcountry($mofluid_store)
    {

        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_country_store" . $mofluid_store;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        $res                = array();
        $country_sort_array = array();
        try {
            $collection = Mage::getModel('directory/country')->getCollection()->loadByStore($mofluid_store);
            foreach ($collection as $country) {
                $mofluid_country["country_id"]   = $country->getId();
                $mofluid_country["country_name"] = $country->getName();
                $mofluid_country_arr[]           = $mofluid_country;
                $country_sort_array[]            = $country->getName();
            }

            array_multisort($country_sort_array, SORT_ASC, $mofluid_country_arr);
            $res["mofluid_countries"] = $mofluid_country_arr;

            $res["mofluid_default_country"]["country_id"] = Mage::getStoreConfig('general/country/default', $mofluid_store);
            return $res;
        }
        catch (Exception $ex) {
            $echo= $ex->getMessage();
            $this->getResponse()->setBody($echo);
        }
       if($enable){
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
        return $res;
    }

    /* =======================get all mofluid app states===================== */

    public function ws_mofluidappstates($mofluid_store, $countryid)
    {

        //~ $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		//~ $enable = $cacheEnable->getData('mofluid_cs_status');
		//~ if($enable){
			//~ $cache     = Mage::app()->getCache();
			//~ $cache_key = "mofluid_states_store" . $mofluid_store . "_countryid" . $countryid;
			//~ if($cache->load($cache_key))
			//~ return json_decode($cache->load($cache_key));
		//~ }
		$count = 0;
        $res = array();
        $mofluid_region = array();
        try {
            $collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($countryid)->load();
            foreach ($collection as $region) {
                $mofluid_region[$count]["region_id"]   = $region->code;
                $mofluid_region[$count]["region_name"] = $region->default_name;
                $count++;
            }
            $res["mofluid_regions"]      = $mofluid_region;
        }
        catch (Exception $ex) {
			echo $ex->getMessage();
        }
        return $res;
         //~ if($enable){
			//~ $cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		//~ }
        //~ return $res;
    }
public function ws_updateOrderStatus($order_id,$status)
{
$order=Mage::getModel('sales/order')->load($order_id);
switch($status){
case "pending":
$order->setState(Mage_Sales_Model_Order::STATE_NEW, true)->save();
break;
case "pending_payment":
$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true)->save();
break;
case "processing":
$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
break;
case "completed" :
$order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true)->save();
break;
case "closed" :
$order->setState(Mage_Sales_Model_Order::STATE_CLOSED, true)->save();
break;
case "canceled" :
$order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->save();
break;
case "holded" :
$order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true)->save();
break;
}

}

    /* ============================gift message======================== */

    public function ws_checkGiftMessage($store)
    {
        $res["status"] = 0;
        $myGiftMessage = array();
        if ($store < 1) {
            $res["msg"] = "Store not valid";
            return $res;
        }
        $gift_message_type = Mage::getStoreConfig('sales/gift_options');
        if ($gift_message_type["allow_order"] == 0)
            $res["allow_order"] = 0;
        else if ($gift_message_type["allow_order"] == 1)
            $res["allow_order"] = 1;
        if ($gift_message_type["allow_items"] == 0)
            $res["allow_items"] = 0;
        else if ($gift_message_type["allow_items"] == 1)
            $res["allow_items"] = 1;
        return $res;
    }

    /* =================================check product============== */

    public function ws_checkProductGiftMessage($store, $Jproduct)
    {
        $res["status"] = "0";
        $productid     = json_decode($Jproduct, true);
        if (count($productid) == 0)
            return $res;
        $res["status"] = "1";
        foreach ($productid as $key => $value) {
            $product = Mage::getModel('catalog/product');
            $product->load($value);
            if ($product->getGift_message_available() == 1 || $product->getGift_message_available() == "")
                $res["data"][$value] = "1";
            else if ($product->getGift_message_available() == 0)
                $res["data"][$value] = "0";
        }
        return ($res);
    }

    /* =========================ebs payment=========================== */

    public function ws_ebspayment($store, $service, $paymentdata)
    {

	//~ $paymentinfo='{
					//~ "account_id"	:"21944",
					//~ "channel"		:"0" ,
					//~ "mode"     		:"TEST",
					//~ "currency"    	:"INR",
					//~ "reference_no"	:"20",
					//~ "return_url"	:"http://127.0.0.1/magento2"
					//~ }';
	//~ $adderinfo='{
					//~ "address"		:"noida",
					//~ "amount"      	:"340",
					//~ "city"			:"noida",
					//~ "country"		:"IN",
					//~ "description" 	:"abc",
					//~ "email"			:"qwerty@gmail.com",
					//~ "name"			:"kaleshwar",
					//~ "phone"			:"2345678",
					//~ "postal_code"	:"201344",
					//~ "state"			:"UP"
					//~
				//~ }';

          //~ $paymentdata='{
						//~
						//~ "account_id"	:"21944",
						//~ "address"		:"noida",
						//~ "amount"      	:"340",
						//~ "channel"		:"0" ,
						//~ "city"			:"noida",
						//~ "country"		:"IN",
						//~ "currency"    	:"INR",
						//~ "description" 	:"abc",
						//~ "email"			:"qwerty@gmail.com",
						//~ "mode"     		:"TEST",
						//~ "name"			:"kaleshwar",
						//~ "phone"			:"2345678",
						//~ "postal_code"	:"201344",
					    //~ "reference_no"	:"20",
						//~ "return_url"	:"http://127.0.0.1/magento2",
						//~ "state"			:"UP"
						//~
					 //~
				//~ }';

	//~ print_r($paymentdata);
	//~ die;
	$paymentdata=base64_decode($paymentdata);


        $mofluid_ebs_data = json_decode($paymentdata);
		//	$hashData="";
				$hashData		="ad1f341c42805bb3c0324ef859170ba6";

		foreach ($mofluid_ebs_data as $key => $value){
			if (strlen($value) > 0) {
				//$hashData .= $value.'|';
				$hashData .= '|'.$value;
			}

		}
		//~ print_r($hashData);
				//~ die;

		if (strlen($hashData) > 0) {
			$secure_hash = strtoupper(hash("sha512",$hashData));
		}

         //$mofluid_ebs_hash       = $mofluid_ebs_data->hash;
		$mofluid_ebs_hash 		  = $secure_hash;
        $mofluid_ebs_account_id   = $mofluid_ebs_data->account_id;
        $mofluid_ebs_channel      = $mofluid_ebs_data->channel;
        $mofluid_ebs_return_url   = $mofluid_ebs_data->return_url;
        $mofluid_ebs_mode         = $mofluid_ebs_data->mode;
        $mofluid_ebs_currency	  = $mofluid_ebs_data->currency;
        $mofluid_ebs_reference_no = $mofluid_ebs_data->reference_no;
        $mofluid_ebs_amount       = $mofluid_ebs_data->amount;
        $mofluid_ebs_description  = $mofluid_ebs_data->description;
        $mofluid_ebs_name         = $mofluid_ebs_data->name;
        $mofluid_ebs_address      = $mofluid_ebs_data->address;
        $mofluid_ebs_city         = $mofluid_ebs_data->city;
        $mofluid_ebs_state        = $mofluid_ebs_data->state;
        $mofluid_ebs_postal_code  = $mofluid_ebs_data->postal_code;
        $mofluid_ebs_country      = $mofluid_ebs_data->country;
        $mofluid_ebs_phone        = $mofluid_ebs_data->phone;
        $mofluid_ebs_email        = $mofluid_ebs_data->email;

        $mofluid_ebs_form = '<center><h2>Please wait, your order is being processed and you will be redirected to the EBS website.</h2></center>
							<center><br/><br/>If you are not automatically redirected to EBS within 5 seconds...<br/><br/>
						   	<form  method="post" action="https://secure.ebs.in/pg/ma/payment/request" name="frmTransaction" id="frmTransaction">

							<input name="account_id" type="hidden" value="' . $mofluid_ebs_account_id . '">
							<input name="address" type="hidden" maxlength="255" value="'.$mofluid_ebs_address.'" />
							<input name="amount" type="hidden" value="'.$mofluid_ebs_amount.'"/>
							<input name="channel" type="hidden" value="'.$mofluid_ebs_channel.'">
							<input name="city" type="hidden" maxlength="255" value="'.$mofluid_ebs_city.'" />
							<input name="country" type="hidden" maxlength="255" value="'.$mofluid_ebs_country.'" />
							<input name="currency" type="hidden" size="60" value="'.$mofluid_ebs_currency.'" />
							<input name="description" type="hidden" value="'.$mofluid_ebs_description.'" />
							<input name="email" type="hidden" size="60" value="'.$mofluid_ebs_email.'" />
							<input name="mode" type="hidden" size="60" value="'.$mofluid_ebs_mode.'" />
							<input name="name" type="hidden" maxlength="255" value="'.$mofluid_ebs_name.'" />
							<input name="phone" type="hidden" maxlength="255" value="'.$mofluid_ebs_phone.'" />
							<input name="postal_code" type="hidden" maxlength="255" value="'.$mofluid_ebs_postal_code.'" />
							<input name="reference_no" type="hidden" value="'.$mofluid_ebs_reference_no.'" />
							<input name="return_url" type="hidden" size="60" value="'.$mofluid_ebs_return_url.'" />
							<input name="state" type="hidden" value="'.$mofluid_ebs_state.'">
							<input name="secure_hash" type="hidden" value="'.$mofluid_ebs_hash.'">
							<input name="submitted" value="Click here" type="submit" />
						    </form></center><script>document.frmTransaction.submit();</script>';

        $echo= $mofluid_ebs_form;
       $this->getResponse()->setBody($echo);
    }
    /* ===========================================mofluid eb response===================== */

    public function ws_mofluid_ebs_pgresponse()
    {

ini_set('display_errors',1);
error_reporting(E_ALL);

$secret_key = "ad1f341c42805bb3c0324ef859170ba6";	 // Pass Your Registered Secret Key from EBS secure Portal
//if(!$this->is_empty($this->getRequest()->getParam()))
if(!$this->is_empty($this->getRequest()->getParam())){
	 //$response = $_REQUEST;
	 $response = $this->getRequest()->getParam();

     $sh = $response['SecureHash'];

     $params = $secret_key;
     ksort($response);
		    foreach ($response as $key => $value){
		        if (strlen($value) > 0 and $key!='SecureHash') {
				        $params .= '|'.$value;
			        }
		        }

    //$hashValue = strtoupper(md5($params));//for MD5
    //$hashValue = strtoupper(hash("sha1",$params));//for SHA1
    $hashValue = strtoupper(hash("sha512",$params));// for SHA512

  	 //~ if($sh!=$hashValue)
		 //~ $echo= "<center><h3>Hash validation Failed!</H3></center>";
}
?>
<HTML>
<HEAD>
<TITLE>E-Billing Solutions Pvt Ltd - Payment Page</TITLE>
<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">
<style>
	h1       { font-family:Arial,sans-serif; font-size:24pt; color:#08185A; font-weight:100; margin-bottom:0.1em}
    h2.co    { font-family:Arial,sans-serif; font-size:24pt; color:#FFFFFF; margin-top:0.1em; margin-bottom:0.1em; font-weight:100}
    h3.co    { font-family:Arial,sans-serif; font-size:16pt; color:#000000; margin-top:0.1em; margin-bottom:0.1em; font-weight:100}
    h3       { font-family:Arial,sans-serif; font-size:16pt; color:#08185A; margin-top:0.1em; margin-bottom:0.1em; font-weight:100}
    body     { font-family:Verdana,Arial,sans-serif; font-size:11px; color:#08185A;}
	th 		 { font-size:12px;background:#015289;color:#FFFFFF;font-weight:bold;height:30px;}
	td 		 { font-size:12px;background:#DDE8F3}
	.pageTitle { font-size:24px;}
</style>
</HEAD>
<BODY LEFTMARGIN=0 TOPMARGIN=0 MARGINWIDTH=0 MARGINHEIGHT=0 bgcolor="#ECF1F7">
<center>
<table width='100%' cellpadding='0' cellspacing="0" ><tr><th width='90%'>&nbsp;</th></tr></table>
	<center><h3>Response</H3></center>
    <table width="600" cellpadding="2" cellspacing="2" border="0">
        <tr>
            <th colspan="2">Transaction Details</th>
        </tr>
				<script>
					var response = '<?php $echo= $response;$this->getResponse()->setBody($echo); ?>' ;
					androidInterfaceCallback(response);
                </script>

<?php

		//~ foreach( $response as $key => $value) {

?>
		<tr>
            <td class="fieldName" width="50%">ResponseMessage</td>
            <td class="fieldName" align="left" width="50%"><?php $echo= $response['ResponseMessage']; $this->getResponse()->setBody($echo);?></td>
        </tr>
        <tr>
            <td class="fieldName" width="50%">Amount</td>
            <td class="fieldName" align="left" width="50%"><?php $echo= $response['Amount'];$this->getResponse()->setBody($echo); ?></td>
        </tr>
         <tr>
            <td class="fieldName" width="50%">ResponseCode</td>
            <td class="fieldName" align="left" width="50%"><?php $echo= $response['ResponseCode'];$this->getResponse()->setBody($echo); ?></td>
        </tr>
        <tr>
            <td class="fieldName" width="50%">TransactionID</td>
            <td class="fieldName" align="left" width="50%"><?php $echo= $response['TransactionID'];$this->getResponse()->setBody($echo); ?></td>
        </tr>

<?php
		//}
?>
	</table>
</center>
<table width='100%' cellpadding='0' cellspacing="0" ><tr><th width='90%'>&nbsp;</th></tr></table>
</body>
</html>;
<?php
    }

    /* ============================print payment response=================== */

    public function ws_printpaymentresponse($store, $mofluidpayaction, $postdata, $mofluid_payment_mode, $mofluid_order_id_unsecure)
    {

        $paypal_url = 'https://www.paypal.com/cgi-bin/webscr';
        if (strtolower($mofluid_payment_mode) == "test") {
            $paypal_url = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
        }
        if ($mofluidpayaction == "success") {
            try {
                if ($postdata['mofluid_order_id'] == "" || $postdata['mofluid_order_id'] == null) {

                    $mofluid_order_id = $mofluid_order_id_unsecure;
                    $this->ws_sendorderemail($mofluid_order_id);
                } else {
                    $mofluid_order_id = $postdata['mofluid_order_id'];
                    $this->ws_sendorderemail($mofluid_order_id);
                }
            }
            catch (Exception $ex) {

            }


            $echo= '<html>
							 <head>
								 <title>Success</title>
								 <meta name="viewport" content="width = 100%" />
								 <meta name="viewport" content="initial-scale=2.5, user-scalable=no" />
							 </head>
							 <body>
								 <center>
									 <h3>Thank you for your order.</h3>
								 </center>';
								 $this->getResponse()->setBody($echo);

            if (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPod') || strstr($_SERVER['HTTP_USER_AGENT'], 'Android') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad')) {
                $dis_body = "<br><br><b>Your transaction was successfull.</b><br><br><br>See Your mail for more details";
            } else {
                $dis_body = "<br><b>Payment Details :- </b><br>";
                $dis_body .= "<br>Payment Status : <b>" . $postdata['payment_status'] . "</b>";
                $dis_body .= "<br>Transaction ID : <b>" . $postdata['txn_id'] . "</b>";
                $dis_body .= "<br>Order ID : " . $postdata['item_name'];
                $dis_body .= "<br>Payment Date : " . $postdata['payment_date'];
                $dis_body .= "<br>Amount : " . $postdata['mc_gross'] . $postdata['mc_currency'];
                $dis_body .= "<br><br>See Your mail " . $postdata['payer_email'] . " for more details";
            }

            $dis_body .= "<br><br><br><br>Please close this window.";
            $echo= '<center>' . $dis_body . '</center></body></html>';
            $this->getResponse()->setBody($echo);
        } else if ($mofluidpayaction == "ipn") {
            $this->validate_ipn($paypal_url, $postdata);
        } else if ($mofluidpayaction == "cancel") {
            $echo= "<html><head><title>Canceled</title></head><body><center><h3>The order was canceled.</h3></center>";
            $this->getResponse()->setBody($echo);
            $echo= "<br><br><br><center>Please Close this window</center></body></html>";
            $this->getResponse()->setBody($echo);
        } else {
            $echo= "<br>Unknown Response<br>";
            $this->getResponse()->setBody($echo);
            //print_r($postdata);
        }
    }

    function validate_ipn($paypal_url, $postdata)
    {
        $ipn_response;
        $log_ipn_results;
        // parse the paypal URL
        $url_parsed  = parse_url($paypal_url);
        $post_string = '';
        foreach ($postdata as $field => $value) {
            $ipn_data["$field"] = $value;
            $post_string .= $field . '=' . urlencode(stripslashes($value)) . '&';
        }

        $post_string .= "cmd=_notify-validate"; // append ipn command
        $fp = fsockopen("ssl://" . $url_parsed['host'], "443", $err_num, $err_str, 30);
        if (!$fp) {
            $last_error = "fsockopen error no. $errnum: $errstr";
            return false;
        } else {
            fputs($fp, "POST $url_parsed[path] HTTP/1.1\r\n");
            fputs($fp, "Host: $url_parsed[host]\r\n");
            fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n");
            fputs($fp, "Content-length: " . strlen($post_string) . "\r\n");
            fputs($fp, "Connection: close\r\n\r\n");
            fputs($fp, $post_string . "\r\n\r\n");
            while (!feof($fp)) {
                $ipn_response .= fgets($fp, 1024);
            }
            fclose($fp); // close connection
        }

        //if (eregi("VERIFIED", $ipn_response)) {
        if (preg_match("/".$pat."/i",$text)) {
            return true;
        } else {
            $last_error = 'IPN Validation Failed.';
            return false;
        }
    }

    public function subscribeNewsletter($user_mail)
    {
        $subscriber = Mage::getModel(ânewsletter / subscriberâ);
        $subscriber->subscribe($user_mail);
    }

    function get_configurable_products($productid, $currentcurrencycode)
    {
        /* $cache = Mage::app()->getCache();
        $cache_key = "mofluid_configurable_products_productid".$productid."_currency".$currentcurrencycode;
        if($cache->load($cache_key))
        return json_decode($cache->load($cache_key));
        */
        $basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();
        try {
            $product_data = Mage::getModel('catalog/product')->load($productid);
            if ($product_data->getTypeID() == "configurable") {
                $productAttributeOptions      = $product_data->getTypeInstance(true)->getConfigurableAttributes($product_data);
                $conf                         = Mage::getModel('catalog/product_type_configurable')->setProduct($product_data);
                $simple_collection            = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                $configurable_array_selection = array();
                $configurable_array           = array();
                $configurable_count           = 0;
                $relation_count               = 0;
                //load data for children
                foreach ($simple_collection as $product) {
                    $a                          = Mage::getModel('catalog/product')->load($product->getId());
                    $taxClassId                 = $a->getData("tax_class_id");
                    $taxClasses                 = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                    $taxRate                    = $taxClasses["value_" . $taxClassId];
                    $b                          = (($taxRate) / 100) * ($product->getPrice());
                    $product_for_custom_options = Mage::getModel('catalog/product')->load($product->getId());
                    $all_custom_option_array    = array();
                    $attVal                     = $product_for_custom_options->getOptions();
                    $optStr                     = "";
                    $inc                        = 0;

                    $configurable_count = 0;
                    foreach ($productAttributeOptions as $attribute) {
                        $productAttribute                                              = $attribute->getProductAttribute();
                        $productAttributeId                                            = $productAttribute->getId();
                        $attributeValue                                                = $product->getData($productAttribute->getAttributeCode());
                        $attributeLabel                                                = $product->getData($productAttribute->getValue());
                        $configurable_array[$configurable_count]["productAttributeId"] = $productAttributeId;
                        $configurable_array[$configurable_count]["selected_value"]     = $attributeValue;
                        $configurable_array[$configurable_count]["label"]              = $attribute->getLabel();
                        $configurable_array[$configurable_count]["is_required"]        = $productAttribute->getIsRequired();
                        $configurable_array[$configurable_count]["id"]                 = $product->getId();
                        $configurable_array[$configurable_count]["sku"]                = $product->getSku();
                        $configurable_array[$configurable_count]["name"]               = $product->getName();
                        $configurable_array[$configurable_count]["image"]              = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $product->getImage();
                        $defaultsplprice                                               = str_replace(",", "", number_format($product->getSpecialprice(), 2));
                        $configurable_array[$configurable_count]["spclprice"]          = strval($this->convert_currency($defaultsplprice, $basecurrencycode, $currentcurrencycode));
                        $configurable_array[$configurable_count]["price"]              = number_format($product->getPrice(), 2);
                        $configurable_array[$configurable_count]["currencysymbol"]     = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                        $configurable_array[$configurable_count]["created_date"]       = $product->getCreatedAt();
                        $configurable_array[$configurable_count]["is_in_stock"]        = $product->getStockItem()->getIsInStock();
                        $configurable_array[$configurable_count]["stock_quantity"]     = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
                        $configurable_array[$configurable_count]["type"]               = $product->getTypeID();
                        $configurable_array[$configurable_count]["shipping"]           = Mage::getStoreConfig('carriers/flatrate/price');
                        $configurable_array[$configurable_count]["data"]               = $this->ws_get_configurable_option_attributes($attributeValue, $attribute->getLabel(), $productid, $currentcurrencycode);
                        $configurable_array[$configurable_count]["tax"]                = number_format($b, 2);
                        try {
                            $configurable_curr_arr = (array) $configurable_array[$configurable_count]["data"];
                            if ($configurable_relation[$relation_count]) {
                                $configurable_relation[$relation_count] = $configurable_relation[$relation_count] . ', ' . str_replace(',', '', str_replace(' ', '', $configurable_curr_arr["label"]));
                            } else {
                                $configurable_relation[$relation_count] = str_replace(',', '', str_replace(' ', '', $configurable_curr_arr["label"]));
                            }
                        }
                        catch (Exception $err) {
                            $echo= 'Error : ' . $err->getMessage();
                            $this->getResponse()->setBody($echo);
                        }
                        $configurable_count++;
                    }
                    $relation_count++;
                    $configurable_array_selection[] = $configurable_array;
                }
                $configurable_array_selection['relation'] = $configurable_relation;
                //load data for parent
                $mofluid_all_product_images               = array();
                $mofluid_non_def_images                   = array();
                $mofluid_product                          = Mage::getModel('catalog/product')->load($productid);
                $mofluid_baseimage                        = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

                foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
                    $mofluid_imagecame = $mofluid_image->getUrl();
                    if ($mofluid_baseimage == $mofluid_imagecame) {
                        $mofluid_all_product_images[] = $mofluid_image->getUrl();
                    } else {
                        $mofluid_non_def_images[] = $mofluid_image->getUrl();
                    }
                }
                $mofluid_all_product_images  = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
                $configurable_product_parent = array();
                $parent_a                    = Mage::getModel('catalog/product')->load($product_data->getId());
                $parent_taxClassId           = $parent_a->getData("tax_class_id");
                $parent_taxClasses           = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $parent_taxRate              = $parent_taxClasses["value_" . $parent_taxClassId];
                $parent_b                    = (($parent_taxRate) / 100) * ($product_data->getPrice());


                $parent_all_custom_option_array = array();
                $parent_attVal                  = $product_data->getOptions();
                $parent_optStr                  = "";
                $parent_inc                     = 0;
                $has_custom_option              = 0;
                foreach ($parent_attVal as $parent_optionKey => $parent_optionVal) {
                    $parent_all_custom_option_array[$parent_inc]['custom_option_name']        = $parent_optionVal->getTitle();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_id']          = $parent_optionVal->getId();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_is_required'] = $parent_optionVal->getIsRequired();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_type']        = $parent_optionVal->getType();
                    $parent_all_custom_option_array[$parent_inc]['sort_order']                = $parent_optionVal->getSortOrder();
                    $parent_all_custom_option_array[$parent_inc]['all']                       = $parent_optionVal->getData();

                    if ($parent_all_custom_option_array[$parent_inc]['all']['default_price_type'] == "percent") {
                        $parent_all_custom_option_array[$parent_inc]['all']['price'] = number_format((($product->getPrice() * $parent_all_custom_option_array[$parent_inc]['all']['price']) / 100), 2);
                    } else {
                        $parent_all_custom_option_array[$parent_inc]['all']['price'] = number_format($parent_all_custom_option_array[$inc]['all']['price'], 2);
                    }

                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'];
                    $parent_inner_inc  = 0;
                    $has_custom_option = 1;
                    foreach ($parent_optionVal->getValues() as $parent_valuesKey => $parent_valuesVal) {
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['id']         = $parent_valuesVal->getId();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['title']      = $parent_valuesVal->getTitle();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['price']      = number_format($parent_valuesVal->getPrice(), 0);
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['price_type'] = $parent_valuesVal->getPriceType();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['sku']        = $parent_valuesVal->getSku();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['sort_order'] = $parent_valuesVal->getSortOrder();

                        $parent_inner_inc++;
                    }
                    $parent_inc++;
                }
                $configurable_product_parent["id"]       = $product_data->getId();
                $configurable_product_parent["sku"]      = $product_data->getSku();
                $configurable_product_parent["name"]     = $product_data->getName();
                $configurable_product_parent["category"] = $product_data->getCategoryIds();
                $configurable_product_parent["discount"] = number_format($product_data->getFinalPrice(), 2);
                $configurable_product_parent["shipping"] = Mage::getStoreConfig('carriers/flatrate/price');
                $configurable_product_parent["image"]    = $mofluid_all_product_images; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/catalog/product'.$product_data->getImage();
                //$defaultprice = str_replace(",","", number_format($product_data->getPrice(),2));
                //$configurable_product_parent["price"] = strval($this->convert_currency($defaultprice,$basecurrencycode,$currentcurrencycode));

                $defaultprice                          = str_replace(",", "", ($product_data->getFinalPrice()));
                $configurable_product_parent["price"]  = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
                $defaultsprice                         = str_replace(",", "", ($product_data->getSpecialprice()));
                $configurable_product_parent["sprice"] = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));

                //$defaultsprice =  str_replace(",","",number_format($product_data->getSpecialprice(),2));
                //$configurable_product_parent["sprice"] = strval($this->convert_currency($defaultsprice,$basecurrencycode,$currentcurrencycode));
                $configurable_product_parent["currencysymbol"]    = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                $configurable_product_parent["url"]               = $product_data->getProductUrl();
                $configurable_product_parent["description"]       = $product_data->getDescription();
                $configurable_product_parent["shortdes"]          = $product_data->getShortDescription();
                $configurable_product_parent["type"]              = $product_data->getTypeID();
                $configurable_product_parent["created_date"]      = $product_data->getCreatedAt();
                $configurable_product_parent["is_in_stock"]       = $product_data->getStockItem()->getIsInStock();
                $configurable_product_parent["quantity"]          = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_data->getId())->getQty();
                $configurable_product_parent["visibility"]        = $product_data->isVisibleInSiteVisibility();
                $configurable_product_parent["weight"]            = $product_data->getWeight();
                $configurable_product_parent["status"]            = $product_data->getStatus();
                $configurable_product_parent["variation"]         = $product_data->getColor();
                $configurable_product_parent["custom_option"]     = $parent_all_custom_option_array;
                $configurable_product_parent["tax"]               = number_format($parent_b, 2);
                $configurable_product_parent["has_custom_option"] = $has_custom_option;

                $configurable_array_selection["parent"] = $configurable_product_parent;
                $configurable_array_selection["size"]   = sizeof($configurable_array_selection);

                // Add code for custom attribute start:
                $custom_attr       = array();
                //$product = $product_data;
                $attributes        = $product_data->getAttributes();
                //$echo= count($attributes);
                $custom_attr_count = 0;
                foreach ($attributes as $attribute) {
                    if ($attribute->is_user_defined && $attribute->is_visible) {
                        $attribute_value = $attribute->getFrontend()->getValue($product);
                        if ($attribute_value == null || $attribute_value == "") {
                            continue;
                        } else {
                            $custom_attr["data"][$custom_attr_count]["attr_code"]  = $attribute->getAttributeCode();
                            $custom_attr["data"][$custom_attr_count]["attr_label"] = $attribute->getStoreLabel($product);
                            $custom_attr["data"][$custom_attr_count]["attr_value"] = $attribute_value;
                            ++$custom_attr_count;
                        }
                    }
                }
                $custom_attr["total"]                             = $custom_attr_count;
                $configurable_array_selection["custom_attribute"] = $custom_attr;
                // Add code for custom attribute end:
                // $cache->save(json_encode($configurable_array_selection), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
                return $configurable_array_selection;
                //$echo= "<pre>"; print_r(json_encode($configurable_array_selection)); die;
            } else
                return "Product Id " . $productid . " is not a Configurable Product";
        }
        catch (Exception $ex) {
            return "Error";
        }
    }

    /*   * ************************************************************************************************************************************** */

    function get_configurable_products_description($productid, $currentcurrencycode,$store)
    {
        /*$cache     = Mage::app()->getCache();
        $cache_key = "mofluid_configurable_products_productid" . $productid . "_currency" . $currentcurrencycode;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));*/
        Mage::app()->setCurrentStore($store);
        $basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();
        try {
            $product_data = Mage::getModel('catalog/product')->load($productid);

            if ($product_data->getTypeID() == "configurable") {

                $productAttributeOptions      = $product_data->getTypeInstance(true)->getConfigurableAttributes($product_data);
                $conf                         = Mage::getModel('catalog/product_type_configurable')->setProduct($product_data);
                $simple_collection            = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                $configurable_array_selection = array();
                $configurable_array           = array();
                $configurable_count           = 0;
                $relation_count               = 0;
                //load data for children
                //print_r($product_data); die;
                foreach ($simple_collection as $product) {
                    $a                          = Mage::getModel('catalog/product')->load($product->getId());
                    $taxClassId                 = $a->getData("tax_class_id");
                    $taxClassId                 = $product_data->getData("tax_class_id");
                    $taxClasses                 = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                    $taxRate                    = $taxClasses["value_" . $taxClassId];
                    $b                          = (($taxRate) / 100) * ($product->getPrice());
                    $product_for_custom_options = Mage::getModel('catalog/product')->load($product->getId());
                    $all_custom_option_array    = array();
                    $attVal                     = $product_for_custom_options->getOptions();
                    $optStr                     = "";
                    $inc                        = 0;

                    $configurable_count = 0;
                    foreach ($productAttributeOptions as $attribute) {
                        $productAttribute                                              = $attribute->getProductAttribute();
                        $productAttributeId                                            = $productAttribute->getId();
                        $attributeValue                                                = $product->getData($productAttribute->getAttributeCode());
                        $attributeLabel                                                = $product->getData($productAttribute->getValue());
                        $configurable_array[$configurable_count]["productAttributeId"] = $productAttributeId;
                        $configurable_array[$configurable_count]["selected_value"]     = $attributeValue;
                        $configurable_array[$configurable_count]["label"]              = $attribute->getLabel();
                        $configurable_array[$configurable_count]["is_required"]        = $productAttribute->getIsRequired();
                        $configurable_array[$configurable_count]["id"]                 = $product->getId();
                        $configurable_array[$configurable_count]["sku"]                = $product->getSku();
                        $configurable_array[$configurable_count]["name"]               = $product->getName();
                        //$configurable_array[$configurable_count]["image"] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/catalog/product'.$product->getImage();
                        $defaultsplprice                                               = str_replace(",", "", number_format($product->getFinalPrice(), 2));
                        $configurable_array[$configurable_count]["spclprice"]          = strval($this->convert_currency($defaultsplprice, $basecurrencycode, $currentcurrencycode));
                        $configurable_array[$configurable_count]["price"]              = number_format($product->getPrice(), 2);
                        $configurable_array[$configurable_count]["currencysymbol"]     = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                        $configurable_array[$configurable_count]["created_date"]       = $product->getCreatedAt();
                        $configurable_array[$configurable_count]["is_in_stock"]        = $product->getStockItem()->getIsInStock();
                        $configurable_array[$configurable_count]["stock_quantity"]     = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
                        $configurable_array[$configurable_count]["type"]               = $product->getTypeID();
                        $configurable_array[$configurable_count]["shipping"]           = Mage::getStoreConfig('carriers/flatrate/price');
                        $configurable_array[$configurable_count]["data"]               = $this->ws_get_configurable_option_attributes($attributeValue, $attribute->getLabel(), $productid, $currentcurrencycode,$store);
                        $configurable_array[$configurable_count]["tax"]                = number_format($b, 2);
                        $stock_product = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
         				$stock_data = $stock_product->getData();
                      //  $configurable_array[$configurable_count]["stock"]                = $stock_data;
                        try {
                            $configurable_curr_arr = (array) $configurable_array[$configurable_count]["data"];
                            if ($configurable_relation[$relation_count]) {
                                $configurable_relation[$relation_count] = $configurable_relation[$relation_count] . ', ' . str_replace(',', '', str_replace(' ', '', $configurable_curr_arr["label"]));
                            } else {
                                $configurable_relation[$relation_count] = str_replace(',', '', str_replace(' ', '', $configurable_curr_arr["label"]));
                            }
                        }
                        catch (Exception $err) {
                            $echo= 'Error : ' . $err->getMessage();
                            $this->getResponse()->setBody($echo);
                        }
                        $configurable_count++;
                    }
                    $relation_count++;
                    $configurable_array_selection[] = $configurable_array;
                }
                $configurable_array_selection['relation'] = $configurable_relation;
                //load data for parent
                /*$mofluid_all_product_images = array();
                $mofluid_non_def_images = array();
                $mofluid_product = Mage::getModel('catalog/product')->load($productid);
                $mofluid_baseimage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

                foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
                $mofluid_imagecame = $mofluid_image->getUrl();
                if ($mofluid_baseimage == $mofluid_imagecame) {
                $mofluid_all_product_images[] = $mofluid_image->getUrl();
                } else {
                $mofluid_non_def_images[] = $mofluid_image->getUrl();
                }
                }
                $mofluid_all_product_images = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
                */
                $configurable_product_parent              = array();
                $parent_a                                 = Mage::getModel('catalog/product')->load($product_data->getId());
                $parent_taxClassId                        = $parent_a->getData("tax_class_id");
                $parent_taxClasses                        = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $parent_taxRate                           = $parent_taxClasses["value_" . $parent_taxClassId];
                $parent_b                                 = (($parent_taxRate) / 100) * ($product_data->getPrice());


                $parent_all_custom_option_array = array();
                $parent_attVal                  = $product_data->getOptions();
                $parent_optStr                  = "";
                $parent_inc                     = 0;
                $has_custom_option              = 0;
                foreach ($parent_attVal as $parent_optionKey => $parent_optionVal) {
                    $parent_all_custom_option_array[$parent_inc]['custom_option_name']        = $parent_optionVal->getTitle();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_id']          = $parent_optionVal->getId();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_is_required'] = $parent_optionVal->getIsRequired();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_type']        = $parent_optionVal->getType();
                    $parent_all_custom_option_array[$parent_inc]['sort_order']                = $parent_optionVal->getSortOrder();
                    $parent_all_custom_option_array[$parent_inc]['all']                       = $parent_optionVal->getData();

                    if ($parent_all_custom_option_array[$parent_inc]['all']['default_price_type'] == "percent") {
                        $parent_all_custom_option_array[$parent_inc]['all']['price'] = number_format((($product->getPrice() * $parent_all_custom_option_array[$parent_inc]['all']['price']) / 100), 2);
                    } else {
                        $parent_all_custom_option_array[$parent_inc]['all']['price'] = number_format($parent_all_custom_option_array[$inc]['all']['price'], 2);
                    }

                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'];
                    $parent_inner_inc  = 0;
                    $has_custom_option = 1;
                    foreach ($parent_optionVal->getValues() as $parent_valuesKey => $parent_valuesVal) {
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['id']         = $parent_valuesVal->getId();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['title']      = $parent_valuesVal->getTitle();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['price']      = number_format($parent_valuesVal->getPrice(), 0);
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['price_type'] = $parent_valuesVal->getPriceType();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['sku']        = $parent_valuesVal->getSku();
                        $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['sort_order'] = $parent_valuesVal->getSortOrder();

                        $parent_inner_inc++;
                    }
                    $parent_inc++;
                }
                $configurable_product_parent["id"]       = $product_data->getId();
                $configurable_product_parent["sku"]      = $product_data->getSku();
                $configurable_product_parent["name"]     = $product_data->getName();
                $configurable_product_parent["category"] = $product_data->getCategoryIds();
                $configurable_product_parent["discount"] = number_format($product_data->getFinalPrice(), 2);
                $configurable_product_parent["shipping"] = Mage::getStoreConfig('carriers/flatrate/price');
                //$configurable_product_parent["image"] = $mofluid_all_product_images; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/catalog/product'.$product_data->getImage();
                //$defaultprice = str_replace(",","", number_format($product_data->getPrice(),2));
                //$configurable_product_parent["price"] = strval($this->convert_currency($defaultprice,$basecurrencycode,$currentcurrencycode));

                $defaultprice                          = str_replace(",", "", ($product_data->getPrice()));
                $configurable_product_parent["price"]  = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
                $defaultsprice                         = str_replace(",", "", ($product_data->getFinalPrice()));
                if($defaultprice == $defaultsprice){
                	$defaultsprice                         = 0;
                }
                $configurable_product_parent["sprice"] = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));

                //$defaultsprice =  str_replace(",","",number_format($product_data->getSpecialprice(),2));
                //$configurable_product_parent["sprice"] = strval($this->convert_currency($defaultsprice,$basecurrencycode,$currentcurrencycode));
                $configurable_product_parent["currencysymbol"]    = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                $configurable_product_parent["url"]               = $product_data->getProductUrl();
                $configurable_product_parent["description"]       = $product_data->getDescription();
                $configurable_product_parent["shortdes"]          = $product_data->getShortDescription();
                $configurable_product_parent["type"]              = $product_data->getTypeID();
                $configurable_product_parent["created_date"]      = $product_data->getCreatedAt();
                $configurable_product_parent["is_in_stock"]       = $product_data->getStockItem()->getIsInStock();
                $configurable_product_parent["quantity"]          = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_data->getId())->getQty();
                $configurable_product_parent["visibility"]        = $product_data->isVisibleInSiteVisibility();
                $configurable_product_parent["weight"]            = $product_data->getWeight();
                $configurable_product_parent["status"]            = $product_data->getStatus();
                $configurable_product_parent["variation"]         = $product_data->getColor();
                $configurable_product_parent["custom_option"]     = $parent_all_custom_option_array;
                $configurable_product_parent["tax"]               = number_format($parent_b, 2);
                $configurable_product_parent["has_custom_option"] = $has_custom_option;

                $configurable_array_selection["parent"] = $configurable_product_parent;
                $configurable_array_selection["size"]   = sizeof($configurable_array_selection);

                // Add code for custom attribute start:
                $custom_attr       = array();
                //$product = $product_data;
                $attributes        = $product_data->getAttributes();
                //$echo= count($attributes);
                $custom_attr_count = 0;
                foreach ($attributes as $attribute) {
                    if ($attribute->is_user_defined && $attribute->is_visible) {
                        $attribute_value = $attribute->getFrontend()->getValue($product);
                        if ($attribute_value == null || $attribute_value == "") {
                            continue;
                        } else {
                            $custom_attr["data"][$custom_attr_count]["attr_code"]  = $attribute->getAttributeCode();
                            $custom_attr["data"][$custom_attr_count]["attr_label"] = $attribute->getStoreLabel($product);
                            $custom_attr["data"][$custom_attr_count]["attr_value"] = $attribute_value;
                            ++$custom_attr_count;
                        }
                    }
                }
                $custom_attr["total"]                             = $custom_attr_count;
                $configurable_array_selection["custom_attribute"] = $custom_attr;
                // Add code for custom attribute end:
                /*$cache->save(json_encode($configurable_array_selection), $cache_key, array(
                    "mofluid"
                ), $this->CACHE_EXPIRY);*/
              // $echo= "<pre>"; print_r($configurable_array_selection); die;
                return $configurable_array_selection;

            } else
                return "Product Id " . $productid . " is not a Configurable Product";
        }
        catch (Exception $ex) {
            return "Error";
        }
    }


    /*   * ********************************************************************************************************************************** */

    function get_configurable_products_image($productid, $currentcurrencycode)
    {
        $cache     = Mage::app()->getCache();
        $cache_key = "mofluid_configurable_products_productidimg" . $productid . "_currency" . $currentcurrencycode;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));

        //$basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();
        try {
            $product_data = Mage::getModel('catalog/product')->load($productid);
            if ($product_data->getTypeID() == "configurable") {
                $productAttributeOptions      = $product_data->getTypeInstance(true)->getConfigurableAttributes($product_data);
                $conf                         = Mage::getModel('catalog/product_type_configurable')->setProduct($product_data);
                $simple_collection            = $conf->getUsedProductCollection()->addAttributeToSelect('*')->addFilterByRequiredOptions();
                $configurable_array_selection = array();
                $configurable_array           = array();
                $configurable_count           = 0;
                $relation_count               = 0;
                //load data for children
                foreach ($simple_collection as $product) {
                    /*$a = Mage::getModel('catalog/product')->load($product->getId());
                    $taxClassId = $a->getData("tax_class_id");
                    $taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                    $taxRate = $taxClasses["value_" . $taxClassId];
                    $b = (($taxRate) / 100) * ($product->getPrice());
                    $product_for_custom_options = Mage::getModel('catalog/product')->load($product->getId());
                    $all_custom_option_array = array();
                    $attVal = $product_for_custom_options->getOptions();
                    $optStr = "";
                    $inc = 0;*/

                    $configurable_count = 0;
                    foreach ($productAttributeOptions as $attribute) {
                        /* $productAttribute = $attribute->getProductAttribute();
                        $productAttributeId = $productAttribute->getId();
                        $attributeValue = $product->getData($productAttribute->getAttributeCode());
                        $attributeLabel = $product->getData($productAttribute->getValue());*/
                        //$configurable_array[$configurable_count]["productAttributeId"] = $productAttributeId;
                        //$configurable_array[$configurable_count]["selected_value"] = $attributeValue;
                        //$configurable_array[$configurable_count]["label"] = $attribute->getLabel();
                        //$configurable_array[$configurable_count]["is_required"] = $productAttribute->getIsRequired();
                        $configurable_array[$configurable_count]["id"]    = $product->getId();
                        //$configurable_array[$configurable_count]["sku"] = $product->getSku();
                        $configurable_array[$configurable_count]["name"]  = $product->getName();
                        $configurable_array[$configurable_count]["image"] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $product->getImage();
                        $defaultsplprice                                  = str_replace(",", "", number_format($product->getSpecialprice(), 2));
                        //$configurable_array[$configurable_count]["spclprice"] = strval($this->convert_currency($defaultsplprice,$basecurrencycode,$currentcurrencycode));
                        //$configurable_array[$configurable_count]["price"] = number_format($product->getPrice(),2);
                        //$configurable_array[$configurable_count]["currencysymbol"] = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                        //$configurable_array[$configurable_count]["created_date"] = $product->getCreatedAt();
                        //$configurable_array[$configurable_count]["is_in_stock"] = $product->getStockItem()->getIsInStock();
                        //$configurable_array[$configurable_count]["stock_quantity"] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
                        /* $configurable_array[$configurable_count]["type"] = $product->getTypeID();
                        $configurable_array[$configurable_count]["shipping"] = Mage::getStoreConfig('carriers/flatrate/price');
                        $configurable_array[$configurable_count]["data"] = $this->ws_get_configurable_option_attributes($attributeValue, $attribute->getLabel(), $productid, $currentcurrencycode);
                        $configurable_array[$configurable_count]["tax"] = number_format($b,2) ; */
                        /* try {
                        $configurable_curr_arr = (array) $configurable_array[$configurable_count]["data"];
                        if ($configurable_relation[$relation_count]) {
                        $configurable_relation[$relation_count] = $configurable_relation[$relation_count] . ', ' . str_replace(',', '', str_replace(' ', '', $configurable_curr_arr["label"]));
                        } else {
                        $configurable_relation[$relation_count] = str_replace(',', '', str_replace(' ', '', $configurable_curr_arr["label"]));
                        }
                        } catch (Exception $err) {
                        $echo= 'Error : ' . $err->getMessage();
                        }*/
                        $configurable_count++;
                    }
                    $relation_count++;
                    $configurable_array_selection[] = $configurable_array;
                }
                // $configurable_array_selection['relation'] = $configurable_relation;
                //load data for parent
                $mofluid_all_product_images = array();
                $mofluid_non_def_images     = array();
                $mofluid_product            = Mage::getModel('catalog/product')->load($productid);
                $mofluid_baseimage          = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();

                foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
                    $mofluid_imagecame = $mofluid_image->getUrl();
                    if ($mofluid_baseimage == $mofluid_imagecame) {
                        $mofluid_all_product_images[] = $mofluid_image->getUrl();
                    } else {
                        $mofluid_non_def_images[] = $mofluid_image->getUrl();
                    }
                }
                $mofluid_all_product_images = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
                /* $configurable_product_parent = array();
                $parent_a = Mage::getModel('catalog/product')->load($product_data->getId());
                $parent_taxClassId = $parent_a->getData("tax_class_id");
                $parent_taxClasses = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
                $parent_taxRate = $parent_taxClasses["value_" . $parent_taxClassId];
                $parent_b = (($parent_taxRate) / 100) * ($product_data->getPrice());*/


                $parent_all_custom_option_array = array();
                $parent_attVal                  = $product_data->getOptions();
                $parent_optStr                  = "";
                $parent_inc                     = 0;
                $has_custom_option              = 0;
                foreach ($parent_attVal as $parent_optionKey => $parent_optionVal) {
                    /*$parent_all_custom_option_array[$parent_inc]['custom_option_name'] = $parent_optionVal->getTitle();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_id'] = $parent_optionVal->getId();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_is_required'] = $parent_optionVal->getIsRequired();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_type'] = $parent_optionVal->getType();
                    $parent_all_custom_option_array[$parent_inc]['sort_order'] = $parent_optionVal->getSortOrder();
                    $parent_all_custom_option_array[$parent_inc]['all'] = $parent_optionVal->getData();

                    if ($parent_all_custom_option_array[$parent_inc]['all']['default_price_type'] == "percent") {
                    $parent_all_custom_option_array[$parent_inc]['all']['price'] = number_format((($product->getPrice() * $parent_all_custom_option_array[$parent_inc]['all']['price']) / 100), 2);
                    } else {
                    $parent_all_custom_option_array[$parent_inc]['all']['price'] = number_format($parent_all_custom_option_array[$inc]['all']['price'], 2);
                    }*/

                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'];
                    $parent_inner_inc  = 0;
                    $has_custom_option = 1;
                    /*foreach ($parent_optionVal->getValues() as $parent_valuesKey => $parent_valuesVal) {
                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['id'] = $parent_valuesVal->getId();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['title'] = $parent_valuesVal->getTitle();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['price'] = number_format($parent_valuesVal->getPrice(), 0);
                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['price_type'] = $parent_valuesVal->getPriceType();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['sku'] = $parent_valuesVal->getSku();
                    $parent_all_custom_option_array[$parent_inc]['custom_option_value_array'][$parent_inner_inc]['sort_order'] = $parent_valuesVal->getSortOrder();

                    $parent_inner_inc++;
                    }*/
                    $parent_inc++;
                }
                $configurable_product_parent["id"]    = $product_data->getId();
                //$configurable_product_parent["sku"] = $product_data->getSku();
                $configurable_product_parent["name"]  = $product_data->getName();
                /* $configurable_product_parent["category"] = $product_data->getCategoryIds();
                $configurable_product_parent["discount"] = number_format($product_data->getFinalPrice(),2);
                $configurable_product_parent["shipping"] = Mage::getStoreConfig('carriers/flatrate/price'); */
                $configurable_product_parent["image"] = $mofluid_all_product_images; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'media/catalog/product'.$product_data->getImage();
                //$defaultprice = str_replace(",","", number_format($product_data->getPrice(),2));
                //$configurable_product_parent["price"] = strval($this->convert_currency($defaultprice,$basecurrencycode,$currentcurrencycode));

                $defaultprice  = str_replace(",", "", ($product_data->getFinalPrice()));
                //$configurable_product_parent["price"] = strval(round($this->convert_currency($defaultprice,$basecurrencycode,$currentcurrencycode),2));
                $defaultsprice = str_replace(",", "", ($product_data->getSpecialprice()));
                //$configurable_product_parent["sprice"] = strval(round($this->convert_currency($defaultsprice,$basecurrencycode,$currentcurrencycode),2));
                //$defaultsprice =  str_replace(",","",number_format($product_data->getSpecialprice(),2));
                //$configurable_product_parent["sprice"] = strval($this->convert_currency($defaultsprice,$basecurrencycode,$currentcurrencycode));
                /* $configurable_product_parent["currencysymbol"] = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
                $configurable_product_parent["url"] = $product_data->getProductUrl();
                $configurable_product_parent["description"] = $product_data->getDescription();
                $configurable_product_parent["shortdes"] = $product_data->getShortDescription();
                $configurable_product_parent["type"] = $product_data->getTypeID();
                $configurable_product_parent["created_date"] = $product_data->getCreatedAt();
                $configurable_product_parent["is_in_stock"] = $product_data->getStockItem()->getIsInStock();
                $configurable_product_parent["quantity"] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_data->getId())->getQty();
                $configurable_product_parent["visibility"] = $product_data->isVisibleInSiteVisibility();
                $configurable_product_parent["weight"] = $product_data->getWeight();
                $configurable_product_parent["status"] = $product_data->getStatus();
                $configurable_product_parent["variation"] = $product_data->getColor();
                $configurable_product_parent["custom_option"] = $parent_all_custom_option_array;
                $configurable_product_parent["tax"] =  number_format($parent_b,2) ;
                $configurable_product_parent["has_custom_option"] = $has_custom_option; */

                $configurable_array_selection["parent"] = $configurable_product_parent;
                $configurable_array_selection["size"]   = sizeof($configurable_array_selection);

                // Add code for custom attribute start:
                // $custom_attr = array();
                //$product = $product_data;
                //$attributes = $product_data->getAttributes();
                //$echo= count($attributes);
                // $custom_attr_count = 0;
                /* foreach ($attributes as $attribute) {
                if ($attribute->is_user_defined && $attribute->is_visible) {
                $attribute_value = $attribute->getFrontend()->getValue($product);
                if ($attribute_value == null || $attribute_value == "") {
                continue;
                } else {
                $custom_attr["data"][$custom_attr_count]["attr_code"] = $attribute->getAttributeCode();
                $custom_attr["data"][$custom_attr_count]["attr_label"] = $attribute->getStoreLabel($product);
                $custom_attr["data"][$custom_attr_count]["attr_value"] = $attribute_value;
                ++$custom_attr_count;
                }
                }
                }*/
                $custom_attr["total"] = $custom_attr_count;
                //$configurable_array_selection["custom_attribute"] = $custom_attr;
                // Add code for custom attribute end:
        if($enable){
			$cache->save(json_encode($configurable_array_selection), $cache_key, array("mofluid"), $this->CACHE_EXPIRY);
		}
                return $configurable_array_selection;
                //$echo= "<pre>"; print_r(json_encode($configurable_array_selection)); die;
            } else
                return "Product Id " . $productid . " is not a Configurable Product";
        }
        catch (Exception $ex) {
            return "Error";
        }
    }

    /*   * ********************************************************************************************************************************** */
          function ws_get_configurable_option_attributes1($selectedValue, $label, $productid, $currentcurrencycode,$store)
    {
        $basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();
        $product_data            = Mage::getModel('catalog/product')->load($productid);
        $productAttributeOptions = $product_data->getTypeInstance(true)->getConfigurableAttributesAsArray($product_data);
        $attributeOptions        = array();
        $count                   = 0;
        foreach ($productAttributeOptions as $productAttribute) {
            $count = 0;
            foreach ($productAttribute['values'] as $attribute) {
                $attributeOptions[$productAttribute['label']][$productAttribute['attribute_code']]["label"]                      = $attribute['label'];
                $attributeOptions[$productAttribute['label']][$productAttribute['attribute_code']]["attribute_code"]    = $productAttribute['attribute_code'];
                $attributeOptions[$productAttribute['label']][$productAttribute['attribute_code']]["attribute_id"]      = $productAttribute['attribute_id'];
                $count++;

            }


        }

        return ($attributeOptions[$label]);
    }
 function ws_get_configurable_option_attributes($selectedValue, $label, $productid, $currentcurrencycode,$store)
   {
	        /*$cache     = Mage::app()->getCache();
        $cache_key = "mofluid_configurable_options_productid" . $productid . "_currency" . $currentcurrencycode . "_selectedValue" . $selectedValue . "_label" . $label;
        if ($cache->load($cache_key))
            return json_decode($cache->load($cache_key));*/


        //get base currency from magento
      //  Mage::app()->setCurrentStore($store);
        $basecurrencycode = Mage::app()->getStore()->getBaseCurrencyCode();

        $product_data            = Mage::getModel('catalog/product')->load($productid);
        $productAttributeOptions = $product_data->getTypeInstance(true)->getConfigurableAttributesAsArray($product_data);
        $attributeOptions        = array();
        $count                   = 0;
        $colors  =  array(
        'aliceblue'=>'F0F8FF',
        'antiquewhite'=>'FAEBD7',
        'aqua'=>'00FFFF',
        'aquamarine'=>'7FFFD4',
        'azure'=>'F0FFFF',
        'beige'=>'F5F5DC',
        'bisque'=>'FFE4C4',
        'black'=>'000000',
        'blanchedalmond '=>'FFEBCD',
        'blue'=>'0000FF',
        'blueviolet'=>'8A2BE2',
        'brown'=>'A52A2A',
        'burlywood'=>'DEB887',
        'cadetblue'=>'5F9EA0',
        'chartreuse'=>'7FFF00',
        'chocolate'=>'D2691E',
        'coral'=>'FF7F50',
        'cornflowerblue'=>'6495ED',
        'cornsilk'=>'FFF8DC',
        'crimson'=>'DC143C',
        'cyan'=>'00FFFF',
        'darkblue'=>'00008B',
        'darkcyan'=>'008B8B',
        'darkgoldenrod'=>'B8860B',
        'darkgray'=>'A9A9A9',
        'darkgreen'=>'006400',
        'darkgrey'=>'A9A9A9',
        'darkkhaki'=>'BDB76B',
        'darkmagenta'=>'8B008B',
        'darkolivegreen'=>'556B2F',
        'darkorange'=>'FF8C00',
        'darkorchid'=>'9932CC',
        'darkred'=>'8B0000',
        'darksalmon'=>'E9967A',
        'darkseagreen'=>'8FBC8F',
        'darkslateblue'=>'483D8B',
        'darkslategray'=>'2F4F4F',
        'darkslategrey'=>'2F4F4F',
        'darkturquoise'=>'00CED1',
        'darkviolet'=>'9400D3',
        'deeppink'=>'FF1493',
        'deepskyblue'=>'00BFFF',
        'dimgray'=>'696969',
        'dimgrey'=>'696969',
        'dodgerblue'=>'1E90FF',
        'firebrick'=>'B22222',
        'floralwhite'=>'FFFAF0',
        'forestgreen'=>'228B22',
        'fuchsia'=>'FF00FF',
        'gainsboro'=>'DCDCDC',
        'ghostwhite'=>'F8F8FF',
        'gold'=>'FFD700',
        'goldenrod'=>'DAA520',
        'gray'=>'808080',
        'green'=>'008000',
        'greenyellow'=>'ADFF2F',
        'grey'=>'808080',
        'honeydew'=>'F0FFF0',
        'hotpink'=>'FF69B4',
        'indianred'=>'CD5C5C',
        'indigo'=>'4B0082',
        'ivory'=>'FFFFF0',
        'khaki'=>'F0E68C',
        'lavender'=>'E6E6FA',
        'lavenderblush'=>'FFF0F5',
        'lawngreen'=>'7CFC00',
        'lemonchiffon'=>'FFFACD',
        'lightblue'=>'ADD8E6',
        'lightcoral'=>'F08080',
        'lightcyan'=>'E0FFFF',
        'lightgoldenrodyellow'=>'FAFAD2',
        'lightgray'=>'D3D3D3',
        'lightgreen'=>'90EE90',
        'lightgrey'=>'D3D3D3',
        'lightpink'=>'FFB6C1',
        'lightsalmon'=>'FFA07A',
        'lightseagreen'=>'20B2AA',
        'lightskyblue'=>'87CEFA',
        'lightslategray'=>'778899',
        'lightslategrey'=>'778899',
        'lightsteelblue'=>'B0C4DE',
        'lightyellow'=>'FFFFE0',
        'lime'=>'00FF00',
        'limegreen'=>'32CD32',
        'linen'=>'FAF0E6',
        'magenta'=>'FF00FF',
        'maroon'=>'800000',
        'mediumaquamarine'=>'66CDAA',
        'mediumblue'=>'0000CD',
        'mediumorchid'=>'BA55D3',
        'mediumpurple'=>'9370D0',
        'mediumseagreen'=>'3CB371',
        'mediumslateblue'=>'7B68EE',
        'mediumspringgreen'=>'00FA9A',
        'mediumturquoise'=>'48D1CC',
        'mediumvioletred'=>'C71585',
        'midnightblue'=>'191970',
        'mintcream'=>'F5FFFA',
        'mistyrose'=>'FFE4E1',
        'moccasin'=>'FFE4B5',
        'navajowhite'=>'FFDEAD',
        'navy'=>'000080',
        'oldlace'=>'FDF5E6',
        'olive'=>'808000',
        'olivedrab'=>'6B8E23',
        'orange'=>'FFA500',
        'orangered'=>'FF4500',
        'orchid'=>'DA70D6',
        'palegoldenrod'=>'EEE8AA',
        'palegreen'=>'98FB98',
        'paleturquoise'=>'AFEEEE',
        'palevioletred'=>'DB7093',
        'papayawhip'=>'FFEFD5',
        'peachpuff'=>'FFDAB9',
        'peru'=>'CD853F',
        'pink'=>'FFC0CB',
        'plum'=>'DDA0DD',
        'powderblue'=>'B0E0E6',
        'purple'=>'800080',
        'red'=>'FF0000',
        'rosybrown'=>'BC8F8F',
        'royalblue'=>'4169E1',
        'saddlebrown'=>'8B4513',
        'salmon'=>'FA8072',
        'sandybrown'=>'F4A460',
        'seagreen'=>'2E8B57',
        'seashell'=>'FFF5EE',
        'sienna'=>'A0522D',
        'silver'=>'C0C0C0',
        'skyblue'=>'87CEEB',
        'slateblue'=>'6A5ACD',
        'slategray'=>'708090',
        'slategrey'=>'708090',
        'snow'=>'FFFAFA',
        'springgreen'=>'00FF7F',
        'steelblue'=>'4682B4',
        'tan'=>'D2B48C',
        'teal'=>'008080',
        'thistle'=>'D8BFD8',
        'tomato'=>'FF6347',
        'turquoise'=>'40E0D0',
        'violet'=>'EE82EE',
        'wheat'=>'F5DEB3',
        'white'=>'FFFFFF',
        'whitesmoke'=>'F5F5F5',
        'yellow'=>'FFFF00',
        'charcoal'=>'36454F',
        'yellowgreen'=>'9ACD32',
        'hawaii'=>'9ACD32',
        'black blue'=>'9ACD32');
        foreach ($productAttributeOptions as $productAttribute) {
            $count = 0;
           // print_r($productAttribute); die
            foreach ($productAttribute['values'] as $attribute) {
				$cname = strtolower($attribute['label']);
				$ccode="";
				if(array_key_exists($cname,$colors))
				  $ccode=$colors[$cname];
             //   $attributeOptions[$productAttribute['label']][$attribute['value_index']]["product_super_attribute_id"] = $attribute['product_super_attribute_id'];
                $attributeOptions[$productAttribute['label']][$attribute['value_index']]["value_index"]                = $attribute['value_index'];
                $attributeOptions[$productAttribute['label']][$attribute['value_index']]["label"]                      = $attribute['label'];
               // print_r($productAttribute['label'][$attribute['value_index']]["color_code"]); die;
                if($productAttribute['label'] == 'Color'){
					if($ccode){
					$attributeOptions[$productAttribute['label']][$attribute['value_index']]["color_code"]                 = '#'.$ccode;
						}
					else{
						$attributeOptions[$productAttribute['label']][$attribute['value_index']]["color_code"]             =  '#00FFFF';
						}
                 }
              //  $attributeOptions[$productAttribute['label']][$attribute['value_index']]["store_label"]                = $attribute['store_label'];
              //  $attributeOptions[$productAttribute['label']][$attribute['value_index']]["is_percent"]                 = $attribute['is_percent'];

                //$defaultprice = str_replace(",","", number_format($attribute['pricing_value'],2));
                //$attributeOptions[$productAttribute['label']][$attribute['value_index']]["pricing_value"] = str_replace(",","", strval($this->convert_currency($defaultprice,$basecurrencycode,$currentcurrencycode)));

                $defaultprice                                                                             = str_replace(",", "", ($attribute['pricing_value']));
               // $attributeOptions[$productAttribute['label']][$attribute['value_index']]["pricing_value"] = str_replace(",", "", strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2)));

                if ($attribute['is_percent'] == 1) {
                    /*if ($product_data->getSpecialprice() > 0 && $product_data->getSpecialprice() < $product_data->getPrice()) {
                        $defaultproductprice                                                                      = str_replace(",", "", ($product_data->getSpecialprice()));
                        $productprice                                                                             = strval(round($this->convert_currency($defaultproductprice, $basecurrencycode, $currentcurrencycode), 2));
                        $attributeOptions[$productAttribute['label']][$attribute['value_index']]["pricing_value"] = str_replace(",", "", round(((floatval($productprice) * floatval($attribute['pricing_value'])) / 100), 2));
                    } else {
                        $defaultproductprice                                                                      = str_replace(",", "", ($product_data->getPrice()));
                        $productprice                                                                             = strval(round($this->convert_currency($defaultproductprice, $basecurrencycode, $currentcurrencycode), 2));
                        $attributeOptions[$productAttribute['label']][$attribute['value_index']]["pricing_value"] = str_replace(",", "", round(((floatval($productprice) * floatval($attribute['pricing_value'])) / 100), 2));
                    }*/
                        $defaultproductprice                                                                      = str_replace(",", "", ($product_data->getFinalPrice()));
                        $productprice                                                                             = strval(round($this->convert_currency($defaultproductprice, $basecurrencycode, $currentcurrencycode), 2));
                        $attributeOptions[$productAttribute['label']][$attribute['value_index']]["pricing_value"] = str_replace(",", "", round(((floatval($productprice) * floatval($attribute['pricing_value'])) / 100), 2));

                }


                //$attributeOptions[$productAttribute['label']][$attribute['value_index']]["use_default_value"] = $attribute['use_default_value'];
               // $attributeOptions[$productAttribute['label']][$attribute['value_index']]["value_id"]          = $attribute['value_id'];
                //$attributeOptions[$productAttribute['label']][$attribute['value_index']]["frontend_label"]    = $productAttribute['frontend_label'];
              //  $attributeOptions[$productAttribute['label']][$attribute['value_index']]["attribute_code"]    = $productAttribute['attribute_code'];
                $attributeOptions[$productAttribute['label']][$attribute['value_index']]["attribute_id"]      = $productAttribute['attribute_id'];
                $count++;
            }
        }
        /*$cache->save(json_encode($attributeOptions[$label][$selectedValue]), $cache_key, array(
            "mofluid"
        ), $this->CACHE_EXPIRY);*/
        /*echo "input=";
         echo '<pre>';
        print_r($label);
        print_r($selectedValue);
        echo "-------------------<br>";
        echo '<pre>';
    print_r($attributeOptions);
     echo "-------------------<br>";*/
     if(array_key_exists($selectedValue,$attributeOptions[$label]))
     return $attributeOptions[$label][$selectedValue];	
   return null;
     
   }

    function ws_mofluid_reorder($store, $service, $jproduct, $orderId, $currentcurrencycode)
    {
        $productids = json_decode($jproduct);
        $countres   = 0;
        $res        = array();
        $order      = Mage::getModel('sales/order')->loadByIncrementId($orderId);
        #get all items
        $items      = $order->getAllItems();
        $itemcount  = count($items);
        $data       = array();
        $i          = 0;
        #loop for all order items
        foreach ($items as $itemId => $product) {
            $current_product_id         = $product->getProductId();
            $current_product_index      = $itemId;
            $has_custom_option          = 0;
            $custom_attr                = array();
            $current_product            = Mage::getModel('catalog/product')->load($current_product_id);
            if($current_product->getId()==NULL || $this->is_empty($current_product->getId()))
                continue;
	    $mofluid_all_product_images = array();
            $mofluid_non_def_images     = array();
            $mofluid_baseimage          = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $current_product->getImage();
            foreach ($current_product->getMediaGalleryImages() as $mofluid_image) {
                $mofluid_imagecame = $mofluid_image->getUrl();
                if ($mofluid_baseimage == $mofluid_imagecame) {
                    $mofluid_all_product_images[] = $mofluid_image->getUrl();
                } else {
                    $mofluid_non_def_images[] = $mofluid_image->getUrl();
                }
            }
            $mofluid_all_product_images              = array_merge($mofluid_all_product_images, $mofluid_non_def_images);
            //get base currency from magento
            $basecurrencycode                        = Mage::app()->getStore()->getBaseCurrencyCode();
            $res[$countres]["id"]                    = $current_product->getId();
            $res[$countres]["is_in_stock"]             = (bool)$current_product->getStockItem()->getIsInStock();
            $res[$countres]["sku"]                   = $current_product->getSku();
            $res[$countres]["name"]                  = $current_product->getName();
            $res[$countres]["category"]              = $current_product->getCategoryIds(); //'category';
            $res[$countres]["image"]                 = $mofluid_all_product_images[0]; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/catalog/product'.$product->getImage();
            $res[$countres]["url"]                   = $current_product->getProductUrl();
            $res[$countres]["description"]["full"]   = base64_encode($current_product->getDescription());
            $res[$countres]["description"]["short"]  = base64_encode($current_product->getShortDescription());
            $res[$countres]["quantity"]["available"] = Mage::getModel('cataloginventory/stock_item')->loadByProduct($current_product->getId())->getQty();
            $res[$countres]["quantity"]["order"]     = $product->getQtyOrdered();
            // $current_product->getQty(); //Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();//$product->getQty();
            $res[$countres]["visibility"]            = $current_product->isVisibleInSiteVisibility(); //getVisibility();
            $res[$countres]["type"]                  = $current_product->getTypeID();
            $res[$countres]["weight"]                = $current_product->getWeight();
            $res[$countres]["status"]                = $current_product->getStatus();
            //convert price from base currency to current currency
            $res[$countres]["currencysymbol"]        = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
            $defaultprice                            = str_replace(",", "", ($product->getPrice()));
            $res[$countres]["price"]                 = strval(round($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2));
            $discountprice                           = str_replace(",", "", ($product->getFinalPrice()));
            $res[$countres]["discount"]              = strval(round($this->convert_currency($discountprice, $basecurrencycode, $currentcurrencycode), 2));
            $defaultshipping                         = Mage::getStoreConfig('carriers/flatrate/price');
            $res[$countres]["shipping"]              = strval(round($this->convert_currency($defaultshipping, $basecurrencycode, $currentcurrencycode), 2));
            $defaultsprice                           = str_replace(",", "", ($product->getSpecialprice()));
            // Get the Special Price
            $specialprice                            = $current_product->getSpecialPrice();
            // Get the Special Price FROM date
            $specialPriceFromDate                    = $current_product->getSpecialFromDate();
            // Get the Special Price TO date
            $specialPriceToDate                      = $current_product->getSpecialToDate();
            // Get Current date
            $today                                   = time();
            if ($specialprice) {
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                    $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                } else {
                    $specialprice =0;
                }
            } else {
                $specialprice=0;
            }
            $current_product_options  = array();
            $res[$countres]["sprice"] = $specialprice;
            $has_custom_option        = 0;
            foreach ($product->getProductOptions() as $opt) {
                $has_custom_option       = 1;
                $current_product_options = $opt['options'];
                if (!$current_product_options) {
                    foreach ($opt as $opt_key => $opt_val) {
                        $current_product_options[$opt_val['option_id']] = $opt_val['option_value'];
                    }
                }
                break;
            } //foreach
            $res[$countres]["has_custom_option"] = $has_custom_option;
            if ($has_custom_option == 1) {
                $res[$countres]["custom_option"] = $current_product_options;
            }
            $res[$countres]["custom_attribute"] = $custom_attr;
            $countres++;
        }
        return ($res);
    }

    public function mofluidUpdateProfile($store, $service, $customerId, $JbillAdd, $JshippAdd, $profile, $billshipflag)
    {

    $billAdd  = json_decode(base64_decode($JbillAdd));
    $shippAdd = json_decode(base64_decode($JshippAdd));
    $profile  = json_decode(base64_decode($profile));
    //    $billAdd  = json_decode($JbillAdd);
      //  $shippAdd = json_decode($JshippAdd);
       // $profile  = json_decode($profile);

        $result                 = array();
        $result['billaddress']  = 0;
        $result['shippaddress'] = 0;
        $result['userprofile']  = 0;

        /* Update User Profile Data */

        $customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($profile->email);

        //check exists email address of users
        if ($customer->getId() && $customer->getId() != $customerId) {
            return $result;
        } else {
            if ($billshipflag == "billingaddress") {
				$_bill_address = array(
                    'firstname' => $billAdd->billfname,
                    'lastname' => $billAdd->billlname,
                    'street' => array(
                        '0' => $billAdd->billstreet1,
                        //'1' => $billAdd->billstreet2
                    ),
                    'city' => $billAdd->billcity,
                    'region_id' => '',
                    'region' => $billAdd->billstate,
                    'postcode' => $billAdd->billpostcode,
                    'country_id' => $billAdd->billcountry,
                    'telephone' => $billAdd->billphone
                );
                $billAddress   = Mage::getModel('customer/address');
                if ($defaultBillingId = $customer->getDefaultBilling()) {
                    $billAddress->load($defaultBillingId);
                    $billAddress->addData($_bill_address);
                } else {
                    $billAddress->setData($_bill_address)->setCustomerId($customerId)->setIsDefaultBilling('1')->setSaveInAddressBook('1');
                }
                try {
                    if ($billAddress->save())
                        $result['billaddress'] = 1;
                }
                catch (Exception $ex) {
                    Zend_Debug::dump($ex->getMessage());
                }
            } else {
                $_shipp_address = array(
                    'firstname' => $shippAdd->shippfname,
                    'lastname' => $shippAdd->shipplname,
                    'street' => array(
                        '0' => $shippAdd->shippstreet1,
                       // '1' => $shippAdd->shippstreet2
                    ),
                    'city' => $shippAdd->shippcity,
                    'region_id' => '',
                    'region' => $shippAdd->shippstate,
                    'postcode' => $shippAdd->shipppostcode,
                    'country_id' => $shippAdd->shippcountry,
                    'telephone' => $shippAdd->shippphone
                );
                $shippAddress   = Mage::getModel('customer/address');
                if ($defaultShippingId = $customer->getDefaultShipping()) {
                    $shippAddress->load($defaultShippingId);
                    $shippAddress->addData($_shipp_address);
                } else {
                    $shippAddress->setData($_shipp_address)->setCustomerId($customerId)->setIsDefaultShipping('1')->setSaveInAddressBook('1');
                }
                try {
                    if ($shippAddress->save())
                        $result['shippaddress'] = 1;
                }
                catch (Exception $ex) {
                    Zend_Debug::dump($ex->getMessage());
                }
            }



            return $result;
        }
    }

    // validate image requirement
    public function validateImageRequirement($type = NULL, $legacy_id, $width, $height, $theme, $is_all){
      // Case: Type is required
      $result = array(
        'status' => 'error'
      );
      if($this->is_empty($type)){
        $result['message'] = 'Type is required';
        return $result;
      }
      /*
       * Case: Type should be one of from these
       * 1. banner
       * 2. product
       * 3. category
       * */
      if($is_all){
        if(!in_array($type, array('banner', 'product'))){
          $result['message'] = 'Invalid type parameter, Valid parameters are: banner or product.';
          return $result;
        }
      }
      else {
        if(!in_array($type, array('product', 'category'))){
          $result['message'] = 'Invalid type parameter, Valid parameters are: product or category.';
          return $result;
        }
      }
      // Case: if type is not banner then legacy_id is required
      if(in_array($type, array('product', 'category')) && $this->is_empty($legacy_id)){
        $result['message'] = 'Id is required.';
        return $result;
      }
      // Case: if type is not banner then legacy_id must be integer
      if(in_array($type, array('product', 'category')) && !is_numeric($legacy_id)){
        $result['message'] = 'Id must be integer.';
        return $result;
      }
      // Case: width is required
      if($this->is_empty($width)){
        $result['message'] = 'Width is required.';
        return $result;
      }
      // Case: width must be integer
      if(!is_numeric($width)){
        $result['message'] = 'Width must be integer.';
        return $result;
      }
      // Case: height is required
      if($this->is_empty($height)){
        $result['message'] = 'Height is required.';
        return $result;
      }
      // Case: height must be integer
      if(!is_numeric($height)){
        $result['message'] = 'Height must be integer.';
        return $result;
      }
      $result = array(
        'status' => 'success'
      );
      return $result;
    }

    public function getImageOfSize($type = NULL, $legacy_id, $width, $height, $store_id){
      $is_valid_input = $this->validateImageRequirement($type, $legacy_id, $width, $height);
      if($is_valid_input['status'] == 'error'){
        return $is_valid_input;
      }
      Mage::app()->setCurrentStore($store_id);
      if($type == 'product'){
        $item = Mage::getModel('catalog/product')->load($legacy_id);
      }
      else if($type == 'category'){
        $item = Mage::getModel('catalog/category')->load($legacy_id);
      }

      $image_path =  Mage::getBaseDir('media') . DS . 'catalog' . DS . $type . DS . $item->getImage();
      if (file_exists($image_path)){
        $mofluid_cache_dir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'mofluid' . DS . $type;
        if (!is_dir($mofluid_cache_dir)) {
          mkdir($mofluid_cache_dir);
        }
        $output_dir = $mofluid_cache_dir . DS . $width . 'x' . $height;
        if (!is_dir($output_dir)) {
          mkdir($output_dir);
        }
        $path_info = pathinfo($image_path);
        $image_name = $path_info['filename'];
        $ext = $path_info['extension'];
        $output_file = $legacy_id . '-' . $width . 'x' . $height . '-' . $image_name . '.' . $ext;
        //if (!file_exists($output_dir . DS . $output_file)){
          $_image = new Varien_Image($image_path);
          $_image->constrainOnly(true);
          $_image->keepAspectRatio(true);
          $_image->keepFrame(false);
          $_image->keepTransparency(true);
          $_image->resize($width, $height);
          $_image->save($output_dir . DS . $output_file);
        //}
        $result['image'] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog' . DS . 'mofluid' . DS . $type . DS . $width . 'x' . $height . DS . $output_file;
      }
      return $result;
    }

    public function getAllImageOfSize($type = NULL, $legacy_id, $width, $height, $store_id, $theme){
      $is_valid_input = $this->validateImageRequirement($type, $legacy_id, $width, $height, $theme, TRUE);

      if($is_valid_input['status'] == 'error'){
        return $is_valid_input;
      }
      $images = array();
      Mage::app()->setCurrentStore($store_id);
      if($type == 'product'){
        $item = Mage::getModel('catalog/product')->load($legacy_id);
        $product_images =  $item->getMediaGalleryImages();
        foreach($product_images as $product_image) {
          $images[] = $product_image->getPath();
        }
      }
      else if($type == 'banner'){
        if($this->is_empty($theme)){
          $theme = 'modern';
        }
        $resource       = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $query          = "SELECT i.mofluid_image_value as image FROM mofluid_themes_images i JOIN mofluid_themes t ON t.mofluid_theme_id = i.mofluid_theme_id WHERE t.mofluid_theme_code = '$theme' AND i.mofluid_image_type = 'banner'";
        $rows        = $readConnection->fetchAll($query);
        foreach($rows as $row){
          $base_url = Mage::getBaseUrl();
          $banner_image = $row['image'];
          $split_image_url = explode($base_url, $banner_image);
          if(count($split_image_url) == 2){
            $images[] = Mage::getBaseDir() . DS . $split_image_url[1];
          }
        }
      }
      $result = array();
      foreach($images as $image_path) {
        if (file_exists($image_path)){
          $mofluid_cache_dir = Mage::getBaseDir('media') . DS . 'catalog' . DS . 'mofluid' . DS . $type;
          if (!is_dir($mofluid_cache_dir)) {
            mkdir($mofluid_cache_dir);
          }
          $output_dir = $mofluid_cache_dir . DS . $width . 'x' . $height;
          if (!is_dir($output_dir)) {
            mkdir($output_dir);
          }
          $path_info = pathinfo($image_path);
          $image_name = $path_info['filename'];
          $ext = $path_info['extension'];
          $output_file = $legacy_id . '-' . $width . 'x' . $height . '-' . $image_name . '.' . $ext;
          //if (!file_exists($output_dir . DS . $output_file)){
            $_image = new Varien_Image($image_path);
            $_image->constrainOnly(true);
            $_image->keepAspectRatio(true);
            $_image->keepFrame(false);
            $_image->keepTransparency(true);
            $_image->resize($width, $height);
            $_image->save($output_dir . DS . $output_file);
          //}
          $result['images'][] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog' . DS . 'mofluid' . DS . $type . DS . $width . 'x' . $height . DS . $output_file;
        }
      }
      return $result;
    }

    public function getProductImages($pid = NULL, $type, $get_all = FALSE){
      if($this->is_empty($pid)){
        return array(
          'status' => 'error',
          'message' => 'Product Id is required.'
        );
      }
      $_product = Mage::getModel('catalog/product')->load($pid);
      if($_product->getEntityId()){
       if(in_array($type, array('image', 'small_image', 'thumbnail'))){
          if($get_all){
            $_images = $_product->getMediaGalleryImages();
            $images = array();
            if($_images){
              foreach($_images as $_image){
                $images[] = (string) Mage::helper('catalog/image')->init($_product, $type, $_image->getFile());
              }
            }
            $image = $images;
          }
          else {
            $image = (string) Mage::helper('catalog/image')->init($_product, $type);
          }
        }
      }
      else {
        return array(
          'status' => 'error',
          'message' => 'Product with id ' . $pid . ' does not exist.',
        );
      }
      return $image;
    }

    /*
     * Webservice of Mydownload section:
     * @param $store_id
     * @param $cust_id
     */
    public function MyDownloads($store_id,$cust_id){
        $res = array();
        //$store_id = 1;
        //$cust_id = 333;
        $purchased = Mage::getResourceModel('downloadable/link_purchased_collection')
            ->addFieldToFilter('customer_id', $cust_id)
            ->addOrder('created_at', 'desc');
        //$this->setPurchased($purchased);
        $purchasedIds = array();
        foreach ($purchased as $_item) {
            $purchasedIds[] = $_item->getId();
        }
        if ($this->is_empty($purchasedIds)) {
            $purchasedIds = array(null);
        }
        $purchasedItems = Mage::getResourceModel('downloadable/link_purchased_item_collection')
            ->addFieldToFilter('purchased_id', array('in' => $purchasedIds))
            ->addFieldToFilter('status',
                array(
                    'nin' => array(
                        Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PENDING_PAYMENT,
                        Mage_Downloadable_Model_Link_Purchased_Item::LINK_STATUS_PAYMENT_REVIEW
                    )
                )
            )
            ->setOrder('item_id', 'desc');
		 $obj = new Mage_Downloadable_Block_Customer_Products_List();
		 $_items = $purchasedItems->getItems();
		 $download_data = array();
		 $mydownloads = array();
		 $base_url = Mage::getBaseUrl();
		 $count = 0;
		 foreach ($_items as $_item){
			 $link_title = $_item->getLinkTitle();
			 $order_date = $_item->getCreatedAt();
			 $link_hash = $_item->getLinkHash();
			 $download_url = $base_url.'downloadable/download/link/id/'.$link_hash;
			 //~ $download_data[$count]['order_id'] = $_item->getPurchased()->getOrderId();
			 $download_data[$count]['order_date'] = $obj->formatDate($order_date);
			 $download_data[$count]['product_name'] = $link_title;
			 $download_data[$count]['download_url'] = $download_url; //$obj->getDownloadUrl($_item);
			 $download_data[$count]['status'] = Mage::helper('downloadable')->__(ucfirst($_item->getStatus()));
			 $download_data[$count]['remaining_download'] = $obj->getRemainingDownloads($_item);
			 $count++;
         }
         $mydownloads['mydownloads'] = $download_data;
         return $mydownloads;
    }



   public function ws_layeredFilter($store_id, $service, $categoryid, $curr_page, $page_size, $sortType, $sortOrder, $currentcurrencycode,$filterdata) {

        Mage::app()->setCurrentStore($store_id);
        $res= array();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;
        $basecurrencycode   = Mage::app()->getStore($store_id)->getBaseCurrencyCode();
        $c_id     = $categoryid;
        $category = new Mage_Catalog_Model_Category();
        $category->load($c_id);
		$collection   = $category->getProductCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store_id);

        $collection->addAttributeToSelect('*')->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            )
        ));


         $collection->addAttributeToFilter('is_in_stock', array(
                         'in' => array(
                                          $is_in_stock_option,
                                          1
                                      )

        ))->addAttributeToFilter('status', array('eq' => 1));

        //$echo= $collection->getSelect();die;
        /* Apply filter action */
       //$filterdata =  '[{"code":"gender","id":"93,94"},{"code":"price","id":"220-230,250-280"},{"code":"material","id":"130"}]';
       $filterdata2 = json_decode($filterdata, true);
		 // print_r($filterdata2); die;
		//~ $echo= '<hr>';
		$sizeflag = '1';
		$colorflag='1';
		$filterArray = array();
	   if($filterdata2 != null){
			foreach ($filterdata2 as $filterCode => $filterValue) {
				$filterArray = array();
				if($filterValue['code'] != 'price'){
					$code=$filterValue['code'];
					if($code == 'size')
					   $sizeflag = '0';
					if($code == 'color')
					   $colorflag = '2';
					$ids = explode(',',$filterValue['id']);
					foreach($ids as $id){
						$filterArray[] = array('attribute'=> $code,'eq' => $id);
					}
					    $collection->addFieldToFilter($filterArray);

				}else{
					$code = $filterValue['code'];
					$filterValueArr = explode('-',$filterValue['id']);
					$priceArray = array(
										array('attribute'=> 'price',array('from'=>$filterValueArr[0],'to'=>$filterValueArr[1])),
										array('attribute'=> 'special_price',array('from'=>$filterValueArr[0],'to'=>$filterValueArr[1]))
										);
					$collection->addFieldToFilter($priceArray);
				}
			}
		}

		if($sizeflag ){
			if($colorflag=='2')
			{
				 $colorArray = array(
										//array('attribute'=> 'visibility',array('like'=>1)),
										array('attribute'=> 'visibility',array('like'=>4))
									);
			     $collection->addAttributeToFilter($colorArray);
			 }
			else
			{
			     $collection->addAttributeToFilter('visibility', 4);
			}
		}
		//$echo= $collection->getSelect();die;
		$res["category_name"]=$category->getName();
		$res["total"] = count($collection);

        /* END */
		 //~ $collection->addAttributeToSort($sortType, $sortOrder);
         //~ $collection->setPage($curr_page, $page_size);
        //print_r($collection);
        foreach ($collection as $_product) {
			$gflag=1;
            $productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
            $defaultprice  = str_replace(",", "", number_format($_product->getPrice(), 2));
            $defaultsprice = str_replace(",", "", number_format($_product->getSpecialprice(), 2));
            try {
                $custom_option_product = Mage::getModel('catalog/product')->load($_product->getId());
                $custom_options        = $custom_option_product->getOptions();
                $has_custom_option     = 0;
                foreach ($custom_options as $optionKey => $optionVal) {
                    $has_custom_option = 1;
                }
            }
            catch (Exception $ee) {
                $has_custom_option = 0;
            }

			$specialprice         = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialPrice();
            // Get the Special Price FROM date
            $specialPriceFromDate = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialFromDate();
            // Get the Special Price TO date
            $specialPriceToDate   = Mage::getModel('catalog/product')->load($_product->getId())->getSpecialToDate();
            // Get Current date
            $today                = time();

            if ($specialprice) {
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                     $new_price=$this->convert_currency($defaultsprice, "INR","INR");
                      $specialprice = strval(round($new_price, 2));

                    //$specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));

                } else {
                    $specialprice = 0;
                }
            } else {
                $specialprice = 0;
            }



             if ($_product->getTypeID() == 'grouped') {

             	$defaultprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
                $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');

             $associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
             	if(count($associatedProducts))
             	{
             		$gflag=1;
             	}
             	else
             	{
             		$gflag=0;
             	}
            }

            /*else if($_product->getTypeID() == 'configurable'){
				//$echo= $_product->getTypeInstance(); die;
				$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                                             ->getParentIdsByChild($_product->getId());

                 $product = Mage::getModel('catalog/product')->load($parentIds[0]);
                 $echo= "<pre>";
                 print_r($parentIds); die;

		     }*/
            else
            {
            	 $defaultprice =  number_format($_product->getPrice(), 2, '.', '');
           		 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }


            if($defaultprice == $specialprice)
                $specialprice = number_format(0, 2, '.', '');
                if($specialprice==0.0)
                $specialprice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($_product,$_product->getPrice());

           if($gflag)
           {//die(number_format($this->convert_currency($specialprice, "INR","INR"), 2, '.', ''));
            $res["data"][] = array(
                "id" => $_product->getId(),
                "name" => $_product->getName(),
                "imageurl" => (string)$productImage,
                "sku" => $_product->getSku(),
                "type" => $_product->getTypeID(),
                "spclprice" => number_format($this->convert_currency($specialprice, "INR","INR"), 2, '.', ''),
                "currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
                "price" => number_format($this->convert_currency($defaultprice,"INR","INR"), 2, '.', ''),
                "created_date" => $_product->getCreatedAt(),
                "is_in_stock" => $_product->getStockItem()->getIsInStock(),
                "hasoptions" => $has_custom_option,
                "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty()
            );

            }

        }
        return ($res);
    }

        /*--------------- cart sync webservice start---------------------- */

       /*--------------- cart sync webservice start---------------------- */

  public function ws_addCartItem($store_id, $service, $custid, $product_id, $qty, $child_id, $key, $value)
    {
        try {

			$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')->getParentIdsByChild($product_id);
			if (!$this->is_empty(array_filter($parentIds))) {
			// item is configurabl child
			$product  = Mage::getModel('catalog/product')->load($product_id);
			$pid = $parentIds[0];
			$child_id = $product_id;
			$product_id = $pid;

			$configurableProduct = Mage::getModel('catalog/product')->load($pid);
			$productAttributeOptions = $configurableProduct->getTypeInstance(true)->getConfigurableAttributesAsArray($configurableProduct);
			$options = array();

		    foreach ($productAttributeOptions as $productAttribute) {
				$allValues = array_column($productAttribute['values'], 'value_index');
				$currentProductValue = $product->getData($productAttribute['attribute_code']);
				if (in_array($currentProductValue, $allValues)) {
					$options[$productAttribute['attribute_id']] = $currentProductValue;
				}

		    }

			} // end of configurable cases


            $product  = Mage::getModel('catalog/product')->load($product_id);
            $customer = Mage::getModel('customer/customer')->load($custid);
            $session  = Mage::getSingleton('customer/session');
            $session->loginById($custid);
            $quote      = Mage::getModel('sales/quote')->loadByCustomer($customer);

            if($product->getTypeId() == "configurable"){
				$productQuantity = Mage::getModel("cataloginventory/stock_item")->loadByProduct($child_id);
				if($productQuantity->getQty() < $qty){
										$result = array(
													"status" => "The maximum quantity available for product is ".$productQuantity->getQty()."."
													);
										return $result;
									}
				$params = array(
						'product' => $product->getId(),
						'super_attribute' => $options,
						'qty' => $qty,
						'form_key'=>Mage::getSingleton('core/session')->getFormKey(),
						'uenc' =>Mage::app()->getRequest()->getParam('uenc', 1),
					);
			}
			else{
				$productQuantity = Mage::getModel("cataloginventory/stock_item")->loadByProduct($product_id);
				if($productQuantity->getQty() < $qty){
										$result = array(
													"status" => "The maximum quantity available for product is ".$productQuantity->getQty()."."
													);
										return $result;
									}
				$params = array(
						'product' => $product->getId(),
						'qty' => $qty,
						'form_key'=>Mage::getSingleton('core/session')->getFormKey(),
						'uenc' =>Mage::app()->getRequest()->getParam('uenc', 1),
					);
			}
			//$echo= "<pre>"; print_r($params);
            $collection = $quote->getItemsCollection(false);
            $searchcounter = 0;
            if ($collection->count() > 0) {
                foreach ($collection as $item) {
                    if ($item && $item->getId()) {
						if($product->getTypeId() == "configurable"){
							$productId = '';
							if ($option = $item->getOptionByCode('simple_product')) {
								$productId = $option->getProduct()->getId();
							}
							if ($productId == $child_id) {
								$searchcounter++;

								if($productQuantity->getUseConfigMinSaleQty() == 1){
									if($productQuantity->getMaxSaleQty() < $qty){
										$result = array(
													"status" => "The maximum quantity allowed for purchase is ".$productQuantity->getMaxSaleQty()."."
													);
										return $result;
									}
									//print_r($productQuantity->getQty());
									if($productQuantity->getQty() < $qty){
										$result = array(
													"status" => "The maximum quantity available in stock is ".$productQuantity->getQty()."."
													);
										return $result;
									}
								}
								//~ $quote->removeItem($item->getId());
								$item->setQty($qty);
								if ($quote->collectTotals()->save()) {
									//~ $quote = Mage::getModel('sales/quote')->loadByCustomer($customer);
									//~ //$echo= "<pre>"; print_r($params);
									//~ $quote->addProduct($product, new Varien_Object($params));
									//~ $quote->collectTotals()->save();
									Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
									$result = array(
										'status' => 'success'
									);
									return $result;
								}
							}
                        }
                        else{
							if ($item->getProduct()->getId() == $product_id) {
								$searchcounter++;

								if($productQuantity->getUseConfigMinSaleQty() == 1){
									if($productQuantity->getMaxSaleQty() < $qty){
										$result = array(
													"status" => "The maximum quantity allowed for purchase is ".$productQuantity->getMaxSaleQty()."."
													);
										return $result;
									}

									if($productQuantity->getQty() < $qty){
										$result = array(
													"status" => "The maximum quantity available in stock is ".$productQuantity->getQty()."."
													);
										return $result;
									}
								}
								//~ $quote->removeItem($item->getId());
								//~ var_dump($item->getQty());die;
								$item->setQty($qty);

								if ($quote->collectTotals()->save()) {
									//~ $cartHelper->getCart()->removeItem($item->getId())->save();
									//~ $quote = Mage::getModel('sales/quote')->loadByCustomer($customer);

									//~ $echo= "<pre>"; print_r($cartHelper->getCart()->getData());
									//~ $quote->addProduct($product, new Varien_Object($params));
									//~ $quote->collectTotals()->save();
									Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
									$result = array(
										'status' => 'success'
									);
									return $result;
								}
							}
						}
                    }
                }
            } else {
            	//$echo= "<pre>"; print_r($params);
                $quote->addProduct($product, new Varien_Object($params));
                $quote->collectTotals()->save();
                Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
                $result = array(
                            'status' => 'success'
                        );
                return $result;
            }
            if ($searchcounter == 0) {
            	//$echo= "<pre>"; print_r($params);
                $quote->addProduct($product, new Varien_Object($params));
                $quote->collectTotals()->save();
                Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
            }
            ############################################

            $result = array(
                'status' => 'success'
            );
        }
        catch (Exception $e) {
            $e->getMessage();
            $result = array(
                'status' => $e->getMessage()
            );
        }

        return $result;
    }

public function ws_addGuestCartItem($store_id,$service,$custid,$product_id){
	try
    {
	//$qty = 1;
	 $product_ids = json_decode(base64_decode($product_id));
	 foreach($product_ids as $pvalue){
		$pid =  $pvalue->product_id;
    $product = Mage::getModel('catalog/product')->load($pid);
    $customer = Mage::getModel('customer/customer')->load($custid);

    $quote = Mage::getModel('sales/quote')->loadByCustomer($customer);
    $quote->addProduct($product,$pvalue->product_qty);
    $quote->collectTotals()->save();
}

    Mage::getSingleton('checkout/session')->setCartWasUpdated(true);
    $result = array(
        'status' => 'success'
      );
    }
    catch(Exception $e)
    {
     $e->getMessage();
    }

      return $result;
}
      public function ws_removeCartItem($store_id, $service, $custid, $product_id, $child_id, $key, $value)
    {
        // $productId = 9333;
        //$customer  = 183;
        $product  = Mage::getModel('catalog/product')->load($product_id);
        if ($custid) {
            $storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
            // get quote table cart detail of all customer added
            $quote   = Mage::getModel('sales/quote')->setStoreId($storeId)->loadByCustomer($custid);
            if ($quote) {
                $collection = $quote->getItemsCollection(false);
                if ($collection->count() > 0) {
                    foreach ($collection as $item) {
                        if ($item && $item->getId()) {
                        	if($product->getTypeId() == "configurable"){
								$productId = '';
								if ($option = $item->getOptionByCode('simple_product')) {
									$productId = $option->getProduct()->getId();
								}
								if ($productId == $child_id) {
									$quote->removeItem($item->getId());
	                                $quote->collectTotals()->save();
	                                //$echo= "Item Removed";
	                                $result = array(
	                                    'status' => 'success'
	                                );
								}
							}
							else{
								if ($item->getProduct()->getId() == $product_id) {
                                	$quote->removeItem($item->getId());
	                                $quote->collectTotals()->save();
	                                //$echo= "Item Removed";
	                                $result = array(
	                                    'status' => 'success'
	                                );
	                            }

							}

                        }
                    }
                }
            }
        }
        return $result;

    }

		public function ws_getCartItem($store_id,$service,$custid,$currency){
   // $productId = 9333;
    //$customer  = 183;
    $currentcurrencycode=$currency;
    $res = array();
    $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();

    $totalCount = 0;
    if ($custid) {
        $storeId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
        // get quote table cart detail of all customer added
//var_dump($custid);die;
        $quote = Mage::getModel('sales/quote')->loadByCustomer($custid);
/*$quote1 = Mage::getModel('sales/quote')->getCollection();
$quote1->addFieldToFilter('customer_id', $custid);
$quote = $quote1->getLastItem();*/


//$echo= '<pre>';print_r($quote->getId());die;
        if ($quote) {
            $collection = $quote->getItemsCollection();

            if (true || $collection->count() > 0) {
                foreach( $collection as $item ) {
$res2 = '';
$_customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
 foreach($_customOptions['attributes_info'] as $_option){
						//   $res2[] =  array(   $_option['label'] => $_option['value']);
						$res2 .= $_option['value'].',';

						}
			if ($item->getParentItemId()) {
			        continue;
			}
$option = rtrim($res2,',');
		$totalCount++;
                  $pid = $item->getProduct()->getId();
			$defaultprice  = str_replace(",", "", number_format($item->getProduct()->getPrice(), 3));
            $defaultsprice = str_replace(",", "", number_format($item->getProduct()->getFinalPrice(), 3));
           //$cartItem = Mage::getSingleton('checkout/cart')->getQuote()->getItemByProduct($pid);
          // $cartItem->getQty();

			 $mofluid_product            = Mage::getModel('catalog/product')->load($pid);

			 //~ $images =  Mage::getBaseUrl('media') .'catalog/category/'. $mofluid_product->getThumbnail();
			 $productImage = Mage::helper('catalog/image')->init($mofluid_product,'thumbnail')->resize(200,200);



			//$res['total'] = round($totalQuantity = Mage::getModel('checkout/cart')->getQuote()->getItemsQty());
		$childProductData = $item->getOptionByCode('simple_product');
		if($childProductData == null){
			$childProduct = $mofluid_product;
		}else{
			$childProduct = $childProductData->getProduct();
		}
		$mofluid_child_product            = Mage::getModel('catalog/product')->load($childProduct->getId);

		$childproductImage = Mage::helper('catalog/image')->init($childProduct,'thumbnail')->resize(200,200);

		//var_dump($childProduct->getImageUrl());die;
				$res["data"][] = array(
                "id" => $childProduct->getId(),
                "parentId"=>$pid,
                "name" => $item->getProduct()->getName(),
                "imageurl" => Mage::getModel('catalog/product_media_config')->getMediaUrl($childProduct->getImage()),
                "img" => (string)$childproductImage,
                "sku" => $item->getProduct()->getSku(),
                "type" => $childProduct->getTypeID(),
                "spclprice" => number_format($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 3, '.', ''),
                "currencysymbol" => Mage::app()->getLocale()->currency($basecurrencycode)->getSymbol(),
                "price" => number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 3, '.', ''),
                "created_date" => $item->getProduct()->getCreatedAt(),
                "is_in_stock" => $childProduct->getStockItem()->getIsInStock(),
                 "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($childProduct->getId())->getQty(),
                "quantity" => $item->getQty(),
                "max_sale_qty" => $childProduct->getStockItem()->getMaxSaleQty(),
                "min_sale_qty" => $childProduct->getStockItem()->getMinSaleQty(),
		 "option " => $option
            );


                }
            } else{
				$res["data"] = array();

				}
        }else{
		$res["data"] = array();
	}
    }
	if(!!$this->is_empty($res["data"])){
		$res["data"] = array();
	}

	$connection = Mage::getSingleton('core/resource')->getConnection('core_write');
	$resource = Mage::getResourceModel('sales/quote');
	$connection->update(
	    $resource->getMainTable(),
	    array('is_active' => 1),
	    array('entity_id = ?' => $quote->getId() ));
	$res['total'] = $totalCount;
	$res['quoteID'] = $quote->getId();
    return $res;

	}
	/* ----- cart sync webservice end ----------------- */
	  /**
     * Method : prepareQuoteUser
     * @param : $custid => Customer Id of the Logged In User
     * @param : $store => Store Id of the Magento Store
     */
    public function prepareQuoteUser($custid, $store, $shipping_code,$address, $currency, $is_create_quote, $find_shipping)
    {
		$quote = Mage::getModel('sales/quote')->loadByCustomer($custid);
		$basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
		$address          = str_replace(" ", "+", $address);
        $address          = json_decode(base64_decode($address));
        //~ $echo= '<pre>';print_r($quote->getShippingAddress()->getData());die;
		$shipping_amount = 0;
		$shipping_methods = array();
		$res = array();
		if($quote->getItemsCount() <= 0){
			$res["coupon_discount"] = 0;
            $res["coupon_status"]   = 0;
            $res["tax_amount"]      = 0;
            $res["total_amount"]    = 0;
            $res["currency"]        = $currency;
            $res["status"]          = "error";
            $res["type"]            = 'Products not found';
            $res["shipping_amount"] = $shipping_amount;
            $res["shipping_method"] = $shipping_methods;
            return $res;
		}


		$totals = $quote->getTotals();
		// Set shipping address in Qoute

		$customerObj     = Mage::getModel('customer/customer')->load($custid);
            // get billing and shipping address of customer
            $shippingAddress = array(
                'prefix' => $address->shipping->prefix,
                'firstname' => $address->shipping->firstname,
                'lastname' => $address->shipping->lastname,
                'company' => $address->shipping->company,
                'street' => $address->shipping->street,
                'city' => $address->shipping->city,
                'postcode' => $address->shipping->postcode,
                'telephone' => $address->shipping->phone,
                'country_id' => $address->shipping->country,
                'region' => $address->shipping->region
            );
            $billingAddress  = array(
                'prefix' => $address->billing->prefix,
                'firstname' => $address->billing->firstname,
                'lastname' => $address->billing->lastname,
                'company' => $address->billing->company,
                'street' => $address->billing->street,
                'city' => $address->billing->city,
                'postcode' => $address->billing->postcode,
                'telephone' => $address->billing->phone,
                'country_id' => $address->billing->country,
                'region' => $address->billing->region
            );
            //Setting Region ID In case of Country is US
            if ($address->billing->country == "US" || $address->billing->country == "USA") {
                $regionModel                 = Mage::getModel('directory/region')->loadByCode($address->billing->region, $address->billing->country);
                $regionId                    = $regionModel->getId();
                $billingAddress["region_id"] = $regionId;
            }
            if ($address->shipping->country == "US" || $address->shipping->country == "USA") {
                $regionModelShipping          = Mage::getModel('directory/region')->loadByCode($address->shipping->region, $address->shipping->country);
                $regionIdShipp                = $regionModelShipping->getId();
                $shippingAddress["region_id"] = $regionIdShipp;
            }
			$addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')->setEntityType('customer_address');
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!$this->is_empty($shippingAddress[$attribute->getAttributeCode()])) {
                    $quote->getShippingAddress()->setData($attribute->getAttributeCode(), $shippingAddress[$attribute->getAttributeCode()]);
                }
            }
            foreach ($addressForm->getAttributes() as $attribute) {
                if (!$this->is_empty($billingAddress[$attribute->getAttributeCode()])) {
                    $quote->getBillingAddress()->setData($attribute->getAttributeCode(), $billingAddress[$attribute->getAttributeCode()]);
                }
            }
            //~ $quote->setBaseCurrencyCode($basecurrencycode);
            $quote->setQuoteCurrencyCode($currency);
            if ($find_shipping) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->save();
            } else {
                $quote->getShippingAddress()->setShippingMethod($shipping_code)->setCollectShippingRates(true);
            }

            //$quote->setTotalsCollectedFlag(false)->collectTotals();
            $quote->collectTotals()->save();

		// Set shipping address in Qoute end
		//~ print_r($quote->getEntityId());die;
		 $quote = Mage::getModel('sales/quote')->load($quote->getEntityId());
		 $totals = $quote->getTotals();
		//~ $echo= '<pre>';print_r(($quote->getShippingAddress()->getData()));die;
		 if ($find_shipping) {
                $shipping                 = $quote->getShippingAddress()->getGroupedAllShippingRates();
                $shipping_methods         = array();
                $index                    = 0;
                $shipping_dropdown_option = '';
                foreach ($shipping as $shipping_method_id => $shipping_method) {
                    foreach ($shipping_method as $current_shipping_method) {
                        $shipping_methods[$index]["id"]            = $shipping_method_id;
                        $shipping_methods[$index]["code"]          = str_replace(" ", "%20", $current_shipping_method->getCode());
                        $shipping_methods[$index]["method_title"]  = $current_shipping_method->getMethodTitle();
                        $shipping_methods[$index]["carrier_title"] = $current_shipping_method->getCarrierTitle();
                        $shipping_methods[$index]["carrier"]       = $current_shipping_method->getCarrier();
                        $shipping_methods[$index]["price"]         = Mage::helper('directory')->currencyConvert($current_shipping_method->getPrice(), $basecurrencycode, $currency);
                        $shipping_methods[$index]["description"]   = $current_shipping_method->getMethodDescription();
                        $shipping_methods[$index]["error_message"] = $current_shipping_method->getErrorMessage();
                        $shipping_methods[$index]["address_id"]    = $current_shipping_method->getAddressId();
                        $shipping_methods[$index]["created_at"]    = $current_shipping_method->getCreatedAt();
                        $shipping_methods[$index]["updated_at"]    = $current_shipping_method->getUpdatedAt();
                        $shipping_option_title                     = $shipping_methods[$index]["carrier_title"];
                        if ($shipping_methods[$index]["method_title"]) {
                            $shipping_option_title .= ' (' . $shipping_methods[$index]["method_title"] . ')';
                        }
                        if ($shipping_methods[$index]["price"]) {
                            $shipping_option_title .= ' + ' . Mage::app()->getLocale()->currency($currency)->getSymbol() . number_format($shipping_methods[$index]["price"], 2);
                        }
                        $shipping_dropdown_option .= '<option id=' . $shipping_methods[$index]["id"] . ' value= ' . $shipping_methods[$index]["code"] . ' price =' . $shipping_methods[$index]["price"] . ' description=' . $shipping_method[0]->getMethodDescription() . '>' . $shipping_option_title . '</option>';
                        $index++;
                    }
                }
               // $res["available_shipping_method"] = base64_encode($shipping_dropdown_option);
            }
		//~ die('ds');
		try {
			$test                = $quote->getShippingAddress();
			$shipping_tax_amount = number_format(Mage::helper('directory')->currencyConvert($test['shipping_tax_amount'], $basecurrencycode, $currency), 2, ".", "");
		}
		catch (Exception $ex) {
			$shipping_tax_amount = 0;
		}
		$dis = 0;
		//Find Applied Tax
		if (!$this->is_empty($totals['tax']) && $totals['tax']->getValue()) {
			$tax_amount = number_format(Mage::helper('directory')->currencyConvert($totals['tax']->getValue(), $basecurrencycode, $currency), 2, ".", "");
		} else {
			$tax_amount = 0;
		}
		if (!$this->is_empty($totals['shipping']) && $totals['shipping']->getValue()) {
			$shipping_amount = number_format(Mage::helper('directory')->currencyConvert($totals['shipping']->getValue(), $basecurrencycode, $currency), 2, ".", "");
		} else {
			$shipping_amount = 0;
		}
		if ($shipping_tax_amount) {
			$shipping_amount += $shipping_tax_amount;
		}

		//  Find Applied coupon
		if (!$this->is_empty($totals['discount']) && $totals['discount']->getValue()) {
			$coupon_status   = 1;
			$coupon_code   = $quote->getCouponCode();
			$coupon_discount = number_format(Mage::helper('directory')->currencyConvert($totals['discount']->getValue(), $basecurrencycode, $currency), 2, ".", "");
		} else {
			$coupon_discount = 0;
			$coupon_status   = 0;
			$coupon_code = '';
		}
		$quoteData              = $quote->getData();
		$dis                    = $quoteData['grand_total'];
		$grandTotal             = number_format(Mage::helper('directory')->currencyConvert($totals['grand_total']->getValue(), $basecurrencycode, $currency), 2, ".", "");

		$res["coupon_discount"] = $coupon_discount;;
		$res["coupon_status"]   = $coupon_status;
		$res["coupon_code"]     = $coupon_code;
		$res["tax_amount"]      = $tax_amount;
		$res["total_amount"]    = $grandTotal;
		$res["currency"]        = $currency;
		$res["status"]          = "success";
		$res["shipping_amount"] = $shipping_amount;
		$res["shipping_method"] = $shipping_methods;
		return $res;

    }
public function ws_getFilteredProducts($store_id, $categoryid, $curr_page, $page_size, $sortType, $sortOrder, $currentcurrencycode,$filterdata){
	try
	{	
		$data=json_decode($filterdata);
		$size=count($data);
		$color_val=array();
		$size_val=array();
		$price_val=array();
		$from=9999999;
		$to=-9999999;

		for($i=0;$i<$size;$i++)
		{$code=$data[$i]->code;
		 switch($code){
			 case "color":
			   $idValues =explode(",",$data[$i]->id);
			   $id_size=count($idValues);
			 for($j=0;$j<$id_size;$j++)
				 $color_val[]=(int)$idValues[$j];
			   break;
			case "size":
				 $sizeValues=explode(",",$data[$i]->id);
				 $size_val_len=count($sizeValues);
				 for($j=0;$j<$size_val_len;$j++)
					 $size_val[]=(int)$sizeValues[$j];
					 break;
			case "price":
				 $pricestr=$data[$i]->id;
				 $priceStrLen=strlen($pricestr);
				 if($pricestr[0]=='-')
					$pricestr="0".$pricestr;
				 if($pricestr[$priceStrLen-1]=='-')
				 $pricestr=$pricestr."9999999";
				 $priceValues=explode(",",$pricestr);
				 $priceValues_len=count($priceValues);
				 for($j=0;$j<$priceValues_len;$j++){
					$cur=$priceValues[$j];
					$cur_arr=explode("-",$cur);
					$cur_len=count($cur_arr);
					for($k=0;$k<$cur_len;$k++){
						$cur_val=(int)$cur_arr[$k];
						if($cur_val<$from)
						   $from=$cur_val;
						if($cur_val>$to)
						   $to=$cur_val;
						}
					 }
				 break;
			 }

		}
		Mage::app()->setCurrentStore($store_id);
		$res= array();
		if(!$this->is_empty($categoryid))
		$products=Mage::getModel('catalog/category')->load((int)$categoryid)
				  ->getProductCollection()
				  ->addAttributeToSelect('*')
				  ->addAttributeToFilter('status', 1);
		else
		$products = Mage::getModel('catalog/product')
					->getCollection()
					->addAttributeToSelect('*')
					->addAttributeToFilter('status', 1);
		$ans;
		//apply filters if required
		$show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
		if($show_out_of_stock==0){
		//$products->addAttributeToFilter('is_in_stock',array('in'=>1));
		}
		if(!$this->is_empty($sortType) && !$this->is_empty($sortOrder)){
		$products->setOrder($sortType,$sortOrder);
		}
		//apply color filter
		if(count($color_val)>0){
		$products->addAttributeToFilter('color', array('in' => $color_val));
		}
		//Apply size filter
		if(count($size_val)>0){
		$products->addAttributeToFilter('size', array('in'=>$size_val));
		}
		//Apply price filter
		if($from!=9999999 || $to!=-9999999){
		$products->addAttributeToFilter('price', array('from'=>$from,'to'=>$to));
		}
		//In last step apply pagination
		if(!$this->is_empty($curr_page) && !$this->is_empty($page_size)){
		$products->setPageSize((int)$page_size);
		$products->setCurPage((int)$curr_page);
		}
		$is_category_name_set=FALSE;
		$res["total"] = count($products);
		foreach($products as $product) {
		$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
		$name=$product->getName();
		$categoryID=$product->getCategoryIds();
		$categoryName="";
		if (!$this->is_empty($categoryID[0]) && $is_category_name_set==FALSE){
		$category = Mage::getModel('catalog/category')->load($categoryID[0]);
		$categoryName = $category->getName();
		$res["category_name"]=$categoryName;
		}
		$pid=$product->getId();
		$pImageUrl = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200);
		$sku=$product->getSku();
		$typeID=$product->getTypeID();
		$actualPrice = $product->getPrice();
		$formattedActualPrice = Mage::helper('core')->currency($product->getPrice(),true,false);
		$specialPrice = $product->getFinalPrice();
		$formattedSpecialPrice = Mage::helper('core')->currency($product->getFinalPrice(),true,false);
		$quantity=(int)$stock->getQty();
		$createdDate=$product->getCreatedAt();
		$isInStock=$quantity>0?1:0;
		$hasOptions=$product->getData('has_options');
		$size=$product->getData('size');
		$color=$product->getData('color');
		$specialPrice=number_format($specialPrice, 3, '.', '');
		$actualPrice=number_format($actualPrice, 3, '.', '');
		if($specialPrice==0.0 || $specialPrice==$actualPrice)
		$specialPrice=Mage::getModel('catalogrule/rule')->calcProductPriceRule($product,$product->getPrice());
			/* $ans.="Name=".$name." categoryID=".json_encode($categoryID)." productId=".$pid. " productImageUrl=".$pImageUrl." sku=".$sku
			  ." typeID=".$typeID." actualPrice=".$actualPrice." formattedActualPrice=".$formattedActualPrice." specialPrice=".$specialPrice
			  ."formattedSpecialPrice=".$formattedSpecialPrice." createdDate=".$createdDate." isInStock=".$isInStock." hasOptions=".$hasOptions." quantity=".$quantity." size=".$size." color=".$color;*/
		$res["data"][] = array(
						"id" => $pid,
						"name" =>$name,
						"imageurl" =>(string)$pImageUrl ,
						"sku" =>$sku,
						"type" =>$typeID,
						"spclprice" =>$specialPrice,
						"currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
						"price" =>$actualPrice,
						"created_date" =>$createdDate,
						"is_in_stock" =>$isInStock,
						"hasoptions" =>$hasOptions,
						"stock_quantity" =>$quantity                  );
		}
		return $res;
	}
	catch(Exception $e)
	{
			echo $e->getMessage();
	}
}
public function ws_getCart($store_id,$customer_id){
        Mage::app()->setCurrentStore($store_id);
$customer = Mage::getModel('customer/customer')->load($customer_id);
$quote=Mage::getSingleton('checkout/cart')->getQuote()->loadByCustomer($customer);
$collection = $quote->getItemsCollection();
$collection=json_decode($collection,true);
var_dump($collection);
       $res=array();
        foreach($collection as $item){
        // $pid=$item->getProduct();
var_dump($item);
         $product = Mage::getModel('catalog/product')->load($pid);
         if($product->getTypeID()!="configurable"){
        $stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product);
         $res[]=array(
                 "prod_id"=>$product->getId()."",
                 "sku" =>$product->getSku()."",
                 "name"=>$product->getName()."",
                 "spclprice"=>$product->getFinalPrice()."",
                 "price"=>$product->getPrice()."",
                 "is_in_stock"=>$product->isInStock ()."",
                 "stock_quantity"=>((int)$stock->getQty())."",
                  "count"=>$item->getQty()."",
                 "type"=>$product->getTypeID()."",
                 "img"=>(string)Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200)

                     );
          }
        }
        return $res;
}
public function ws_removeItemFromCart($store_id,$customer_id,$product_id){
	    Mage::app()->setCurrentStore($store_id);
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $cart = Mage::getModel('sales/quote')->loadByCustomer($customer);
        $collection = $cart->getItemsCollection(false);
       $count=0;
        foreach($collection as $item){
         $pid=$item->getProductId();
         if($pid==$product_id){
			 $cart->removeItem($item->getId());
			 $cart->collectTotals()->save();
			 ++$count;
			 }
        }
        $res["item_removed"]=$count;
        return $res;
	}

public function ws_clearCart($store_id,$customer_id){
	    Mage::app()->setCurrentStore($store_id);
        $customer = Mage::getModel('customer/customer')->load((int)$customer_id);
        $cart = Mage::getModel('sales/quote')->loadByCustomer($customer);
        $collection = $cart->getItemsCollection(false);
       $count=0;
        foreach($collection as $item){
         $pid=$item->getProductId();
			 $cart->removeItem($item->getId());
			 $cart->collectTotals()->save();
			 ++$count;
        }
        $res["item_removed"]=$count;
        return $res;
	}

public function ws_updateCartItem($store_id,$customer_id,$product_id,$count){
	Mage::app()->setCurrentStore($store_id);// $echo= "store=".$store_id." customeid=".$customer_id."productid=".$product_id."count=".$count;
       $res1["item_updated"]=false;
        $res2["item_updated"]=true;
	$customer = Mage::getModel('customer/customer')->load($customer_id);
	 $quote = Mage::getModel('sales/quote')->loadByCustomer($customer);
        if($quote==null || $this->is_empty($quote))
         return $res1;
	 $product = Mage::getModel('catalog/product')->load($product_id);
         if($product==null || count($product)<=0 || $this->is_empty($product)) return $res1;
	 $item = $quote->getItemByProduct($product);
          if($item==null || $this->is_empty($item) || $item==false) return $res1;
       if(!$quote->hasProductId($product_id))
          return $res1;
      $item->setQty($count);
      $item->save();
        // $quote->getCart()->updateItem(array($item->getId()=>array('qty'=>$count)));
        // $quote->getCart()->save();
	// $quote->updateItem(array($item->getId()=>array('qty'=>$count)));
        //$quote->save();
        return $res2;
	}

public function ws_addItemtoCart($store_id,$customer_id,$product_id){
	Mage::app()->setCurrentStore($store_id);
        $res1["item_updated"]=false;
        $res2["item_updated"]=true;
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $cart = Mage::getModel('sales/quote')->loadByCustomer($customer);
        if($cart==null || $this->is_empty($cart))
        return $res1;
        $product = Mage::getModel('catalog/product')->load($product_id);
        if($product==null || $this->is_empty($product))
        return $res1;
        $cart->addProduct($product, 1);
        $cart->setIsActive(1);
        $cart->collectTotals()->save();
        return $res2;
	}
public function ws_getAddressList($store_id,$customer_id){
	$customer = Mage::getModel('customer/customer')->load($customer_id);
        $allAddress=$customer->getAddresses();
//	$allAddress = Mage::getModel('customer/address')->getCollection()->setCustomerFilter($customer);
        $res=array();
        foreach ($allAddress as $address){
             $id=$address->getId();
	     $fname=$address->getFirstname();
             $lname=$address->getLastname();
             $mno=$address->getTelephone();
             $faddress=$address->getStreet();
             $city=$address->getCity();
             $country_id=$address->getCountry_id();
	    $country=Mage::getModel('directory/country')->loadByCode($country_id)->getName();
             $region_id=$address->getRegion();
	     $region=Mage::getModel('directory/region')->load($region_id)->getName();
             $postcode=$address->getPostcode();
             $res[]=array(
			"id"=>$id,
			"firstname"=> $fname,
			"lastname"=>$lname,
			"contactno"=> $mno,
			"street"=> $faddress,
			"city"=>$city,
  		        "country_code"=> $country_id,
			"country"=>$country,
			"region_id"=>$region_id,
		        "region"=>$region,
			"pincode"=>$postcode
			);
		}
  return $res;
	}
public function ws_getAddress($store_id,$customer_id,$address_id){
        $customer = Mage::getModel('customer/customer')->load($customer_id);
        $allAddress=$customer->getAddresses();
//      $allAddress = Mage::getModel('customer/address')->getCollection()->setCustomerFilter($customer);
        $res=array();
        foreach ($allAddress as $address){
             $id=$address->getId();
            if($id==$address_id){
             $fname=$address->getFirstname();
             $lname=$address->getLastname();
             $mno=$address->getTelephone();
             $faddress=$address->getStreet();
             $city=$address->getCity();
             $country_id=$address->getCountry_id();
            $country=Mage::getModel('directory/country')->loadByCode($country_id)->getName();
             $region_id=$address->getRegion();
             $region=Mage::getModel('directory/region')->load($region_id)->getName();
             $postcode=$address->getPostcode();
             $res[]=array(
                        "id"=>$id,
                        "firstname"=> $fname,
                        "lastname"=>$lname,
                        "contactno"=> $mno,
                        "street"=> $faddress,
                        "city"=>$city,
                        "country_code"=> $country_id,
                        "country"=>$country,
                        "region_id"=>$region_id,
                        "region"=>$region,
                        "pincode"=>$postcode
                        );
                   }
                }
  return $res;

	}
public function ws_updateAddress($store_id,$customer_id,$address_id,$address_data){
$address_obj=json_decode($address_data,true);
$res1["item_updated"]=false;
$res2["item_updated"]=true;
$customer = Mage::getModel('customer/customer')->load($customer_id);
        $allAddress=$customer->getAddresses();
//      $allAddress = Mage::getModel('customer/address')->getCollection()->setCustomerFilter($customer);
        $res=array();
        foreach ($allAddress as $address){
             $id=$address->getId();
            if($id==$address_id){
$address->setFirstname($address_obj[0]["firstname"])

->setLastname($address_obj[0]["lastname"])

->setCountryId($address_obj[0]["country_code"])

->setPostcode($address_obj[0]["pincode"])

->setCity($address_obj[0]["city"])

->setTelephone($address_obj[0]["contactno"])

->setStree($address_obj[0]["street"])

->setIsDefaultBilling('1')

->setIsDefaultShipping('1')

->setSaveInAddressBook('1');

$address->save();

                   }
                }
return $res2;

}
public function ws_createAddress($store_id,$customer_id,$address_data){
$address_obj=json_decode($address_data,true);
$address = Mage::getModel("customer/address");
$address->setCustomerId($customer_id)
->setFirstname($address_obj[0]["firstname"])

->setLastname($address_obj[0]["lastname"])

->setCountryId($address_obj[0]["country_code"])

->setPostcode($address_obj[0]["pincode"])

->setCity($address_obj[0]["city"])

->setTelephone($address_obj[0]["contactno"])

->setStree($address_obj[0]["street"])

->setIsDefaultBilling('1')

->setIsDefaultShipping('1')

->setSaveInAddressBook('1');

$address->save();
return $this->ws_getAddress($store_id,$customer_id,$address->getId());
}
public function getWebsiteInfo(){
  $res=array();
 foreach(Mage::app()->getWebsites() as $website) {
   $web_sites=array();
   $web_sites["name"]=$website->getName();
   $web_sites["id"]=$website->getId();
    foreach ($website->getGroups() as $group) {
        $stores = $group->getStores();
        foreach ($stores as $store) {
          $stores=array();
          $stores["store"]=$group->getName();
          $stores["store_id"]=$group->getGroupId();;
          $stores["root_category_id"]=$store->getRootCategoryId();
           $views=array();
           $views["current_currency_code"]=$store->getCurrentCurrencyCode();
          $views["name"]=$store->getName();
          $views["store_view_id"]=$store->getStoreId();
          $views["store_url"]=$store->getHomeUrl();
          $views["store_code"]=$store->getCode();
          $storeLocale =Mage::getStoreConfig('general/locale/code',$views["store_view_id"]);
          $storeLocale = explode("_",$storeLocale);
          $views["store_lang_code"]=$storeLocale[0];
          $views["current_currency_symbol"]=Mage::app()->getLocale()->currency($views["current_currency_code"])->getSymbol();;
          $views["base_currency_code"]=$store->getBaseCurrencyCode();
          $views["sort_order"]=$store->getSortOrder();
          $views["is_active"]=$store->getIsActive();
          $stores["views"][]=$views;
        }

    }
$web_sites["stores"][]=$stores;
$res["websites"][]=$web_sites;
}
$res["status"]=true;
return $res;
}
//Service to get Current Deals on Website
public function ws_currentDeals($store_id, $service){
		 $results = array();
		$now = Mage::getModel('core/date')->date('Y-m-d H:i:s', time());
        $currentFlashsales = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                            ->addFieldToFilter('fs_enabled', array('eq' => '1'))
                                            ->addFieldToFilter('fs_end_date', array('gteq' => $now))
                                            ->addFieldToFilter('fs_start_date', array('lteq' => $now))
                                            ->setOrder('fs_start_date', 'DESC');
        $ccurrentFlashsales = $currentFlashsales->getData();
        foreach($ccurrentFlashsales as $flashsales){
			
			$enddate = $flashsales["fs_end_date"];
			
			$interval = intval(Mage::helper('aktionen')->getCountdownDays($enddate));
           
			$results ["data"][]= array(
			"deal_id"=>$flashsales["fs_id"],
			"description" =>$flashsales["fs_description"],
			"category_id" =>$flashsales["fs_category_id"],
			"imageurl" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$flashsales["fs_picture"],
			"day_left" => $interval,
			);
			}
			if(empty($results)){
				$results = array("data"=>array());
				}
		return $results;
		
	} 	 
	// webservice : upcomming deals
	public function ws_upcommingDeals($store_id, $service){
		 $results = array();
		$now = Mage::getModel('core/date')->date('Y-m-d H:i:s', time());
       $upcommingFlashsales = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                            ->addFieldToFilter('fs_enabled', array('eq' => '1'))
                                            ->addFieldToFilter('fs_start_date', array('gteq' => $now))
                                            ->setOrder('fs_start_date', 'ASC');
        $uupcommingFlashsales = $upcommingFlashsales->getData();
        foreach($uupcommingFlashsales as $flashsales){
			
			$startdate = $flashsales["fs_start_date"];
			
			$interval = Mage::helper('aktionen')->getEuropeanDateFormat($startdate);
           
			$results ["data"][]= array(
			"description" =>$flashsales["fs_description"],
			"category_id" =>$flashsales["fs_category_id"],
			"imageurl" => Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$flashsales["fs_picture"],
			"start_from" => $interval,
			);
			}
			if(empty($results)){
				$results = array("data"=>array());
				}
		return $results;
		
	} 
	// webservice : deals details    
	public function ws_dealsDetails($store_id, $service,$deal_id,$curr_page,$page_size,$sortType, $sortOrder, $currency, $price){
		$store_id = 2;
		$results = array();
		$now = Mage::getModel('core/date')->date('Y-m-d H:i:s', time());
        $currentFlashsales = Mage::getModel('PrivateSales/FlashSales')->getCollection()
                                            ->addFieldToFilter('fs_enabled', array('eq' => '1'))
                                            ->addFieldToFilter('fs_id', array('eq' => $deal_id));
        $ccurrentFlashsales = $currentFlashsales->getData();
        $cat_id = $ccurrentFlashsales[0]["fs_category_id"];
        $imageurl =  Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA).$ccurrentFlashsales[0]["fs_picture"];
        $category = Mage::getModel('catalog/category')->load($cat_id);
      
        $categoryName = $category->getName();

        $categoryDesc = $category->getDescription();
        $results["deal_category"] = $categoryName;
        $results["deal_category_desc"] = strip_tags($categoryDesc);
        $results["deal_image"] = $imageurl;
		$results['products'] = $this->ws_dealsproducts($store_id, $service, $cat_id, $curr_page, $page_size, $sortType, $sortOrder, $currency, $price);
	
	      return $results;
		
	} 
	/****** deals product listing *******/
	public function ws_dealsproducts($store_id, $service, $categoryid, $curr_page, $page_size, $sortType, $sortOrder, $currentcurrencycode, $price){
        Mage::app()->setCurrentStore($store_id);
        $res                = array();
        $show_out_of_stock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
        $is_in_stock_option = $show_out_of_stock ? 0 : 1;
        $basecurrencycode   = Mage::app()->getStore($store_id)->getBaseCurrencyCode();
        Mage::app()->setCurrentStore($store_id);
        $c_id     = $categoryid;
        $category = new Mage_Catalog_Model_Category();
        $category->load($c_id);
        $children1 = $category->getProductCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store_id)->addAttributeToSelect('*')->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            )
        ))->addAttributeToFilter('visibility',array(2,4))->addAttributeToFilter('is_in_stock', array(
            'in' => array(
                $is_in_stock_option,
                1
            )
        ))->addAttributeToFilter('status', array('eq' => 1));
       
        $res["total"] = count($children1);
        $collection   = $category->getProductCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store_id)->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            )
        ));
        $collection->addAttributeToSelect('*')->addAttributeToFilter('type_id', array(
            'in' => array(
                Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
                Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
                Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
                Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
            )
        ))->addAttributeToFilter('visibility',array(2,4))->addAttributeToFilter('is_in_stock', array(
            'in' => array(
                $is_in_stock_option,
                1
            )
        ))->addAttributeToFilter('status', array('eq' => 1));
        $collection->addAttributeToSort($sortType, $sortOrder);
        $collection->setPage($curr_page, $page_size); 
                foreach ($collection as $_product) {
           	$gflag=1;
            $productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200); 
            $defaultprice  = str_replace(",", "", number_format($_product->getPrice(), 2));
            $defaultsprice = str_replace(",", "", number_format($_product->getSpecialprice(), 2));
            ;
            
            try {
                $custom_option_product = Mage::getModel('catalog/product')->load($_product->getId());
                $custom_options        = $custom_option_product->getOptions();
                $has_custom_option     = 0;
                foreach ($custom_options as $optionKey => $optionVal) {
                    $has_custom_option = 1;
                }
            }
            catch (Exception $ee) {
                $has_custom_option = 0;
            }
           
            //print_r($defaultprice); echo "<br>";
            // Get the Special Price
            $specialprice         = $_product->getSpecialPrice();
           
            // Get the Special Price FROM date
            $specialPriceFromDate = $_product->getSpecialFromDate();
            // Get the Special Price TO date
            $specialPriceToDate   = $_product->getSpecialToDate();
            // Get Current date
            $today                = time();
            
            if ($specialprice) {
                if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
                    
                    $specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
                } else {
                    $specialprice = 0;
                }
            } else {
                $specialprice = 0;
            }
             if ($_product->getTypeID() == 'grouped') {
             
             	$defaultprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
                $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
              // 	$mofluid_all_product_images[0] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . 'media/catalog/product' . $mofluid_product->getImage();
             $associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
             	if(count($associatedProducts))
             	{
             		$gflag=1;
             	} 
             	else 
             	{ 
             		$gflag=0; 
             	} 
            }
            else
            {
            	 $defaultprice =  number_format($_product->getPrice(), 2, '.', '');
           		 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }
            if($defaultprice == $specialprice)
                $specialprice = number_format(0, 2, '.', '');
           if($gflag)
           {
            $res["data"][] = array(
                "id" => $_product->getId(),
                "name" => $_product->getName(),
                "imageurl" => (string)$productImage,
                "sku" => $_product->getSku(),
                "type" => $_product->getTypeID(),
                "spclprice" => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                "currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
                "price" => number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                "created_date" => $_product->getCreatedAt(),
                "is_in_stock" => $_product->getStockItem()->getIsInStock(),
                "hasoptions" => $has_custom_option,
                "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty()
            );
            }
            
        }
        return ($res);
    }
    /*
	 * Function for getting the search filter for product collection.
	 * Disabled category filter's as it's been disabled on the website. To get the category filter code please follow function: ws_getNewCategoryFilter
	*/
	public function ws_getSearchFilter($store,$searchData,$filterData = null,$getCollection = 0)
	{
		$filterCount 	= 0;
		$actvCount		= 0;
		$res = $activeFilters = $filter = [];
		//$res['status'] 	= 'success';
		//$res['total']	= 0;
		//$res['activeFilters'] = $res['filters'] = [];
		//$res['redirect_category_id'] = '';
		//$res['redirect_product_id'] = '';
		try
		{
			Mage::app()->setCurrentStore($store);
			$params = Mage::app()->getRequest()->getParams();
			if($searchData)
				$params[Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME] = $searchData;
			Mage::app()->getRequest()->setParams($params);
			$query = Mage::helper('catalogsearch')->getQuery();
			$query->setStoreId($store);
			if($query->getQueryText() != '') {
				if (Mage::helper('catalogsearch')->isMinQueryLength()) {
					$query->setId(0)->setIsActive(1)->setIsProcessed(1);
				}
				else {
					
					if ($query->getRedirect()){
						$query->save();
					}
					else {
						$query->prepare();
					}
				}
				//Mage::helper('catalogsearch')->checkNotes(); //Additional notes not showing on the app.
				if (!Mage::helper('catalogsearch')->isMinQueryLength()) {
					$query->save();
					$layer = Mage::getModel("catalogsearch/layer");
					$attributes = $layer->getFilterableAttributes();
					$filterData = json_decode($filterData, true);
					if($filterData)
					{
						foreach($filterData as $actvFilters)
						{
							if(isset($actvFilters['code']) && isset($actvFilters['id']))
							{
								$params[$actvFilters['code']] = $actvFilters['id'];
								$activeFilters[$actvCount] = $actvFilters;
								$actvCount++;
							}
						}
						if($params)
							Mage::app()->getRequest()->setParams($params); //Setting the params to the request object which is used by the layer model.
					}
					foreach($attributes as $attribute)
					{	
						if ($attribute->getIsFilterableInSearch()) {
							$filterModelName = 'catalogsearch/layer_filter_attribute';
							Mage::getModel($filterModelName)->setLayer($layer)->setAttributeModel($attribute)->apply(Mage::app()->getRequest(),null);
						}
					}
					if($getCollection)
						return $layer->getProductCollection();
					if(!$this->canShowBlock($layer)) //Condition for checking the minimum product collection for which to show layered navigation.
						return $res;

					foreach($attributes as $attribute) //Looping each layered attributes to get the updated options for the attributes based on the filters applied.
					{
						if(!array_key_exists($attribute->attribute_code,$params)) //Bypassing the attributes which are applied as filters.
						{
							if ($attribute->getIsFilterableInSearch()) {
								$filterModelName = 'catalogsearch/layer_filter_attribute';
								$filterVal = Mage::getModel($filterModelName)->setLayer($layer)->setAttributeModel($attribute)->getItems();
								$valueCount	= 0;
								$valueData	= [];
								foreach($filterVal as $option) {
									$valueData[$valueCount]["count"] =  $option->getCount();
									$valueData[$valueCount]["label"] =  strip_tags($option->getLabel());
									$valueData[$valueCount]["id"] =  $option->getValue();
									$valueCount++;
								}
								if($valueData)
								{
									$filter[$filterCount]["code"]	= $attribute->attribute_code;
									$filter[$filterCount]["type"]	= $attribute->frontend_input;
									$filter[$filterCount]["label"]	= $attribute->frontend_label;
									$filter[$filterCount]["values"] = $valueData;
									$filterCount++;
								}
							}
						}
					}
					//$res['total']	= $filterCount;
					//$res['filters']	= $filter; //setting the updated filters to the data. 
					//$res['activeFilters'] = $activeFilters; //setting the applied filters to the return data.
					$res = $filter;
				}
				else
				{
					$res['status'] = 'error';
					$res['message'] = Mage::helper('catalogsearch')->__('Minimum Search query length is %s', $query->getMinQueryLength());
				}
			}
			else
			{
				$res['status'] = 'error';
				$res['message'] = 'Invalid Request! Unable to fetch search query.';
			}
		}
		catch(Exception $e)
		{
			$res['status'] 	= 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}
	//~ public function canShowBlock($layer)
	//~ {
		//~ $_isLNAllowedByEngine = Mage::helper('catalogsearch')->getEngine()->isLeyeredNavigationAllowed();
		//~ if (!$_isLNAllowedByEngine) {
			//~ return false;
		//~ }
		//~ $availableResCount = (int) Mage::app()->getStore()
			//~ ->getConfig(Mage_CatalogSearch_Model_Layer::XML_PATH_DISPLAY_LAYER_COUNT);
		//~ if (!$availableResCount
			//~ || ($availableResCount > $layer->getProductCollection()->getSize())) {
			//~ return true;//return parent::canShowBlock();
		//~ }
		//~ return false;
	//~ }


	public function ws_getSearchCollection($store, $searchData, $filterData = null, $curr_page = null, $page_size = null, $sortType = null, $sortOrder = null, $currentcurrencycode)
	{
		$res = [];
		$res['status']	= 'success';
		$res['total'] 	= 0;
		$res['data']	= [];
		$res['redirect_category_id'] = '';
		$res['redirect_product_id'] = '';
		if($searchData)
		{
			try
			{
				Mage::app()->setCurrentStore($store);
				$basecurrencycode	= Mage::app()->getStore()->getBaseCurrencyCode();
				$curr_page = isset($curr_page) ? $curr_page : 1;
				$page_size = isset($page_size) ? $page_size : 20;
				$sortType  = isset($sortType) ? $sortType : 'relevance';
				$sortOrder = isset($sortOrder) ? $sortOrder : 'desc';
				$collection	= $this->ws_getSearchFilter($store,$searchData,$filterData,1); //getting the product collection based on the filters applied.
				if(is_array($collection)) //Check for handling error which might occur during fetching the layer product collection.
				{
					if(isset($collection['message']))
						throw new Exception($collection['message']);
					else
						throw new Exception('Something went wrong while fetching the products.');
				}
				if($collection->getSize())
				{
					$res['total'] = $collection->getSize();
					$collection->setOrder($sortType, $sortOrder); //applying the sorting on the collection.
					$collection->setPage($curr_page, $page_size); //applying the pagination on the collection.
					foreach($collection as $collProd)
					{//code within this loop is fetched from the old mofluid code of ws_layeredFilter api.
						$gflag=1;
						$_product = Mage::getModel('catalog/product')->load($collProd->getId());
						$productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
						$defaultprice  = str_replace(",", "", number_format($_product->getPrice(), 2));
						$defaultsprice = str_replace(",", "", number_format($_product->getSpecialprice(), 2));
						try
						{
							$has_custom_option     = 0;
							$custom_options        = $_product->getOptions();
							if($custom_options)
								$has_custom_option = 1;
						}
						catch(Exception $e)
						{
							$has_custom_option = 0;
						}
						$specialprice			= $_product->getSpecialPrice();
						// Get the Special Price FROM date
						$specialPriceFromDate	= $_product->getSpecialFromDate();
						// Get the Special Price TO date
						$specialPriceToDate		= $_product->getSpecialToDate();
						// Get Current date
						$today	= time();
						if($specialprice)
						{
							if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
								$specialprice = strval(round($this->convert_currency($defaultsprice, $basecurrencycode, $currentcurrencycode), 2));
							} else {
								$specialprice = 0;
							}
						}
						else
						{
							$specialprice = 0;
						}
						if($_product->getTypeID() == 'grouped')
						{
							$defaultprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 2, '.', '');
							$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
							$associatedProducts = $_product->getTypeInstance(true)->getAssociatedProducts($_product);
							if(!count($associatedProducts))
							{
								$gflag = 0;
							}
						}
						else
						{
							$defaultprice =  number_format($_product->getPrice(), 2, '.', '');
							$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
						}
						if($defaultprice == $specialprice)
							$specialprice = number_format(0, 2, '.', '');
						if($gflag)
						{
							$res["data"][] = array(
								"id" => $_product->getId(),
								"name" => $_product->getName(),
								"imageurl" => (string)$productImage,
								"sku" => $_product->getSku(),
								"type" => $_product->getTypeID(),
								"spclprice" => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
								"currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
								"price" => number_format($this->convert_currency($defaultprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
								"created_date" => $_product->getCreatedAt(),
								"is_in_stock" => $_product->getStockItem()->getIsInStock(),
								"hasoptions" => $has_custom_option,
								"stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty(),
							);
						}
					}
				}
			}
			catch(Exception $e)
			{
				$res['status'] 	= 'error';
				$res['message']	= $e->getMessage();
			}
		}
		else
		{
			$res['status'] 	= 'error';
			$res['message']	= 'Invalid Request! Please specify the search query.';
		}
		return $res;
	}
	
	/***** Newly made api's ****/
	
	/**** Function to get the list of the brands ****/
	
	public function listBrands() {
		
		$res = array();
		
		try {
			$media = Mage::getBaseUrl('media').'aitmanufacturers/list/';
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			
			/**** Getting the required field from the database table aitmanufacturers ****/
			$sql	= "Select id, manufacturer_id, list_image from aitmanufacturers";
			$rows = $connection->fetchAll($sql);
			
			$cnt = 0;
			$temp = array();
			
			/**** Getting the manufacturer_id for the same ****/
			foreach($rows as $row) {
				$temp[$cnt]["imageUrl"] = $media.$row["list_image"]; 
				$temp[$cnt]["manufacturerId"] = $row["manufacturer_id"];
				$cnt++;
			}
			
			/**** Getting the result for the same ****/
			$res["status"] = (string)1;
			$res["blogList"] = $temp;
		}catch(Exception $e) {
			$res["status"] = (string)0;
			$res["message"] = $e->getMessage();
		}
		return $res;
	}
	
	/**** Function to get the loaded product collection that is used in getProductCollection ****/
	
	public function getLoadedPdt($store) {
		
		// setting the default category id
		$catid = $catid ? $catid : 2;
		Mage::app()->setCurrentStore($store);
		
		$layer = Mage::getModel('catalog/layer');
		$category = Mage::getModel('catalog/category')->load($catid);
		$layer->setCurrentCategory($category);
		
		// getting the collection on the product
		$collection = $layer->getProductCollection();
		
		return $collection;
	}
	
	/**** Function to get the attribute id for the given manufacturer id ****/
	
	public function getAttributeId($manufacturerId) {
		// getting the manufacturer id
		return Mage::getModel('aitmanufacturers/config')->getAttributeIdByOption($manufacturerId);
	}
	
	/**** Function that filter out the required product based on the given manufacturer id ****/
	
	public function getProductCollection($store, $manufacturerId) {
		
		$res = array();
		$manufacturerAttrId = $this->getAttributeId($manufacturerId);
		$collectionResult = array();
		$collection = null;
		
		/* !AITOC_MARK:manufacturer_collection */
		if (is_null($collection)) {
			
			$collection = $this->getLoadedPdt($store);
			$helper = Mage::helper('aitmanufacturers');
			
			if ($helper->canUseLayeredNavigation($manufacturerAttrId) && !$helper->isLNPEnabled()) {
				
				// the store id is set at this time.
				$collection
					->addAttributeToSelect('sort')
					->joinAttribute('sort', 'catalog_product/aitmanufacturers_sort', 'entity_id', null, 'left');                 
				
				$productIds = Mage::getModel('aitmanufacturers/aitmanufacturers')->getProductsByManufacturer($manufacturerId, Mage::app()->getStore()->getId(), $manufacturerAttrId);
				
				$collection->addIdFilter($productIds);
				Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
				Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
				
				$collectionResult = $collection;
			} else {
				
				$collection = Mage::getResourceModel('catalog/product_collection');
				$attributes = Mage::getSingleton('catalog/config')->getProductAttributes();
				
				$collection->addAttributeToSelect($attributes)->addAttributeToSelect('sort')
					->addMinimalPrice()
					->addFinalPrice()
					->addTaxPercents()
					->addStoreFilter()
					->joinAttribute('sort', 'catalog_product/aitmanufacturers_sort', 'entity_id', null, 'left');

				$productIds = Mage::getModel('aitmanufacturers/aitmanufacturers')->getProductsByManufacturer($manufacturerId, Mage::app()->getStore()->getId(), $manufacturerAttrId);
				//$collection->addAttributeToFilter(Mage::helper('aitmanufacturers')->getAttributeCode(), array('eq' => $this->_manufacturer), 'left');
				$collection->addIdFilter($productIds);
				Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($collection);
				Mage::getSingleton('catalog/product_visibility')->addVisibleInCatalogFilterToCollection($collection);
				
				$collectionResult = $collection;
			}
			if (!is_null($collection)){
				$visibleIds = Mage::getSingleton('catalog/product_visibility')->getVisibleInCatalogIds();
				$collectionResult = $collection;
			}
		}
		return $collectionResult;
	}
	
	/**** Function for the listing of the products for the given brands ****/
	
	public function listBrandProducts($store, $service, $currency, $manufacturerId, $currentPage, $pageSize) {
		
		if(!(isset($manufacturerId) && isset($currency))) {
			return array("status"=>"0", "message"=>"Manufacturer Id or Currency is not given.");
		}
		
		$res = array();
		$productList  = array();
		
		try {
			
			/**** for getting the brand image url ****/
			$media = Mage::getBaseUrl('media').'aitmanufacturers/list/';
			$connection = Mage::getSingleton('core/resource')->getConnection('core_read');
			
			/**** Getting the required field from the database table aitmanufacturers ****/
			$sql	= "Select id, list_image from aitmanufacturers where manufacturer_id = $manufacturerId ";
			$row = $connection->fetchAll($sql);
			
			$res["imageUrl"] = $media.$row[0]["list_image"];
			
			/**** setting te current store ****/
			Mage::app()->setCurrentStore($store);
			
			$_products = $this->getProductCollection($store, $manufacturerId);
			
			$size = $_products->getSize();
			
			/**** Setting the current page and current page size to the collection that we got ****/
			$_products->setPageSize($pageSize)->setCurPage($currentPage);
			$lastPage = $_products->getLastPageNumber();
			
			$i = 0;
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			
			if($size) {
				
				foreach($_products->getItems() as $product) {
					
					$product = Mage::getModel('catalog/product')->load($product->getId());
					
					$productId = $product->getId(); // Getting the id
					$productName  = $product->getName();  // Getting the name for the product
					$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
					$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
					$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
					$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
					$today                = time();
					
					/**** Checking if the special price got applied ****/
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
						$productSprice = number_format($product->getSpecialprice(), 2);
					else
						$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
					
					$productStatus  = $product->getStockItem()->getIsInStock();
					$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
					
					if ($productStatus == 1 && $stockQuantity < 0)
						$productStatus == 1;
					else
						$productStatus == 0;
					
					$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
					
					/**** Tax Calculation ****/
					$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
					$taxClassId     = $product->getData("tax_class_id");
					$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
					$taxRate        = $taxClasses["value_" . $taxClassId];
					
					$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
					
					if ($taxType == 0) {
						$defaultPrice = str_replace(",", "", $productPrice);
					} else {
						$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
					}
					
					$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
					$defaultSprice = str_replace(",", "", $productSprice);
					$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
					// Get the Special Price
					$specialPrice         = $product->getSpecialPrice();
					// Get the Special Price FROM date
					$specialPriceFromDate = $product->getSpecialFromDate();
					// Get the Special Price TO date
					$specialPriceToDate   = $product->getSpecialToDate();
					// Get Current date
					$today                = time();
					
					/**** I don't know what is happening right now ****/
					if ($specialPrice) {
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
							$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						} else {
							$specialPrice = 0;
						}
					} else {
						$specialPrice = 0;
					}
					
					$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
					if ($taxType == 0) {
						$specialPrice = $specialPrice;
					} else {
						$specialPrice = $specialPrice - $taxPriceForSpecial;
					}
					
					if ($product->getTypeID() == 'grouped') {
						$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}else {
						$actualPrice =  number_format($product->getPrice(), 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}
					
					$shortDescription = $product->getShortDescription();
					$description = $product->getDescription();
					$cheers = is_null($product->getCheers()) ? 0 : $product->getCheers();
					
					if($actualPrice == $specialPrice) {
						$specialPrice = number_format(0, 2, '.', '');
						array_push($productList, array('id' => $productId, 'name' => $productName,'image' => (string)$productImage,'type' => $product->getTypeID(),'shortDescription'=>$shortDescription,'description'=>$description,'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus, 'cheers'=>$cheers));
					}
				}
				
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}else {
				/**** No product is present ****/
				$res["status"] = 1;
				$res["showStatus"] = 0;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}
		}catch(Exception $e) {
			$res["status"] = (string)0; // need to change
			$res["message"] = $e->getMessage();
		}
		return $res;
	}
	
	/****** Rewritten By Sagar ****/
	
	public function ws_getBestSellerProducts($store, $service, $currency, $currentPage, $pageSize, $sortType, $sortOrder) {
		
		$res = array();
		
		/**** setting te current store ****/
		Mage::app()->setCurrentStore($store);
		
		try {
			
			$currentPage = isset($currentPage) ? $currentPage : 1;
			$pageSize = isset($pageSize) ? $pageSize : 10;
			$sortType  = isset($sortType) ? $sortType : 'relevance';
			$sortOrder = isset($sortOrder) ? $sortOrder : 'desc';
			
			/**** Getting the base currency code ****/
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			
			/**** Getting the info for the showing out of stock ****/
			$showOutOfStock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
			$isInStock = $showOutOfStock ? 0 : 1;
			
			/**** Logic for the best sellar products ****/
			$_products = Mage::getResourceModel('reports/product_collection')
			->addAttributeToSelect('*')
			->addAttributeToFilter("status", Mage_Catalog_Model_Product_Status::STATUS_ENABLED)
			->addPriceData()
			->addOrderedQty()
			->setOrder("ordered_qty", "desc")
			->setStore($store)
			->addStoreFilter($store)
			->addAttributeToSort($sortType, $sortOrder)
			->setPageSize($pageSize)->setCurPage($currentPage);
			
			$lastPage = $_products->getLastPageNumber();
			$size = $_products->getSize();
			
			/**** Logic ends ***/
			
			/**** Getting all the values for the entity_id in best sellar option ****/
			$_products = $_products->getColumnValues('entity_id');
			
			/**** Getting the attributes of name, description, price and small_image for the products ****/
			$_products = Mage::getResourceModel('catalog/product_collection')
			->addAttributeToFilter('entity_id',array('in' => $_products))->addAttributeToSelect('*');
			
			$productList = array();
			$i = 0;
			
			if($size) {
				
				foreach($_products->getItems() as $product) {
					
					$product = Mage::getModel('catalog/product')->load($product->getId());
					$productId = $product->getId(); // Getting the id
					$productName  = $product->getName();  // Getting the name for the product
					$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
					$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
					$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
					$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
					$today                = time();
					$shortDescription = $product->getShortDescription();
					$description = $product->getDescription();
					
					/**** Checking if the special price got applied ****/
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
						$productSprice = number_format($product->getSpecialprice(), 2);
					else
						$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
					
					$productStatus  = $product->getStockItem()->getIsInStock();
					$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
					
					if ($productStatus == 1 && $stockQuantity < 0)
						$productStatus == 1;
					else
						$productStatus == 0;
					
					$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
					
					/**** Tax Calculation ****/
					$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
					$taxClassId     = $product->getData("tax_class_id");
					$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
					$taxRate        = $taxClasses["value_" . $taxClassId];
					
					$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
					
					if ($taxType == 0) {
						$defaultPrice = str_replace(",", "", $productPrice);
					} else {
						$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
					}
					
					$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
					$defaultSprice = str_replace(",", "", $productSprice);
					$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
					// Get the Special Price
					$specialPrice         = $product->getSpecialPrice();
					// Get the Special Price FROM date
					$specialPriceFromDate = $product->getSpecialFromDate();
					// Get the Special Price TO date
					$specialPriceToDate   = $product->getSpecialToDate();
					// Get Current date
					$today                = time();
					
					/**** I don't know what is happening right now ****/
					if ($specialPrice) {
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
							$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						} else {
							$specialPrice = 0;
						}
					} else {
						$specialPrice = 0;
					}
					
					$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
					if ($taxType == 0) {
						$specialPrice = $specialPrice;
					} else {
						$specialPrice = $specialPrice - $taxPriceForSpecial;
					}
					
					if ($product->getTypeID() == 'grouped') {
						$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}else {
						$actualPrice =  number_format($product->getPrice(), 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}
					$cheers = is_null($product->getCheers()) ? 0 : $product->getCheers();
					
					if($actualPrice == $specialPrice) {
						$specialPrice = number_format(0, 2, '.', '');
						array_push($productList, array('id' => $productId, 'name' => $productName,'image' => (string)$productImage,'type' => $product->getTypeID(),'shortDescription'=>$shortDescription,'description'=>$description,'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus, 'cheers'=>$cheers));
					}
				}
				
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}else {
				/**** No product is present ****/
				$res["status"] = 1;
				$res["showStatus"] = 0;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}
		}catch(Exception $e) {
			$res["status"] = (string)0; // need to change
			$res["errorMessage"] = $e->getMessage();
		}
		return $res;
	}
	
	/**** Function to list the blogs according to the current page and page size ****/
	
	public function listBlogs($store, $service, $currPage, $pageSize) {
		
		$res = array();
		
		try {
			
			/**** getting the post collection from the wordpress ****/
			$collection =  Mage::getResourceModel('wordpress/post_collection');
			
			/**** getting the post according to the descending order of the posted date ****/
			$collection->addIsViewableFilter()->addOrder('post_date', 'desc');
			
			/**** getting the current page and page size ****/
			$collection->setPageSize($pageSize)->setCurPage($currPage);
			
			$blogContent = array();
			$cnt = 0;
			foreach($collection as $collections) { 
				array_push($blogContent, array("id"=>$collections->getData("ID"),"title"=>$collections->getData("post_title"),"blogContent"=>$collections->getData("post_content")));
			}
			
			/**** getting the blogcontent ****/
			$res["status"] = (string)1;
			$res["blogList"] = $blogContent;
		}catch(Exception $e) {
			/**** if any error occurs ****/
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage();
		}
		return $res;
	}
	
	public function blog($store, $service, $blogId) {
		
		$res = array();
		
		if(!isset($blogId)) {
			return array("status"=>"0","errorMessage"=>"Blog Id is not sent");
		}
		
		try {
			/**** loading the model with the given blog id ****/
			$model = Mage::getModel('wordpress/post')->load($blogId);
			$res["status"] = (string)1;
			$res["id"] = $model->getData('ID'); // getting the blog id 
			$res["title"] = $model->getData('post_title'); // getting the title
			$res["blogContent"] = $model->getData('post_content');  // getting the blog content id
		}catch(Exception $e) {
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage();
		}
		return $res;
	}
	
	public function welcomeScreen($store, $service) {
		
		$res = array();
		
		$tempRes = array();
		
		/******* Question 1 *****/
		
		$question1 = array("id"=>"1","title"=>"WHAT ARE YOU HUNTING FOR?","optionA"=>"SOMETHING SPANKING NEW","optionB"=>"FOR MY STOCK","optionC"=>"SOMETHING TO GIFT","optionD"=>"SOMETHING TO PORTION OUT");
		
		array_push($tempRes, $question1);
		
		/******* Question 2 *****/
		
		$question2 = array("id"=>"2","title"=>"HOW FAMILIAR ARE YOU WITH WHISKEY?","optionA"=>"NONE AT ALL","optionB"=>"AMATUER","optionC"=>"AFICIONADO","optionD"=>"GOURMET");
		
		array_push($tempRes, $question2);
		
		/******* Question 3 *****/
		
		$question3 = array("id"=>"3","title"=>"WHAT KIND OF WHISKEY YOU \'D LIKE TO SEARCH FOR?","optionA"=>"SCOTCH","optionB"=>"AMERICAN","optionC"=>"IRISH","optionD"=>"WORLD");
		
		array_push($tempRes, $question3);
		
		/******* Question 4 *****/
		
		$question4 = array("id"=>"4","title"=>"WHERE WILL YOU BE ENJOYING IT?","optionA"=>"AT YOUR HOME","optionB"=>"AT THE BAR","optionC"=>"IN A PARTY","optionD"=>"ON A VACATION");
		
		array_push($tempRes, $question4);
		
		/******* Question 5 *****/
		
		$question5 = array("id"=>"5","title"=>"WHAT IS YOUR LEVEL OF ADVENTUROUSNESS?","optionA"=>"NOT VERY","optionB"=>"MODERATE","optionC"=>"PRETTY MUCH","optionD"=>"EXTREMELY HIGH");
		
		array_push($tempRes, $question5);
		
		$res["status"] = (string)1;
		$res["questions"] = $tempRes;
		
		return $res;
	}
	
	/**** Function to verify login ****/
	
	public function verifyLogin($store, $service, $username, $password) {
		
		// Password is sent in base64 decode
		
		/**** check whether the firstname or lastname or email or password is set or not ****/
		if(!(isset($username) && isset($password))) {
			return array("status"=>"0","errorMessage"=>"username or password missing");
		}
		
		/**** check whether the encoding is right or not ****/
		if(!$this->checkBase64Encoding($password)) {
			return array("status"=>"0","errorMessage"=>"error in decoding the parameters please check it");
		}
		
		/**** setting te current store ****/
		Mage::app()->setCurrentStore($store);
		
		if($username && $password) {
		
			$websiteId       = Mage::getModel('core/store')->load($store)->getWebsiteId();
			$res             = array();
			
			try {
				$res["userName"] = $username;
				// Password 
				$loginCustomerResult = Mage::getModel('customer/customer')->setWebsiteId($websiteId)->authenticate($username, base64_decode($password));
				$loginCustomer        = Mage::getModel('customer/customer')->setWebsiteId($websiteId);
				$loginCustomer->loadByEmail($username);
				
				$model = Mage::getModel('mofluid_tokensystem/token');
				$token = $model->createToken($username, $loginCustomer->getId());
				
				if($token) {
				
					$res["status"] = (string)1;
					$res["loginStatus"] = (string)1;
					$res["token"] = $token;
				}else {
					$res["status"] = (string)0;
					$res["loginStatus"] = (string)0;
					$res["errorMessage"] = "Error in creating token";
				}
			} catch (Exception $e) {
				$res["status"] = (string)0;
				$res["loginStatus"] = (string)0;
				$res["errorMessage"] = $e->getMessage();
			}
		}else {
			$res["status"] = (string)0;
			$res["loginStatus"] = (string)0;
			$res["errorMessage"] = "Parameters missing";
		}
		return $res;
	}
	
	/**** for checking the base64 encoding is correct or not ****/
	
	function checkBase64Encoding($data) {
		
		if(base64_encode(base64_decode($data, true)) === $data){
			return true;
		} else {
			return false;
		}
	}
	
	/**** Function to create user ****/
	
	 public function createUser($store, $service, $firstName, $lastName, $email, $password) {
		 
		/**** IMPORTANT ****/
		
		/* firstName and lastName and password are in base64encoded */
		 
		$res					= array();
		
		/**** check whether the firstname or lastname or email or password is set or not ****/
		if(!(isset($firstName) && isset($lastName) && isset($email) && isset($password))) {
			return array("status"=>"0","errorMessage"=>"firstName or lastName or email or password missing");
		}
		
		/**** check whether the encoding is right or not ****/
		if(!($this->checkBase64Encoding($firstName) && $this->checkBase64Encoding($lastName) && $this->checkBase64Encoding($password))) {
			return array("status"=>"0","errorMessage"=>"error in decoding the parameters please check it");
		}
		
		try {
			
			/**** setting the current store ****/
			Mage::app()->setCurrentStore($store);
			
			/**** getting the customer model and set the firstname, lastname, email and password ****/
			$websiteId            = Mage::getModel("core/store")->load($store)->getWebsiteId();
			$customer             = Mage::getModel("customer/customer");
			$customer->website_id = $websiteId;
			$customer->setCurrentStore($store);
			$customer->firstname     = base64_decode($firstName);
			$customer->lastname      = base64_decode($lastName);
			$customer->email         = $email;
			$customer->password_hash = md5(base64_decode($password));
			
			/**** setting the result ****/
			
			$res["email"]            = $email;
			$res["firstName"]        = base64_decode($firstName);
			$res["lastName"]         = base64_decode($lastName);
			$res["password"]         = $password;
			$res["status"]           = (string)0;
			$res["id"]               = (string)0;
			$cust                    = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
			
			//check exists email address of users
			if ($cust->getId()) {
				/**** If the customer exists ****/
				$res["status"] = (string)0;
				$res["id"]     = $cust->getId();
				$res["errorMessage"] = "Customer Already Exists";
			} else {
				
				if ($customer->save()) {
					
					/*** This is basically the process if it is for registered ***/
					/***
					$customer->sendNewAccountEmail(
						$isJustConfirmed ? 'confirmed' : 'registered',
						'',
						Mage::app()->getStore()->getId(),
						$this->getRequest()->getPost('password')
					);***/

					/**** if the customer got saved successfully ****/
					$customer->sendNewAccountEmail('confirmation', '', Mage::app()->getStore()->getId(), base64_decode($password));
					$this->send_Password_Mail_to_NewUser(base64_decode($firstName), base64_decode($password), $email);
					$res["status"] = (string)1;
					$res["id"]     = $customer->getId();
					$res["message"] = "Account confirmation is required. Please, check your email for the confirmation link.";
				} else {
					/**** failed to save the customer ****/
					$res["status"] = (string)0;
					$res["errorMessage"] = "Failed to save the customer";
				}
			}
		}catch (Exception $e) {
			/**** Exception occurs due to any reason ****/
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage(); 
		}
		return $res;
    }
    
	public function forgotPassword($email = "") {
		
		$res             = array();
		
		try {
			if ($email) {
				/** @var $customer Mage_Customer_Model_Customer */
				$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
				
				if ($customer->getId()) {
					try {
						/**** Logic for getting the password link reset ****/
						$newResetPasswordLinkToken = Mage::helper('customer')->generateResetPasswordLinkToken();
						$customer->changeResetPasswordLinkToken($newResetPasswordLinkToken);
						$customer->sendPasswordResetConfirmationEmail();
						$res["status"] = (string)1;
						$res["message"] = "Password Link sent successfully";
					}catch (Exception $e) {
						$res["status"] = (string)0;
						$res["errorMessage"] = $e->getMessage();
					}
				}else {
					$res["status"] = (string)0;
					$res["errorMessage"] = "No such customer exists";
				}
			}else {
				$res["status"] = (string)0;
				$res["errorMessage"] = "Parameter email missing";
			}
		}catch(Exception $ex) {
			$res["status"] = (string)1;
			$res["errorMessage"] = $ex->getMessage();
		}
        return $res;
    }
    
	/* Function call to login user from Email address */

	public function ws_loginwithsocial($store, $username, $firstName, $lastName) {
		
		/***** IMPORTANT :: firstname and lastname should be in base64 encoded ****/
		
		if(!isset($username)) {
			return array("status"=>"0","errorMessage"=>"username is not sent");
		}
		
	//	$firstName = base64_decode($firstName);
	//	$lastName = base64_decode($lastName);
		
		/***** Getting the website id ****/
		$websiteId       = Mage::getModel('core/store')->load($store)->getWebsiteId();
		
		$res             = array();
		$res["userName"] = $username;
		
		/**** By default the login status is zero ****/
		$loginStatus    = 0;
		
		try {
			
			/**** Getting the username and load the model with the help of useremail ****/
			$loginCustomer = Mage::getModel('customer/customer')->setWebsiteId($websiteId);
			$loginCustomer->loadByEmail($username);
			
			if ($loginCustomer->getId()) {
				/**** IMPORTANT :: NO Authentication required because the authentication has been already done. ****/
				
				/**** If the customer found  ****/
				$model = Mage::getModel('mofluid_tokensystem/token');
				$token = $model->createToken($username, $loginCustomer->getId());
				
				if($token) {
					$res["status"] = (string)1;
					$res["token"] = $token;
					$loginStatus = 1; // setting the login status to 1 i.e customer exists
				}else {
					$res["status"] = (string)0;
					$loginStatus = 0;
					$res["errorMessage"] = "Error in creating token";
				}
			} else {
				/**** If the customer does not found then register it in magento ****/
				
				$tempRes          = $this->ws_registerwithsocial($store, $username, $firstName, $lastName);
				if ($tempRes["status"] == 1) {
					$model = Mage::getModel('mofluid_tokensystem/token');
					$token = $model->createToken($username, $tempRes["id"]);
					if($token) {
						$res["status"] = (string)1;
						$res["token"] = $token;
						$loginStatus = 1; // setting the login status to 1 i.e customer exists
					}else {
						$res["status"] = (string)0;
						$loginStatus = 0;
						$res["errorMessage"] = "Error in creating token";
					}
				}else {
					$res["status"] = (string)0;
					$loginStatus = 0;
					$res["errorMessage"] = $tempres["errorMessage"];
				}
			}
		}catch (Exception $e) {
			/**** Exception ahas occurred ***/
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage();
		}
		/**** setting the loginStatus ****/
		$res["loginStatus"] = (string)$loginStatus;
		return $res;
	}

	/* Function call to register user from its Email address */

	public function ws_registerwithsocial($store, $email, $firstName, $lastName) {
		
		$res                  = array();
		
		try {
			
			$websiteId            = Mage::getModel('core/store')->load($store)->getWebsiteId();
			$customer             = Mage::getModel("customer/customer");
			
			$customer->website_id = $websiteId;
			$customer->setCurrentStore($store);
			
			// If new, save customer information
			$customer->firstname     = $firstName;
			$customer->lastname      = $lastName;
			$customer->email         = $email;
			$password                = base64_encode(rand(11111111, 99999999));
			$customer->password_hash = md5(base64_decode($password));
			$res["email"]            = $email;
			$res["firstname"]        = $firstName;
			$res["lastname"]         = $lastName;
			$res["password"]         = $password;
			$res["status"]           = 0;
			$res["id"]               = 0;
			$cust                    = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);

			/***** No need to check whether email exists or not because it has been checked previously ****/
			
			if ($customer->save()) {
				/**** if the customer got saved successfully ****/
				$customer->sendNewAccountEmail('confirmed');
				$this->send_Password_Mail_to_NewUser(base64_decode($firstName), base64_decode($password), $email);
				$res["status"] = (string)1;
				$res["id"]     = $customer->getId();
				$res["message"] = "Customer Created Successfully";
			} else {
				/**** failed to save the customer ****/
				$res["status"] = (string)0;
				$res["errorMessage"] = "Failed to save the customer";
			}
		}catch (Exception $e) {
			/**** Exception occurs due to any reason ****/
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage(); 
		}
		return $res;
	}
	
	public function productListing($store, $service, $currency, $categoryId, $currentPage, $pageSize, $sortType, $sortOrder) {
		
		$res = array();
		
		/**** setting te current store ****/
		Mage::app()->setCurrentStore($store);
		
		if(!isset($categoryId) || ($categoryId == '')) {
			return array("status"=>"0","errorMessage"=>"Field missing category id");
		}
		
		try {
			
			$currentPage = isset($currentPage) ? $currentPage : 1;
			$pageSize = isset($pageSize) ? $pageSize : 10;
			$sortType  = isset($sortType) ? $sortType : 'relevance';
			$sortOrder = isset($sortOrder) ? $sortOrder : 'desc';
			
			/**** Getting the base currency code ****/
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			
			/**** Getting the info for the showing out of stock ****/
			$showOutOfStock  = Mage::getStoreConfig('cataloginventory/options/show_out_of_stock');
			$isInStock = $showOutOfStock ? 0 : 1;
			
			$category = new Mage_Catalog_Model_Category();
			$category->load($categoryId);
			
			$collection = $category->getProductCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($store)->addAttributeToSelect('*')->addAttributeToFilter('type_id', array(
				'in' => array(
					Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
					Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE,
					Mage_Catalog_Model_Product_Type::TYPE_GROUPED,
					Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE,
				)
			))->addAttributeToFilter('visibility', 4)->addAttributeToFilter('is_in_stock', array(
				'in' => array(
					$isInStock,
					1
				)
			))->addAttributeToFilter('status', array('eq' => 1));
			
			$_products = $collection;
			$size = $_products->getSize();
			
			$_products->addAttributeToSort($sortType, $sortOrder);
			$_products->setPageSize($pageSize)->setCurPage($currentPage);
			$lastPage = $_products->getLastPageNumber();
			
			$productList = array();
			$i = 0;
			
			if($size) {
				
				foreach($_products->getItems() as $product) {
					
					$product = Mage::getModel('catalog/product')->load($product->getId());
					
					$productId = $product->getId(); // Getting the id
					$productName  = $product->getName();  // Getting the name for the product
					$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
					$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
					$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
					$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
					$today                = time();
					
					/**** Checking if the special price got applied ****/
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
						$productSprice = number_format($product->getSpecialprice(), 2);
					else
						$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
					
					$productStatus  = $product->getStockItem()->getIsInStock();
					$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
					
					if ($productStatus == 1 && $stockQuantity < 0)
						$productStatus == 1;
					else
						$productStatus == 0;
					
					$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
					
					/**** Tax Calculation ****/
					$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
					$taxClassId     = $product->getData("tax_class_id");
					$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
					$taxRate        = $taxClasses["value_" . $taxClassId];
					
					$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
					
					if ($taxType == 0) {
						$defaultPrice = str_replace(",", "", $productPrice);
					} else {
						$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
					}
					
					$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
					$defaultSprice = str_replace(",", "", $productSprice);
					$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
					// Get the Special Price
					$specialPrice         = $product->getSpecialPrice();
					// Get the Special Price FROM date
					$specialPriceFromDate = $product->getSpecialFromDate();
					// Get the Special Price TO date
					$specialPriceToDate   = $product->getSpecialToDate();
					// Get Current date
					$today                = time();
					
					/**** I don't know what is happening right now ****/
					if ($specialPrice) {
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
							$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						} else {
							$specialPrice = 0;
						}
					} else {
						$specialPrice = 0;
					}
					
					$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
					if ($taxType == 0) {
						$specialPrice = $specialPrice;
					} else {
						$specialPrice = $specialPrice - $taxPriceForSpecial;
					}
					
					if ($product->getTypeID() == 'grouped') {
						$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}else {
						$actualPrice =  number_format($product->getPrice(), 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}
					
					$shortDescription = $product->getShortDescription();
					$description = $product->getDescription();
					$cheers = is_null($_product->getCheers()) ? 0 : $_product->getCheers();
					
					if($actualPrice == $specialPrice) {
						$specialPrice = number_format(0, 2, '.', '');
						array_push($productList, array('id' => $productId, 'name' => $productName,'image' => (string)$productImage,'type' => $product->getTypeID(),'shortDescription'=>$shortDescription,'description'=>$description,'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus,'cheers'=>$cheers));
					}
				}
				
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}else {
				/**** No product is present ****/
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}
		}catch(Exception $e) {
			$res["status"] = (string)0; // need to change
			$res["errorMessage"] = $e->getMessage();
		}
		return $res;
	}
	
	public function getProductRating($productId) {
		
		$ratingModel = Mage::getModel('rating/rating')->getEntitySummary($productId);
		$ratingData = $ratingModel->getData();
		$ratingVal = $ratingData['sum'];
		$ratingCount = $ratingData['count'];
		
		if($ratingCount != 0)
			$ratingPercent = ($ratingVal/($ratingCount*100))*100;
		else
			$ratingPercent = 0;
		$ratingPercent = (int)$ratingPercent;
		
		return (string)$ratingPercent;
	}
	
	public function submitReview($storeId, $service, $ratingDescription, $ratingStar, $productId) {
		
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();

		if(!isset($customerId) || is_null($customerId)) {
				return array("status"=>"0","errorMessage"=>"Invalid Token.");
		}

		if(!isset($ratingStar)) {
			return array("status"=>"0","errorMessage"=>"Rating Information is not sent."); 
		}
	
		if(!is_numeric($ratingStar) || $ratingStar > 5) {
			return array("status"=>"0","errorMessage"=>"Please provide a valid rating.");
		}
		
		if(!isset($productId)) {
			return array("status"=>"0","errorMessage"=>"Product Id is not sent.");
		}

		$allowGuest = Mage::helper('review')->getIsGuestAllowToWrite();

		if(!isset($allowGuest)&& (!isset($customerId))) {
			return array("status"=>"0","errorMessage"=>"Guests are not allowed to write the review.");
		} 

		/**** Update all the three attributes for the review i.e price, quality and value ****/
		$ratingStar = array("1"=>$ratingStar,"2"=>$ratingStar+5,"3"=>$ratingStar+10);
			
		/**** Rating description is the description for the review ****/
		$ratingDescription = base64_decode($ratingDescription);		
		
		$ratingTitle = substr($ratingDescription, 0, 30);
		
		$nickName = $this->getCustomerNameForReview($customerId);

		Mage::app()->setCurrentStore($storeId);
		
		$cropData = array("detail"=>$ratingDescription, "title"=>$ratingTitle, "nickname"=>$nickName);
		$review = Mage::getModel('review/review')->setData($cropData);	
		$validate = $review->validate();

		if($validate === true) {
			try {

				$review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
						->setEntityPkValue($productId)
						->setStatusId(Mage_Review_Model_Review::STATUS_APPROVED)
						->setCustomerId($customerId)
						->setStoreId(Mage::app()->getStore()->getId())
						->setStores(array(Mage::app()->getStore()->getId()))
						->save();
				
				 foreach ($ratingStar as $ratingId => $optionId) {
						Mage::getModel('rating/rating')
						->setRatingId($ratingId)
						->setReviewId($review->getId())
						->setCustomerId($customerId)
						->addOptionVote($optionId, $productId);
				}
				
				/**** it is done for getting the customer name ****/
				$customer = Mage::getModel('customer/customer')->load($customerId);
				$customerName = $customer->getName();
				
				/**** updating the latest update table for the review ****/
				Mage::dispatchEvent('latest_update_review_after', array('customer_id'=>$customerId,'customer_name'=>$customerName,'review_id'=>$review->getId()));
				
				$review->aggregate();
				$res["status"] = (string)1;
				$res["message"] = "Review updated successfully.";
			}catch(Exception $e) {
				$res["status"] = (string)0;
				$res["errorMessage"] = $e->getMessage();
			}
		}else {
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage();
		}
		return $res; 
	}



	protected function getCustomerNameForReview($customerId) {

		/**** if the customer id is not given then customer name is set to guest ****/
		if(!isset($customerId)) return 'Guest';
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		$customerName = '';

		if($customer->getId()) {
			/**** getting the customer name ****/
			$customerName = $customer->getName();
			
			/**** if the customer name is still empty *****/
            if($customerName == '') 
				$customerName = 'Customer'; 
		}else {
			$customerName = 'Guest';
		}
		
		return $customerName;
	}

	public function getReviewForProduct($storeId, $productId, $pageSize, $currPage) {
		
		if(!isset($productId) || !isset($storeId)) {
			return array("status"=>"0","errorMessage"=>"Please provide the required field. Required fields are productid and storeid.");
		}
		
		$_product = Mage::getModel('catalog/product')->load($productId);
		
		if(!$_product->getId()) {
			return array("status"=>"0","errorMessage"=>"Invalid Product Id. Please provide valid product id");
		}

		$currPage = isset($currPage) ? $currPage : 1;
		$pageSize = isset($pageSize) ? $pageSize : 5;

		Mage::app()->setCurrentStore($storeId);

		try { 
			$reviews = Mage::getModel('review/review')
			->getResourceCollection()
			->addStoreFilter(Mage::app()->getStore()->getId())
			->addEntityFilter('product', $_product->getId())
			->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
			->setDateOrder()
			->setPageSize($pageSize)
			->setCurPage($currPage)
			->addRateVotes();
			
			$reviewArr = array();

			foreach($reviews->getItems() as $review) {

				$title = $review->getTitle();
				$reviewId = $review->getReviewId();
				$detail = $review->getDetail();
				$nickName = $review->getNickname();
				$ratingVotes = $review->getRatingVotes();
				$ratingVotesData = $ratingVotes->getData();
				$totalRatingPercent = 0;
				$total = 0;
				foreach($ratingVotesData as $ratingPercent) {
					$totalRatingPercent += $ratingPercent['percent'];
					$total++;
				}
				$ratingPercent = $totalRatingPercent/$total;
				$ratingPercent = (string)(int)$ratingPercent;	
				array_push($reviewArr,array("reviewId"=>$reviewId,"name"=>$nickName,"ratingPercent"=>$ratingPercent,"content"=>$detail));
			}
			$res["status"] = (string)1;
			$res["count"] = (string)$reviews->getSize();
			$res["reviews"] = $reviewArr;
		}catch(Exception $e) {
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage();
		}
		return $res;		
	}	
		
	public function authorizeApi() {

			$currToken = Mage::app()->getRequest()->getHeader('Authorization');
			$model = Mage::getModel('mofluid_tokensystem/token');
			return $model->getCustomerIdFromToken($currToken);
	}
	
	
	public static function getFormattedData($data, $formattedDigit) {
		return number_format($data, $formattedDigit);
	}
	
	/**** Getting the attributes made by the user and visible on the frontend shown in the admin information page ****/
		
	public function getProductDetailDescription($storeId, $productId, $currentCurrencyCode) {

		try {
		
			if(!isset($productId) || !isset($currentCurrencyCode) || $currentCurrencyCode=="" || $productId =="") {
				return array("status"=>"0","errorMessage"=>"Parameters missing either product id or currency");
			}
			
			Mage::app()->setCurrentStore($storeId);
			$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
	
			$res = array();
			
			/**** Load the product ****/

			$_product = Mage::getModel('catalog/product')->load($productId);
			$attributeArr = array();
			$attributes = $_product->getAttributes();
			$attrCount = 0;
			
			/**** Attributes made by the user and visible on the frontend shown in admin information page ****/

			foreach($attributes as $attribute) {
				if($attribute->is_user_defined && $attribute->is_visible) {
					
					$attributeValue = $attribute->getFrontend()->getValue($_product);
					
					if(is_null($attributeValue) || $attributeValue == "") {
						continue;
					}else {
						$attrCode = $attribute->getAttributeCode();
						$attrLabel = $attribute->getStoreLabel($_product);
						$attrValue = $attributeValue;
						$attrCount++;
						array_push($attributeArr, array("attributeCode"=>$attrCode,"attributeLabel"=>$attrLabel,"attributeValue"=>$attrValue));
					} 		
				}
			}

			/**** Custom Attributes made by the user please check it may contain errrors ****/	
	
			$customAttrArr = array();
			$customAttributeCount = 0;	
			$customAttributes = $_product->getOptions();

			foreach($customAttributes as $customAttribute) {

				$customAttributeCount++;
				$title = $customAttribute->getTitle();
				$customAttrId = $customAttribute->getId();
				$customAttrIsReqd = $customAttribute->getIsRequire();
				$customAttrType = $customAttribute->getType();
				$customAttrSortOrder = $customAttribute->getSortOrder();
				//~ $customAttrAll = $customAttribute->getData();
				array_push($customAttrArr, array("title"=>$title,"attributeId"=>$customAttrId,"attributeIsRequired"=>$customAttrIsReqd, "attributeType"=>$customAttrType,"attributeSortOrder"=>$customAttrSortOrder));	
			}

			/**** Getting the image of the product and the product stock ****/
			$productImageUrl = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);		
			$stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product);
			$stockQty = (int)$stock->getQty();	
			$isInStock = $_product->getStockItem()->getIsInStock() ? 1 : 0;				
			$ratingPercent = $this->getProductRating($_product->getId());
			$productId = $_product->getId();
			
			/**** Getting the product attributes ****/
			$poductWeight = self::getFormattedData($_product->getWeight(), 2);
			$productVisibleStatus = $_product->getStatus();
			$productDescription = $_product->getDescription();
			$productName = $_product->getName();
			$productSku = $_product->getSku();
			$productCategoriesIds = $_product->getCategoryIds();
			$productStatus = $_product->getStatus();
			$shortDescription = $_product->getShortDescription();
			$productType = $_product->getTypeID();
			
			/**** special price calculation ****/
			$specialPrice         = $_product->getSpecialPrice();
			$specialPriceFromDate =$_product->getSpecialFromDate();
			$specialPriceToDate   = $_product->getSpecialToDate();
			$currentTime = Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s');
			
			/**** If the special price is not valid then set to zero ****/
			if ($specialPrice) {
				if ($currentTime >= ($specialPriceFromDate) && $currentTime <= ($specialPriceToDate)) {
					$specialPrice = strval(round($this->convert_currency($specialPrice, $baseCurrencyCode, $currentCurrencyCode), 2));
				} else {
					$specialPrice = 0;
				}
			} else {
				$specialPrice = 0;
			}
			
			$specialPrice = (string)$this->getFormattedData($specialPrice, 2);
			
			/**** Final Price of the product with discount without tax not used ****/
			$finalPrice = $_product->getFinalPrice();
			
			/**** Getting the product price data ****/
			$defaultPrice = $_product->getPrice();
			$convertedPrice = $this->convert_currency($defaultPrice, $baseCurrencyCode, $currentCurrencyCode);
			$defaultPrice =  number_format($convertedPrice, 2, '.', '');
			
			/**** default shipping price ****/
			$defaultShippingPrice = Mage::getStoreConfig('carriers/flatrate/price');
			$defaultShippingPrice = strval(round($this->convert_currency($defaultShippingPrice, $baseCurrencyCode, $currentCurrencyCode), 2));
			$defaultShippingPrice = (string)$this->getFormattedData($defaultShippingPrice, 2);
			$cheers = is_null($_product->getCheers()) ? 0 : $_product->getCheers();
			$cheerStatus = Mage::helper('mofluid_cheers')->getCheers($_product);
			
			/**** Getting the current currency symbol ****/
			$currencySymbol = Mage::app()->getLocale()->currency($currentCurrencyCode)->getSymbol();

			$res = array("status"=>"1","stockQty"=>(string)$stockQty,"sku"=>(string)$productSku, "name"=>$productName, "categories"=>$productCategoriesIds,
						"description"=>$productDescription,"shortDescription"=>$shortDescription,"weight"=> $poductWeight, "visibilityStatus"=>$productStatus, "isInStock"=> (string)$isInStock,"productType"=>$productType,"originalPrice"=>$defaultPrice,"specialPrice"=>$specialPrice,"defaultShippingPrice"=>$defaultShippingPrice,"currencySymbol"=>$currencySymbol,
						"productId"=> (string)$productId, "imageUrl"=> (string)$productImageUrl, "productRating"=> (string)$ratingPercent,"customAttribute"=> $customAttrArr,"customAttributeCount"=> (string)$customAttributeCount, "allVisibleAttributes"=> $attributeArr,"visibleAttributeCount"=>(string)$attrCount,"cheers"=>(string)$cheers,"cheerStatus"=>$cheerStatus);

			/**** Remaining to put the configurable products and the grouped products and downloadable products ****/
		}catch(Exception $e) {
			$res["status"] = (string)0;
			$res["errorMessage"] = $e->getMessage();
		}
		return $res;
	}
	
	/**** Getting the store details for the api ****/
	
	public function getStoreDetails($storeId, $theme) {
		
		try { 
			/**** Getting the default store id which is active true is for that ****/
			$defaultStoreId = Mage::app()->getWebsite(true)->getDefaultGroup()->getDefaultStoreId();
			
			$storeId = $storeId ? $storeId : $defaultStoreId ;
			
			Mage::app()->setCurrentStore($storeId);
			
			$storeDetails = array();
			
			/**** Getting the time zone ****/
			$date		= Mage::app()->getLocale()->date();
			$timeZone	= $date->getTimezone();
			
			/**** Getting the store details ****/
			$storeData = Mage::app()->getStore($storeId)->getData();
			
			$storeId = $storeData["store_id"];
			$storeCode = $storeData["code"];
			$webSiteId = $storeData["website_id"];
			$groupId = $storeData["group_id"];
			$storeName = $storeData["name"];
			$storeIsActive = $storeData["is_active"];
			
			$frontName = Mage::app()->getStore($storeId)->getFrontendName();
			
			$storeLogo = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN) . 'frontend/default/default/' . Mage::getStoreConfig('design/header/logo_src');
			$bannerImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN).'frontend/default/default/images/banner.png';
			
			/**** admin name and email ****/
			$adminName = Mage::getStoreConfig('trans_email/ident_sales/name');
			$adminEmail = Mage::getStoreConfig('trans_email/ident_sales/email');
			
			$adminDetails = array("adminName"=>$adminName, "adminEmail"=>$adminEmail);
			
			/**** base currency of the store ****/
			$baseCurrencyCode = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
			$baseCurrencyName = Mage::app()->getLocale()->currency(Mage::app()->getStore($storeId)->getBaseCurrencyCode())->getName();
			$baseCurrencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore($storeId)->getBaseCurrencyCode())->getSymbol();
			
			$baseCurrencyArr = array("code"=>$baseCurrencyCode,"name"=>$baseCurrencyName,"symbol"=>$baseCurrencySymbol);
			
			/**** current currency of the store ****/
			$currentCurrencyCode = Mage::app()->getStore($storeId)->getCurrentCurrencyCode();
			$currentCurrencyName = Mage::app()->getLocale()->currency(Mage::app()->getStore($storeId)->getCurrentCurrencyCode())->getName();
			$currentCurrencySymbol = Mage::app()->getLocale()->currency(Mage::app()->getStore($storeId)->getCurrentCurrencyCode())->getSymbol();
			
			$currentCurrencyArr = array("code"=>$currentCurrencyCode,"name"=>$currentCurrencyName,"symbol"=>$currentCurrencySymbol);
			
			/**** allowed currency in the store ****/
			$allowedCurrency = Mage::getStoreConfig("currency/options/allow");
			
			$currencyArr = array("baseCurrencyDetails"=>$baseCurrencyArr, "currentCurrencyDetails"=>$currentCurrencyArr,"allowedCurrency"=>$allowedCurrency);
			
			/**** various urls used in the websites ****/
			$storeUrl   = Mage::helper("core/url")->getHomeUrl();
			$mediaUrl   = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA);
			$skinUrl    = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_SKIN);
			$jsUrl      = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_JS);
			$rootUrl    = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB);
			
			$usefulUrl = array("storeUrl"=>$storeUrl, "mediaUrl"=>$mediaUrl, "skinUrl"=>$skinUrl, "jsUrl"=>$jsUrl, "rootUrl"=>$rootUrl);
			
			/**** option to show out of stock option ****/
			$showOutOfStockOption = Mage::getStoreConfig("cataloginventory/options/show_out_of_stock");
			
			/**** getting the google client id ****/
			$resource       = Mage::getSingleton("core/resource");
			$readConnection = $resource->getConnection("core_read");
			$query = "SELECT * FROM ".$resource->getTableName("mofluid_themes");
			$results = $readConnection->fetchAll($query);

			/**** fetching the privacy policy, contact us etc from the mofluid module NOT FROM THE WEBSITE ****/
			foreach ($results as $result) {
				
				/**** if the theme is modern ****/
				if ($result["mofluid_theme_code"] == "modern") {
					$googleClientId = $result["google_ios_clientid"];
					$googleLogin     = $result["google_login"];
					$cmsPageId = $result["cms_pages"];
					$aboutUsPageId = $result["about_us"];
					$termConditionPageId = $result["term_condition"];
					$privacyPolicyPageId = $result["privacy_policy"];
					$returnPrivacyPolicyPageId = $result["return_privacy_policy"];
					$taxFlag = $result["tax_flag"];
				}
			}
			
			/**** mofluid -> theme -> modern -> configuration ****/
			$mofluidConfiguration = array("googleClientId"=>$googleClientId,"loginWithGoogleStatus"=>$googleLogin,"cmsPageId"=>$cmsPageId,"aboutUsPageId"=>$aboutUsPageId,"termConditionPageId"=>$termConditionPageId,"privacyPolicyPageId"=>$privacyPolicyPageId,"returnPrivacyPolicyPageId"=>$returnPrivacyPolicyPageId, "taxFlag"=>$taxFlag);
			
			
			/**** get google analytics ****/
			$modules      = Mage::getConfig()->getNode("modules")->children();
			$modulesArray = (array) $modules;
			$googleAnalytics = array();

			/**** if the module exists ****/
			if (!empty($modulesArray["Mofluid_Ganalyticsm"])) {
				$mofluidGoogleAnalytics      = Mage::getModel("mofluid_ganalyticsm/ganalyticsm")->load(23);
				
				/**** get the google analytics account id and google account status ****/
				$mofuidGoogleAnalyicsAccountId = $mofluidGoogleAnalytics->getData("mofluid_ga_accountid");
				$mofluidGoogleAnalyticsStatus    = $mofluidGoogleAnalytics->getData("mofluid_ga_status");
				if (!$mofluidGoogleAnalyticsStatus) {
					$mofluidGoogleAnalyticsStatus = 0;
				}
				$googleAnalytics = array("googleAnalyticsStatus"=>$mofluidGoogleAnalyticsStatus,"googleAnalyticsAccountId"=>$mofuidGoogleAnalyicsAccountId);
			}
			
			/**** Getting the themes ****/
			$mofluidThemeId = "1";
			
			if ($theme == null || $theme == "") {
				$theme = "elegant";
			}
			
			/**** Getting the theme settings ****/
			$mofluidConfigModelSetting = Mage::getModel("mofluid_thememofluidelegant/thememofluidelegant")->getCollection()->addFieldToFilter("mofluid_theme_code", $theme)->getData();
			
			/**** Load the mofluid theme model ****/
			$mofluidThemeModel = Mage::getModel("mofluid_thememofluidelegant/images");
			
			$mofluidThemeId                      = $mofluidConfigModelSetting[0]["mofluid_theme_id"];
			
			$mofluidThemeBanner          = $mofluidThemeModel->getCollection()->addFieldToFilter("mofluid_theme_id", $mofluidThemeId)->addFieldToFilter("mofluid_image_type", "banner");
			
			$mofluidThemeBannerData = $mofluidThemeBanner->setOrder("mofluid_image_sort_order", "ASC")->getData();
			$mofluidThemeBannerImageType = $mofluidConfigModelSetting[0]["mofluid_theme_banner_image_type"];
			
			$bannerDataArr = array();
			
			if ($mofluidThemeBannerImageType == "1") {
				
				foreach ($mofluidThemeBannerData as $bannerKey => $bannerValue) {
					
					try {
						$mofluidImageAction = json_decode(base64_decode($bannerValue["mofluid_image_action"]));
						if ($mofluidImageAction->base == "product") {
							$_products = Mage::getModel('catalog/product')->getCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($storeId)->addAttributeToFilter('entity_id', $mofluidImageAction->id);
							foreach ($_products as $_product) {
								$productStatus  = $_product->getStockItem()->getIsInStock();
								$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
								if ($productStatus == 1 && $stockQuantity < 0)
									$productStatus == 1;
								else
									$productStatus == 0;
								break;
							}
							$mofluidImageAction->status         = $productStatus;
							$bannerValue["mofluid_image_action"] = base64_encode(json_encode($mofluidImageAction));
						}
					}
					catch (Exception $ex) {
						$ex->getMessage();
					}
					if ($bannerValue["mofluid_store_id"] == $storeId) {
						$bannerDataArr[] = $bannerValue;
					} else if ($bannerValue["mofluid_store_id"] == 0) {
						$bannerDataArr[] = $bannerValue;
					} else {
						continue;
					}
				}
			}else {
				
				foreach ($mofluidThemeBannerData as $bannerKey => $bannerValue) {
					try {
						$mofluidImageAction = json_decode(base64_decode($bannerValue["mofluid_image_action"]));
						
						if ($mofluidImageAction->base == "product") {
							$_products = Mage::getModel('catalog/product')->getCollection()->joinField('is_in_stock', 'cataloginventory/stock_item', 'is_in_stock', 'product_id=entity_id', '{{table}}.stock_id=1', 'left')->addStoreFilter($storeId)->addAttributeToFilter('entity_id', $mofluidImageAction->id);
							foreach ($_products as $_product) {
								$productStatus  = $_product->getStockItem()->getIsInStock();
								$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty();
								if ($productStatus == 1 && $stockQuantity < 0)
									$productStatus == 1;
								else
									$productStatus == 0;
								break;
							}
							$mofluidImageAction->status         = $productStatus;
							$bannerValue["mofluid_image_action"] = base64_encode(json_encode($mofluidImageAction));
						}
					}
					catch (Exception $ex) {

					}
					if ($bannerValue["mofluid_image_isdefault"] == '1' && $bannerValue["mofluid_store_id"] == $storeId) {
						$bannerDataArr[] = $bannerValue;
						break;
					} else if ($bannerValue["mofluid_image_isdefault"] == '1' && $bannerValue["mofluid_store_id"] == 0) {
						$bannerDataArr[] = $bannerValue;
						break;
					} else {
						continue;
					}
				}
				if (count($bannerDataArr) <= 0) {
					$bannerDataArr[] = $mofluidThemeBannerData[0]; //$banner_value;
				}
			}
			
			/**** Getting the banners ends ****/
			
			/**** Getting the logo image ****/
			$mofluidThemeLogo      = $mofluidThemeModel->getCollection()->addFieldToFilter("mofluid_image_type", "logo")->addFieldToFilter("mofluid_theme_id", $mofluidThemeId);
			$mofluidThemeLogoData = $mofluidThemeLogo->getData();

			$mofluidThemeLogoImage   = $mofluidThemeLogoData;
			$mofluidThemeLogoAltImage     = Mage::getStoreConfig("design/header/logo_alt");
			
			$mofluidLogoArr = array("mofluidThemeLogoImage"=>$mofluidThemeLogoData,"mofluidThemeLogoAltImage"=>$mofluidThemeLogoAltImage);
			
			$res = array("status"=>"1","storeId"=>$storeId,"timeZone"=>$timeZone,"websiteId"=>$webSiteId,"storeCode"=>$storeCode,"groupId"=>$groupId,"storeName"=>$storeName,"storeIsActive"=>$storeIsActive, "frontName"=>$frontName, "storeLogo"=>$storeLogo, "adminDetails"=>$adminDetails, "currency"=>$currencyArr,"usefulUrl"=>$usefulUrl, "mofluidConfiguration"=>$mofluidConfiguration, "googleAnalytics"=>$googleAnalytics, "logoDetails"=>$mofluidLogoArr,"bannerDetails"=>$bannerDataArr);
		}catch(Exception $e) {
			$res = array("status"=>"0","errorMessage"=>$e->getMessage());
		}
		return $res;
	}
	
	/**** Cart Sync written by sagar but nothing in comparison to Lakshya Sir ****/
    
   /**** Cart Sync written by sagar but nothing in comparison to Lakshya Sir ****/
	
	/**** Function to merge the cart of the customers ****/
	
	/**** return type :: Mage_Sales_Quote model ****/
	
	public function mergeCartItems($customerId, $quoteId) {
		
		/**** load the models of the quotes of both ****/
		$customerQuote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->loadByCustomer($customerId);
		$currentQuote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->loadActive($quoteId);
		
		/**** if the current quote is not valid ****/
		if(!$currentQuote->getId()) {
			return $customerQuote;
		}
		
		$currentQuoteId = $currentQuote->getId();
		$customerQuoteId = $customerQuote->getId();
		
		/**** if the customer quote is not equal to the given quote ****/
		if($customerQuote->getId() && $currentQuoteId != $customerQuoteId) {
			
			if($currentQuote->getId()) {
				$customerQuote->merge($currentQuote)->collectTotals()->save();
				
				/**** delete the current quote ****/
				$currentQuote->delete();
			}
			/*** assigning the quote to the customer quote ****/
			$quote = $customerQuote;
			
		}else {
			
			/**** $quote is set to the current quote IMPORTANT ****/
			if($currentQuote->getId()) 
				$quote = $currentQuote;
			else
				$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId());
			
			/**** save the quote after calculation ****/
			$quote->getBillingAddress();
			$quote->getShippingAddress()->setCollectShippingRates(true);
			$quote->setTotalsCollectedFlag(false)->collectTotals();
			$quote->setCustomer(Mage::getModel('customer/customer')->load($customerId));
			$quote->save();
			
			/**** reload the quote ****/
			$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->loadActive($quote->getId());
		}
		
		/**** $quote is always set ****/
		return $quote;
	}
	
	/**** function to get the required quote ****/
	
	public function getQuote($storeId, $customerId = null, $quoteId = null) {
		
		/**** setting the current store ****/
		Mage::app()->setCurrentStore($storeId);
		
		/**** Getting the model of the quote for the current store ****/ 
		$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId()); 
		
		/**** Logic for loading the quote on precedence order ****/
		
		/* if the customerId and quote id both are set then merge the quote and load it **/
		/* if is customerId set load the quote by the customer id */
		/* if the quote id is set load the quote by the quote id */
		/* if nothing is set load the quote of the customer*/
		
		$customer = null;
		$isGuestQuote = 0;
		
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		$quoteId = $quoteId != '' ? $quoteId : null;
		
		$result = array();
		
		if(isset($customerId)) {
			
			/**** if customer id is set the customer must exists ****/
			$customer = Mage::getModel('customer/customer')->load($customerId);
			
			if(!$customer->getId()) {
				return array("status"=>"0", "errorMessage"=>"Customer with $customerId does not exists.");
			}
		}
		
		if(isset($customerId) && isset($quoteId)) {
			
			/**** if both are set ****/
			$quote = $this->mergeCartItems($customerId, $quoteId);
		}elseif(isset($customerId)) {
			/**** if only customer id is set ****/
			$quote = $quote->loadByCustomer($customerId);
			
			if(!$quote->getId()) {
				/**** if the quote of the customer does not exists ****/
				/**** save the quote for the customer ****/
				$quote->getBillingAddress();
				$quote->getShippingAddress();
				$quote->setCustomer($customer)->setTotalsCollectedFlag(false)->collectTotals()->save();
				
				/**** get the customer currently created quote ****/
				$tempQuote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->loadActive($quote->getId());
				$quote = $tempQuote;
			}
		}elseif(isset($quoteId)) {
			/**** if if quote id is set ****/
			$quote = $quote->loadActive($quoteId);
			
			if(!$quote->getId()) {
				/**** if the quote of the customer does not exists ****/
				return array("status"=>"0", "errorMessage"=>"Invalid quote id.");
			}
		}else {
			/**** if nothing is set it is a guest quote ****/
			
			/**** create the quote for the guest ****/
			$quote->getBillingAddress();
			$quote->getShippingAddress();
			$quote->setTotalsCollectedFlag(false)->collectTotals()->save();
			
			/**** Load the currently created quote ****/
			$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->loadActive($quote->getId());
		}
		
		$result["status"] = (string)1;
		$result["quote"] = $quote;
		return $result;
	}
	
	public function getCartItems($storeId, $quoteId = null, $customerId = null, $currency = 'USD', $updateCartMessages = null, $isInitialize = 0) {
		
		$isEnableCheckout = 0;
		
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
	
		if(is_null($quoteId) && is_null($customerId)) {
			/**** bypassing the cart of the guest ****/
			return array("status"=>"1", "count"=>"0", "couponCode"=>"", "cartTotalQuantity"=>"0", "quoteId"=>"", "priceInfo"=>array(), "cartItems"=>array(),"cartMessages"=>array(), "enableCheckout"=>"0");
		}
	
		$tempQuoteRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($tempQuoteRes['status'] == 0) {
			return array("status"=>"0", "errorMessage"=>$tempQuoteRes['errorMessage']);
		}
		
		/**** Load the quote properly ****/
		$quote = $tempQuoteRes['quote'];
		
		/**** This section is because if the cart is going to be initialized for checkout ****/
		if($isInitialize) {
			
			/**** If the cart contains error or does not have any items in it ****/
			if (!$quote->hasItems() || $quote->getHasError()) {
				return array('status'=> 0, 'errorMessage'=>'Cart Contains Error.');
			}
			
			/**** If the cart does not qualified for the minimum amount of credit for checkout ****/
			if (!$quote->validateMinimumAmount()) {
				$error = Mage::getStoreConfig('sales/minimum_order/error_message') ?
					Mage::getStoreConfig('sales/minimum_order/error_message') :
					Mage::helper('checkout')->__('Subtotal must exceed minimum order amount');
				return array('status'=>0, 'errorMessage'=>$error);
			}
			
			/**** you can see it in the quote cart->init() ****/
			$quote->removeAllAddresses()->removePayment();
			
			$customer = Mage::getModel('customer/customer')->load($customerId);
			
			/**** taken from the Mage_Sales_Model_Quote on line number 412 ****/
			/**** doing because before every checkout the shipping address and billing address are set to the default one ****/
			$quote = $quote->assignCustomer($customer);
			
			/**** calculating the billing and shipping address ****/
			$quote->getBillingAddress();
			$quote->getShippingAddress()->setCollectShippingRates(true);
			$quote->setTotalsCollectedFlag(false)->collectTotals();
			$quote->save();
		}
		
		/**** getting the number of cart items in cart ****/
		$noOfCartItem = $quote->getItemsCount();
		
		$result = array();
		$tax = 0.00;
		$discount = 0.00;
		
		if($noOfCartItem > 0) {
			
			/**** If number of item in the cart is greater than zero ****/
			$allVisibleItems = $quote->getAllVisibleItems();
			
			if(!is_null($customerId))
				$isEnableCheckout = 1;
			
			$tempRes = array();
			
			/**** get the cart item properties of the customer ****/
			foreach($allVisibleItems as $item) {

				$itemId = $item->getId();
				$productId = $item->getProductId();
				$_product = Mage::getModel('catalog/product')->load($productId);
				$productName = $item->getName();
				$quantity = (string)$item->getQty();
				$rowTotal = number_format($this->convertCurrency($item->getRowTotal(), self::$_baseCurrencyCode, $currency), 2, '.', '');
				$price = number_format($this->convertCurrency($item->getPrice(), self::$_baseCurrencyCode, $currency), 2, '.', '');
				$priceInclTax = number_format($this->convertCurrency($item->getPriceInclTax(), self::$_baseCurrencyCode, $currency), 2, '.', '');
				$itemType = $item->getProductType();
				$sku = $item->getSku();
				$name = $item->getName();
				$imgUrl = (string)Mage::helper('catalog/image')->init($_product,'thumbnail')->resize(200,200);
				if($item->getHasError()) $isEnableCheckout = 0;
				array_push($tempRes, array("itemId"=>$itemId,"productId"=>$productId,"name"=>$productName, "quantity"=>$quantity, "price"=>$price, "priceIncludingTax"=>$priceInclTax,"rowTotal"=>$rowTotal,"productType"=>$itemType,"sku"=>$sku,"imageUrl"=>$imgUrl));
				/**** Remaining to add the configurable, downloadable options link. Not necessary now. You can look sokolin cart sync for that ****/
			}
			
			
			$couponCode = $quote->getCouponCode() ? $quote->getCouponCode() : "";
			$tempPriceArr = $this->getQuoteTotalDescription($quote);
			
			$messages = array();
			
			$quoteMessages = $quote->getMessages();
			
			/**** Getting the error messages ****/
			if ($quoteMessages) {
				foreach ($quoteMessages as $quoteMessage) {
					array_push($messages, array('type'=>$quoteMessage->getType(), 'text'=>$quoteMessage->getCode()));
					if($quoteMessage->getType() == 'error')
						$isEnableCheckout = 0;
				}
			}
			
			if (!$quote->validateMinimumAmount()) {
				$isEnableCheckout = 0;
				$minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
					->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

				$warning = Mage::getStoreConfig('sales/minimum_order/description')
					? Mage::getStoreConfig('sales/minimum_order/description')
					: Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

				$messages = array_push($messages, $warning);
			}
			
			$result["status"] = (string)1;
			$result["count"] = (string)$noOfCartItem;
			$result["quoteId"] = (string)$quote->getId();
			$result["couponCode"] = $couponCode;
			$result["priceInfo"] = $tempPriceArr;
			$result["cartTotalQuantity"] = (string)intval($quote->getItemsQty());
			$result["cartItems"] = $tempRes;
			$result["cartMessages"] = $messages;
			$result["enableCheckout"] = (string)$isEnableCheckout;
		}else {
			
			/**** if no item is present in the cart ****/
			$result["status"] = (string)1;
			$result["count"] = (string)0;
			$result["couponCode"] = "";
			$result["cartTotalQuantity"] = (string)0;
			$result["quoteId"] = (string)$quote->getId();
			$result["priceInfo"] = array();
			$result["cartItems"] = array();
			$result["cartMessages"] = array();
			$result["enableCheckout"] = (string)0;
		}	
		return $result;
	}

	 /**** function to convert the currency from to to ****/
	 public function convertCurrency($price, $from, $to) {
		return Mage::helper('directory')->currencyConvert($price, $from, $to);
	}
	
	/**** Function to empty the cart with the given quote id ****/
	
	public function emptyCart($storeId, $quoteId = null, $customerId = null) {
		
		if(is_null($quoteId)) {
			return array("status"=>"0", "errorMessage"=>"Field missing quote id.");
		}
		
		$result = array();
		
		$quoteRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteRes['status'] == 0) {
			return array("status"=>"0", "errorMessage"=>$quoteRes["errorMessage"]);
		}
		
		/**** Getting the quote from the quote result ****/
		$quote = $quoteRes['quote'];
		
		try { 
			/**** remove all items ****/
			$quote->removeAllItems();
			
			$quoteId = $quote->getId();
			/**** calculate the billing and shipping address rates after deletion IMPORTANT ****/
			$quote->getBillingAddress();
			$quote->getShippingAddress()->setCollectShippingRates(true);
			$quote->setTotalsCollectedFlag(false)->collectTotals();
			$quote->save();
			$result["status"] = (string)1;
			$result["message"] = "Cart with $quoteId is cleared.";
		}catch(Exception $e) {
			$result["status"] = (string)0;
			$result["errorMessage"] = $e->getMessage();
		}
		return $result;
	}
	
	/**** function to delete the item from the cart ****/
	
	public function deleteItemFromCart($storeId, $quoteId = null, $itemId, $customerId = null) {
		
		if(is_null($quoteId)) {
			/**** quote id must be set ****/
			return array("status"=>"0", "errorMessage"=>"Field missing quote id.");
		}
		
		$quoteRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteRes['status'] == 0) {
			return array("status"=>"0", "errorMessage"=>$quoteRes["errorMessage"]);
		}
		
		/**** Getting the quote from the quote result ****/
		$quote = $quoteRes['quote'];
		
		$result = array();
		
		if($itemId) {
			try {
				$isItemValid = $quote->getItemById($itemId);
				
				if($isItemValid) {
					$quote->removeItem($itemId)->save();
					/**** calculate the billing and shipping address rates after deletion IMPORTANT ****/
					$quote->getBillingAddress();
					$quote->getShippingAddress()->setCollectShippingRates(true);
					$quote->setTotalsCollectedFlag(false)->collectTotals();
					$quote->save();
					$result["status"] = (string)1;
					$result["message"] = "$itemId from the cart with quote id $quoteId has been removed.";
				}else {
					$result["status"] = (string)0;
					$result["errorMessage"] = "Item with item id $itemId does not exists.";
				}
			}catch(Mage_Sales_Exception $exc) {
				$result["status"] = (string)0;
				$result["errorMessage"] = $exc->getMessage();
			}catch(Exception $e) {
				
				$result["status"] = (string)0;
				$result["errorMessage"] = $e->getMessage();
			}
		}else {
			$result["status"] = (string)0;
			$result["errorMessage"] = "Field missing Item id";
		}
		return $result;
	}
	
	/**** Function to apply coupon code ****/

	public function applyCouponCode($storeId, $quoteId = null, $couponCode = null, $couponCodeRemoveFlag = 0) {
		
		/**** quote id is must not relying on the customer ****/
		
		if(is_null($couponCode) || is_null($quoteId)) {
			return array("status"=>"0","errorMessage"=>"Field missing coupon code or quote id.");
		}
		
		$quote = Mage::getModel('sales/quote')->setStoreId(Mage::app()->getStore()->getId())->loadActive($quoteId);
		
		if(!$quote->getId()) {
			return array("status"=>"0", "errorMessage"=>"Invalid quote id.");
		}
		
		if(!$quote->getItemsCount()) {
			return array("status"=>"0", "errorMessage"=>"Cart does not contain any item.");
		}
		
		$result = array();
		
		try {
			
			if($couponCodeRemoveFlag) {
				$couponCode = "";
			}
			
			$codeLength = strlen($couponCode);
			$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
			$quote->getShippingAddress()->setCollectShippingRates(true);
			
			$quote->setCouponCode($isCodeLengthValid ? $couponCode : '')->collectTotals()->save();
			
			if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $quote->getCouponCode()) {
					$result["status"] = (string)1;
					$result["message"] = 'Coupon code '.Mage::helper('core')->escapeHtml($couponCode).' was applied.';
                } else {
					$result["status"] = (string)1;
					$result["message"] = 'Coupon code '.Mage::helper('core')->escapeHtml($couponCode).' was not valid.';
                }
			}else {
				$result["status"] = (string)1;
				$result["message"] = 'Coupon code '.Mage::helper('core')->escapeHtml($couponCode).' was canceled.';
			}
		}catch(Exception $e) {
			$result["status"] = (string)0;
			$result["errorMessage"] = $e->getMessage();
		}
		return $result;
	}
	
	/**** add the item in the cart ****/
	
	public function addItemToCart($storeId, $customerId = null, $quoteId = null, $jsonProductInfo, $currency = 'USD') {
		
		Mage::app()->setCurrentStore($storeId);
		
		$result = array();
		/**** getting the request information ****/
		
		$currentUrl = explode('?',Mage::helper('core/url')->getCurrentUrl());
		$currentUrl = $currentUrl[0];
		$productOrigRequestInfo = json_decode(base64_decode($jsonProductInfo),true);
		
		if($productOrigRequestInfo && isset($productOrigRequestInfo['product_id']) && is_numeric($productOrigRequestInfo['product_id'])) {
			
			/**** Load the product ****/
			$_product = Mage::getModel('catalog/product')->load($productOrigRequestInfo['product_id']);
			
			if(!$_product->getId()) {
				return array("status"=>"0", "errorMessage"=>"Invalid Product Id.");
			}
			
			/**** Get the quote for the customer ****/
			$quoteRes = $this->getQuote($storeId, $customerId, $quoteId);
			
			if($quoteRes['status'] == 0) {
				return array("status"=>"0", "errorMessage"=>$quoteRes["errorMessage"]);
			}
			
			$quote = $quoteRes['quote'];
			
			/**** Making the request info for the addition of the product ****/
			$requestInfo = array();
			$requestInfo['uenc'] = base64_encode($currentUrl);
			$requestInfo['product'] = $_product->getId();
			$requestInfo['qty'] = isset($productOrigRequestInfo['quantity']) ? $productOrigRequestInfo['quantity'] : "1";
			
			/**** if the product type is downloadable ****/
			if($_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE) {
				$requestInfo['super_attribute'] = $productOrigRequestInfo['super_attribute'];
			}
			
			/**** if the product type is downloadable ****/
			if($_product->getTypeId() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE) {
				$requestInfo['links'] = $productOrigRequestInfo['links'];
			}
			
			/**** if the product type is grouped product ****/
			if ($_product->getTypeId() == Mage_Catalog_Model_Product_Type::TYPE_GROUPED){
				$requestInfo['super_group'] = $productOrigRequestInfo['super_group'];
				/**** unset the quantity for the grouped product ****/
				if(array_key_exists('qty',$requestInfo))
					unset($requestInfo['qty']);
			}
			
			/**** For the simple product the request info is automatically set e.g [uenc], [product] [qty] *****/
			
			/**** making the request info ****/
			$requestInfo = new Varien_Object($requestInfo);
			
			/**** if the product sale is set for the quantity ****/
			if (!$_product->isConfigurable() && $_product->getStockItem()) {
				$minimumQty = $_product->getStockItem()->getMinSaleQty();
				//If product was not found in cart and there is set minimal qty for it
				if ($minimumQty && $minimumQty > 0 && $requestInfo->getQty() < $minimumQty && !$quote->hasProductId($_product->getId())) {
					$requestInfo->setQty($minimumQty);
				}
			}
			
			$productId = $_product->getId();
			
			try {
				/**** add the product to the cart ****/
				$isItemAdded = $quote->addProduct($_product, $requestInfo);
				
				/**** on failure it returns a string ****/
				if(is_string($isItemAdded)) {
					$quote->getBillingAddress();
					$quote->getShippingAddress()->setCollectShippingRates(true);
					$quote->setTotalsCollectedFlag(false)->collectTotals();
					$quote->save();
					throw new Exception($isItemAdded);
				}
				if ($remoteAddr = Mage::helper('core/http')->getRemoteAddr())
					$quote->setRemoteIp($remoteAddr);
				
				/**** calculate the shipping address totals ****/
				$quote->getBillingAddress();
				$quote->getShippingAddress()->setCollectShippingRates(true);
				$quote->setTotalsCollectedFlag(false)->collectTotals();
				$quote->save();
				
				$productName = Mage::helper('core')->escapeHtml($_product->getName());
				
				$quoteId = $quote->getId();
				$result["status"] = (string)1;
				$result["quoteId"] = $quote->getId();
				$result["message"] = "$productName was added to your shopping cart.";
			} catch (Mage_Core_Exception $e) {
				/**** If the error occurs ****/
				$result["status"] = (string)0;
				$result["errorMessage"] = $e->getMessage();
			} catch(Exception $e) {
				$result["status"] = (string)0;
				$result["errorMessage"] = $e->getMessage();
			}
		}else {
			$result["status"] = (string)0;
			$result["errorMessage"] = "Field missing product_id.";
		}
		return $result;
	}
	
	/**** suggest item qty for the cart ****/
	
	public function suggestItemsQty($data, $quote) {
	
		foreach ($data as $itemId => $itemInfo) {
			
			if (!isset($itemInfo['qty'])) {
				continue;
			}
			$qty = (float) $itemInfo['qty'];
			if ($qty <= 0) {
				continue;
			}
			
			$quoteItem = $quote->getItemById($itemId);
			if (!$quoteItem) {
				continue;
			}

			$product = $quoteItem->getProduct();
			if (!$product) {
				continue;
			}

			/* @var $stockItem Mage_CatalogInventory_Model_Stock_Item */
			$stockItem = $product->getStockItem();
			if (!$stockItem) {
				continue;
			}

			$data[$itemId]['before_suggest_qty'] = $qty;
			$data[$itemId]['qty'] = $stockItem->suggestQty($qty);
		}

		return $data;
	}
	
	/* Params :
	 *  
	 * jsonCartInfo = Array ( ( [42] => Array([qty]=> 1)), [51] => Array([qty] => 2), [53]=> Array([qty] => 1) )
	 * 
	 * [itemId] => Array([qty] => 2)
	 * 
	 * */
	
	public function updateCartItems($storeId, $customerId = null, $quoteId = null, $jsonCartInfo = null) {
		
		Mage::app()->setCurrentStore($storeId);
		
		/**** customer id or quote id must be set for updation of cart ****/
		if(is_null($quoteId)) {
			return array("status"=>"0", "errorMessage"=>"Field missing customer id or quote id.");
		}
		
		/**** if cart info is not set ****/
		if(is_null($jsonCartInfo)) {
			return array("status"=>"0", "errorMessage"=>"Field missing cart information.");
		}
		
		$result = array();
		
		/**** getting the quote a/c to customer id or quote id ****/
		$quoteRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteRes["status"] == 0) {
			return array("status"=>"0", "errorMessage"=>$quoteRes["errorMessage"]);
		}
		
		$quote = $quoteRes["quote"];
		
		/**** getting the request information ****/
		
		$currentUrl = explode('?',Mage::helper('core/url')->getCurrentUrl());
		$currentUrl = $currentUrl[0];
		
		$cartData = json_decode(base64_decode($jsonCartInfo),true);
		
		if(empty($cartData)) {
			return array("status"=>"1", "message"=>"Nothing to import.");
		}
		
		/**** change the cart data by the suggest item quantity ****/
		$cartData = $this->suggestItemsQty($cartData, $quote);
		
		/**** to get the cart error messages ****/
		$cartErrorMessages = array();
		
		/**** to get the cart calculation messages ****/
		$cartMessages = array();
		
		/**** check if the quantity was recalculated or not ****/
		$qtyRecalculatedFlag = false;
		
		try { 
			
			/**** logic to update the cart items ****/
			foreach ($cartData as $itemId => $itemInfo) {
				
				$item = $quote->getItemById($itemId);
				
				if (!$item) {
					continue;
				}

				if (!empty($itemInfo['remove']) || (isset($itemInfo['qty']) && $itemInfo['qty']=='0')) {
					/**** if item quantity is 0 then remove the item ****/
					$quote->removeItem($itemId);
					continue;
				}
				
				$qty = isset($itemInfo['qty']) ? (float) $itemInfo['qty'] : false;
				if ($qty > 0) {
					
					$item->setQty($qty);

					$itemInQuote = $quote->getItemById($item->getId());

					if (!$itemInQuote && $item->getHasError()) {
						array_push($cartErrorMessages, $item->getMessage());
						//~ Mage::throwException($item->getMessage());
					}

					if (isset($itemInfo['before_suggest_qty']) && ($itemInfo['before_suggest_qty'] != $qty)) {
						$qtyRecalculatedFlag = true;
						$before_suggest_qty = $itemInfo['before_suggest_qty'];
						array_push($cartMessages, array("Quantity was recalculated from $before_suggest_qty to $qty. "));
					}
				}else {
					$quote->removeItem($itemId);    //removing the product if quantity passed is less than or equal to zero
				}
			}
			if ($qtyRecalculatedFlag) {
				array_push($cartMessages, array("'Some products quantities were recalculated because of quantity increment mismatch"));
			}
			
			/**** calculate the shipping address totals ****/
			$quote->getBillingAddress();
			$quote->getShippingAddress()->setCollectShippingRates(true);
			$quote->setTotalsCollectedFlag(false)->collectTotals();
			$quote->save();
			$result["status"] = (string)1;
			$result["quoteId"] = $quote->getId();
			$result["cartMessages"] = $cartMessages;
			$result["cartErrorMessages"] = $cartErrorMessages;
		}catch(Exception $e) {
			$result["status"] = (string)0;
			$result["message"] = $e->getMessage();
		}
		return $result;
	}
	
	/**** Function for getting my orders of particular customer ****/
	
	public function gettingMyOrders($storeId, $currency = 'USD', $currentPage = 1, $pageSize = 10) {
		
		/**** setting up the current store id ****/
		
		Mage::app()->setCurrentStore($storeId); 
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		/**** Logic for getting the orders ****/
		$orders = Mage::getResourceModel('sales/order_collection')
			->addFieldToSelect('*')
			->addFieldToFilter('customer_id', $customerId )
			->addFieldToFilter('state', array('in' => Mage::getModel('sales/order_config')->getVisibleOnFrontStates()))
			->setOrder('created_at', 'desc');
			
		$orderTotalCount = $orders->getLastPageNumber();
		
		/**** applying the pagination concept ****/
		$orders->setPage($currentPage, $pageSize);
		
		$result = array();
		$orderArr = array();
		
		/**** getting the order details ****/
		foreach( $orders as $order) {
			
			$tempOrderDetail = $this->gettingOrderDetails($storeId, $currency, $order, 1);
			array_push($orderArr, $tempOrderDetail);
		}
		
		$result["status"] = 1;
		$result["lastPageNumber"] = $orderTotalCount;
		$result["orderInfo"] = $orderArr;
		
		return $result;
	}
	
	/**** Function to get the details of the particular order ****/
	
	public function getOrderDetails($storeId, $currency, $orderIncrementId) {
		
		if(!isset($orderIncrementId) || $orderIncrementId =='')
			return array("status"=>0, "errorMessage"=>"Field missing order increment id.");
		
		if(!isset($currency) || $currency =='') 
			return array("status"=>0, "errorMessage"=>"Field missing currency.");
			
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		
		/**** if the order does not exist ****/
		if(!$order->getId()) {
			return array("status"=> 0, "errorMessage"=>"Invalid order id.");
		}
		
		/**** getting the order details of the specific order id ****/
		$orderInfo = $this->gettingOrderDetails($storeId, $currency, $order);
		
		/**** there is no any chance of getting error so by passing it ****/
		$result["status"] = 1;
		$result["orderInfo"] = $orderInfo;
		
		return $result;
	}
	
	/**** Function to get the order details of an order ****/
	/**** Parameter passed -> Mage_Sales_Order object ****/
	
	public function gettingOrderDetails($storeId, $currency, $order, $countFlag = 0) {
		
		$baseCurrencyCode = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
		
		$addressInfo = $this->getOrderAddressInfo($order);
		
		$paymentInfo = $this->getOrderPaymentInfo($order);
		
		$shippingMethodInfo = $this->getOrderShippingInfo($order);
		
		/**** Getting all the items of the product ****/
		$items = $order->getAllVisibleItems();
		
		$itemArr = array();
		
		foreach($items as $item) {
			$tempItemRes = $this->getItemDetails($storeId, $currency, $item, $countFlag);
			array_push($itemArr, $tempItemRes);
			
			/**** Done for the my account page only one item of the product is returned ****/
			if($countFlag) break;
		}
		
		/**** Getting the order attributes of the product ****/
		
		$orderEntityId = $order->getId();
		$orderIncrementId = $order->getRealOrderId();
		$orderStatus = $order->getStatus();
		$orderSubtotal = number_format($this->convert_currency(floatval($order->getSubtotal()), $baseCurrencyCode, $currency), 2, '.', '');
		$orderDate = Mage::getModel('core/date')->date('D jS F,g:ia', strtotime($order->getCreatedAt()));
		$grandTotal = number_format($this->convert_currency(floatval($order->getGrandTotal()), $baseCurrencyCode, $currency), 2, '.', '');
		$shippingAmount = number_format($this->convert_currency(floatval($order->getShippingAmount()), $baseCurrencyCode, $currency), 2, '.', '');
		$taxAmount = number_format($this->convert_currency(floatval($order->getTaxAmount()), $baseCurrencyCode, $currency), 2, '.', '');
		$orderCurrency = $order->getOrderCurrencyCode();
		$orderCurrencySymbol = Mage::app()->getLocale()->currency($orderCurrency)->getSymbol();
		$orderDiscountAmount = number_format($this->convert_currency(floatval($order->getDiscountAmount()), $baseCurrencyCode, $currency), 2, '.', '');
		$couponCode = $order->getCouponCode();
		$couponUsed = 0;
		
		$priceArr = array("Subtotal"=>$orderSubtotal,"Shipping & Handling"=> $shippingAmount, "Grand Total (Incl. Tax)"=>$grandTotal, "Tax"=>$taxAmount, "Grand Total (Excl. Tax)"=>($grandTotal-$taxAmount));
		
		/**** setting the order information ****/
		if($couponCode != "")  {
			$couponUsed = 1;
			$orderDiscountAmount = floatval(number_format($this->convert_currency(floatval($order->getDiscountAmount()), $baseCurrencyCode, $currency), 2, '.', '')) * -1;
		}
		
		/**** making the attributes of the product ****/
		return array("orderEntityId"=>$orderEntityId, "orderIncrementId"=> $orderIncrementId, "orderStatus"=> $orderStatus, "orderDate"=> $orderDate,"priceInfo"=>$priceArr, "orderCurrency"=>$orderCurrency, "discountAmount"=>$orderDiscountAmount,"couponCode"=>$couponCode, "couponUsed"=>$couponUsed, "orderCurrencySymbol"=> $orderCurrencySymbol, "shippingMethodInfo"=> $shippingMethodInfo, "paymentInfo"=>$paymentInfo, "addressInfo"=>$addressInfo, "itemProducts"=>$itemArr);
		
	}
	
	/**** Function to get the ordered item information ****/
	/**** Parameter passed -> Mage_Sales_Order object ****/
	
	function getItemDetails($storeId, $currency, $item) {
		
		$baseCurrencyCode = Mage::app()->getStore($storeId)->getBaseCurrencyCode();
		
		/**** getting the item attributes ****/
		$itemId = $item->getId();
		$productId = $item->getProductId();
		$_product = Mage::getModel('catalog/product')->load($productId);
		$productName = $item->getName();
		$rowTotal = number_format($this->convertCurrency($item->getRowTotal(), self::$_baseCurrencyCode, $currency), 2, '.', '');
		$price = number_format($this->convertCurrency($item->getPrice(), self::$_baseCurrencyCode, $currency), 2, '.', '');
		$priceInclTax = number_format($this->convertCurrency($item->getPriceInclTax(), self::$_baseCurrencyCode, $currency), 2, '.', '');
		$itemType = $item->getProductType();
		$itemOrderedQuantity = (int)$item->getQtyOrdered();
		$itemShippedQuantity = (int)$item->getQtyShipped();
		$sku = $item->getSku();
		$name = $item->getName();
		$imgUrl = (string)Mage::helper('catalog/image')->init($_product,'thumbnail')->resize(200,200);
		$result =  array("itemId"=>$itemId,"productId"=>$productId,"name"=>$productName, "quantityOrdered"=>$itemOrderedQuantity, "quantityShipped"=>$itemShippedQuantity, "price"=>$price, "priceIncludingTax"=>$priceInclTax,"rowTotal"=>$rowTotal,"productType"=>$itemType,"sku"=>$sku,"imageUrl"=>$imgUrl);
		
		return $result;
	}
	
	/**** Function to get the shipping and billing address from the order ****/
	/**** Parameter passed -> Mage_Sales_Order object ****/
	
	public function getOrderAddressInfo($order) {
		
		$shippingAddr = array();
		$billingAddr = array();
		
		/**** Getting the shipping address details ****/
		
		$shippingAddress = $order->getShippingAddress();
		
		if (is_object($shippingAddress)) {
			
			$shippingAddr = array(
				"firstName" => $shippingAddress->getFirstname(),
				"lastName" => $shippingAddress->getLastname(),
				"company" => $shippingAddress->getCompany(),
				"street" => $shippingAddress->getStreetFull(),
				"region" => $shippingAddress->getRegion(),
				"city" => $shippingAddress->getCity(),
				"pincode" => $shippingAddress->getPostcode(),
				"countryId" => $shippingAddress->getCountry_id(),
				"contactNumber" => $shippingAddress->getTelephone(),
			);
		}
		
		/**** getting the billing address details ****/
		
		$billingAddress = $order->getBillingAddress();
		
		if (is_object($billingAddress)) {
			
			$billingAddr = array(
				"firstName" => $billingAddress->getFirstname(),
				"lastName" => $billingAddress->getLastname(),
				"company" => $billingAddress->getCompany(),
				"street" => $billingAddress->getStreetFull(),
				"region" => $billingAddress->getRegion(),
				"city" => $billingAddress->getCity(),
				"pincode" => $billingAddress->getPostcode(),
				"countryId" => $billingAddress->getCountry_id(),
				"contactNumber" => $billingAddress->getTelephone()
			);
		}
		
		$result = array("billingAddress"=>$shippingAddr, "shippingAddress"=>$billingAddr);
		
		return $result;
	}
	
	/**** Function to get the payment of order ****/
	/**** Parameter passed -> Mage_Sales_Order object ****/
	
	public function getOrderPaymentInfo($order) {
		
		/**** getting the payment information ****/
		$paymentInfo = array();
		
		try {
			
			/**** getting the payment object from the order ****/
			$payment = array();
			$payment = $order->getPayment();
			
			$paymentResult = array(
				"paymentMethodTitle" => $payment->getMethodInstance()->getTitle(),
				"paymentMethodCode" => $payment->getMethodInstance()->getCode()
			);
			
			$paymentInfo = $paymentResult;
			
			/**** Don't know what it is doing ****/
			
			//~ if ($payment->getMethodInstance()->getCode() == "banktransfer") {
				//~ $payment_result["payment_method_description"] = $payment->getMethodInstance()->getInstructions();
			//~ }
			
		} catch (Exception $ex2) {

		}
		
		return $paymentInfo;
	}
	
	/**** Function to get the shipping method title and code ****/
	
	/**** Parameter passed -> Mage_Sales_Order object ****/
	
	public function getOrderShippingInfo($order) {
		
		$shippingMethodDesc = $order->getShippingDescription();
		$shippingMethod = $order->getShippingMethod();
		return array("shippingMethodCode"=> $shippingMethodDesc, "shippingMethodTitle"=>$shippingMethod);
	}
	
	public function getAllActiveShippingMethods() {
 
		$methods = Mage::getSingleton('shipping/config')->getActiveCarriers();
 
		$shipMethodCollection = new Varien_Data_Collection();
 
		foreach ($methods as $shippingCode => $shippingModel) {
			 
			 $shippingTitle = Mage::getStoreConfig('carriers/'.$shippingCode.'/title');
			 
			 $tempAllowedMethod = array();
			 
			 if ($allMethods = $shippingModel->getAllowedMethods()) {
				 
				 foreach($allMethods as $method) {
					 
					 array_push($tempAllowedMethod, array("methodName"=>$method->getTitle(),"methodCode"=>$method->getCode()));
				 }
			 }
			 
			 $shipMethod = new Varien_Object(array(
				 'code' => $shippingCode,
				 'title' => $shippingTitle,
				 'allMethods'=> $tempAllowedMethod
			 ));
 
			 $shipMethodCollection->addItem($shipMethod);
		 }
		 return $shipMethodCollection->toArray();
	 }
	
	/**** Function to get the country list alongwith the region provided by the magento ****/
	/**** Please don't confuse it with the paypal allowed country ****/
	
	public function getCountryList() {

		$result = array();
		$tempRes = array();
		/**** Getting the sorted country list ****/

		$sortedCollection = $this->getCountrySortedList();
	   
		/**** getting the country region list ****/
		foreach($sortedCollection as $sortedKey => $sortedVal) {

			$tempArr = array();
			$tempArr['countryCode'] = $sortedVal;
			$tempArr['countryName'] = $sortedKey;
		
			/**** Getting the collection of the region code of the collection ****/
			$tempRegion = array();

			$regionCol = Mage::getModel('directory/region')->getResourceCollection()
				 ->addCountryFilter($sortedVal)->load();

			foreach($regionCol as $region) {
				array_push($tempRegion, array("regionId"=>$region->getId(), "regionCode"=>$region->getCode(), "regionName"=>$region->getName()));
			}
			
			$tempArr['countryRegion'] = $tempRegion;
			array_push($tempRes, $tempArr); 
		}

		/**** There will be no error because if error occurs then magento does not exists ****/
		$result['status'] = 1;
		$result['countryList'] = $tempRes; 
		return $result;
	}
	
	/**** Function to get the sorted collection of country ****/
	
	public function getCountrySortedList() {
		
		 /**** Get the country collection ****/
		 
		$collection = Mage::getModel('directory/country')->getResourceCollection()
				  ->loadByStore(Mage::app()->getStore()->getId());

		/**** sort the array with the helper of the core/string ****/
		
		$sortedCollection = array();

		foreach($collection as $col) {
			
			$name = $col->getName();
			$sortedCollection[$name] = $col->getId();
		}
		
		/**** sort the array with the help of the upper key ****/
		Mage::helper('core/string')->ksortMultibyte($sortedCollection);

		return $sortedCollection;
	}
    
	/**** checkout api's started ****/
	
	/**** Function to get the post data having the content-type as json/application ****/
	
	/*
	 * return type array of posted data 
	 * 
	 * */
     
	public function getJsonPostedData() {
	
		/**** getting the address with the content-type application/json ****/
		
		$filePointer = fopen('php://input', 'r');
		$rawData = stream_get_contents($filePointer);
		return json_decode($rawData, true);
	}
	
	/**** Get the parsed data address ****/
	
	public function getParsedAddress($address) {
		
		/**** Getting the address data ****/
		$addressData = $address->getData();
		
		/**** Getting the country name with the help of the country id ****/
		//~ $country = Mage::app()->getLocale()->getCountryTranslation($addressData['country_id']);
		
		$address = array('addressId'=>$addressData['entity_id'], 'createdAt'=>$addressData['created_at'], 'updatedAt'=>$addressData['updated_at'],'firstName'=>$addressData['firstname'],'middleName'=>$addressData['middlename'],'lastName'=>$addressData['lastname'], 'city'=>$addressData['city'], 'region'=>$addressData['region'], 'regionId'=>$addressData['region_id'], 'postCode'=>$addressData['postcode'], 'telephone'=>$addressData['telephone'],'street'=>$addressData['street'], 'countryCode'=>$addressData['country_id']);
		
		return $address;
    }

	/**** Get the primary shipping address ****/
	
	public function getCustomerPrimaryShippingAddress() {
		
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		/**** Getting the address for the primary shipping address ****/
		$shippingAddress = $customer->getPrimaryShippingAddress();
		$result = $this->getParsedAddress($shippingAddress);
		
		/**** No errors will occur if the error occurs magneto does not exists ****/
		return array("status"=>"1","shippingAddress"=>$result);

	}
	
	/**** Get the primary billing address ****/
	
	public function getCustomerPrimaryBillingAddress() {
		
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		/**** Getting the address for the primary billing address ****/
		$billingAddress = $customer->getPrimaryBillingAddress();
		$result = $this->getParsedAddress($billingAddress);
		
		/**** No errors will occur if the error occurs magneto does not exists or a very basic issue ****/
		return array("status"=>"1","billingAddress"=>$result);
	}
	
	/**** Get the customer primary billing and shipping address ****/
	
	public function getCustomerPrimaryAddress() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		/**** Getting the address for the primary shipping address ****/
		$billingAddress = $customer->getPrimaryBillingAddress();
		$billingAddress = $this->getParsedAddress($billingAddress);
		
		/**** Getting the address for the primary shipping address ****/
		$shippingAddress = $customer->getPrimaryShippingAddress();
		$shippingAddress = $this->getParsedAddress($shippingAddress);
		
		/**** No errors will occur if the error occurs magneto does not exists ****/
		return array("status"=>"1","billingAddress"=>$billingAddress, "shippingAddress"=>$shippingAddress);
	}

	/**** Get the customer my profile data ****/
	
	public function getMyProfileData() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		/**** Getting the address for the primary shipping address ****/
		
		if($billingAddress = $customer->getPrimaryBillingAddress())
			$billingAddress = $this->getParsedAddress($billingAddress);
		else
			$billingAddress = array();
		
		/**** bypassing it only billing address is used for the billing address ****/
		/**** Getting the address for the primary shipping address ****/
		//~ $shippingAddress = $customer->getPrimaryShippingAddress();
		//~ $shippingAddress = $this->getParsedAddress($shippingAddress);
		
		$addressInfo = array();
		
		array_push($addressInfo, array("billingAddress"=>$billingAddress));
		//~ array_push($addressInfo, array("shippingAddress"=>$shippingAddress));
		
		$customerName = $customer->getName();
		$customerEmail = $customer->getEmail();
		
		return array("status"=>"1","name"=>$customerName, "email"=>$customerEmail, "addressInfo"=>$addressInfo);
	}
	
	public function updateMyProfileData() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing billing data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		return $this->saveCustomerAddress($postedData, $customerId);
		
	}
	
	public function saveCustomerAddress($postedData = array(), $customerId) {
		
		/**** Loading the customer ****/
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		$addressId = $postedData['address_id'];
		
		/* @var $address Mage_Customer_Model_Address */
		$address  = Mage::getModel('customer/address');
		if($addressId) {
			$existAddress = $customer->getAddressById($addressId);
			if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
				$address->setId($existsAddress->getId());
			}
		}
		
		$errors = array();
		
		/* @var $addressForm Mage_Customer_Model_Form */
		$addressForm = Mage::getModel('customer/form');
		$addressForm->setFormCode('customer_address_edit')
			->setEntity($address);
		
		/**** removing this part because it just extract the data ****/
		//~ $addressData    = $addressForm->extractData($this->getRequest());
		$addressErrors  = $addressForm->validateData($postedData['billing_data']);
		if ($addressErrors !== true) {
			$errors = $addressErrors;
		}
		
		try {
			/**** not setting this address as the default shipping always setting it as default billing address ****/
			$addressForm->compactData($addressData);
			$address->setCustomerId($customer->getId())
				->setIsDefaultBilling(1)
				->setIsDefaultShipping(false);
			
			/**** validating the address ****/
			$addressErrors = $address->validate();
			if ($addressErrors !== true) {
				$errors = array_merge($errors, $addressErrors);
			}
			
			if (count($errors) === 0) {
				$address->save();
				return array('status'=>1, 'message'=> 'The address has been saved.');
			} else {
				$tempRes = array();
				$tempRes['status'] = 0;
				$tempRes['errorMessage'] = array();
				foreach ($errors as $errorMessage) {
					array_push($tempRes['errorMessage'], $errorMessage);
				}
				return $tempRes;
			}
		}catch (Mage_Core_Exception $e) {
			return array('status'=>0, 'message'=>$e->getMessage());
		} catch (Exception $e) {
			return array('status'=>0, 'message'=> $e->getMessage());
		}
	}
	
	public function getCustomerAllAddresses() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		/**** Getting all the addresses of the customer ****/
		
		$addresses = $customer->getAddresses();
		$addressArr = array();

		foreach($addresses as $address) {
			array_push($addressArr, $this->getParsedAddress($address));
		}
		
		/**** hopefully not any error will occur ****/
		
		return array('status'=>1, 'customerAddressList'=>$addressArr);
    }
	
	/**** It assumes that the checkout will be carried by only the logged in user ****/
	
	public function saveBillingAddress() {

		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing billing data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$storeId = $postedData['store_id'];
		
		/**** setting the customer id to be false while updating if the customer does not choose to be the currently existing address ****/
		$customerAddressId = isset($postedData['billing_address_id']) ? $postedData['billing_address_id'] : false; 
		
		/**** getting the quote a/c to customer id or quote id ****/
		$quoteRes = $this->getQuote($storeId, $customerId, $postedData['quote_id']);
		
		if($quoteRes["status"] == 0) {
			return array("status"=>"0", "errorMessage"=>$quoteRes["errorMessage"]);
		}
		
		$quote = $quoteRes["quote"];
		
		return $this->saveBilling($postedData['billing'], $customerAddressId, $quote);
	}
	
	 /**** Params $data = posted billing address and $quote is the Mage_Sales_Model_Quote instance ****/

	public function saveBilling($data = array(), $customerAddressId, $quote) {
		
		if(empty($data)) {
			return array('status'=>'0','errorMessage'=>'Invalid Data.');
		}
		
		/**** additional field which will be set if the shipping gets complete  may be use ****/
		
		$isShippingComplete = 0; 
		
		/**** getting the customer billing address IMPORTANT which is the address id passed from the frontend ****/
		
		$address = $quote->getBillingAddress();
		
		$addressForm = Mage::getModel('customer/form');
		$addressForm->setFormCode('customer_address_edit')
			->setEntityType('customer_address');
		
		if (!empty($customerAddressId)) {
			/**** if the customer address id is given it is given as priority ****/ 
			$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
			
			if ($customerAddress->getId()) {
				if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
					return array('status'=>'0',
						'message' => 'Customer Address is not valid.'
					);
				}
				/**** it is already saved in the address book ****/
				$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
				$addressForm->setEntity($address);
				$addressErrors  = $addressForm->validateData($address->getData());
				/**** if it contains the error ****/
				if ($addressErrors !== true) {
					return array('status'=>'0', 'message' => $addressErrors);
				}
			}
		}else {
			$addressForm->setEntity($address);
			// emulate request object
			$addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
			$addressErrors  = $addressForm->validateData($addressData);
			if ($addressErrors !== true) {
				return array('status' => '0', 'message' => array_values($addressErrors));
			}
			$addressForm->compactData($addressData);
			
			/**** unset billing address attributes which were not shown in form ****/
			foreach ($addressForm->getAttributes() as $attribute) {
				if (!isset($data[$attribute->getAttributeCode()])) {
					$address->setData($attribute->getAttributeCode(), NULL);
				}
			}
			/*** creating a new address id ****/
			$address->setCustomerAddressId(null);
			/*** Additional form data, not fetched by extractData (as it fetches only attributes) ***/
			$address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
		}
		
		 /**** set email for newly created user can be skipped ***/
		if (!$address->getEmail() && $quote->getCustomerEmail()) {
			$address->setEmail($quote->getCustomerEmail());
		}
		
		/**** validate billing address ****/
		if (($validateRes = $address->validate()) !== true) {
			return array('status' => 0, 'message' => $validateRes);
		}
		
		$address->implodeStreetAddress();
		
		if (!$quote->isVirtual()) {
			/**
			 * Billing address using otions
			 */
			$usingCase = isset($data['use_for_shipping']) ? (int)$data['use_for_shipping'] : 0;
			
			switch ($usingCase) {
				
				case 0:
					$shipping = $quote->getShippingAddress();
					$shipping->setSameAsBilling(0);
					break;
				case 1:
					$billing = clone $address;
					$billing->unsAddressId()->unsAddressType();
					$shipping = $quote->getShippingAddress();
					$shippingMethod = $shipping->getShippingMethod();
					
					/**** Billing address properties that must be always copied to shipping address ****/
					$requiredBillingAttributes = array('customer_address_id');
					
					// don't reset original shipping data, if it was not changed by customer
					foreach ($shipping->getData() as $shippingKey => $shippingValue) {
						if (!is_null($shippingValue) && !is_null($billing->getData($shippingKey))
							&& !isset($data[$shippingKey]) && !in_array($shippingKey, $requiredBillingAttributes)
						) {
							$billing->unsetData($shippingKey);
						}
					}
					
					/**** save the shipping data as the billing data ****/
					$shipping->addData($billing->getData())
						->setSameAsBilling(1)
						->setSaveInAddressBook(0)
						->setShippingMethod($shippingMethod)
						->setCollectShippingRates(true);
					$isShippingComplete = 1; /*** addtional field to calculate the shipping rate for the quote ***/
					if ($couponCode = $quote->getCartCouponCode()) {
						$quote->setCouponCode($couponCode);
					 }
					break;
			}
		}
		
		/**** calculate the collect totals of the magento ****/
		$quote->collectTotals();
		$quote->save();
		
		if (!$quote->isVirtual() && $isShippingComplete) {
			//Recollect Shipping rates for shipping methods
			$quote->getShippingAddress()->setCollectShippingRates(true);
		}
		
		/**** calculating the price array for the cart ****/
		$tempPriceArr = $this->getQuoteTotalDescription($quote);
		
		if($isShippingComplete) {
			$message = 'Billing and Shipping address are set for the order.';
		}else {
			$message = 'Billing address is set for the order.';
		}
		
		return array('status'=> 1,'message'=>$message ,'priceInfo'=>$tempPriceArr);
	}
	
	/**** Function to save the shipping address ****/
	
	public function saveShippingAddress() {
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();
		
		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>0,'errorMessage'=>'Field missing billing data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>0,'errorMessage'=>'Field missing store_id.');
		}
		
		$storeId = $postedData['store_id'];
		
		/**** setting the customer id to be false while updating if the customer does not choose to be the currently existing address ****/
		$customerAddressId = isset($postedData['shipping_address_id']) ? $postedData['shipping_address_id'] : false;
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>0, "errorMessage"=>"Invalid token id.");
		}
		
		/**** getting the quote a/c to customer id or quote id ****/
		$quoteRes = $this->getQuote($storeId, $customerId, $postedData['quote_id']);
		
		if($quoteRes["status"] == 0) {
			return array("status"=>0, "errorMessage"=>$quoteRes["errorMessage"]);
		}
		
		$quote = $quoteRes["quote"];
		
		/**** if errors will occur then it is got handled below ****/
		return $this->saveShipping($postedData['shipping'], $customerAddressId, $quote);
	}
	
	/**** Parameter is shipping data and the customer quote Mage_Sales_Quote ****/

	public function saveShipping($data, $customerAddressId, $quote) {
		
		if (empty($data)) {
			return array('status' => 0, 'errorMessage' => Mage::helper('checkout')->__('Invalid data.'));
		}
		
		/**** Getting the shipping address of the customer which is passed from the frontend ****/
		
		$address = $quote->getShippingAddress();

		/* @var $addressForm Mage_Customer_Model_Form */
		$addressForm    = Mage::getModel('customer/form');
		$addressForm->setFormCode('customer_address_edit')
			->setEntityType('customer_address');
			
		if (!empty($customerAddressId)) {
			/**** if address id is given it will be given as the priority ****/
			$customerAddress = Mage::getModel('customer/address')->load($customerAddressId);
			if ($customerAddress->getId()) {
				if ($customerAddress->getCustomerId() != $quote->getCustomerId()) {
					return array('status' => 0,
						'errorMessage' => Mage::helper('checkout')->__('Customer Address is not valid.')
					);
				}
				
				$address->importCustomerAddress($customerAddress)->setSaveInAddressBook(0);
				$addressForm->setEntity($address);
				$addressErrors  = $addressForm->validateData($address->getData());
				if ($addressErrors !== true) {
					return array('status' => 0, 'errorMessage' => $addressErrors);
				}
			}
		} else {
			$addressForm->setEntity($address);
			// emulate request object
			$addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
			$addressErrors  = $addressForm->validateData($addressData);
			if ($addressErrors !== true) {
				return array('status' => 0, 'erorMessage' => $addressErrors);
			}
			$addressForm->compactData($addressData);
			/**** unset shipping address attributes which were not shown in form ****/
			foreach ($addressForm->getAttributes() as $attribute) {
				if (!isset($data[$attribute->getAttributeCode()])) {
					$address->setData($attribute->getAttributeCode(), NULL);
				}
			}

			$address->setCustomerAddressId(null);
			/**** Additional form data, not fetched by extractData (as it fetches only attributes) ****/
			$address->setSaveInAddressBook(empty($data['save_in_address_book']) ? 0 : 1);
			$address->setSameAsBilling(empty($data['same_as_billing']) ? 0 : 1);
		}
		
		$address->implodeStreetAddress();
		$address->setCollectShippingRates(true);
		
		if (($validateRes = $address->validate())!==true) {
			return array('status' => 0, 'errorMessage' => $validateRes);
		}
		
		if ($couponCode = $quote->getCartCouponCode()) {
			$quote->setCouponCode($couponCode);
		}
		
		$quote->collectTotals()->save();
		
		$tempPriceArr = $this->getQuoteTotalDescription($quote);
		
		return array('status'=> 1, 'message'=>'Shipping Address Saved.', 'priceInfo'=>$tempPriceArr); 
	}
	
	public function myProfilePasswordUpdate() {
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();
		
		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Invalid Data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		$storeId = $postedData['store_id'];
		$oldPassword = $postedData['old_password'];
		$newPassword = $postedData['new_password'];
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		Mage::app()->setCurrentStore($storeId);
		
		/**** Token id authenticate that the customer exists so no need to check it again ****/
		$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->load($customerId);
		
		$userEmail = $customer->getEmail();
		
		try {
			$loginResult = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->authenticate($userEmail, $oldPassword);
		}catch(Exception $e) {
			return array('status'=> 0, 'errorMessage'=>$e->getMessage());
		}
		
		try {
			$customer = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->load($customerId);
			$customer->setPassword($newPassword);
			$customer->save();
			$res = array('status'=>1, 'userName'=> $userEmail, 'message'=>'Your password has been changed successfully.');
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
		}
		return $res;
	}
	
	public function initializeCheckout($storeId, $quoteId = null, $currency = 'USD') {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		/**** Getting the cart details ****/
		
		$cartRes = $this->getCartItems($storeId, $quoteId, $customerId, $currency, $updateCartMessages, 1);
		
		if($cartRes['status']==0) {
			/**** if the cart contains error or have not qualified for the minimum amount for checkout ****/
			return $cartRes;
		}
		
		/**** Getting customer primary shipping address ****/
		
		$customer = Mage::getModel('customer/customer')->load($customerId);
		
		/**** Getting the address for the primary shipping address ****/
		$shippingAddress = $customer->getDefaultShippingAddress();
		
		if($shippingAddress)
			$shippingAddress = $this->getParsedAddress($shippingAddress);
		else
			$shippingAddress = array();
		
		/**** Getting the address for the primary billing address ****/
		
		$billingAddress = $customer->getDefaultBillingAddress();
		
		if($billingAddress)
			$billingAddress = $this->getParsedAddress($billingAddress);
		else
			$billingAddress = array();
		
		$addressArr = array();
		
		array_push($addressArr, array('defaultBillingAddress'=>$billingAddress));
		array_push($addressArr, array('defaultShippingAddress'=>$shippingAddress));
		
		unset($cartRes['status']);
		
		$result = array('status'=>1, 'cartDetails'=>$cartRes, 'addressDetails'=>$addressArr);
		
		return $result;
	}
	
	/* Prepare quote for customer order submit */
	
	/* Params :: $quote -> Mage_Core_Model_Quote
	 * $customer -> Mage_Core_Model_Customer */
	
	public function prepareCustomerQuote($quote, $customer) {
		
		/**** Getting the billing and shipping address ****/
		$billing    = $quote->getBillingAddress();
		$shipping   = $quote->isVirtual() ? null : $quote->getShippingAddress();
		
		/**** Save the billing address in the address book ****/
		if (!$billing->getCustomerId() || $billing->getSaveInAddressBook()) {
			$customerBilling = $billing->exportCustomerAddress();
			$customer->addAddress($customerBilling);
			$billing->setCustomerAddress($customerBilling);
		}
		
		/**** Save the shipping address in the address book ****/
		if ($shipping && !$shipping->getSameAsBilling() &&
			(!$shipping->getCustomerId() || $shipping->getSaveInAddressBook())) {
			$customerShipping = $shipping->exportCustomerAddress();
			$customer->addAddress($customerShipping);
			$shipping->setCustomerAddress($customerShipping);
		}
		
		/**** Setting the default billing address ****/
		if (isset($customerBilling) && !$customer->getDefaultBilling()) {
			$customerBilling->setIsDefaultBilling(true);
		}
		/**** Setting the default shipping address ****/
		if ($shipping && isset($customerShipping) && !$customer->getDefaultShipping()) {
			$customerShipping->setIsDefaultShipping(true);
		} else if (isset($customerBilling) && !$customer->getDefaultShipping()) {
			$customerBilling->setIsDefaultShipping(true);
		}
		/**** Setting the customer to the quote ****/
		$quote->setCustomer($customer);
		
		return $quote;
	}
	
	/**
	 * Submit the quote. Quote submit process will create the order based on quote data
	 *
	 * @return Mage_Sales_Model_Order
	 */
	 
	/** Few points that are to be kept in the minds for this place order api ****/
	/* 1. Not deleting the nominal items ****/
	/* 2. This place order will work only when customer is assigned to the quote ****/
	/* 3. Aggrement id is not convered in it (YOU CAN SEE IT IN Mage_Core_Checkout_Controller_OnepageController.php or in local if overridden ) ****/
	
	public function createOrder() {
		
		/**** result array which will be returned ****/
		$res = array();
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing posted data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		if(!isset($postedData['quote_id']) || empty($postedData['quote_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing quote_id.');
		}
		
		/**** Getting the quote for the placing the order ****/
		
		$quoteTempRes = $this->getQuote($postedData['store_id'], $customerId, $postedData['quote_id']);
		
		if($quoteTempRes['status']==0) {
			return array('status'=>'0', 'errorMessage'=>$quoteTempRes['errorMessage']);
		}
		
		$quote = $quoteTempRes['quote'];
		
		/**** Getting the customer ****/
		
		$customer = Mage::getModel('customer/customer')->load($customerId); 
		
		/**** setting the payment method  this is not appropriate but right now don't do import data because it will start checkout ****/
		//~ $quote->getShippingAddress()->setPaymentMethod('paypal_standard');
		//~ $paymentData['method'] = 'cashondelivery';
		//~ $payment = $quote->getPayment();
		//~ $payment->importData($paymentData);
		
		//~ $quote->setTotalsCollectedFlag(false)
			//~ ->collectTotals();

		
		
		/**** prepare quote for the place order submit ****/
		
		$quote = $this->prepareCustomerQuote($quote, $customer);
		
		/**** validate the quote whether it is good or not ****/
		
		$validRes = $this->_validate($quote);
		
		if($validRes["status"] == 0) {
			/**** if the error occurs please return it ****/
			return $validRes;
		}
		
		/**** Getting the convertor object for the conversion of the quote to the order ****/
		$convertor   = Mage::getModel('sales/convert_quote');
		
		/**** Getting the flag whether it is virtual or not ****/
		$isVirtual = $quote->isVirtual();
		
		/**** Getting the transaction resource. Please don't confuse it with the actual transaction with the money.
		 *    Basically it is the concept of the transaction  ****/
		
		$transaction = Mage::getModel('core/resource_transaction');
		
		if ($quote->getCustomerId()) {
			/**** adding the customer object to the transaction ****/
			$transaction->addObject($quote->getCustomer());
		}
		
		/**** adding the quote object to the transaction ****/
		$transaction->addObject($quote);
		
		/**** setting the reserve order id ****/
		$quote->reserveOrderId();
		
		/**** add the address to convert to the convertor ****/
		if ($isVirtual) {
			$order = $convertor->addressToOrder($quote->getBillingAddress());
		} else {
			$order = $convertor->addressToOrder($quote->getShippingAddress());
		}
		
		/**** add the billing address to the order ****/
		$order->setBillingAddress($convertor->addressToOrderAddress($quote->getBillingAddress()));
		
		if ($quote->getBillingAddress()->getCustomerAddress()) {
			$order->getBillingAddress()->setCustomerAddress($quote->getBillingAddress()->getCustomerAddress());
		}
		
		/**** if the quote is not virtual then add the shipping address to the order ****/
		if (!$isVirtual) {
			$order->setShippingAddress($convertor->addressToOrderAddress($quote->getShippingAddress()));
			if ($quote->getShippingAddress()->getCustomerAddress()) {
				$order->getShippingAddress()->setCustomerAddress($quote->getShippingAddress()->getCustomerAddress());
			}
		}
		
		/**** setting the order payment information from the quote ****/
		$order->setPayment($convertor->paymentToOrderPayment($quote->getPayment()));
		
		/* List of additional order attributes which will be added to order before save not now */
		$orderDataArr = array();
		
		/*** right now i don't know what to set so it is left blank ****/
		foreach ($orderDataArr as $key => $value) {
			$order->setData($key, $value);
		}
		
		/*** quote->getAllItems() will return all the parent item as well ***/
		foreach ($quote->getAllItems() as $item) {
			$orderItem = $convertor->itemToOrderItem($item);
			if ($item->getParentItem()) {
				$orderItem->setParentItem($order->getItemByQuoteItemId($item->getParentItem()->getId()));
			}
			$order->addItem($orderItem);
		}
		
		/*** setting the quote to the order ***/
		$order->setQuote($quote);
		
		/*** setting the state of the order to pending payment ***/
		$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, "Order is placed from the mobile native app.", false);
		
		/*** adding the object to the transaction object so that we can roll back if any error occurs ****/
		$transaction->addObject($order);
		$transaction->addCommitCallback(array($order, 'place'));
		$transaction->addCommitCallback(array($order, 'save'));
		
		/*** dispatching the event for various purposes ****/
		/*** this event will trigger the saving of the quote and then download the downloadable product so i am not triggering it ***/
		//~ Mage::dispatchEvent('checkout_type_onepage_save_order', array('order'=>$order, 'quote'=>$quote));
		
		/*** this event will trigger the action of decreasing the inventory stock ***/
		/*** In CatalogInventory/Model/Observer.php ***/
		/*** basically it used before placing the order save/place transaction smaller ***/
		/*** Let it be the event dispatch only after that we will do something later if necessary ***/
		/*** it will call subtractQuoteInventory function ****/
		
		try {
			/**** if all the items are not present in the stock then please add this try catch ****/
			Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order'=>$order, 'quote'=>$quote));
		}catch(Exception $e) {
			/**** just return the catalog quantity on the failure i.e restore the quote for that ****/
			Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order'=>$order, 'quote'=>$quote));
			/**** return the status and the error message for that ****/
			return array("status"=>0, "errorMessage"=>$e->getMessage());
		}
		
		try {
			$transaction->save();
			
			/*** inactivate the quote ***/
			$quote->setIsActive(false);
			
			/*** emptying the shopping cart ***/
			$quote->save();
			
			/*** Event is dispatched for the reindexing of the quote inventory ***/
			/** Refresh stock index for specific stock items after succesful order placement **/
			/** This function is called in the CatalogInventory/Model/Observer.php ***/
			/** basically it is used after the placing of the order ***/
			/** Let it be the event dispatch now we will do something later if necessary ***/
			/** it will call reindexQuoteInventory function ****/
			Mage::dispatchEvent('sales_model_service_quote_submit_success', array('order'=>$order, 'quote'=>$quote));
			/** saving the quote or order **/
			
			$res = array("status"=>1,"orderIncrementId"=>$order->getIncrementId(), "orderStatus"=>$order->getStatus(), "message"=>"Order Placed Successfully.","amount"=>$order->getBaseGrandTotal());
			
		} catch(Exception $e ) {
			
			/*** logging the exception ***/
			 Mage::logException($e);
			
			/*** sending the failure mail but skipping this part now ***/ 
			//~ Mage::helper('checkout')->sendPaymentFailedEmail($this->getOnepage()->getQuote(), $e->getMessage());
			
			/*** reset order ID's on exception because order not saved ***/
			$order->setId(null);
			
			/** @var $item Mage_Sales_Model_Order_Item */
			foreach ($order->getItemsCollection() as $item) {
				$item->setOrderId(null);
				$item->setItemId(null);
			}
			/*** Event is dispatched for the reverting of the quote inventory ***/
			/** Revert quote items inventory data (cover not success order place case) **/
			/** This function is called in the CatalogInventory/Model/Observer.php ***/
			/** basically it is used after the failure of the order ***/
			/** Let it be teh event dispatch now we will do something later if necessary ***/
			/** it will call revertQuoteInventory function **/
			Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order'=>$order, 'quote'=>$quote));
			
			$res = array("status"=>"0", "errorMessage"=>$e->getMessage());
		}
		
		/*** This event is triggered after the submission of the quote service ***/
		/** Revert emulated customer group_id **/
		/** I am not going to call it in it because it is not necessary in our case ***/
		//~ Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order'=>$order, 'quote'=>$quote));
		return $res;
	}
	
	/**
	 * IMPORTANT Please check Mage_Sales_Model_Service_Quote
	 * 
	 * Validate quote data before converting to order
	 * @param : $quote -> Mage_Sales_Model_Quote
	 * @return Mage_Sales_Model_Service_Quote if successful or errorMessage if error
	 */
	protected function _validate($quote) {
		
		if (!$quote->isVirtual()) {
			/**** if the quote is not virtual there will be no shipping order set ****/
			
			/**** validate the shipping address ****/
			$address = $quote->getShippingAddress();
			$addressValidation = $address->validate();
			if ($addressValidation !== true) {
				/**** if address validation is not true then return ****/
				$var = implode(' ', $addressValidation); 
				return array("status"=>"0","errorMessage"=>"Please check shipping address information. $var");
			}
			
			/**** checking the shipping method validation ****/
			$method= $address->getShippingMethod();
			$rate  = $address->getShippingRateByCode($method);
			if (!$quote->isVirtual() && (!$method || !$rate)) {
				return array("status"=>"0","errorMessage"=>"Please specify a shipping method.");
			}
		}
		
		/**** validate the billing address ****/
		$addressValidation = $quote->getBillingAddress()->validate();
		if ($addressValidation !== true) {
			/**** if address validation is not true then return ****/
			$var = implode(' ', $addressValidation); 
			return array("status"=>"0","errorMessage"=>"Please check billing address information. $var");
		}
		
		/**** checking the payment method ****/
		if (!($quote->getPayment()->getMethod())) {
			return array("status"=>"0", "errorMessage"=>"Please select a valid payment method.");
		}
		return array("status"=>"1","quote"=> $quote);
	}
	
	public function updatePaypalOrderSuccessStatus() {
		
		/**** As far as i have searched, there is no any new order status after completion of the payment from paypal ****/
		/**** I am definitely wrong here ****/
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>"0", "errorMessage"=>"Invalid token id.");
		}
		
		$res = array();
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing posted data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		if(!isset($postedData['order_increment_id']) || empty($postedData['order_increment_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing order_increment_id.');
		}
		
		if(!isset($postedData['payer_id']) || empty($postedData['payer_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing payer_id.');
		}
		
		/**** remaining to making the checks of the payment method ****/
		
		$orderIncrementId = $postedData['order_increment_id'];
		$payerId = $postedData['payer_id'];
		
		$order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
		$capturedAmount = number_format($order->getBaseGrandTotal(), 2);
		
		if(!$order->getId()) {
			return array('status'=> 0, 'errorMessage'=>'Order does not exists.');
		}
		
		try { 
			
			$payment = $order->getPayment();
			$payment->setTransactionId($payerId);
			
			$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true, "Transaction Success from Paypal SDK. Captured amount of $capturedAmount online.\nPayer Id : $payerId ", false);
			
			/**** Preparing the invoice for the product ****/
			$invoice = $order->prepareInvoice();
			
			if($invoice->getTotalQty()){
				$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE);
				$invoice->getOrder()->setCustomerNoteNotify(false);
				$invoice->register();
				Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder())->save();
				$invoice->sendEmail(false); 
			}
			
			$payment = $order->getPayment();
			$payment->setMethod('paypal_express');
			$payment->setTransactionId($payerId);
			
			/**** For authorization :: Mage_Sales_Model_Order_Payment_Transaction::TYPE_AUTH ****/
			$transaction = $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_CAPTURE, null, false, null);
			$transaction->setIsClosed(0);
			$order->sendNewOrderEmail();
			$order->setEmailSent(true);
			$order->save();
			
			/**** updating the latest order table for which the event is triggered ****/
			Mage::dispatchEvent('latest_update_order_after', array('customer_id'=>$order->getCustomerId(),'customer_name'=>$order->getCustomerName(),'order_id'=>$order->getIncrementId())); 
			
			$res = array('status'=>1, 'orderStatus'=>$order->getStatus(), 'message'=>'Order placed successfully.');
		}catch(Exception $e) {
			Mage::logException($e);
			$res = array('status'=>0, 'errorMessage'=>'Order placed but payment error occurs due to .'.$e->getMessage());
		}
		return $res;
	}
	
	/**** Function to set the shipping method ****/
	
	public function saveShippingMethod() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>0, "errorMessage"=>"Invalid token id.");
		}
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing posted data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		/**** Quote id is must ****/
		if(!isset($postedData['quote_id']) || empty($postedData['quote_id'])) {
			return array('status'=>0, 'errorMessage'=>'Field missing quote_id.');
		}
		
		/**** Shipping method code is must ****/
		if(!isset($postedData['shipping_method_code']) || empty($postedData['shipping_method_code'])) {
			return array('status'=>0, 'errorMessage'=>'Field missing shipping_method_code.');
		}
		
		$storeId = $postedData['store_id'];
		$quoteId = $postedData['quote_id'];
		$shippingMethod = $postedData['shipping_method_code'];
		
		/**** Getting the quote for the placing the order ****/
		
		$quoteTempRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteTempRes['status']==0) {
			return array('status'=>0, 'errorMessage'=>$quoteTempRes['errorMessage']);
		}
		
		$quote = $quoteTempRes['quote'];
		
		$quoteShippingAddress = $quote->getShippingAddress();
		
		if(is_null($quoteShippingAddress->getId())) {
			return array('status'=>0, 'errorMessage'=>'Shipping Address is not set.');
		}
		
		$rate = $quote->getShippingAddress()->collectShippingRates()->getShippingRateByCode($shippingMethod);
		
		if (!$rate) {
			return array('status'=> 0, 'errorMessage'=>'Shipping Method is not available.');
		}
		
		try {
			$quote->getShippingAddress()->setShippingMethod($shippingMethod);
			$quote->collectTotals()->save();
			
			$tempPriceArr = $this->getQuoteTotalDescription($quote);
			
			$res = array('status'=>1, 'priceInfo'=>$tempPriceArr, 'message'=>'Shipping method is saved.');
		} catch(Mage_Core_Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>'Unable to set the shipping method.'.$e->getMessage());
		} catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
		}
		return $res;
	}
	
	/**** Function to list the shipping method ****/
	
	public function listShippingMethod($storeId, $quoteId = null) {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>0, "errorMessage"=>"Invalid token id.");
		}
		
		/**** Quote id is must ****/
		if(is_null($quoteId) || !isset($quoteId)) {
			return array('status'=>0, 'errorMessage'=>'Field missing quote_id');
		} 
		
		/**** Getting the quote for the placing the order ****/
		
		$quoteTempRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteTempRes['status']==0) {
			return array('status'=>0, 'errorMessage'=>$quoteTempRes['errorMessage']);
		}
		
		$quote = $quoteTempRes['quote'];
		
		$res = array();
		
		/**** Getting the quote shipping address ****/
		
		$quoteShippingAddress = $quote->getShippingAddress();
		
		if (is_null($quoteShippingAddress->getId())) {
			/**** if the shipping address is not set ****/
			return array('status'=>0, 'errorMessage'=>'Shipping Address is not set.');
		}
		
		$currencySymbol = Mage::app()->getLocale()->currency($quote->getCurrency())->getSymbol();
		
		try {
			
			
			$quoteShippingAddress->collectShippingRates()->save();
			$groupedRates = $quoteShippingAddress->getGroupedAllShippingRates();

			$ratesResult = array();
			foreach ($groupedRates as $carrierCode => $rates ) {
				$carrierName = $carrierCode;
				
				if (!is_null(Mage::getStoreConfig('carriers/'.$carrierCode.'/title'))) {
					$carrierName = Mage::getStoreConfig('carriers/'.$carrierCode.'/title');
				}

				foreach ($rates as $rate) {	
					
					$addressId = $rate->getAddressId();
					$code = $rate->getCode();
					$price = number_format($rate->getPrice(), 2);
					$rateItem = array('addressId'=>$addressId, 'shippingMethodCode'=>$code,'price'=>$price, 'carrierName'=>$carrierName, 'currencySymbol'=>$currencySymbol);
					$ratesResult[] = $rateItem;
					unset($rateItem);
				}
			}
			$res = array('status'=>1, 'quoteId'=>$quote->getId(), 'shippingMethodList'=>$ratesResult);
		} catch (Mage_Core_Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>'Shipping methods list could not be retrived.'. $e->getMessage());
		}
		return $res;
	}
	
	/**
	 * Retrieve available payment methods for a quote
	 * copied from Mage_Checkout_Model_Cart_Payment_Api.php
	 * @param int $quoteId
	 * @param int $store
	 * @return array
	 */
	
	/**
	 * IMPORTANT GATEWAYS ARE NOT LISTED it is has to be bypassed in canusepaymentmethod below 
	 * 
	 * */
	
	public function getPaymentMethodsList($storeId, $quoteId = null) {
		
		/**** Quote id is must ****/
		if(is_null($storeId) || !isset($storeId)) {
			return array('status'=>0, 'errorMessage'=>'Field missing store_id');
		} 
		
		Mage::app()->setCurrentStore($storeId);
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>0, "errorMessage"=>"Invalid token id.");
		}
		
		/**** Quote id is must ****/
		if(is_null($quoteId) || !isset($quoteId)) {
			return array('status'=>0, 'errorMessage'=>'Field missing quote_id');
		} 
		
		/**** Getting the quote for the placing the order ****/
		
		$quoteTempRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteTempRes['status']==0) {
			return array('status'=>0, 'errorMessage'=>$quoteTempRes['errorMessage']);
		}
		
		$quote = $quoteTempRes['quote'];
		
		try { 
			$store = $quote->getStoreId();
			$total = $quote->getBaseSubtotal();
			
			$methodsResult = array();
			$methods = Mage::helper('payment')->getStoreMethods($store, $quote);
			
			foreach ($methods as $method) {
				/** @var $method Mage_Payment_Model_Method_Abstract */
				if ($this->_canUsePaymentMethod($method, $quote)) {
					$isRecurring = $quote->hasRecurringItems() && $method->canManageRecurringProfiles();
					
					if ($total != 0 || $method->getCode() == 'free' || $isRecurring) {
						$methodsResult[] = array(
							'paymentMethodCode' => $method->getCode(),
							'title' => $method->getTitle(),
							'cardTypes' => $this->_getPaymentMethodAvailableCcTypes($method),
						);
					}
				}
			}
			$res = array('status'=>1, 'paymentMethods'=> $methodsResult);
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
		}
		
		return $res;
	}
	
	/**** return the available payment credit card types ****/
	
	protected function _getPaymentMethodAvailableCcTypes($method) {
		
		$ccTypes = Mage::getSingleton('payment/config')->getCcTypes();
		$methodCcTypes = explode(',', $method->getConfigData('cctypes'));
		foreach ($ccTypes as $code => $title) {
			if (!in_array($code, $methodCcTypes)) {
				unset($ccTypes[$code]);
			}
		}
		if (empty($ccTypes)) {
			return null;
		}
		
		return $ccTypes;
	}
	
	/**** checks whether we can use the payment method or not ****/
	/*** modified payment method ***/
	/**
	 * @param  $method
	 * @param  $quote
	 * @return bool
	 */
	protected function _canUsePaymentMethod($method, $quote) {
		
		if (!($method->isGateway() || $method->canUseInternal())) {
			/**** here it is hard coded just to explain the express checkout ****/
			if($method->getCode() == 'paypal_express') return true;
			return false;
		}
		
		if (!$method->canUseForCountry($quote->getBillingAddress()->getCountry())) {
			return false;
		}
		
		if (!$method->canUseForCurrency(Mage::app()->getStore($quote->getStoreId())->getBaseCurrencyCode())) {
			return false;
		}
		/**
		 * Checking for min/max order total for assigned payment method
		 */
		$total = $quote->getBaseGrandTotal();
		$minTotal = $method->getConfigData('min_order_total');
		$maxTotal = $method->getConfigData('max_order_total');
		
		if ((!empty($minTotal) && ($total < $minTotal)) || (!empty($maxTotal) && ($total > $maxTotal))) {
			return false;
		}
		
		return true;
	}
	
	/*** Save the payment method ***/
	
	/*** For your logic please see the custom logic section ****/
	
	public function savePaymentMethod() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>0, "errorMessage"=>"Invalid token id.");
		}
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing posted data.');
		}
		
		if(!isset($postedData['store_id']) || empty($postedData['store_id'])) {
			return array('status'=>'0','errorMessage'=>'Field missing store_id.');
		}
		
		/**** Quote id is must ****/
		if(!isset($postedData['quote_id']) || empty($postedData['quote_id'])) {
			return array('status'=>0, 'errorMessage'=>'Field missing quote_id.');
		}
		
		/**** Shipping method code is must ****/
		if(!isset($postedData['payment_method_code']) || empty($postedData['payment_method_code'])) {
			return array('status'=>0, 'errorMessage'=>'Field missing payment_method_code.');
		}
		
		$storeId = $postedData['store_id'];
		$quoteId = $postedData['quote_id'];
		$paymentMethod = $postedData['payment_method_code'];
		
		Mage::app()->setCurrentStore($storeId);
		
		/**** Getting the quote for the placing the order ****/
		
		$quoteTempRes = $this->getQuote($storeId, $customerId, $quoteId);
		
		if($quoteTempRes['status']==0) {
			return array('status'=>0, 'errorMessage'=>$quoteTempRes['errorMessage']);
		}
		
		$quote = $quoteTempRes['quote'];
		
		/**** Custom Logic included for your payment ****/
		
		if($paymentMethod == 'paypal_direct' || $paymentMethod == 'paypal_express') {
			/*** changing it to the paypal_standard because it will redirect it to the sites ***/
			/*** after placing of the order we will revert it to the express checkout ***/
			$paymentMethod = 'paypal_standard';
		}
		
		if ($quote->isVirtual()) {
			// check if billing address is set
			if (is_null($quote->getBillingAddress()->getId())) {
				return array('status'=>0,'errorMessage'=>'Billing address is not set.');
			}
			$quote->getBillingAddress()->setPaymentMethod(
				isset($paymentMethod) ? $paymentMethod : null
			);
		} else {
			// check if shipping address is set
			if (is_null($quote->getShippingAddress()->getId())) {
				return array('status'=>0, 'errorMessage'=>'Shipping address is not set.');
			}
			$quote->getShippingAddress()->setPaymentMethod(
				isset($paymentMethod) ? $paymentMethod : null
			);
		}
		
		if (!$quote->isVirtual() && $quote->getShippingAddress()) {
			$quote->getShippingAddress()->setCollectShippingRates(true);
		}
		
		/*** This must be implemented but due to some reason skipping this at this time ***/
		
		//~ $total = $quote->getBaseSubtotal();
		//~ $methods = Mage::helper('payment')->getStoreMethods($storeId, $quote);
		
		//~ foreach ($methods as $method) {
			//~ if ($method->getCode() == $paymentMethod) {
				//~ /** @var $method Mage_Payment_Model_Method_Abstract */
				//~ if (!($this->_canUsePaymentMethod($method, $quote)
					//~ && ($total != 0
						//~ || $method->getCode() == 'free'
						//~ || ($quote->hasRecurringItems() && $method->canManageRecurringProfiles())))
				//~ ) {
					//~ return array('status'=>0, 'errorMessage'=>'Payment method not allowed.');
				//~ }
			//~ }
		//~ }
		
		
		try {
			
			$quotePayment = $quote->getPayment();
			$quotePayment->setMethod($paymentMethod);
			$quote->setPayment($quotePayment);
			//~ $payment->importData($paymentData);
			
			$quote->setTotalsCollectedFlag(false)
				->collectTotals()
				->save();
			
			$tempPriceArr = $this->getQuoteTotalDescription($quote);
			
			$res = array('status'=>1, 'priceInfo'=>$tempPriceArr, 'message'=>'Payment method is saved.');
		} catch (Mage_Core_Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>'Payment method is not set. '.$e->getMessage());
		}
		return $res;
	}
	
	public function ws_getNewCategoryFilter($storeId, $categoryId, $filterData = null ,$getCollection = 0){
		
		$filterCount 	= 0;
		$actvCount		= 0;
		$res = $activeFilters = $filter = $params = [];
		$res['status'] 	= 1;
		$res['total']	= 0;
		$res['activeFilters'] = $res['filters'] = [];
		if($categoryId)
		{
			try
			{
				Mage::app()->setCurrentStore($storeId);
				$layer = Mage::getModel("catalog/layer");
				$category = Mage::getModel("catalog/category")->load($categoryId);
				$layer->setCurrentCategory($category);
				$attributes = $layer->getFilterableAttributes();
				$filterData = json_decode(base64_decode($filterData), true);
				if($filterData)
				{
					foreach($filterData as $actvFilters)
					{
						if(isset($actvFilters['code']) && isset($actvFilters['value']))
						{
							$params[$actvFilters['code']] = $actvFilters['value'];
							$activeFilters[$actvCount] = $actvFilters;
							$actvCount++;
						}
					}
					if($params)
						Mage::app()->getRequest()->setParams($params); //Setting the params to the request object which is used by the layer model.
				}
				/* Commented the category filter implementation as it's also commented on the website */
				//Mage::getModel('catalog/layer_filter_category')->setLayer($layer)->apply(Mage::app()->getRequest(),null);
				foreach($attributes as $attribute)
				{
					if ($attribute->getAttributeCode() == 'price') {
						$filterModelName = 'catalog/layer_filter_price';
					} elseif ($attribute->getBackendType() == 'decimal') {
						$filterModelName = 'catalog/layer_filter_decimal';
					} else {
						$filterModelName = 'catalog/layer_filter_attribute';
					}
					Mage::getModel($filterModelName)->setLayer($layer)->setAttributeModel($attribute)->apply(Mage::app()->getRequest(),null);
				}
				if($getCollection)
					return $layer->getProductCollection(); 

				foreach($attributes as $attribute) //Looping each layered attributes to get the updated options for the attributes based on the filters applied.
				{
					if(!array_key_exists($attribute->attribute_code,$params)) //Bypassing the attributes which are applied as filters.
					{
						if ($attribute->getAttributeCode() == 'price') {
							$filterModelName = 'catalog/layer_filter_price';
						} elseif ($attribute->getBackendType() == 'decimal') {
							$filterModelName = 'catalog/layer_filter_decimal';
						} else {
							$filterModelName = 'catalog/layer_filter_attribute';
						}
						$filterVal = Mage::getModel($filterModelName)->setLayer($layer)->setAttributeModel($attribute)->getItems();
						$valueCount	= 0;
						$valueData	= [];
						foreach($filterVal as $option) {
							$valueData[$valueCount]["count"] =  $option->getCount();
							$valueData[$valueCount]["label"] =  strip_tags($option->getLabel());
							$valueData[$valueCount]["value"] =  $option->getValue();
							$valueCount++;
						}
						if($valueData)
						{
							$filter[$filterCount]["code"]	= $attribute->attribute_code;
							$filter[$filterCount]["type"]	= $attribute->frontend_input;
							$filter[$filterCount]["label"]	= $attribute->frontend_label;
							$filter[$filterCount]["values"] = $valueData;
							$filterCount++;
						}
					}
				}
				$res['total']	= $filterCount;
				$res['filters']	= $filter; //setting the updated filters to the data. 
				$res['activeFilters'] = $activeFilters; //setting the applied filters to the return data.
			}
			catch(Exception $e)
			{
				$res['status'] 	= 0;
				$res['message']	= $e->getMessage();
			}
		}
		else
		{
			$res['status'] 	= 0;
			$res['message']	= 'Invalid Request! Please specify the category id.';
		}
		return $res;
	}
	
	public function ws_getLayerCollection($storeId, $categoryId, $filterData = null, $currentPage = 1 , $pageSize = 10, $sortType = 'relevance', $sortOrder = 'desc', $currency){
		
		if(!isset($storeId) || !isset($categoryId) || !isset($currency)) {
			return array('status'=>0, 'errorMessage'=>'Field missing category id or store id or currency.');
		}
		
		Mage::app()->setCurrentStore($storeId);
		
		try {
			
			/**** getting the collection ****/
			$collection	= $this->ws_getNewCategoryFilter($storeId, $categoryId, $filterData, 1);
			
			if(is_array($collection))  {
				if(isset($collection['message']))
					throw new Exception($collection['message']);
				else
					throw new Exception('Something went wrong while fetching the products.');
			}
			
			$_products = $collection;
			
			/**** Setting the current page and current page size to the collection that we got ****/
			$_products->setPageSize($pageSize)->setCurPage($currentPage);
			$_products->setOrder($sortType, $sortOrder);
			
			$productList = array();
			
			$i = 0;
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			$size = $_products->getSize();
			$lastPage = $_products->getLastPageNumber();
			
			if($size) {
				
				foreach($_products as $product) {
					
					$product = Mage::getModel('catalog/product')->load($product->getId());
					
					$productId = $product->getId(); // Getting the id
					$productName  = $product->getName();  // Getting the name for the product
					$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
					$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
					$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
					$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
					$today                = time();
					
					/**** Checking if the special price got applied ****/
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
						$productSprice = number_format($product->getSpecialprice(), 2);
					else
						$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
					
					$productStatus  = $product->getStockItem()->getIsInStock();
					$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
					
					if ($productStatus == 1 && $stockQuantity < 0)
						$productStatus == 1;
					else
						$productStatus == 0;
					
					$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
					
					/**** Tax Calculation ****/
					$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
					$taxClassId     = $product->getData("tax_class_id");
					$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
					$taxRate        = $taxClasses["value_" . $taxClassId];
					
					$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
					
					if ($taxType == 0) {
						$defaultPrice = str_replace(",", "", $productPrice);
					} else {
						$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
					}
					
					$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
					$defaultSprice = str_replace(",", "", $productSprice);
					$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
					// Get the Special Price
					$specialPrice         = $product->getSpecialPrice();
					// Get the Special Price FROM date
					$specialPriceFromDate = $product->getSpecialFromDate();
					// Get the Special Price TO date
					$specialPriceToDate   = $product->getSpecialToDate();
					// Get Current date
					$today                = time();
					
					/**** I don't know what is happening right now ****/
					if ($specialPrice) {
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
							$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						} else {
							$specialPrice = 0;
						}
					} else {
						$specialPrice = 0;
					}
					
					$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
					if ($taxType == 0) {
						$specialPrice = $specialPrice;
					} else {
						$specialPrice = $specialPrice - $taxPriceForSpecial;
					}
					
					if ($product->getTypeID() == 'grouped') {
						$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}else {
						$actualPrice =  number_format($product->getPrice(), 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}
					
					$shortDescription = $product->getShortDescription();
					$description = $product->getDescription();
					$cheers = is_null($product->getCheers()) ? 0 : $product->getCheers();
					
					if($actualPrice == $specialPrice) {
						$specialPrice = number_format(0, 2, '.', '');
						array_push($productList, array('id' => $productId, 'name' => $productName,'shortDescription'=>$shortDescription, 'description'=>$description,'image' => (string)$productImage,'type' => $product->getTypeID(),'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus,'cheers'=>$cheers));
					}
				}
				
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}else {
				/**** No product is present ****/
				$res["status"] = 1;
				$res["showStatus"] = 0;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
		}
		return $res;
	}
	
	public function getCategoryFilter($storeId, $categoryId) {
		
		if(!isset($storeId) || !isset($categoryId)) {
			return array('status'=>0, 'errorMessage'=>'Field missing store_id or category_id');
		}
		
		Mage::app()->setCurrentStore($storeId);
		
		/**** result array ****/
		$res = array();
		
		try { 
			/**** Load the categories ****/
			$layer = Mage::getModel("catalog/layer");
			$category = Mage::getModel("catalog/category")->load($categoryId);  
			$layer->setCurrentCategory($category);
			
			/**** Get the filterable attributes ****/
			$attributes = $layer->getFilterableAttributes();
			
			$filters = array();
			
			foreach($attributes as $attribute) {
				
				$filterArr = array();
				
				/**** setting the block filter name don't know what is happening right now ****/
				if ($attribute->getAttributeCode() == "price") {
					$filterBlockName = "catalog/layer_filter_price";
				} elseif ($attribute->getBackendType() == "decimal") {
					$filterBlockName = "catalog/layer_filter_decimal";
				} else {
					$filterBlockName = "catalog/layer_filter_attribute";
				}
				
				/**** setting up the attributes ****/
				$filterArr["code"] = $attribute->attribute_code;
				$filterArr["type"] =  $attribute->frontend_input;
				$filterArr["label"] =  $attribute->frontend_label;
				
				$tempRes = array();
				$result = Mage::app()->getLayout()->createBlock($filterBlockName)->setLayer($layer)->setAttributeModel($attribute)->init();
				
				foreach($result->getItems() as $option) {
					array_push($tempRes, array("count"=>(string)$option->getCount(),"label"=>strip_tags($option->getLabel()),"id"=>$option->getValue()));
				}
				$filterArr["values"] = $tempRes;
				array_push($filters, $filterArr);
			}
			$res = array("status"=>1, "filters"=>$filters);
		}catch(Exception $e) {
			$res = array("status"=>"0","errorMessage"=>$e->getMessage());
		}
		return $res;
	}
	
	/**** function to check the required data ****/
	
	public static function checkRequired($dataArr = array()) {
		
		foreach($dataArr as $data) {
			if(!isset($data) || $data == '' || is_null($data)) {
				self::$_requiredError = true;
				return true;
			}
		}
		return false;
	}
	
	/**** function to handle the required error ****/
	
	public static function handleRequiredError() {
		return array("status"=>"0","message"=>"Field missing.");
	}
	
	/**** function to get the search filter ****/
	
	public function getSearchFilterCollection($storeId, $searchData, $filterData = null, $getCollection = 0) {
		
		$filterCount = 0;
		$actvCount = 0;
		$res = $activeFilters = $filter =[];
		$res['total'] = 0;
		$res['status'] = 1;
		
		try {
			
			Mage::app()->setCurrentStore($storeId);
			$params  = Mage::app()->getRequest()->getParams();
			
			$searchData = base64_decode($searchData);
			
			if($searchData)
				$params[Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME] = $searchData;
			
			Mage::app()->getRequest()->setParams($params);
			
			$query = Mage::helper('catalogsearch')->getQuery();
			$query->setStoreId($storeId);
			
			if($query->getQueryText() != '') {
				
				if(Mage::helper('catalogsearch')->isMinQueryLength()) {
					$query->setId(0)->setIsActive(1)->setIsProcessed(1);
				}else {
					if($query->getId()) {
						/**** updating the popularity only in case of loading product collection ****/
						if($getCollection) {
							$query->setPopularity($query->getPopularity()+1);
						}
					}else {
						if($getCollection)
							$query->setPopularity(1);
					}
					/**** Leaving the redirect part ****/
					if($query->getRedirect()) {
						$query->save();
					}else {
						$query->prepare();
					}
				}
				
				if(!Mage::helper('catalogsearch')->isMinQueryLength()) {
					
					$query->save();
					
					$layer = Mage::getModel('catalogsearch/layer');
					$attributes = $layer->getFilterableAttributes();
					$filterData = json_decode(base64_decode($filterData),true);
					
					if($filterData) {
						foreach($filterData as $actvFilters) {
							if(isset($actvFilters['code']) && isset($actvFilters['value'])) {
								$params[$actvFilters['code']] = $actvFilters['value'];
								$activeFilters[$actvCount] = $actvFilters;
								$actvCount++;
							}
						}
						/**** setting the params to the request object which is used by the layer model ****/
						if($params)
							Mage::app()->getRequest()->setParams($params);
						}
						
						/**** Applying the filters to the collection i don't know how it works ****/
						
						foreach($attributes as $attribute) {
							if($attribute->getIsFilterableInSearch()) {
								$filterModelName = 'catalogsearch/layer_filter_attribute';
								Mage::getModel($filterModelName)->setLayer($layer)->setAttributeModel($attribute)->apply(Mage::app()->getRequest(), null);
							}
						}
						
						/**** returning the collection ****/
						if($getCollection) 
							return $layer->getProductCollection();
							
						/**** skipping the minimum product collection block ****/
						
						//~ if(!$this->canShowBlock($layer)) //Condition for checking the minimum product collection for which to show layered navigation.
							//~ return $res;
							
						//Looping each layered attributes to get the updated options for the attributes based on the filters applied.
						
						foreach($attributes as $attribute){
							
							//Bypassing the attributes which are applied as filters.
							
							if(!array_key_exists($attribute->attribute_code,$params)) {
								
								if ($attribute->getIsFilterableInSearch()) {
									$filterModelName = 'catalogsearch/layer_filter_attribute';
									$filterVal = Mage::getModel($filterModelName)->setLayer($layer)->setAttributeModel($attribute)->getItems();
									$valueCount = 0;
									$valueData  = [];
									foreach($filterVal as $option) {
										$valueData[$valueCount]["count"] =  $option->getCount();
										$valueData[$valueCount]["label"] =  strip_tags($option->getLabel());
										$valueData[$valueCount]["value"] =  $option->getValue();
										$valueCount++;
									}
									if($valueData) {
										$filter[$filterCount]["code"]   = $attribute->attribute_code;
										$filter[$filterCount]["type"]   = $attribute->frontend_input;
										$filter[$filterCount]["label"]  = $attribute->frontend_label;
										$filter[$filterCount]["values"] = $valueData;
										$filterCount++;
									}
								}
							}
						}
						$res['total']   = $filterCount;
						$res['filters'] = $filter; //setting the updated filthe data. 
						$res['activeFilters'] = $activeFilters; //setting thd filters to the return data.
				}else {
					$res['status'] = 0;
					$res['errorMessage'] = Mage::helper('catalogsearch')->__('Minimum Search Query Length is %s', $query->getMinQueryLength());
				}
			}else {
				$res['status'] = 0;
				$res['errorMessage'] = 'Invalid Request ! Unable to fetch search query.';
			}
		}catch(Exception $e) {
			$res['status'] = 0;
			$res['errorMessage'] = $e->getMessage();
		}
		return $res;
	}
	
	/**** function to check the allowed engine ****/
	
	public function canShowBlock($layer) {
		
		$_isLNAllowedByEngine = Mage::helper('catalogsearch')->getEngine()->isLeyeredNavigationAllowed();
		if (!$_isLNAllowedByEngine) {
			return false;
		}
		$availableResCount = (int) Mage::app()->getStore()->getConfig(Mage_CatalogSearch_Model_Layer::XML_PATH_DISPLAY_LAYER_COUNT);
		if (!$availableResCount
			|| ($availableResCount > $layer->getProductCollection()->getSize())) {
			return true;//return parent::canShowBlock();
		}
		return false;
	}

	
	/**** function to get the search filter (copy of Lakshya Sir ) ****/
	
	public function search($storeId, $searchData, $filterData = null, $currentPage = 1 , $pageSize = 9, $sortType = 'relevance', $sortOrder = 'asc', $currency) {
		
		$res = array();
		
		if($searchData) {
			
			try {
				
				Mage::app()->setCurrentStore($storeId);
				
				$collection = $this->getSearchFilterCollection($storeId, $searchData, $filterData, 1);
				
				if(is_array($collection))  {
					if(isset($collection['message']))
						throw new Exception($collection['message']);
					else
						throw new Exception('Something went wrong while fetching the products.');
				}
				
				$_products = $collection;
				
				/**** Setting the current page and current page size to the collection that we got ****/
				$_products->setPageSize($pageSize)->setCurPage($currentPage);
				$_products->setOrder($sortType, $sortOrder);
				
				$productList = array();
				
				$i = 0;
				$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
				$size = $_products->getSize();
				$lastPage = $_products->getLastPageNumber();
				
				if($size) {
					
					foreach($_products as $product) {
						
						$product = Mage::getModel('catalog/product')->load($product->getId());
						
						$productId = $product->getId(); // Getting the id
						$productName  = $product->getName();  // Getting the name for the product
						$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
						$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
						$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
						$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
						$today                = time();
						
						/**** Checking if the special price got applied ****/
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
							$productSprice = number_format($product->getSpecialprice(), 2);
						else
							$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
						
						$productStatus  = $product->getStockItem()->getIsInStock();
						$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
						
						if ($productStatus == 1 && $stockQuantity < 0)
							$productStatus == 1;
						else
							$productStatus == 0;
						
						$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
						
						/**** Tax Calculation ****/
						$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
						$taxClassId     = $product->getData("tax_class_id");
						$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
						$taxRate        = $taxClasses["value_" . $taxClassId];
						
						$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
						
						if ($taxType == 0) {
							$defaultPrice = str_replace(",", "", $productPrice);
						} else {
							$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
						}
						
						$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
						$defaultSprice = str_replace(",", "", $productSprice);
						$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						// Get the Special Price
						$specialPrice         = $product->getSpecialPrice();
						// Get the Special Price FROM date
						$specialPriceFromDate = $product->getSpecialFromDate();
						// Get the Special Price TO date
						$specialPriceToDate   = $product->getSpecialToDate();
						// Get Current date
						$today                = time();
						
						/**** I don't know what is happening right now ****/
						if ($specialPrice) {
							if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
								$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
							} else {
								$specialPrice = 0;
							}
						} else {
							$specialPrice = 0;
						}
						
						$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
						if ($taxType == 0) {
							$specialPrice = $specialPrice;
						} else {
							$specialPrice = $specialPrice - $taxPriceForSpecial;
						}
						
						if ($product->getTypeID() == 'grouped') {
							$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
							$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
						}else {
							$actualPrice =  number_format($product->getPrice(), 2, '.', '');
							$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
						}
						
						$shortDescription = $product->getShortDescription();
						$description = $product->getDescription();
						$cheers = is_null($product->getCheers()) ? 0 : $product->getCheers();
						
						if($actualPrice == $specialPrice) {
							$specialPrice = number_format(0, 2, '.', '');
							array_push($productList, array('id' => $productId, 'name' => $productName,'shortDescription'=>$shortDescription, 'description'=>$description, 'image' => (string)$productImage,'type' => $product->getTypeID(),'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus,'cheers'=>$cheers));
						}
					}
					
					$res["status"] = 1;
					$res["showStatus"] = 1;
					$res["lastPage"] = $lastPage;
					$res["productList"] = $productList;
				}else {
					/**** No product is present ****/
					$res["status"] = 1;
					$res["showStatus"] = 0;
					$res["lastPage"] = $lastPage;
					$res["productList"] = $productList;
				}
			}catch(Exception $e) {
				$res = array('status'=>0, 'errorMessage'=>'Something went wrong '.$e->getMessage());
			}
		}else {
			$res = array('status'=>0, 'errorMessage'=>'The query text is not given.');
		} 
		return $res;
	}
	
	public function getTopReviewedProducts($storeId, $currentPage = 1 , $pageSize = 10, $sortType = 'relevence', $sortOrder = 'desc', $currency) {
		
		try {
			
			Mage::app()->setCurrentStore($storeId);
			
			/**** Logic for getting the top rated products it includes the product for the rating score greater than or equal to 50 % ****/
		
			$_productCollection = Mage::getModel('catalog/product')->getCollection()
							->addAttributeToSelect('*')
							->joinField('rating_score', 
								'review_entity_summary', 
								'rating_summary', 
								'entity_pk_value=entity_id', 
								array('entity_type'=>1, 'store_id'=> Mage::app()->getStore()->getId(),'rating_summary'=> array('gteq'=>50)),
								'right'
							)->addAttributeToSort('rating_score', 'desc');
			
			$_products = $_productCollection;
			
			/**** Setting the current page and current page size to the collection that we got ****/
			$_products->setPageSize($pageSize)->setCurPage($currentPage);
			$_products->setOrder($sortType, $sortOrder);
			
			$productList = array();
			
			$i = 0;
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			$size = $_products->getSize();
			$lastPage = $_products->getLastPageNumber();
			
			if($size) {
				
				foreach($_products as $product) {
					
					$product = Mage::getModel('catalog/product')->load($product->getId());
					
					$productId = $product->getId(); // Getting the id
					$productName  = $product->getName();  // Getting the name for the product
					$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
					$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
					$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
					$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
					$today                = time();
					
					/**** Checking if the special price got applied ****/
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
						$productSprice = number_format($product->getSpecialprice(), 2);
					else
						$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
					
					$productStatus  = $product->getStockItem()->getIsInStock();
					$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
					
					if ($productStatus == 1 && $stockQuantity < 0)
						$productStatus == 1;
					else
						$productStatus == 0;
					
					$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
					
					/**** Tax Calculation ****/
					$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
					$taxClassId     = $product->getData("tax_class_id");
					$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
					$taxRate        = $taxClasses["value_" . $taxClassId];
					
					$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
					
					if ($taxType == 0) {
						$defaultPrice = str_replace(",", "", $productPrice);
					} else {
						$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
					}
					
					$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
					$defaultSprice = str_replace(",", "", $productSprice);
					$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
					// Get the Special Price
					$specialPrice         = $product->getSpecialPrice();
					// Get the Special Price FROM date
					$specialPriceFromDate = $product->getSpecialFromDate();
					// Get the Special Price TO date
					$specialPriceToDate   = $product->getSpecialToDate();
					// Get Current date
					$today                = time();
					
					/**** I don't know what is happening right now ****/
					if ($specialPrice) {
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
							$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						} else {
							$specialPrice = 0;
						}
					} else {
						$specialPrice = 0;
					}
					
					$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
					if ($taxType == 0) {
						$specialPrice = $specialPrice;
					} else {
						$specialPrice = $specialPrice - $taxPriceForSpecial;
					}
					
					if ($product->getTypeID() == 'grouped') {
						$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}else {
						$actualPrice =  number_format($product->getPrice(), 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}
					
					$shortDescription = $product->getShortDescription();
					$description = $product->getDescription();
					$cheers = is_null($product->getCheers()) ? 0 : $product->getCheers();
					$productRating = $this->getProductRating($product->getId());
					
					/***** Getting the top reviewed products ****/
					
					$reviews = Mage::getModel('review/review')->getResourceCollection()
							  ->addStoreFilter(Mage::app()->getStore()->getId())
							  ->addEntityFilter('product', $productId)
							  ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
							  ->setDateOrder()
							  ->addRateVotes()
							  ->setPageSize(1)
							  ->setCurPage(1);
					
					$reviews->getSelect()->joinInner(
						'rating_option_vote',
						'main_table.review_id = rating_option_vote.review_id',
						array('review_value' => 'rating_option_vote.value')
					);
					
					$reviewDetails = $reviews->getFirstItem()->getDetail();
					
					if($actualPrice == $specialPrice) {
						$specialPrice = number_format(0, 2, '.', '');
						array_push($productList, array('id' => $productId, 'name' => $productName,'shortDescription'=>$shortDescription, 'description'=>$description, 'image' => (string)$productImage,'type' => $product->getTypeID(),'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus,'productRating'=>$productRating,'reviewDetails'=>$reviewDetails,'cheers'=>$cheers));
					}
				}
				
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}else {
				/**** No product is present ****/
				$res["status"] = 1;
				$res["showStatus"] = 0;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>'Something went wrong '.$e->getMessage());
		}
		return $res;
	}
	
	/**** @params Mage_Sales_Model_Quote ****/
	/**** return type array of description ****/
	
	public function getQuoteTotalDescription($quote) {
		
		/**** Getting the totals of the quote ****/
		$totals = $quote->getTotals();
		$totalArr = array();
		
		foreach($totals as $total) {
			
			$totalCode = $total->getCode();
			
			if($totalCode == 'shipping') {
				array_push($totalArr, array("title"=>Mage::helper('sales')->__('Shipping & Handling'), "value"=>number_format($total->getValue(), 2)));
			}
			
			if($totalCode == 'grand_total') {
				array_push($totalArr, array("title"=>Mage::helper('sales')->__('Grand Total'), "value"=>number_format($total->getValue(), 2)));
			}
			
			if($totalCode == 'subtotal') {
				array_push($totalArr, array("title"=>Mage::helper('sales')->__('Subtotal'), "value"=>number_format($total->getValue(), 2)));
			}
			
			if($totalCode == 'discount') {
				array_push($totalArr, array("title"=>Mage::helper('sales')->__('Discount'), "value"=>number_format($total->getValue(), 2)));
			}
			
			if($totalCode == 'tax') {
				array_push($totalArr, array("title"=>Mage::helper('sales')->__('Tax'), "value"=>number_format($total->getValue(), 2)));
			}
		}
		
		return $totalArr;
	}
	
	/***** Getting the brand exclusive products ****/
	
	public function getBrandExclusiveProducts($storeId, $sortType, $sortOrder) {
		
		try {
			
			Mage::app()->setCurrentStore($storeId);
			
			$visibility = array(
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
				Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
			);
			
			/**** Getting the cateogry with the category id 150 ****/
			$category = Mage::getModel('catalog/category')->load(150);
			
			/**** Logic for getting the brand exclusive products ****/
			$_productCollection = $category->getProductCollection()->addCategoryFilter($category)
									->addAttributeToFilter('type_id', 'simple')
									->setStoreId($storeId)
									->addStoreFilter($storeId)
									->addAttributeToFilter('visibility', $visibility)
									->addAttributeToSelect('status')
									->setPage(1, 8)
									->addAttributeToSelect('*');
			
			$_productCollection->getSelect()->orderRand();

			Mage::getSingleton('catalog/product_status')->addVisibleFilterToCollection($_productCollection);
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($_productCollection);
			Mage::getSingleton('catalog/product_visibility')->addVisibleInSearchFilterToCollection($_productCollection);
			
			/**** end the logic ****/
			
			$_products = $_productCollection;
			
			/**** Setting the current page and current page size to the collection that we got ****/
			$_products->setOrder($sortType, $sortOrder);
			
			$productList = array();
			
			$i = 0;
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			$size = $_products->getSize();
			$lastPage = $_products->getLastPageNumber();
			
			if($size) {
				
				foreach($_products as $product) {
					
					$product = Mage::getModel('catalog/product')->load($product->getId());
					
					$productId = $product->getId(); // Getting the id
					$productName  = $product->getName();  // Getting the name for the product
					$productImage = Mage::helper('catalog/image')->init($product,'small_image')->resize(200,200); // Getting the product image
					$productPrice = number_format($product->getPrice(), 2); // Getting the normal price
					$specialPriceFromDate = $product->getSpecialFromDate(); // Getting the starting date for the special price
					$specialPriceToDate   = $product->getSpecialToDate();	// Getting the last date for the special price
					$today                = time();
					
					/**** Checking if the special price got applied ****/
					if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate))
						$productSprice = number_format($product->getSpecialprice(), 2);
					else
						$productSprice = "0.00"; // Special price is set to zero if the current date is not applicable for the special price
					
					$productStatus  = $product->getStockItem()->getIsInStock();
					$stockQuantity = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty();
					
					if ($productStatus == 1 && $stockQuantity < 0)
						$productStatus == 1;
					else
						$productStatus == 0;
					
					$currencySymbol = Mage::app()->getLocale()->currency($currency)->getSymbol();
					
					/**** Tax Calculation ****/
					$taxType       = Mage::getStoreConfig('tax/calculation/price_includes_tax');
					$taxClassId     = $product->getData("tax_class_id");
					$taxClasses     = Mage::helper("core")->jsonDecode(Mage::helper("tax")->getAllRatesByProductClass());
					$taxRate        = $taxClasses["value_" . $taxClassId];
					
					$taxPrice      = str_replace(",", "", number_format(((($taxRate) / 100) * ($product->getPrice())), 2));
					
					if ($taxType == 0) {
						$defaultPrice = str_replace(",", "", $productPrice);
					} else {
						$defaultPrice = str_replace(",", "", $productPrice) - $taxPrice;
					}
					
					$actualPrice   = strval(round($this->convert_currency($defaultPrice, $baseCurrencyCode, $currency), 2));
					$defaultSprice = str_replace(",", "", $productSprice);
					$splSprice     = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
					// Get the Special Price
					$specialPrice         = $product->getSpecialPrice();
					// Get the Special Price FROM date
					$specialPriceFromDate = $product->getSpecialFromDate();
					// Get the Special Price TO date
					$specialPriceToDate   = $product->getSpecialToDate();
					// Get Current date
					$today                = time();
					
					/**** I don't know what is happening right now ****/
					if ($specialPrice) {
						if ($today >= strtotime($specialPriceFromDate) && $today <= strtotime($specialPriceToDate) || $today >= strtotime($specialPriceFromDate) && is_null($specialPriceToDate)) {
							$specialPrice = strval(round($this->convert_currency($defaultSprice, $baseCurrencyCode, $currency), 2));
						} else {
							$specialPrice = 0;
						}
					} else {
						$specialPrice = 0;
					}
					
					$taxPriceForSpecial = (($taxRate) / 100) * ($specialPrice);
					if ($taxType == 0) {
						$specialPrice = $specialPrice;
					} else {
						$specialPrice = $specialPrice - $taxPriceForSpecial;
					}
					
					if ($product->getTypeID() == 'grouped') {
						$actualPrice = number_format($this->getGroupedProductPrice($product->getId(), $currency) , 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}else {
						$actualPrice =  number_format($product->getPrice(), 2, '.', '');
						$specialPrice =  number_format($product->getFinalPrice(), 2, '.', '');
					}
					
					$shortDescription = $product->getShortDescription();
					$description = $product->getDescription();
					$cheers = is_null($product->getCheers()) ? 0 : $product->getCheers();
					
					if($actualPrice == $specialPrice) {
						$specialPrice = number_format(0, 2, '.', '');
						array_push($productList, array('id' => $productId, 'name' => $productName,'shortDescription'=>$shortDescription, 'description'=>$description, 'image' => (string)$productImage,'type' => $product->getTypeID(),'price' => number_format($this->convert_currency($actualPrice, $baseCurrencyCode, $currency), 2, '.', ''),'specialPrice' => number_format($this->convert_currency($specialPrice, $baseCurrencyCode, $currency), 2, '.', ''),'currencySymbol' => $currencySymbol,'isStockStatus' => $productStatus, 'cheers'=>$cheers));
					}
				}
				$res["status"] = 1;
				$res["showStatus"] = 1;
				$res["lastPage"] = 1; /**** it is hard coded because there is only one page to display as on the frontend ****/
				$res["productList"] = $productList;
			}else {
				/**** No product is present ****/
				$res["status"] = 1;
				$res["showStatus"] = 0;
				$res["lastPage"] = $lastPage;
				$res["productList"] = $productList;
			}
		}catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>'Something went wrong '.$e->getMessage());
		}
		return $res;
	}
	
	public function deleteAddress() {
		
		/**** Authenticate the api ****/
		$customerId = Mage::getModel('mofluid_tokensystem/token')->authenticateApi();
		
		if(is_null($customerId)) {
			return array("status"=>0, "errorMessage"=>"Invalid token id.");
		}
		
		/**** getting the address with the content-type application/json ****/
		$postedData = $this->getJsonPostedData();

		if(!isset($postedData) || empty($postedData)) {
			return array('status'=>'0','errorMessage'=>'Field missing posted data.');
		}
		
		$addressId = $postedData['address_id'];
		
		if(!isset($addressId) || ($addressId=="") || (!is_numeric($addressId))) {
			return array("status"=>0, "errorMessage"=>"Address Id is not sent.");
		}
		
		$address = Mage::getModel('customer/address')->load($addressId);
		
		if(!$address->getId()) {
			return array("status"=>0, "errorMessage"=>"No such address.");
		}
		
		if($address->getCustomerId() != $customerId) {
			return array("status"=>0, "errorMessage"=>"The address does not belong to this customer.");
		}
		
		try {
			/**** Delete the address ****/
			$address->delete();
			return array("status"=>1, "message"=>"The address has been deleted.");
		}catch(Exception $e) {
			return array("status"=>0, "message"=>"Failed to delete the address.".$e->getMessage());
		}
	}
	
	/**** Function to get the suggestion for the search *****/
	/**** All credit goes to Lakshya Sir ****/
	
	public function getSearchSuggestion($storeId, $searchData) {
		
		$res = array();
		
		try {
			
			Mage::app()->setCurrentStore($storeId);
			
			$params = Mage::app()->getRequest()->getParams();
			
			/**** decoding the search data ****/
			$searchData = base64_decode($searchData);
			
			if($searchData)
				$params[Mage_CatalogSearch_Helper_Data::QUERY_VAR_NAME] = $searchData;
			
			Mage::app()->getRequest()->setParams($params);
			
			$query = Mage::helper('catalogsearch')->getQuery();
			$query->setStoreId($storeId);
			
			if($query->getQueryText() != '') {
				
				if(!Mage::helper('catalogsearch')->isMinQueryLength()) {
					
					$suggestCollection = $query->getSuggestCollection();
					
					foreach ($suggestCollection as $item) {
						
						$_data = array(
							'title' => $item->getQueryText(),
							'numOfResults' => $item->getNumResults()
						);
						
						if ($item->getQueryText() == $query->getQueryText()) {
							array_unshift($data, $_data);
						}
						else {
							$data[] = $_data;
						}
					}
					
					$total = $suggestCollection->getSize();
					$suggestTerms = $data;
					
					$res = array('status'=>1,'total'=> $total, 'suggestedTerms'=>$suggestTerms);
				}else {
					$res = array('status'=>0, 'errorMessage'=>'Query is not of the minimum length required.');
				}
			} else {
				$res = array('status'=>0, 'errorMessage'=>'Invalid Request ! Unable to fetch the search query.');
			}
		} catch(Exception $e) {
			$res = array('status'=>0, 'errorMessage'=>$e->getMessage());
		}
		return $res;
	}
	
	public function getMostCheeredProducts($storeId) {
		
		
		$products = Mage::getModel('catalog/product')
        ->getCollection();
echo $products->getSelect(); echo "<br>";
		
		$collection = Mage::getModel('catalog/product')->getCollection();
$collection->addAttributeToSelect('name');  
$collection->addAttributeToSelect('cheers')->setStoreId($storeId);    

//filter for products whose orig_price is greater than (gt) 100
//~ $collection->addFieldToFilter(array(
    //~ array('attribute'=>'orig_price','gt'=>'100'),
//~ )); 

//AND filter for products whose orig_price is less than (lt) 130
$collection->addFieldToFilter(array(
    'attribute'=>'cheers','eq'=>'1',
));

echo ($collection->getSelect());
echo $collection->getSize();
		
		die;
		
		
		Mage::app()->setCurrentStore($storeId);
		
		//~ $_productCollection = Mage::getModel('catalog/product')->getCollection()
							//~ ->addAttributeToSelect('*')
							//~ ->joinField('rating_score', 
								//~ 'review_entity_summary', 
								//~ 'rating_summary', 
								//~ 'entity_pk_value=entity_id', 
								//~ array('entity_type'=>1, 'store_id'=> Mage::app()->getStore()->getId(),'rating_summary'=> array('gteq'=>50)),
								//~ 'right'
							//~ )->addAttributeToSort('rating_score', 'desc');
		
		$collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSelect('*');
		$collection->setPageSize(20)->setCurPage(1);
		$collection->addAttributeToSort('cheers', Varien_Data_Collection::SORT_ORDER_DESC);
		//~ $collection->setOrder('','desc');
		
		$productCollection = Mage::getResourceModel('catalog/product_collection')
                       ->addAttributeToSelect('cheers')
                       ->addFieldToFilter(array(
                           array('attribute'=>'cheers','eq'=>'1'),
                       ));
                       
                       echo $productCollection->getSize();
                       
                       foreach($productCollection as $prod) {
						   echo $prod->getId();
					   } die;
		
		
		echo "<pre>"; print_r($collection->getSelect()); 
		
		//~ $collection = Mage::getModel('catalog/product')->getCollection()->setOrder('cheers','DESC')->setPageSize(20)->setCurPage(1);
		//~ $collection = Mage::getModel('catalog/product')->getCollection()->addAttributeToSort('cheers','DESC')->setPageSize(20)->setCurPage(1);
		
		foreach($collection as $col) {
			echo $col->getId();
			echo "\n";
		}
		//~ echo "<pre>"; print_r($collection->getOrder());
		//~ echo "<pre>"; print_r($collection->getOrder());
		return array();
	}
	
}

