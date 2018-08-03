<?php

/*
Mofluidapi119_Catalog_Products v0.0.1
(c) 2016-2017 by Mofluid. All rights reserved.
Kaleshwar Jaiswal
*/
include_once('Catalog/Products.php');

class Service
{
    
    /** Function : ws_category
     * Service Name : Category
     * @param $store : Store Id for Magento Stores
     * @param $service : Name of the Webservice
     * @return JSON Array
     * Description : Service to fetch all category
     * */
    public $CACHE_EXPIRY; //= 300; //in Seconds
    protected $_relatedCollection; 
    public function __construct(){ 
			$expireTime = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
     		$cacheExpireTime = $expireTime->getData('mofluid_cs_accountid');
			$this->CACHE_EXPIRY = $cacheExpireTime ;
			$this->_relatedCollection = null;
	}
    public function ws_sidecategory($store, $service)
    {
        
    }
	
	
	function getChildCategories($id){
		$all_child = $result = $shopbywine = [];
		$result['categories'] = $result['shopbywine'] = [];
		$counter = 0;
		$catPath = '1/'.$id.'/%';
		try
		{
			$allCategories = Mage::getModel('catalog/category')
							->getCollection()
							->addAttributeToSelect(array('is_anchor','sort_order_mobileapp','display_name_in_mobileapp_menu','name'))
							->addAttributeToFilter('path', array('like' => $catPath))
							->addAttributeToFilter('show_in_mobileapp_menu',1)
							->addIsActiveFilter(); 
			foreach($allCategories as $_category)
			{
				if($_category->getId() != 13)
				{
					if($_category->getIsAnchor()){
						$productsCount = $_category->getProductCollection()->getSize();
					}
					else{
						$productsCount = $_category->getProductCount();
					}
					if($productsCount)
					{
						$all_child[$counter]["id"] = $_category->getId();
						$all_child[$counter]["name"] = $_category->getDisplayNameInMobileappMenu() ? $_category->getDisplayNameInMobileappMenu() : $_category->getName();
						$all_child[$counter]["products"] = $_category->getProductCount();
						$all_child[$counter]["position"] = $_category->getSortOrderMobileapp() ? $_category->getSortOrderMobileapp() : 0;
						$counter++;
					}
				}
				else
				{
					$shopbywine[0]["id"] 	= $_category->getId();
					$shopbywine[0]["name"]	= $_category->getDisplayNameInMobileappMenu() ? $_category->getDisplayNameInMobileappMenu() : $_category->getName();
					$shopbywine[0]["products"] = $_category->getProductCount();
					$shopbywine[0]["position"] = $_category->getSortOrderMobileapp() ? $_category->getSortOrderMobileapp() : 0;
				}
			}
			usort($all_child, function ($a, $b) {
				if ($a['position'] == $b['position']) return 0;
				return $a['position'] < $b['position'] ? -1 : 1;
			});
			$result['categories'] = $all_child;
			$result['shopbywine'] = $shopbywine;
			return $result;
		}
		catch(Exception $e)
		{
			$res['status']  = 'error';
			$res['message'] = $e->getMessage();
			return $res; 
		}
	}
	
    function getChildCategories_old($id){
		$cat = Mage::getModel('catalog/category')->load($id);
		$subcats = $cat->getChildren();
		$all_child = array();
		$counter = 0;
	
		foreach(explode(',',$subcats) as $subCatid)
		{
		 $_category = Mage::getModel('catalog/category')->load($subCatid);
		 //echo "<pre>";print_r($_category->getData());
		 if($_category->getIsAnchor()){
			$productsCount = $_category->getProductCollection()->getSize();
         }
         else{
			$productsCount = $_category->getProductCount();
		 }
		 if($_category->getIsActive() && $productsCount) {
			if($_category->getShowInMobileappMenu())
			{
				$all_child[$counter]["id"] = $_category->getId();
				$all_child[$counter]["name"] = $_category->getDisplayNameInMobileappMenu() ? $_category->getDisplayNameInMobileappMenu() : $_category->getName();
				$all_child[$counter]["products"] = $_category->getProductCount();
				$all_child[$counter]["position"] = $_category->getSortOrderMobileapp() ? $_category->getSortOrderMobileapp() : 0;
				$counter++;
			}
			$sub_cat = $_category;
			$sub_subcats = $sub_cat->getChildren();
			$setcount = 0;
			
			foreach(explode(',',$sub_subcats) as $sub_subCatid)
			{
				 $_sub_category = Mage::getModel('catalog/category')->load($sub_subCatid);
				 if($_sub_category->getIsActive() && $_sub_category->getProductCount() && $_sub_category->getShowInMobileappMenu()) { 
					 $all_child[$counter]["id"] = $_sub_category->getId();
					 $all_child[$counter]["name"] = $_sub_category->getDisplayNameInMobileappMenu() ? $_sub_category->getDisplayNameInMobileappMenu() : $_sub_category->getName();
					 $all_child[$counter]["products"] = $_sub_category->getProductCount();
					 $all_child[$counter]["position"] = $_sub_category->getSortOrderMobileapp() ? $_sub_category->getSortOrderMobileapp() : 0;
					 $counter++;
				 }
				 
			}
			//$all_child[$counter]["products"] = $sub_cat->getProductCount();
		 }
		}
		usort($all_child, function ($a, $b) {
			if ($a['position'] == $b['position']) return 0;
			return $a['position'] < $b['position'] ? -1 : 1;
		});
		return $all_child;
	}
    /*   * *fetch initial data** */
    
    public function fetchInitialData($store, $service, $currency)
    {
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
        $result = $this->getChildCategories($rootcatId);
        //$result["categories"] = $this->getChildCategories($rootcatId);
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
				continue;
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
        $res["total"] = count($children1);
        $res["category"] = $category->getName();;
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
        ))->addAttributeToFilter('visibility', 4)->addAttributeToFilter('is_in_stock', array(
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
            
            $ratingValue = '';
            $formatValue = '';
            if(isset($_product['soko_rating_value']))
               $ratingValue = $_product->soko_rating_value;
            if(isset($_product['soko_format_value']))
               $formatValue = $_product->soko_format_value;
            
            $res["data"][] = array(
                "id" => $_product->getId(),
                "name" => $this->getNamePrefix($_product).$_product->getName(),
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
                "soko_rating_value" => $ratingValue,
                "soko_format_value" => $formatValue,
                "description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) : ''
            );
            }
            
        }
		if($enable){ 
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY); 
		} 
        return ($res);
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
                $productName  = $this->getNamePrefix($_product).$_product->getName();
                $productPrice         = number_format($_product->getPrice(), 3);
                $productImage  =$_product->getImageUrl();
              // echo 'New Price '.$productPrice; die;			
                $specialPriceFromDate = $_product->getSpecialFromDate();
                $specialPriceToDate   = $_product->getSpecialToDate();
                //echo 'New Price '.$specialPriceToDate; die;		
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
                //echo PHP_EOL.$productPrice;
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
                
                $ratingValue = '';
                $formatValue = '';
                if(isset($_product['soko_rating_value']))
                  $ratingValue = $_product->soko_rating_value;
                if(isset($_product['soko_format_value']))
                  $formatValue = $_product->soko_format_value;
                
                if($actualprice == $specialprice)
                    $specialprice = number_format(0, 3, '.', '');
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
                     "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty(),
                     "soko_rating_value" => $ratingValue,
                     "soko_format_value" => $formatValue,
                     "description" => $_product->getDescription()
                    
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
        $categoryId = 26; // a category id that you can get from admin
		$category = Mage::getModel('catalog/category')->load($categoryId);
		$_products = Mage::getModel('catalog/product')->getCollection();
		$_products->getSelect()->order('rand()');
		$_products->addCategoryFilter($category)
					->addAttributeToSelect('*')
					->addAttributeToFilter('visibility', array('eq' => 4));
       
        $featuredProducts = array();
        $i                = 0;
        if ($_products->getSize()) {
            foreach ($_products->getItems() as $_product) {
                $product_id   = $_product->getId();
                $productName  = $this->getNamePrefix($_product).$_product->getName();
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
                //echo PHP_EOL.$productPrice;
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
                
                $ratingValue = '';
                $formatValue = '';
                if(isset($_product['soko_rating_value']))
                  $ratingValue = $_product->soko_rating_value;
                if(isset($_product['soko_format_value']))
                  $formatValue = $_product->soko_format_value;
                
                $featuredProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'currency_symbol' => $currencysymbol,
                    'is_stock_status' => $productStatus,
                    "soko_rating_value" => $ratingValue,
                    "soko_format_value" => $formatValue,
                    "description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) : ''
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
                        ->addAttributeToSelect('*') 
                        ->addAttributeToFilter('entity_id',array('in' => $_products));

//~ foreach($_products as $_products){
    //~ echo $product->getName();
    //~ echo $product->getDescription();
    //~ echo $product->getPrice();
    //~ echo Mage::helper('catalog/image')->init($product,'small_image');
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
                $productName  = $this->getNamePrefix($_product).$_product->getName();
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
                //echo PHP_EOL.$productPrice;
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
                
                $ratingValue = '';
                $formatValue = '';
                if(isset($_product['soko_rating_value']))
                  $ratingValue = $_product->soko_rating_value;
                if(isset($_product['soko_format_value']))
                  $formatValue = $_product->soko_format_value;
                
                $getBestsellerProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'currency_symbol' => $currencysymbol,
                    'is_stock_status' => $productStatus,
                    "soko_rating_value" => $ratingValue,
                    "soko_format_value" => $formatValue,
                    "description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) : ''
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
        $categoryId = 28; // a category id that you can get from admin
		$category = Mage::getModel('catalog/category')->load($categoryId);
		$_products = Mage::getModel('catalog/product')->getCollection();
		$_products->getSelect()->order('rand()');
		$_products->addCategoryFilter($category)
					->addAttributeToSelect('*')
					->addAttributeToFilter('visibility', array('eq' => 4));
        
        
        $featuredProducts = array();
        $i                = 0;
        if ($_products->getSize()) {
            $count = 0;
            foreach ($_products->getItems() as $_product) {
                //if ($count == 10)
                //    break;
                $count++;
                
                $product_id   = $_product->getId();
                $productName  = $this->getNamePrefix($_product).$_product->getName();
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
               $ratingValue = '';
               $formatValue = '';
               if(isset($_product['soko_rating_value']))
                  $ratingValue = $_product->soko_rating_value;
               if(isset($_product['soko_format_value']))
                  $formatValue = $_product->soko_format_value;
               
               // $actualprice =  number_format($_product->getPrice(), 2, '.', '');
                //$specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
                if($actualprice == $specialprice)
                    $specialprice = number_format(0, 2, '.', '');
                $featuredProducts["products_list"][$i++] = array(
                    'id' => $product_id,
                    'name' => $productName,
                    'image' => (string)$productImage,
                    "type" => $_product->getTypeID(),
                    'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                    'currency_symbol' => $currencysymbol,
                    'is_stock_status' => $productStatus,
                    'soko_rating_value' => $ratingValue,
                    'soko_format_value' => $formatValue,
                    'description' => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) : ''
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
        $res["confirmation_required"] = false;
        $res["confirmation_message"] = '';
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
			if($e->getCode() == Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED)
			{
				$res["confirmation_required"] = true;
				$res["confirmation_message"] = 'This account is not confirmed.';
			}
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
        //  echo 'Phase 2';
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
            $res['confirmation_sent'] = false;
			$res['confirmation_message'] = '';
            $cust                    = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
            
            //check exists email address of users  
            if ($cust->getId()) {
                $res["id"]     = $cust->getId();
                $res["status"] = 0;
            } else {
                //echo 'Phase 2.5';
                if ($customer->save()) {
					if ($customer->isConfirmationRequired()) {
						$customer->sendNewAccountEmail(
							'confirmation',
							'',
							$store
						);
						$res['confirmation_sent'] = true;
						$res['confirmation_message'] = 'Account confirmation is required. Please, check your email for the confirmation link.';
					}
					else
					{
						$customer->sendNewAccountEmail('confirmed');
						$customer->setConfirmation(null);
					}
					$customer->save();
					if(!$customer->isConfirmationRequired())
						$this->send_Password_Mail_to_NewUser($firstname, base64_decode($password), $email);
                    $res["id"]     = $customer->getId();
                    $res["status"] = 1;
                } else {
                    //echo "Already Exist";
                    $exist_customer = Mage::getModel("customer/customer");
                    $exist_customer->setWebsiteId($websiteId);
                    $exist_customer->setCurrentStore($store);
                    $exist_customer->loadByEmail($email);
                    try
					{
						$customerSession  = Mage::getSingleton('customer/session');
						$customerSession->setCustomerAsLoggedIn($login_customer);
					}
					catch (Exception $e) {
					}
                    $res["id"]     = $exist_customer->getId();
                    $res["status"] = 1;
                    
                    //echo "An error occured while saving customer";
                }
            }
            //echo 'Phase 3';
        }
        catch (Exception $e) {
            
            //echo "Already Exist Exception";
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
    
	public function ws_resendConfirmation($store)
	{
		$res = [];
		$res['status'] = 'success';
		$res['message'] = '';
		$customer = Mage::getModel('customer/customer');
		$email = Mage::app()->getRequest()->getParam('email');
		$email = base64_decode($email);
		try
		{
			if ($email) {
				$customer->setWebsiteId(Mage::app()->getStore($store)->getWebsiteId())->loadByEmail($email);
				if (!$customer->getId()) {
					throw new Exception('Customer with this email doesn\'t exists.');
				}
				if ($customer->getConfirmation()) {
					$customer->sendNewAccountEmail('confirmation', '', $store);
					$res['message'] = 'Please, check your email for confirmation key.';
				} else {
					$res['status'] = 'error';
					$res['message'] = 'This email does not require confirmation.';
				}
			}
			else
			{
				$res['status'] = 'error';
				$res['message'] = 'Invalid Request! Please provide customer email.';
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}
    
    public function ws_customerLogout($store, $customerId)
    {
		$res['status'] = 'success';
		if($customerId)
		{
			try
            {
				$customerSession  = Mage::getSingleton('customer/session');
				$customerSession->logout();
			}
			catch (Exception $e) {
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
        //echo count($attributes);
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
                $_associatedProductsArray[$i]["product_name"] = $this->getNamePrefix($_associatedProduct).$_associatedProduct->getName();//$_associatedProduct->getName();
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
        
        // --------------------------------- Additional Info Data ----------------------------------------//
			$additionalInfo = [];
			$_helper = Mage::helper('catalog/output');
			$additionalCount = 0;
			$additionalInfo[$additionalCount]['label'] = 'Product ID';
			$additionalInfo[$additionalCount]['value'] = $_helper->productAttribute($product, nl2br($product->getSku()), 'sku');
			$additionalCount++;
			$additionalInfo[$additionalCount]['label'] = 'Availability';
			if (strpos($product->getAttributeText('soko_format'),'Kit') !== false)
				$additionalInfo[$additionalCount]['value'] = 'In Stock';
			else
			{
				if($product->isSaleable())
				{
					$availabilityValue = explode("</strong>",Mage::helper('hpmodules')->crazy($_helper->productAttribute($product, $product->getSKU(), 'sku')));
					if(count($availabilityValue) > 1)
						$availabilityFinalValue = $availabilityValue[1];
					else
						$availabilityFinalValue = $availabilityValue[0];
					$additionalInfo[$additionalCount]['value'] = $availabilityFinalValue;
				}
				else
					$additionalInfo[$additionalCount]['value'] = 'Out of Stock';
			}
			$additionalCount++;
			if ($product->getAttributeText('soko_vintage') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Vintage';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_vintage');
				$additionalCount++;
			}
			if ((strpos($product->getAttributeText('soko_format'),'Kit') !== false) or (strpos($product->getAttributeText('soko_format'),'3 Pack') !== false))
			{
				$additionalInfo[$additionalCount]['label'] = 'Format';
				$additionalInfo[$additionalCount]['value'] = 'Gift Set';
				$additionalCount++;
			}
			else
			{
				$additionalInfo[$additionalCount]['label'] = 'Format';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_format');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_color') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Color';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_color');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_country') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Country';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_country');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_producer') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Producer';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_producer');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_rating') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Rating';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_rating');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_region') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Region';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_region');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_type_of_wine') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Type of Wine';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_type_of_wine');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_varietal') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Varietal';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_varietal');
				$additionalCount++;
			}
			// ---------------------------------------------------------------------------------------------//
			
            $res["id"]          = $product->getId();
            $res["sku"]         = $product->getSku();
            $res["name"]        = $this->getNamePrefix($product).$product->getName();
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
            $res["minprice"]    = $min_price;
            //convert price from base currency to current currency
            $res["currencysymbol"] = Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol();
            $res["soko_rating_value"] = $product->getAttributeText('soko_rating') ? $product->getAttributeText('soko_rating') : '';
            $res["soko_format_value"] = $product->getAttributeText('soko_format') ? $product->getAttributeText('soko_format') : '';
            $res["additional_info"] = $additionalInfo;
            
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
			//	echo "<pre>";print_r("hello"); die;
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
            if ($has_custom_option) {
                //$res["custom_option"] = $all_custom_option_array;
                 $custom_option = $all_custom_option_array;
            }    $product_data=$product;
            $config_option = array();
           
               
				if($qty == 0){
					$qty =strval(round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($productid)->getQty(), 2));
					}
			// Code wriiten to check manage stock status 
			//$product_stock= $this->getProductStock($productid);
			//if ($product_stock["manage_stock"]==0){
			//$qty=5000;
			//}
			$product_stock= $this->getProductStockNew($productid);
			$qty = (int)$product_stock['qty'];
			
			// --------------------------------- Additional Info Data ----------------------------------------//
			$additionalInfo = [];
			$_helper = Mage::helper('catalog/output');
			$additionalCount = 0;
			$additionalInfo[$additionalCount]['label'] = 'Product ID';
			$additionalInfo[$additionalCount]['value'] = $_helper->productAttribute($product, nl2br($product->getSku()), 'sku');
			$additionalCount++;
			$additionalInfo[$additionalCount]['label'] = 'Availability';
			if (strpos($product->getAttributeText('soko_format'),'Kit') !== false)
				$additionalInfo[$additionalCount]['value'] = 'In Stock';
			else
			{
				if($product->isSaleable())
				{
					$availabilityValue = explode("</strong>",Mage::helper('hpmodules')->crazy($_helper->productAttribute($product, $product->getSKU(), 'sku')));
					if(count($availabilityValue) > 1)
						$availabilityFinalValue = $availabilityValue[1];
					else
						$availabilityFinalValue = $availabilityValue[0];
					$additionalInfo[$additionalCount]['value'] = $availabilityFinalValue;
				}
				else
					$additionalInfo[$additionalCount]['value'] = 'Out of Stock';
			}
			$additionalCount++;
			if ($product->getAttributeText('soko_vintage') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Vintage';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_vintage');
				$additionalCount++;
			}
			if ((strpos($product->getAttributeText('soko_format'),'Kit') !== false) or (strpos($product->getAttributeText('soko_format'),'3 Pack') !== false))
			{
				$additionalInfo[$additionalCount]['label'] = 'Format';
				$additionalInfo[$additionalCount]['value'] = 'Gift Set';
				$additionalCount++;
			}
			else
			{
				$additionalInfo[$additionalCount]['label'] = 'Format';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_format');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_color') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Color';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_color');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_country') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Country';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_country');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_producer') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Producer';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_producer');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_rating') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Rating';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_rating');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_region') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Region';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_region');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_type_of_wine') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Type of Wine';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_type_of_wine');
				$additionalCount++;
			}
			if ($product->getAttributeText('soko_varietal') !== false)
			{
				$additionalInfo[$additionalCount]['label'] = 'Varietal';
				$additionalInfo[$additionalCount]['value'] = $product->getAttributeText('soko_varietal');
				$additionalCount++;
			}
			// ---------------------------------------------------------------------------------------------//
					
            	   $res = array(
				  'sku'       => $product->getSku(),
				   
				  'name'      => $this->getNamePrefix($product).$product->getName(),
				   'category'	=> $product->getCategoryIds(),
				  'url'        => $product->getProductUrl(),
				   'description'=> $product->getDescription(),
				   //~ //print_r($res);die;
				   'shortdes'   => $product->getShortDescription(),
				   'quantity'   => (string)$qty,
				   'manage_stock'=>$product_stock["manage_stock"],
				   'visibility' => $product->getProductUrl(),
				   'type'       => $product->getTypeID(),
				   'weight'     => $product->getWeight(),
				   'status'   	=> $product->getStatus(),
				   'img'  		=> (string)$productImage,
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
				   'config_option' =>$config_option,
				   'soko_rating_value' => $product->getAttributeText('soko_rating') ? $product->getAttributeText('soko_rating') : '' ,
				   'soko_format_value' => $product->getAttributeText('soko_format') ? $product->getAttributeText('soko_format') : '',
				   'additional_info' => $additionalInfo
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
					$configurable_array=[];
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
 		public function configurable($_product,$currentcurrencycode,$defaultsprice,$basecurrencycode,$store){
					$product_data = Mage::getModel('catalog/product')->load($_product->getId());
					$configurable_count = 0;
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
	
						try {
                            $configurable_curr_arr = (array) $configurable_array1[$configurable_count]["data"];
                            if ($configurable_relation[$relation_count]) {
                                $configurable_relation[$relation_count] = $configurable_relation[$relation_count] . ',' . str_replace(',', '', str_replace(' ','', $configurable_curr_arr["label"]));
                            } else {
                                $configurable_relation[$relation_count] = str_replace(',', '', str_replace(' ','', $configurable_curr_arr["label"]));
                            }
                        }
                        catch (Exception $err) {
                            echo 'Error : ' . $err->getMessage();
                        }
                        $configurable_count++;
				}   $relation_count++;  
				$res = array('config_relation'=>$configurable_relation);
				
				$ratingValue = '';
                $formatValue = '';
                if(isset($product['soko_rating_value']))
                  $ratingValue = $product->soko_rating_value;
                if(isset($product['soko_format_value']))
                  $formatValue = $product->soko_format_value; 
				
				//print_r($configurable_relation); die;
				$configurable_array[][$product->getId()] = array(
							//"productAttributeId" => $productAttributeId,
						//	"selected_value"	=> $attributeValue,
							//"base_label"       =>  $attribute->getLabel(),
							"is_required"    =>  $productAttribute->getIsRequired(),
							"sku"  			=>  $product->getSku(),
							"name"          => $this->getNamePrefix($product).$product->getName(),
							"spclprice"     => strval($this->convert_currency($defaultsplprice, $basecurrencycode, $currentcurrencycode)),
							"price"        =>number_format($product->getPrice(), 3),
							"currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
							"created_date"  => $product->getCreatedAt(),
							"is_in_stock" =>  $product->getStockItem()->getIsInStock(),
							"stock_quantity" => strval(round(Mage::getModel('cataloginventory/stock_item')->loadByProduct($product->getId())->getQty(), 2)),
							"type"			=>  $product->getTypeID(),
							"shipping"   =>  Mage::getStoreConfig('carriers/flatrate/price'),
							"soko_rating_value" => $ratingValue,
							"soko_format_value" => $formatValue,
							"Total_config" => $total,
							"description" => $product->getResource()->getAttributeRawValue($product->getId(),'description',$store) ? $product->getResource()->getAttributeRawValue($product->getId(),'description',$store) : '',
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

        $mofluid_product           = Mage::getModel('catalog/product')->load($productid);
        $res                = array();
        $mofluid_all_product_images = array();
		foreach ($mofluid_product->getMediaGalleryImages() as $mofluid_image) {
                $mofluid_all_product_images[] = $mofluid_image->getUrl();
        }
        
        if(count($mofluid_all_product_images) <= 0)
        {
			if($productid)
			{
				if($mofluid_product->getImage() != 'no_selection' && $mofluid_product->getImage())
					$mofluid_all_product_images[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $mofluid_product->getImage();
				else
					$mofluid_all_product_images[] = Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/default/red_zoom.jpg';
			}
		}
        
        $res["id"]     = $mofluid_product->getId();
        $res["image"]  = $mofluid_all_product_images; // Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB).'/media/catalog/product'.$product->getImage();
        $res["status"] = $mofluid_product->getStatus();
        
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
	//  echo "<pre>"; print_r($product->getCompleteProductInfo()); die;
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
        
        
        // echo "<pre>"; print_r($cache_array); exit; 
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
            $below_main1     = 1;
            $below_main2    = 1;
            $middle_banner  = 1;
            $top_slider		= 1;
            $top_first_slider = 1;
            $mofluid_theme_elegant_banner_data = [];$banner_below_main1 = [];$banner_below_main2 = [];$banner_middle = [];
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
                        echo $ex->getMessage();
                    }
                    if ($banner_value['mofluid_store_id'] == $store) {
						if($banner_value['mofluid_banner_position'] == 1 && $below_main1)
						{
							$banner_below_main1[] = $banner_value;
							$below_main = 0; 
						}
						if($banner_value['mofluid_banner_position'] == 2 && $below_main2)
						{
							$banner_below_main2[] = $banner_value; 
							$below_main1 = 0;
						}
						if($banner_value['mofluid_banner_position'] == 3 && $middle_banner)
						{
							$banner_middle[] = $banner_value; 
							$middle_banner = 0;
						}
						if($banner_value['mofluid_banner_position'] == 0)
							$mofluid_theme_elegant_banner_data[] = $banner_value;
                    } else if ($banner_value['mofluid_store_id'] == 0) {
                        if($banner_value['mofluid_banner_position'] == 1 && $below_main1)
						{
							$banner_below_main1[] = $banner_value;
							$below_main1 = 0; 
						}
						else if($banner_value['mofluid_banner_position'] == 2 && $below_main2)
						{
							$banner_below_main2[] = $banner_value; 
							$below_main2 = 0;
						}
						else if($banner_value['mofluid_banner_position'] == 3 && $middle_banner)
						{
							$banner_middle[] = $banner_value; 
							$middle_banner = 0;
						}
						if($banner_value['mofluid_banner_position'] == 0)
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
                    if($top_first_slider && $banner_value['mofluid_banner_position'] == 0 && ($banner_value['mofluid_store_id'] == $store || $banner_value['mofluid_store_id'] == 0))
                    {
						$top_first_slider_banner = $banner_value;
						$top_first_slider = 0;
					}
					if ($banner_value['mofluid_store_id'] == $store || $banner_value['mofluid_store_id'] == 0) {
						if($banner_value['mofluid_banner_position'] == 1 && $below_main1)
						{
							$banner_below_main1[] = $banner_value;
							$below_main1 = 0; 
						}
						if($banner_value['mofluid_banner_position'] == 2 && $below_main2)
						{
							$banner_below_main2[] = $banner_value; 
							$below_main2 = 0;
						}
						if($banner_value['mofluid_banner_position'] == 3 && $middle_banner)
						{
							$banner_middle[] = $banner_value; 
							$middle_banner = 0;
						}
					}

                    if ($banner_value['mofluid_image_isdefault'] == '1' && $banner_value['mofluid_store_id'] == $store && $banner_value['mofluid_banner_position'] == 0 && $top_slider == 1) {
                        $mofluid_theme_elegant_banner_data[] = $banner_value;
						$top_slider = 0;
                    } else if ($banner_value['mofluid_image_isdefault'] == '1' && $banner_value['mofluid_store_id'] == 0 && $banner_value['mofluid_banner_position'] == 0 && $top_slider == 1) {
                        $mofluid_theme_elegant_banner_data[] = $banner_value;
						$top_slider = 0;
                    } else {
                        continue;
                    }
                }
                if (count($mofluid_theme_elegant_banner_data) <= 0 && $top_first_slider_banner) {
					$mofluid_theme_elegant_banner_data[] = $top_first_slider_banner; //$banner_value;
                }
            }
            
            $mofluid_theme_elegant_logo      = $mofluid_theme_elegant_model->getCollection()->addFieldToFilter('mofluid_image_type', 'logo')->addFieldToFilter('mofluid_theme_id', $mofluid_theme_id);
            $mofluid_theme_elegant_logo_data = $mofluid_theme_elegant_logo->getData();
            
            $mofluid_theme_data["code"]            = $theme;
            $mofluid_theme_data["logo"]["image"]   = $mofluid_theme_elegant_logo_data;
            $mofluid_theme_data["logo"]["alt"]     = Mage::getStoreConfig('design/header/logo_alt');
            $mofluid_theme_data["banner"]["image"] = $mofluid_theme_elegant_banner_data;
            $mofluid_theme_data["banner"]["below_main1"]  = $banner_below_main1;
            $mofluid_theme_data["banner"]["below_main2"] = $banner_below_main2;
            $mofluid_theme_data["banner"]["middle"] = $banner_middle;
            $res["theme"]                          = $mofluid_theme_data;
            
            
            
            
            //get google analytics
            $modules      = Mage::getConfig()->getNode('modules')->children();
            $modulesArray = (array) $modules;
            
            if (isset($modulesArray['Mofluid_Ganalyticsm'])) {
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
            echo $ex;
        }
        if($enable){ 
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY); 
		}
        return $res;
    }
    
   public function ws_search($store, $service, $search_data, $curr_page, $page_size, $sortType, $sortOrder, $currentcurrencycode)
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
            ))->setPage($curr_page, $page_size)->load();
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
                    
                    $tax_price_for_special = (($taxRate) / 100) * ($specialprice);
                    /* if ($tax_type == 0) {
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
                                 {
                                 	$g_flag_group2++;
                                 }
                                 
                                 $g_flag_group1++;
                                $grouped_prices[] = $grouped_product->getPriceModel()->getPrice($grouped_product);
                            }
                            
                              
                        }
                        
                        sort($grouped_prices);
                        $original_price = strval(round($this->convert_currency($grouped_prices[0], $basecurrencycode, $currentcurrencycode), 2));
                    } else 
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

                     $ratingValue = '';
                     $formatValue = '';
                     if(isset($_product['soko_rating_value']))
                        $ratingValue = $_product->soko_rating_value;
                     if(isset($_product['soko_format_value']))
                        $formatValue = $_product->soko_format_value;

/*  SUMIT KUMAR 
If product type is grouped then it will check that all child product is disable or not if 
disable then product will not show in search page
*/
if ($_product->getTypeID() == 'grouped') {
					  if($g_flag_group2!=$g_flag_group1)
					  {
						$res["data"][] = array(
                        "id" => $_product->getId(),
                        "name" => $this->getNamePrefix($_product).$_product->getName(),
                        "imageurl" => (string)$productImage,
                        "sku" => $_product->getSku(),
                        "type" => $_product->getTypeID(),
                        "hasoptions" => $has_custom_option,
                        "currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
                        "price" => number_format($this->convert_currency($original_price, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                        "spclprice" =>number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                        "created_date" => $_product->getCreatedAt(),
                        "is_in_stock" => $_product->getStockItem()->getIsInStock(),
                        "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty(),
                        "soko_rating_value" => $ratingValue,
                        "soko_format_value" => $formatValue,
                        "description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) : ''
                    );
							}
						}
					  else {
                    
                   	 $res["data"][] = array(
                        "id" => $_product->getId(),
                        "name" => $this->getNamePrefix($_product).$_product->getName(),
                        "imageurl" => (string)$productImage,
                        "sku" => $_product->getSku(),
                        "type" => $_product->getTypeID(),
                        "hasoptions" => $has_custom_option,
                        "currencysymbol" => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
                        "price" => number_format($this->convert_currency($original_price, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                        "spclprice" =>number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 2, '.', ''),
                        "created_date" => $_product->getCreatedAt(),
                        "is_in_stock" => $_product->getStockItem()->getIsInStock(),
                        "stock_quantity" => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty(),
                        "soko_rating_value" => $ratingValue,
                        "soko_format_value" => $formatValue,
                        "description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store) : ''
                    );
                }
                    
                    
                    
                }
            }
        }
        catch (Exception $ex) {
            echo $ex;
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
            $product[$itemcounter]["name"]  = $this->getNamePrefix($mofluid_product).$item->getName();
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
        //echo "<pre>"; print_r($res); die;
        if($enable){ 
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY); 
		}
        return $res;
    }
    
    public function ws_myOrder($cust_id, $curr_page, $page_size, $store, $currency)
    {	
    	//update the function "ws_myOrder_webApp" accordingly as it is working for the webapp
        
        $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
        $res              = array();
        $totorders        = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $cust_id);
        $res["total"]     = count($totorders);
        $orders           = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $cust_id)->setOrder('created_at', 'desc')->setPage($curr_page, $page_size);
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
            $name                        = array();
            $unitPrice                   = array();
            $sku                         = array();
            $ids                         = array();
            $qty                         = array();
            $images                      = array();
            $smallimg                    = array();
            $test_p                      = array();
            $itemsExcludingConfigurables = array();
            foreach ($items as $itemId => $item) {
                $_prod = Mage::getModel('catalog/product')->load($item->getProductId());
                $name[] = $this->getNamePrefix($_prod).$item->getName();
                //echo $item->getName();
                if ($item->getOriginalPrice() > 0) {
                    $unitPrice[] = number_format($this->convert_currency(floatval($item->getOriginalPrice()), $basecurrencycode, $currency), 2, '.', '');
                } else {
                    $unitPrice[] = number_format($this->convert_currency(floatval($item->getPrice()), $basecurrencycode, $currency), 2, '.', '');
                }
                
                $sku[]    = $item->getSku();
                $ids[]    = $item->getProductId();
                
                $psmallImg = Mage::helper('catalog/image')->init($_prod,'small_image')->resize(200,200);
                $smallimg[] = (string)$psmallImg;
                //$qty[]=$item->getQtyToInvoice();
                $qty[]    = $item->getQtyOrdered();
                $products = Mage::getModel('catalog/product')->load($item->getProductId());
                $images[] = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . '/media/catalog/product' . $products->getThumbnail();
            }
            $product = array();
            $product = array(
                "name" => $name,
                "sku" => $sku,
                "id" => $ids,
                "quantity" => $qty,
                "unitprice" => $unitPrice,
                "image" => $images,
                "small_image" => $smallimg,
                "total_item_count" => $itemcount,
                "price_org" => $test_p,
                "price_based_curr" => 1
            );
            
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
                "product" => $product,
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
            $orderData["multifees_amount"] 	= '0';
            if(isset($order['multifees_amount']) && isset($order['details_multifees']))
            {
				if($order['multifees_amount'] > 0)
				{
					$orderData["multifees_amount"] = number_format($this->convert_currency(floatval($order['multifees_amount']), $basecurrencycode, $currency), 2, '.', '');
				}
			}
            $res["data"][] = $orderData;
        }
        return $res;
    }

    public function ws_myOrder_webApp($cust_id, $curr_page, $page_size, $store, $currency)
    {
        
        $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
        $res              = array();
        $totorders        = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $cust_id);
        $res["total"]     = count($totorders);
        $orders           = Mage::getResourceModel('sales/order_collection')->addFieldToSelect('*')->addFieldToFilter('customer_id', $cust_id)->setOrder('created_at', 'desc')->setPage($curr_page, $page_size);
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
            $name                        = array();
            $unitPrice                   = array();
            $sku                         = array();
            $ids                         = array();
            $qty                         = array();
            $images                      = array();
            $smallimg                    = array();
            $test_p                      = array();
            $itemsExcludingConfigurables = array();

            $item_count = 0;
            $product = array();

            foreach ($items as $itemId => $item) {
                $_prod = Mage::getModel('catalog/product')->load($item->getProductId());
                $name = $this->getNamePrefix($_prod).$item->getName();
                //echo $item->getName();
                if ($item->getOriginalPrice() > 0) {
                    $unitPrice = number_format($this->convert_currency(floatval($item->getOriginalPrice()), $basecurrencycode, $currency), 2, '.', '');
                } else {
                    $unitPrice = number_format($this->convert_currency(floatval($item->getPrice()), $basecurrencycode, $currency), 2, '.', '');
                }
                
                $sku    = $item->getSku();
                $ids    = $item->getProductId();
                
                $psmallImg = Mage::helper('catalog/image')->init($_prod,'small_image')->resize(200,200);
                $smallimg = (string)$psmallImg;
                //$qty[]=$item->getQtyToInvoice();
                $qty    = $item->getQtyOrdered();
                $products = Mage::getModel('catalog/product')->load($item->getProductId());
                $images = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_WEB) . '/media/catalog/product' . $products->getThumbnail();

                $product[$item_count] = array(

                	"name" => $name,
	                "sku" => $sku,
	                "id" => $ids,
	                "quantity" => $qty,
	                "unitprice" => $unitPrice,
	                "image" => $images,
	                "small_image" => $smallimg,
	                "total_item_count" => $itemcount,
	                "price_org" => $test_p,
	                "price_based_curr" => 1

                );

                $item_count = $item_count + 1;

            }
            /*$product = array();
            $product = array(
                "name" => $name,
                "sku" => $sku,
                "id" => $ids,
                "quantity" => $qty,
                "unitprice" => $unitPrice,
                "image" => $images,
                "small_image" => $smallimg,
                "total_item_count" => $itemcount,
                "price_org" => $test_p,
                "price_based_curr" => 1
            );*/
            
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
                "product" => $product,
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
            $orderData["multifees_amount"] 	= '0';
            if(isset($order['multifees_amount']) && isset($order['details_multifees']))
            {
				if($order['multifees_amount'] > 0)
				{
					$orderData["multifees_amount"] = number_format($this->convert_currency(floatval($order['multifees_amount']), $basecurrencycode, $currency), 2, '.', '');
				}
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
            echo $ex2;
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
            echo $ex;
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
    
    /* Function call to register user from its Email address */
    
    public function ws_registerwithsocial($store, $email, $firstname, $lastname)
    {
        $res                  = array();
        $websiteId            = Mage::getModel('core/store')->load($store)->getWebsiteId();
        $customer             = Mage::getModel("customer/customer");
        $customer->website_id = $websiteId;
        $customer->setCurrentStore($store);
        try {
            // If new, save customer information
            $customer->firstname     = $firstname;
            $customer->lastname      = $lastname;
            $customer->email         = $email;
            $password                = base64_encode(rand(11111111, 99999999));
            $customer->password_hash = md5(base64_decode($password));
            $res["username"]            = $email;
            $res["firstname"]        = $firstname;
            $res["lastname"]         = $lastname;
            $res["password"]         = $password;
            $res["status"]           = 0;
            $res["id"]               = 0;
            $cust                    = Mage::getModel('customer/customer')->setWebsiteId(Mage::app()->getStore()->getWebsiteId())->loadByEmail($email);
            
            //check exists email address of users  
            if ($cust->getId()) {
                $res["id"]     = $cust->getId();
                $res["status"] = 1;
            } else {
                if ($customer->save()) {
                    $customer->sendNewAccountEmail('confirmed');
                    $this->send_Password_Mail_to_NewUser($firstname, base64_decode($password), $email);
                    $res["id"]     = $customer->getId();
                    $res["status"] = 1;
                } else {
                    $exist_customer = Mage::getModel("customer/customer");
                    $exist_customer->setWebsiteId($websiteId);
                    $exist_customer->setCurrentStore($store);
                    $exist_customer->loadByEmail($email);
                    $res["id"]     = $exist_customer->getId();
                    $res["status"] = 1;
                }
            }
        }
        catch (Exception $e) {
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
        $cache->save(json_encode($shipping), $cache_key, array(
            "mofluid"
        ), $this->CACHE_EXPIRY);
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
    public function prepareQuote($custid, $Jproduct, $store, $address, $shipping_code, $couponCode, $currency, $is_create_quote, $find_shipping, $theme = null, $quoteId, $giftcode = null,$giftcredit = null, $defaultShipMethod = null)
    {
        $Jproduct         = str_replace(" ", "+", $Jproduct);
        $orderproduct     = json_decode(base64_decode($Jproduct));
        $address          = str_replace(" ", "+", $address);
        $address          = json_decode(base64_decode($address),true);
        $config_manage_stock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
        $config_max_sale_qty = Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty');
        $basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
        
        // =============== Updating Session for all cases related to giftcard =============== //
        $session = Mage::getSingleton('checkout/session');
		$session ->setUseGiftCard(null)                       //Updating the gift card details in the session.
                 ->setGiftCodes(null)
                 ->setBaseAmountUsed(null)
                 ->setBaseGiftVoucherDiscount(null)
                 ->setGiftVoucherDiscount(null)
                 ->setCodesBaseDiscount(null)
                 ->setCodesDiscount(null)
                 ->setGiftMaxUseAmount(null)
                 ->setUseGiftCardCredit(0)
                 ->setMaxCreditUsed(null)
                 ->setBaseUseGiftCreditAmount(null)
                 ->setUseGiftCreditAmount(null);
		$customerSession = Mage::getSingleton('customer/session');	//customer session required is for Multifees extension only.
		$customerSession->unsetAll();								//clearing the earlier customer session.
		if($custid)
		{
			$customerObj = Mage::getModel('customer/customer')->load($custid);	//loading customer object for passing to customer session.
			if($customerObj && $custid != $customerSession->getCustomerId())	//default cart sync api does not require use of session.
			{
				$customerSession->setCustomer($customerObj);
			}
		}
        // ================================================================================== //
        
        try {
            //$customerObj     = Mage::getModel('customer/customer')->load($custid);
            // get billing and shipping address of customer
            $billingStreet = $address['billing']['street'][0];
			if(isset($address['billing']['street'][1]))
				$billingStreet = $billingStreet.' '.$address['billing']['street'][1];
            
            $shippingStreet = $address['shipping']['street'][0];
			if(isset($address['shipping']['street'][1]))
				$shippingStreet = $shippingStreet.' '.$address['shipping']['street'][1];
            
            // get billing and shipping address of customer
            $shippingAddress = array(
                'prefix' => $address['shipping']['prefix'],
                'firstname' => $address['shipping']['firstname'],
                'lastname' => $address['shipping']['lastname'],
                'company' => $address['shipping']['company'],
                'street' => $shippingStreet,
                'city' => $address['shipping']['city'],
                'postcode' => $address['shipping']['postcode'],
                'telephone' => $address['shipping']['telephone'],
                'country_id' => $address['shipping']['country_id'],
                'region' => $address['shipping']['region']
            );
            $billingAddress  = array(
                'prefix' => $address['billing']['prefix'],
                'firstname' => $address['billing']['firstname'],
                'lastname' => $address['billing']['lastname'],
                'company' => $address['billing']['company'],
                'street' => $billingStreet,
                'city' => $address['billing']['city'],
                'postcode' => $address['billing']['postcode'],
                'telephone' => $address['billing']['telephone'],
                'country_id' => $address['billing']['country_id'],
                'region' => $address['billing']['region']
            );
            
            if(isset($address['billing']['region_id']))
				$billingAddress['region_id'] = $address['billing']['region_id'];
            
            if(isset($address['shipping']['region_id']))
				$shippingAddress['region_id'] = $address['shipping']['region_id'];
            
            
            //Setting Region ID In case of Country is US
            if (($address['billing']['country'] == "US" || $address['billing']['country'] == "USA") && !isset($address['billing']['region_id'])) {
                $regionModel                 = Mage::getModel('directory/region')->loadByCode($address['billing']['region'], $address['billing']['country']);
                $regionId                    = $regionModel->getId();
                $billingAddress["region_id"] = $regionId;
            }
            if (($address['shipping']['country'] == "US" || $address['shipping']['country'] == "USA") && !isset($address['shipping']['region_id'])) {
                $regionModelShipping          = Mage::getModel('directory/region')->loadByCode($address['shipping']['region'], $address['shipping']['country']);
                $regionIdShipp                = $regionModelShipping->getId();
                $shippingAddress["region_id"] = $regionIdShipp;
            }
            
            $quote = Mage::getModel('sales/quote');
            if($quoteId)
				$quote->loadActive($quoteId);
			if(!$quote->getId())
			{
				$msg = 'Unable to fetch the Cart. It seems the cart is already used.'; 
				throw new Exception($msg);
			}
			
            $addressForm = Mage::getModel('customer/form');
            $addressForm->setFormCode('customer_address_edit')->setEntityType('customer_address');
            foreach ($addressForm->getAttributes() as $attribute) {
                if (isset($shippingAddress[$attribute->getAttributeCode()])) {
                    $quote->getShippingAddress()->setData($attribute->getAttributeCode(), $shippingAddress[$attribute->getAttributeCode()]);
                }
            }
            foreach ($addressForm->getAttributes() as $attribute) {
                if (isset($billingAddress[$attribute->getAttributeCode()])) {
                    $quote->getBillingAddress()->setData($attribute->getAttributeCode(), $billingAddress[$attribute->getAttributeCode()]);
                }
            }
            $quote->setBaseCurrencyCode($basecurrencycode);
            $quote->setQuoteCurrencyCode($currency);
            if ($find_shipping) {
                $quote->getShippingAddress()->setCollectShippingRates(true);
                $quote->save()->setTotalsCollectedFlag(false)->collectTotals();
            } else {
                $quote->getShippingAddress()->setShippingMethod($shipping_code)->setCollectShippingRates(true);
            }

            if($defaultShipMethod){

            	$quote->getShippingAddress()->setShippingMethod($defaultShipMethod)->setCollectShippingRates(true);
            	
            }

            //Check if applied for coupon
            if (!empty($couponCode)) {
                $quote->setCouponCode($couponCode);
                $coupon_status = 1;
            } else {
                $coupon_status = 0;
            }
            //$quote->collectTotals()->save();
            //$totals = $quote->getTotals();
            try {
                $test                = $quote->getShippingAddress();
                $shipping_tax_amount = number_format(Mage::helper('directory')->currencyConvert($test['shipping_tax_amount'], $basecurrencycode, $currency), 2, ".", "");
            }
            catch (Exception $ex) {
                $shipping_tax_amount = 0;
            }
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
            
            // =============================== Code for giftcard and giftcredit implementation ================================ //
            
            $res['giftcodeReturn'] = [];
            $res['giftcredit'] = [];
            if($giftcredit && Mage::getStoreConfig('giftvoucher/general/active', $store) && Mage::getStoreConfig('giftvoucher/general/enablecredit', $store))
            {
				$session->setUseGiftCardCredit(1);
				$session->setMaxCreditUsed(floatval($giftcredit));
			}
            if($giftcode && Mage::getStoreConfig('giftvoucher/general/active', $store))
            {
				$giftcodeReturn = $this->ws_applyGiftCard($store,$custid,$session,$quote,$giftcode);
			}
			$quote->setTotalsCollectedFlag(false)->collectTotals()->save(); 
			$totals = $quote->getTotals();

			if($giftcode && Mage::getStoreConfig('giftvoucher/general/active', $store))
            {
				if($giftcodeReturn['status'] == 'success')
				{
					$totalKey = Mage::getStoreConfig('giftvoucher/general/apply_after_tax', $store);
					if($totalKey)
						$totalLabel = 'giftvoucher_after_tax';
					else
						$totalLabel = 'giftvoucher';
					if(isset($totals[$totalLabel]))
					{
						$codesArray = explode(',',$totals[$totalLabel]->getGiftCodes());
						if($codesArray)
						{
							$codesDiscountArray = explode(',',$totals[$totalLabel]->getCodesDiscount());
							$discounts = array_combine($codesArray,$codesDiscountArray);
							$giftcodeCount = 0;
							foreach($discounts as $code => $value)
							{
								$res['giftcodeReturn']['giftcodes'][$giftcodeCount]['code']	=	Mage::helper('giftvoucher')->getHiddenCode($code);
								$value = -$value;
								$res['giftcodeReturn']['giftcodes'][$giftcodeCount]['value']= 	number_format($this->convert_currency($value, $basecurrencycode, $currency), 2, '.', '');
								$giftcodeCount++;
							}
							$res['giftcodeReturn']['status'] = 'success';
							$res['giftcodeReturn']['title'] = $totals[$totalLabel]->getTitle();
							$res['giftcodeReturn']['value'] = number_format($this->convert_currency($totals[$totalLabel]->getValue(), $basecurrencycode, $currency), 2, '.', '');
						}
						else
						{
							$res['giftcodeReturn']['status']	= 'success';
							$res['giftcodeReturn']['title'] 	= '';
							$res['giftcodeReturn']['value'] 	= '';
							$res['giftcodeReturn']['giftcodes']	= [];
						}
					}
				}
				else
				{
					$res['giftcodeReturn']['status'] = 'error';
					$res['giftcodeReturn']['status'] = $giftcodeReturn['message'];
				}
			}

			if($giftcredit && Mage::getStoreConfig('giftvoucher/general/active', $store) && Mage::getStoreConfig('giftvoucher/general/enablecredit', $store))
			{
				$totalKey = Mage::getStoreConfig('giftvoucher/general/apply_after_tax', $store);
				if($totalKey)
					$totalLabel = 'giftcardcredit_after_tax';
				else
					$totalLabel = 'giftcardcredit';
				if(isset($totals[$totalLabel]))
				{
					$res['giftcredit']['title'] = $totals[$totalLabel]->getTitle();
					$res['giftcredit']['value'] = number_format($this->convert_currency($totals[$totalLabel]->getValue(), $basecurrencycode, $currency), 2, '.', '');
				}
			}
			
			if(empty($res['giftcodeReturn']))
				$res['giftcodeReturn']	= (object)$res['giftcodeReturn']; 
			
			if(empty($res['giftcredit']))
				$res['giftcredit']	= (object)$res['giftcredit'];
            
            // =================================================================================================================//
            
            
            $dis = 0;
            //Find Applied Tax
            if (isset($totals['tax']) && $totals['tax']->getValue()) {
                $tax_amount = number_format(Mage::helper('directory')->currencyConvert($totals['tax']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            } else {
                $tax_amount = '0';
            }
            if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
                $shipping_amount = number_format(Mage::helper('directory')->currencyConvert($totals['shipping']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            } else {
                $shipping_amount = 0;
            }
            if ($shipping_tax_amount) {
                $shipping_amount += $shipping_tax_amount;
            }
            //Find Applied Discount
            if (isset($totals['discount']) && $totals['discount']->getValue() && $quote->getCouponCode()) {
                $coupon_status   = 1;
                $coupon_discount = number_format(Mage::helper('directory')->currencyConvert($totals['discount']->getValue(), $basecurrencycode, $currency), 2, ".", "");
				$coupon_code = $quote->getCouponCode();
            } else {
                $coupon_discount = '0';
                $coupon_status   = 0;
                $coupon_code = '';
            }
            
            $res['additional_fees'] = $this->getQuoteMultifeesTotal($store,$quote,$currency);            
            
            if ($is_create_quote == 1) {
                $quote->save();
                $res["quote_id"] = $quote->getId();
            }
            
            if($quote->getHasError())
            {
				$quoteErrorsList = '';
				$quoteErrors = $quote->getErrors();
				foreach($quoteErrors as $quoteError)
				{
					$quoteErrorsList .= $quoteError->getCode().' '; 
				}
				throw new Exception($quoteErrorsList);
			}
            
            $quoteData              = $quote->getData();
            $dis                    = $quoteData['grand_total'];
            $grandTotal             = number_format(Mage::helper('directory')->currencyConvert($totals['grand_total']->getValue(), $basecurrencycode, $currency), 2, ".", "");
            $res["coupon_discount"] = $coupon_discount;
            $res["coupon_status"]   = $coupon_status;
            $res["coupon_code"]   	= $coupon_code;
            $res["tax_amount"]      = $tax_amount;
            $res["total_amount"]    = $grandTotal;
            $res["currency"]        = $currency;
            $res["status"]          = "success";
            $res["shipping_amount"] = $shipping_amount;
            $res["shipping_method"] = $shipping_methods;
            
            return $res;
        }
        catch (Exception $ex) {
			$session->unsetAll();
			$customerSession->unsetAll();
            $res["coupon_discount"] = '0';
            $res["coupon_status"]   = 0;
            $res["tax_amount"]      = '0';
            $res["total_amount"]    = '0';
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
    
    public function getProductStockNew($product_id)
    {
        $stock_data    = array();
        $config_manage_stock = Mage::getStoreConfig('cataloginventory/item_options/manage_stock');
        $config_max_sale_qty = Mage::getStoreConfig('cataloginventory/item_options/max_sale_qty');
        $product_stock = Mage::getModel('cataloginventory/stock_item')->loadByProduct($product_id);
        $stock_data    = $product_stock->getData();
        $product_stock_quantity = '';
        try
        { 
                  if($product_stock['use_config_manage_stock']==0)
                  {
                   if($product_stock['manage_stock']==0)
                        {
                         if($product_stock['use_config_max_sale_qty']==0)
                         { 
                          $product_final_stockk = $product_stock['max_sale_qty'];
                          if(!$product_stock['max_sale_qty'])
                                 $product_final_stockk = '10000'; 
                          $product_stock_quantity = $product_final_stockk;
                         } 
                         else 
                         {
                          if(!$config_max_sale_qty)
                                 $config_max_sale_qty = '10000'; 
                          $product_stock_quantity = $config_max_sale_qty;
                         }
                        }
                        else
                        {
                         $product_stock_quantity = $product_stock['qty'];
                        }
                  }
                  else
                  {
                   $stock_data['manage_stock'] = $config_manage_stock;
                   if($config_manage_stock==0)
                   {
                        if(!$config_max_sale_qty)
                           $config_max_sale_qty = '10000'; 
                        $product_stock_quantity = $config_max_sale_qty; 
                   } 
                   else 
                   {
                        $product_stock_quantity = $product_stock['qty'];
                   }
                  }
        }
        catch(Exception $ex)
        {
                   
        }
        if($product_stock_quantity)
			$stock_data['qty'] = $product_stock_quantity; 
        return $stock_data;
    }
    
    public function setQuoteGiftMessage($quote, $message, $custid = null)
    {
        $message_id = array();
        $message    = json_decode(base64_decode($message), true);
        if($message)
        {
			//foreach ($message as $key => $value) {
            $giftMessage = Mage::getModel('giftmessage/message');
            if($custid)
				$giftMessage->setCustomerId($custid);
            $giftMessage->setSender($message["sender"]);
            $giftMessage->setRecipient($message["receiver"]);
            $giftMessage->setMessage($message["message"]);
            $giftObj                 = $giftMessage->save();
            $message_id["msg_id"][]  = $giftObj->getId();
            $message_id["prod_id"][] = $value["product_id"];
            $quote->setGiftMessageId($giftObj->getId());
            $quote->save();
			//}
        }
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
                //get total quantity
                $stockItem = Mage::getModel('cataloginventory/stock_item')->loadByProduct($productId);
                $totalqty  = (int) $stockItem->getQty();
                //calculate new quantity
                $newqty    = $totalqty - $orderQty;
                //update new quantity
                try {
						if ($stockItem->getId() > 0 and $stockItem->getManageStock()) {
							$stockItem->setQty($newqty);
							$stockItem->setIsInStock((int)($newqty > 0));
							$stockItem->save();
						}
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
    
    public function placeorder($custid, $Jproduct, $store, $address, $couponCode, $is_create_quote, $transid, $payment_code, $shipping_code, $currency, $message, $theme = null, $quoteId = null, $giftcode = null,$giftcredit = null,$platform = null)
    {
        $res            = array();
        $quantity_error = array();
        try {
            $quote_data = $this->prepareQuote($custid, $Jproduct, $store, $address, $shipping_code, $couponCode, $currency, 1, 0, null, $quoteId, $giftcode,$giftcredit);
            
            if ($quote_data["status"] == "error") {
				$quote_data["status"] = 0;
                return $quote_data;
            }
            $quote        = Mage::getModel('sales/quote')->load($quote_data['quote_id']);
            
            if($message)
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
            catch (Exception $e) {
                throw $e;
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
                throw $e;
            }
            $order->setBillingAddress($convertQuote->addressToOrderAddress($quote->getBillingAddress()));
            if (!$quote->getIsVirtual()) {
                $order->setShippingAddress($convertQuote->addressToOrderAddress($quote->getShippingAddress()));
            }
            Mage::dispatchEvent('sales_model_service_quote_submit_before', array('order'=>$order, 'quote'=>$quote));
            
            //----------------- Applying the gift codes to the order ------------------//
            try {
                 $session = Mage::getSingleton('checkout/session');
                 if($giftcode)
                 {
                  $giftcodes = json_decode(base64_decode($giftcode),true);
                  $codecount = 0;
                  $codes = [];
                  foreach($giftcodes as $code)
                  {
                   $codes[$codecount] = $code['gift_code'];
                   $codecount++;
                  }
                  $order->setGiftCodes(implode(',', $codes));
                  $order->setBaseGiftVoucherDiscount($session->getBaseGiftVoucherDiscount());
                  $order->setGiftVoucherDiscount($session->getGiftVoucherDiscount());
                  $order->setCodesBaseDiscount($session->getCodesBaseDiscount());
                  $order->setCodesDiscount($session->getCodesDiscount());
                 }
                 if($giftcredit)
                 {
					$basecurrencycode = Mage::app()->getStore($store)->getBaseCurrencyCode();
					$order->setGiftcardCreditAmount($giftcredit);
					$order->setBaseUseGiftCreditAmount($this->convert_currency($giftcredit,$currency,$basecurrencycode));
					$order->setUseGiftCreditAmount($giftcredit);
				 }
            }
            catch (Exception $e) {
            	  throw new Exception($e->getMessage());
            }

            try 
            {
				if($payment_code == Mage::getSingleton('authnetcim/method')->getCode())
				{
					//$payData = 'eyJtZXRob2QiOiJhdXRobmV0Y2ltIiwiY2FyZF9pZCI6IiIsImNjX3R5cGUiOiJWSSIsImNjX251bWJlciI6IjQxMTExMTExMTExMTExMTEiLCJjY19leHBfbW9udGgiOiIxIiwiY2NfZXhwX3llYXIiOiIyMDIwIiwiY2NfY2lkIjoiMzIxIiwic2F2ZSI6IjEifQ==';
					$this->ws_orderAuthorizePayment($quote);
					$transaction = Mage::getModel('core/resource_transaction');
					if ($quote->getCustomerId()) {
						$transaction->addObject($quote->getCustomer());
					}
					$transaction->addObject($quote);
					$order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
					$order->setQuote($quote);
					$transaction->addObject($order);
					$transaction->addCommitCallback(array($order, 'place'));
					$transaction->addCommitCallback(array($order, 'save'));
					try 
					{
						$transaction->save();
					} catch (Exception $e) {
						//reset order ID's on exception, because order not saved
						$order->setId(null);
						/** @var $item Mage_Sales_Model_Order_Item */
						foreach ($order->getItemsCollection() as $item) {
							$item->setOrderId(null);
							$item->setItemId(null);
						}
						throw $e;
					}
				}
				else
				{
					$order->setPayment($convertQuote->paymentToOrderPayment($quote->getPayment()));
				}
				$order->save();
			}
			catch(Exception $e)
			{
				Mage::dispatchEvent('sales_model_service_quote_submit_failure', array('order'=>$order, 'quote'=>$quote));
				throw $e;
			}
			try
			{
				$quote->setIsActive(false);
				$quote->save();
				Mage::dispatchEvent('sales_model_service_quote_submit_success', array('order'=>$order, 'quote'=>$quote));
				Mage::dispatchEvent('sales_model_service_quote_submit_after', array('order'=>$order, 'quote'=>$quote));
				//------------------- Firing Event in case of giftcodes is used and clearing the session in all cases. -------------------//
				Mage::dispatchEvent('sales_order_place_after', array('order'=>$order));
				Mage::getSingleton('checkout/session')->clear();
				//----------------------------------------------------------------------------------------------------------//
				$res["status"]          = 1;
				$res["id"]              = $order->getId();
				$res["orderid"]         = $order->getIncrementId();
				$res["transid"]         = $order->getPayment()->getTransactionId();
				$res["shipping_method"] = $shipping_code;
				$res["payment_method"]  = $payment_code;
				$res["quantity_error"]  = $quantity_error;
				if(!$platform)
					$platform = ' ';
				else
					$platform = ' '.$platform.' ';
				$orderMessage = "Order was placed using".$platform."Mobile App";
				$order->addStatusHistoryComment($orderMessage)->setIsVisibleOnFront(false)->setIsCustomerNotified(false);
				if ($res["orderid"] > 0 && ($payment_code == "cashondelivery" || $payment_code == "banktransfer" || $payment_code == "free")) {
					$this->ws_sendorderemail($res["orderid"]);
					$order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
					$res["order_status"] = "PROCESSING";
				} else {
					$res["order_status"] = $order->getStatusLabel();
					if($payment_code != Mage::getSingleton('authnetcim/method')->getCode())
					{
						$order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true);
						$res["order_status"] = "PENDING_PAYMENT";
					}
					$order->save();
					$order->sendNewOrderEmail();
					$order->setEmailSent(true);
				}
			}
			catch(Exception $e)
			{
			}
        }
        catch(Exception $except) {
            $res["status"]          = 0;
            $res["shipping_method"] = $shipping_code;
            $res["payment_method"]  = $payment_code;
            $res["message"] = $except->getMessage();
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
                //echo $ex->getMessage(); 
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
        // echo"<pre>"; print_r($page_data);
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
            $storeConfig = 'payment/'.$payvalue["payment_method_order_code"].'/active';
            if($mofluid_pg_code == 'authnetcim' && !Mage::getStoreConfig($storeConfig,$store)) {
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
                    echo $ex->getMessage();
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
            echo $ex->getMessage();
        }
       if($enable){ 
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY); 
		}
        return $res;
    }
    
    /* =======================get all mofluid app states===================== */
    
    public function ws_mofluidappstates($mofluid_store, $countryid)
    {
        
        $cacheEnable = Mage::getModel('mofluid_mofluidcache/mofluidcache')->load(25);
		$enable = $cacheEnable->getData('mofluid_cs_status');
		if($enable){
			$cache     = Mage::app()->getCache();
			$cache_key = "mofluid_states_store" . $mofluid_store . "_countryid" . $countryid;
			if($cache->load($cache_key))
			return json_decode($cache->load($cache_key));
		}
        
        $res = array();
        try {
            $collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($countryid)->load();
            /* =============== Custom Code For disabled Regions ================= */
			$disabledRegions = [];
			$modEnabled = Mage::helper('core')->isModuleEnabled('Eltrino_Region');
			if($modEnabled)
			{
				$disabledRegionsCollection = Mage::getResourceModel('eltrino_region/entity_collection')->addFieldToFilter('country_id',$countryid)->load();
				foreach ($disabledRegionsCollection as $item) {
					$disabledRegions[] = $item->getRegionId();
				}
			}
			/* ================================================================== */
            foreach ($collection as $region) {
				if(!in_array($region->region_id,$disabledRegions))
				{
					$mofluid_region["region_id"]   = $region->region_id;
					$mofluid_region["region_name"] = $region->default_name;
					$mofluid_region["region_code"] = $region->code;
					$res["mofluid_regions"][]      = $mofluid_region;
				}
            }
            return $res;
        }
        catch (Exception $ex) {
            
        }
         if($enable){ 
			$cache->save(json_encode($res), $cache_key, array("mofluid"), $this->CACHE_EXPIRY); 
		}
        return $res;
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
        
        echo $mofluid_ebs_form;
       
    }
    
    /* ===========================================mofluid eb response===================== */
    
    public function ws_mofluid_ebs_pgresponse()
    {
  
ini_set('display_errors',1);
error_reporting(E_ALL);

$secret_key = "ad1f341c42805bb3c0324ef859170ba6";	 // Pass Your Registered Secret Key from EBS secure Portal
if(isset($_REQUEST)){
	 $response = $_REQUEST;
	
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
		 //~ echo "<center><h3>Hash validation Failed!</H3></center>";
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
					var response = '<?php echo $response ?>' ;
					androidInterfaceCallback(response);
                </script>
        
<?php

		//~ foreach( $response as $key => $value) {
		
?>		
		<tr>
            <td class="fieldName" width="50%">ResponseMessage</td>
            <td class="fieldName" align="left" width="50%"><?php echo $response['ResponseMessage']; ?></td>
        </tr>	
        <tr>
            <td class="fieldName" width="50%">Amount</td>
            <td class="fieldName" align="left" width="50%"><?php echo $response['Amount']; ?></td>
        </tr>
         <tr>
            <td class="fieldName" width="50%">ResponseCode</td>
            <td class="fieldName" align="left" width="50%"><?php echo $response['ResponseCode']; ?></td>
        </tr>
        <tr>
            <td class="fieldName" width="50%">TransactionID</td>
            <td class="fieldName" align="left" width="50%"><?php echo $response['TransactionID']; ?></td>
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
            
            
            echo '<html>
							 <head>
								 <title>Success</title>
								 <meta name="viewport" content="width = 100%" />
								 <meta name="viewport" content="initial-scale=2.5, user-scalable=no" />
							 </head>
							 <body>
								 <center>
									 <h3>Thank you for your order.</h3>
								 </center>';
            
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
            echo '<center>' . $dis_body . '</center></body></html>';
        } else if ($mofluidpayaction == "ipn") {
            $this->validate_ipn($paypal_url, $postdata);
        } else if ($mofluidpayaction == "cancel") {
            echo "<html><head><title>Canceled</title></head><body><center><h3>The order was canceled.</h3></center>";
            echo "<br><br><br><center>Please Close this window</center></body></html>";
        } else {
            echo "<br>Unknown Response<br>";
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
        
        if (eregi("VERIFIED", $ipn_response)) {
            return true;
        } else {
            $last_error = 'IPN Validation Failed.';
            return false;
        }
    }
    
    public function subscribeNewsletter($user_mail)
    {
        $subscriber = Mage::getModel(‘newsletter / subscriber’);
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
                            echo 'Error : ' . $err->getMessage();
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
                //echo count($attributes);
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
                //echo "<pre>"; print_r(json_encode($configurable_array_selection)); die;
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
                            echo 'Error : ' . $err->getMessage();
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
                //echo count($attributes);
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
              // echo "<pre>"; print_r($configurable_array_selection); die;
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
                        echo 'Error : ' . $err->getMessage();
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
                //echo count($attributes);
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
                //echo "<pre>"; print_r(json_encode($configurable_array_selection)); die;
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
        'yellowgreen'=>'9ACD32'); 
        foreach ($productAttributeOptions as $productAttribute) {
            $count = 0;
           // print_r($productAttribute); die
            foreach ($productAttribute['values'] as $attribute) {
				$cname = strtolower($attribute['label']);
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
        
        return ($attributeOptions[$label][$selectedValue]);
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
            $res[$countres]["sku"]                   = $current_product->getSku();
            $res[$countres]["name"]                  = $this->getNamePrefix($current_product).$current_product->getName();
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
                    $specialprice = 0;
                }
            } else {
                $specialprice = 0;
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
        //echo "<br / ><pre>"; print_r($res); die;      
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
                        '1' => $billAdd->billstreet2
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
                        '1' => $shippAdd->shippstreet2
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
      if(empty($type)){
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
      if(in_array($type, array('product', 'category')) && empty($legacy_id)){
        $result['message'] = 'Id is required.';
        return $result;
      }
      // Case: if type is not banner then legacy_id must be integer
      if(in_array($type, array('product', 'category')) && !is_numeric($legacy_id)){
        $result['message'] = 'Id must be integer.';
        return $result;
      }
      // Case: width is required
      if(empty($width)){
        $result['message'] = 'Width is required.';
        return $result;
      }
      // Case: width must be integer
      if(!is_numeric($width)){
        $result['message'] = 'Width must be integer.';
        return $result;
      }
      // Case: height is required
      if(empty($height)){
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
        if(empty($theme)){
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
      if(empty($pid)){
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
        if (empty($purchasedIds)) {
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
        $res                = array();
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
        
        //echo $collection->getSelect();die;
        /* Apply filter action */
       //$filterdata =  '[{"code":"gender","id":"93,94"},{"code":"price","id":"220-230,250-280"},{"code":"material","id":"130"}]';
       $filterdata2 = json_decode($filterdata, true);
		 // print_r($filterdata2); die;
		//~ echo '<hr>';
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
		//echo $collection->getSelect();die;
		$res["category_name"]=$category->getName();
		$res["total"] = count($collection);

        /* END */
		 //~ $collection->addAttributeToSort($sortType, $sortOrder);
         //~ $collection->setPage($curr_page, $page_size);
        
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
				//echo $_product->getTypeInstance(); die;
				$parentIds = Mage::getResourceSingleton('catalog/product_type_configurable')
                                             ->getParentIdsByChild($_product->getId());
                                             
                 $product = Mage::getModel('catalog/product')->load($parentIds[0]);
                 echo "<pre>";
                 print_r($parentIds); die;                           
				
		     }*/
            else
            {
            	 $defaultprice =  number_format($_product->getPrice(), 2, '.', '');
           		 $specialprice =  number_format($_product->getFinalPrice(), 2, '.', '');
            }
            
           
            if($defaultprice == $specialprice)
                $specialprice = number_format(0, 2, '.', '');
           
           
           if($gflag)
           {
			   
			$ratingValue = '';
            $formatValue = '';
            if(isset($_product['soko_rating_value']))
               $ratingValue = $_product->soko_rating_value;
            if(isset($_product['soko_format_value']))
               $formatValue = $_product->soko_format_value;
			   
            $res["data"][] = array(
                "id" => $_product->getId(),
                "name" => $this->getNamePrefix($_product).$_product->getName(),
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
                "soko_rating_value" => $ratingValue,
                "soko_format_value" => $formatValue,
                "description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) : ''
            );
            }
            
        }
        return ($res);
    }
    
    /*--------------- cart sync webservice start---------------------- */
	
	/*========================== Api: AddCartItem Api ============================*/
    public function ws_addCartItem($store, $customerId = null, $quoteId = null, $products, $currency)
    {
		$res = [];
		try
		{
			Mage::app()->setCurrentStore($store);
			$currentUrl = explode('?',Mage::helper('core/url')->getCurrentUrl());
			$products = str_replace(" ", "+", $products);
			$quoteProduct = json_decode(base64_decode($products),true);
			if($quoteProduct && isset($quoteProduct['id']) && is_numeric($quoteProduct['id']))
			{
				$quote = Mage::getModel('sales/quote');
				if($customerId)
					$quote = $quote->loadByCustomer($customerId);
				else
				{
					if($quoteId)
						$quote = $quote->loadActive($quoteId);
				}
				if(!$quote->getId())
				{
					//$quote = Mage::getModel('sales/quote');
					$customer = Mage::getModel('customer/customer')->load($customerId);
					$quote->assignCustomer($customer);
					$quote->setStore(Mage::app()->getStore($store));
				}
				$product = Mage::getModel('catalog/product')->load($quoteProduct['id']); 
				$requestInfo = array(
								'uenc' => base64_encode($currentUrl[0]),
								'product' => $quoteProduct['id']
				);
				
				if(isset($quoteProduct['quantity']))
					$requestInfo['qty'] = $quoteProduct['quantity'];
				
				if(isset($quoteProduct['options']) && $quoteProduct['options'])
					$requestInfo['options'] = $quoteProduct['options'];
				
				if($quoteProduct['type'] == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
				{
					$requestInfo['super_attribute'] = $quoteProduct['super_attribute'];
				}
				else if ($quoteProduct['type'] == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE)
				{
					$requestInfo['links'] = $quoteProduct['links'];
				}
				else if ($quoteProduct['type'] == Mage_Catalog_Model_Product_Type::TYPE_GROUPED)
				{
					$requestInfo['super_group'] = $quoteProduct['super_group'];
					if(array_key_exists('qty',$requestInfo))
						unset($requestInfo['qty']);
				}
				$itemadded = $quote->addProduct(
								$product,
								new Varien_Object($requestInfo)
				);
				if(is_string($itemadded))
				{
					throw new Exception($itemadded);
				}
				if ($remoteAddr = Mage::helper('core/http')->getRemoteAddr())
					$quote->setRemoteIp($remoteAddr);
				$quote->getBillingAddress();
				$quote->getShippingAddress()->setCollectShippingRates(true);
				$quote->setTotalsCollectedFlag(false)->collectTotals();
				$quote->save();
				$res['status']="success";
				$res['quoteId'] = $quote->getId();
				$res['products_added']['productid'] = $quoteProduct['id'];
				$res['products_added']['type'] = $quoteProduct['type'];
				$res["cart_items_count"] = $quote->getItemsCount();
				$res["cart_items_qty"] = $quote->getItemsQty();
			}
			else 
			{
				$res['status'] = "error";
				$res['message'] = "Invalid product data sent for Cart Addition";
			}  
	    }
	    catch(Exception $e)
	    {
		 $res['status'] = "error";
		 $res['message'] = $e->getMessage();
		}
        return $res;
    }
    /*================================================================================*/
    
    /*============================ Api: getAllCartItems Api ==========================*/
    public function ws_getAllCartItems($store, $customerId = null, $quoteId = null, $currentCurrencyCode, $updateCartMessages = null)
    {
		$res = [];$products = [];$cartMessages = [];$count = $cartMsgCount = 0;
		$enableCheckout = false;
		$res["cart_items_count"] = (string)$count;
		$res["cart_items_qty"] = (string)$count;
		$res["cart_total_amount"] = (string)$count;
		$res["quote_id"] = '';
		try
		{
			Mage::app()->setCurrentStore($store);
			$quote = Mage::getModel('sales/quote');
			if($customerId)
				$quote = $quote->loadByCustomer($customerId);
			else
			{
				if($quoteId)
					$quote = $quote->loadActive($quoteId);
			}
			if($quote->getId())
			{
				$basecurrencycode   = Mage::app()->getStore($store)->getBaseCurrencyCode();
				$tax_flag = Mage::getStoreConfig('tax/cart_display/price',$store);
				switch($tax_flag)
				{
					case 2: $showPrice = 'Including Tax';
					break;
					case 3: $showPrice = 'Both Including and Excluding Tax';
					break;
					default: $showPrice = 'Excluding Tax';
				}
				$items = $quote->getAllVisibleItems();
				if($items)
				{
					$enableCheckout = true;
					foreach($items as $item)
					{
						$optionsArr = [];$messages = [];$options = null;$optionCount = $msgCount = 0;
						$_product = Mage::getModel('catalog/product')->load($item->getProductId());
						$products[$count]['cart_item_id'] = $item->getItemId();
						$products[$count]['quantity'] = $item->getQty();
						$products[$count]['price'] = number_format($this->convert_currency($item->getPrice(), $basecurrencycode, $currentCurrencyCode), 2, '.', '');
						$products[$count]['item_price_including_tax'] = number_format($this->convert_currency($item->getPriceInclTax(), $basecurrencycode, $currentCurrencyCode), 2, '.', '');
						$products[$count]['item_row_total'] = number_format($this->convert_currency($item->getRowTotal(), $basecurrencycode, $currentCurrencyCode), 2, '.', '');
						$products[$count]['item_row_total_including_tax'] = number_format($this->convert_currency($item->getRowTotalInclTax(), $basecurrencycode, $currentCurrencyCode), 2, '.', '');
						$products[$count]['type'] = $item->getProductType();
						$products[$count]['sku'] = $item->getSku();
						$products[$count]['name'] = $this->getNamePrefix($_product).$item->getName();
						$products[$count]['id'] = $item->getProductId();
						$products[$count]['img'] = (string)Mage::helper('catalog/image')->init($_product,'thumbnail')->resize(200,200);
						$products[$count]['spclprice'] = 0;		//Custom key added by app frontend team for feasiblity of model in app.
						$products[$count]['is_in_stock'] = 1;	//Custom key added by app frontend team for feasiblity of model in app.
						$products[$count]['imageurl'] = '';		//Custom key added by app frontend team for feasiblity of model in app.
						$products[$count]['stock_quantity'] = 1;//Custom key added by app frontend team for feasiblity of model in app.
						$products[$count]['parentId'] = 0;		//Custom key added by app frontend team for feasiblity of model in app.
						$products[$count]['description'] = '';	//Custom key added by app frontend team for feasiblity of model in app.
					
					
						if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_CONFIGURABLE)
							$options = Mage::helper('catalog/product_configuration')->getConfigurableOptions($item);
						else if($item->getProductType() == Mage_Catalog_Model_Product_Type::TYPE_BUNDLE)
							$options = Mage::helper('bundle/catalog_product_configuration')->getOptions($item);
						else
							$options = Mage::helper('catalog/product_configuration')->getCustomOptions($item);
					
						if($options)
						{
							foreach($options as $_option)
							{
								$formatedOptionValue = Mage::helper('catalog/product_configuration')->getFormattedOptionValue($_option['value'], null);
								
								$optionsArr[$optionCount]['label']       = $_option['label'];
								$optionsArr[$optionCount]['value']       = strip_tags($formatedOptionValue['value']);
								if(isset($_option['option_type']) && $_option['option_type'])
									$optionsArr[$optionCount]['option_type'] = $_option['option_type'];
								else
									$optionsArr[$optionCount]['option_type'] = '';
								$optionCount++;
							}
						}
						
						if($item->getProductType() == Mage_Downloadable_Model_Product_Type::TYPE_DOWNLOADABLE)
						{
							if($itemLinks = Mage::helper('downloadable/catalog_product_configuration')->getLinks($item))
							{
								$itemLinksValue = '';
								$optionsArr[$optionCount]['label']       = Mage::helper('downloadable/catalog_product_configuration')->getLinksTitle($item->getProduct());
								foreach ($itemLinks as $itemLink)
								{
									$itemLinksValue .= strip_tags($itemLink->getTitle())."\n";
								}
								$itemLinksValue = rtrim($itemLinksValue,"\n");
								$optionsArr[$optionCount]['value']       = strip_tags($itemLinksValue);
								$optionsArr[$optionCount]['option_type'] = '';
								$optionCount++;
							}
						}
						
						$products[$count]['options'] = $optionsArr; 
					
						if($updateCartMessages)
						{
							if(array_key_exists($item->getItemId(),$updateCartMessages))
							{
								$messages[$msgCount]['text'] = $updateCartMessages[$item->getItemId()];
								$messages[$msgCount]['type'] = 'notice';
								$msgCount++;
							}
						}
						$baseMessages = $item->getMessage(false);
						if ($baseMessages) {
							foreach ($baseMessages as $message) {
								$messages[$msgCount]['text'] = $message;
								$messages[$msgCount]['type'] = $item->getHasError() ? 'error' : 'notice';
							}
						}
						$products[$count]['messages'] = $messages;
						
						if($item->getHasError())
							$enableCheckout = false;
						$count++;
					}
					$res["cart_items_count"] = $quote->getItemsCount();
					$res["cart_items_qty"] = (string)intval($quote->getItemsQty());
					$res["item_show_price_flag"] = $showPrice;
					$totals = $quote->getTotals();
					$res["cart_total_amount"] = number_format($this->convert_currency($totals["subtotal"]->getValue(), $basecurrencycode, $currentCurrencyCode), 2, '.', '');
				
					if($updateCartMessages)
					{
						if(array_key_exists('quote_message',$updateCartMessages))
						{
							$cartMessages[$cartMsgCount]['text'] = $updateCartMessages['quote_message'];
							$cartMessages[$cartMsgCount]['type'] = 'notice';
							$cartMsgCount++;
						}
					}
					$quoteMessages = $quote->getMessages();
					if ($quoteMessages) {
						foreach ($quoteMessages as $quoteMessage) {
							$cartMessages[$cartMsgCount]['text'] = $quoteMessage->getCode();
							$cartMessages[$cartMsgCount]['type'] = $quoteMessage->getType();
							if($quoteMessage->getType() == 'error')
								$enableCheckout = false;
							$cartMsgCount++;
						}
					}
					
					if (!$quote->validateMinimumAmount()) {
						$enableCheckout = false;
						$minimumAmount = Mage::app()->getLocale()->currency($currentCurrencyCode)
										->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount',$store));
						$cartMessages[$cartMsgCount]['text'] = Mage::getStoreConfig('sales/minimum_order/description',$store)
																? Mage::getStoreConfig('sales/minimum_order/description',$store)
																: 'Minimum order amount is '.$minimumAmount;
						$cartMessages[$cartMsgCount]['type'] = 'notice';
						$cartMsgCount++;
					}
					
				}
				$res["quote_id"] = $quote->getId();
				
			}
			$res['status'] = "success"; //if no quote or customer is provided in the api, we are bypassing it as success with zero products in cart.
		}
	    catch(Exception $e)
	    {
		  $res['status'] = "error";	
		  $res['message'] = $e->getMessage();
		}
		$res["cart_products"] = $products;
		$res["cart_messages"] = $cartMessages;
		$res["enable_checkout"] = $enableCheckout;
		return $res;
	}
    /*================================================================================*/
    
    /*========================== Api: removeCartItem Api =============================*/
    public function ws_removeCartItem($store,$customerId = null,$quoteId = null,$cartItemId,$removeAllFlag)
    {
		$res = [];
		try
		{
			Mage::app()->setCurrentStore($store);
			$quote = Mage::getModel('sales/quote');
			if($customerId)
				$quote = $quote->loadByCustomer($customerId);
			else
			{
				if($quoteId)
					$quote = $quote->loadActive($quoteId);
			}
			if($quote->getId())
			{
				if($removeAllFlag)
				{
					$hasItems = $quote->hasItems($quote);
					if($hasItems)
					{
						$quote->removeAllItems();
						$res['status'] = "success";
						$res['message'] = "Cart Cleared Successfully";
					}
					else
					{
						$res['status'] = "error";
						$res['message'] = "Cart is already empty";
					}
				}
				else
				{
					$isItemvalid = $quote->getItemById($cartItemId);
					if($isItemvalid)
					{
						$quote->removeItem($cartItemId);
						$res['status'] = "success";
						$res['message'] = "Selected Item Removed from Cart Successfully";
					}
					else
					{
						$res['status'] = "error";
						$res['message'] = "The Selected Product doesn't exist in Cart";
					}   
				}
				$quote->getBillingAddress();
				$quote->getShippingAddress()->setCollectShippingRates(true);
				$quote->setTotalsCollectedFlag(false)->collectTotals();
				$quote->save();
			}
			else
			{
				$res['status'] = "error";
				$res['message'] = "Cart could not be fetched for respected customer/quote.";
			}
	    }  
		catch(Exception $e)
	    {
		  $res['status'] = "error";
		  $res['message'] = $e->getMessage();
		}
        return $res;
    }
    /*================================================================================*/
    
    /*=========================== Api: updateCartItem Api ============================*/
    public function ws_updateCartItems($store,$customerId = null,$quoteId = null,$products,$currentCurrencyCode)  //basically used to update the qty of the cart item
    {
		$res = [];$messages = [];$qtyRecalculatedFlag = false;
		try
		{
			Mage::app()->setCurrentStore($store);
			$quote = Mage::getModel('sales/quote');
			if($customerId)
				$quote = $quote->loadByCustomer($customerId);
			else
			{
				if($quoteId)
					$quote = $quote->loadActive($quoteId);
			}
			if($quote->getId())
			{
				$products = str_replace(" ", "+", $products);
				$cartProducts = json_decode(base64_decode($products),true);
				if($cartProducts)
				{
					$updateData = $this->suggestItemsQty($cartProducts,$quote);
					foreach($updateData as $updateItemId => $updateItem)
					{
						$isItemvalid = $quote->getItemById($updateItemId);   //checking if the item id passed is correct or not
						if($isItemvalid) //Bypassing the item if it's not valid.
						{
							if((float)$updateItem['qty'] <= 0 )
							{
								$quote->removeItem($updateItemId);    //removing the product if quantity passed is less than or equal to zero
							}
							else 
							{
								$isItemvalid->setQty((float)$updateItem['qty']);
								//~ if ($isItemvalid->getHasError()) {
									//~ throw new Exception($isItemvalid->getMessage());
								//~ }
								if (isset($updateItem['before_suggest_qty']) && ($updateItem['before_suggest_qty'] != $updateItem['qty'])) {
									$qtyRecalculatedFlag = true;
									$messages[$updateItemId] = 'Quantity was recalculated from '.$updateItem['before_suggest_qty'].' to '.$updateItem['qty'];
								}
								
							}
						}
					}
					
					if ($qtyRecalculatedFlag) {
						$messages['quote_message'] = 'Some products quantities were recalculated because of quantity increment mismatch';
					}
					
					$quote->getBillingAddress();
					$quote->getShippingAddress()->setCollectShippingRates(true);
					$quote->setTotalsCollectedFlag(false)->collectTotals();
					$quote->save();
					$quoteId = $quote->getId();
					$returnGetAllItems = $this->ws_getAllCartItems($store, $customerId, $quoteId, $currentCurrencyCode, $messages);
					return $returnGetAllItems;
				}
				else 
				{
					$res['status'] = "error";
					$res['message'] = "Invalid product data sent for Cart Updation";
				}    
			}
			else
			{
				$res['status'] = "error";
				$res['message'] = "Cart could not be fetched for respected customer/quote.";
			}
		}
		catch(Exception $e)
		{
			$res['status'] = "error";
			$res['message'] = $e->getMessage();
		}
		return $res;
    }
    /*================================================================================*/
    
    public function suggestItemsQty($cartProducts,$quote)
    {
		$data = [];
		foreach ($cartProducts as $cartProduct) {
            if(isset($cartProduct['quantity']) && isset($cartProduct['cart_item_id']))
            {
				$itemId = $cartProduct['cart_item_id'];
				$data[$itemId]['qty'] = $cartProduct['quantity'];
			}
			else
				continue;
            $qty = (float) $cartProduct['quantity'];
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
	
	/*========================== Api: mergeCartItems Api ============================*/
    public function ws_mergeCartItems($store, $customerId = null, $quoteId = null, $currentCurrencyCode)
    {
		$res = [];
		try
		{
			Mage::app()->setCurrentStore($store);
			$quote = Mage::getModel('sales/quote');
			if($quoteId)
				$quote = $quote->loadActive($quoteId);
			
			if(!$quote->getId() && !$customerId)
			{
				$res['status'] = "error";
				$res['message'] = "The Cart could not be fetched for the respective Quote/Customer";
				return $res;
			}
			elseif(!$quote->getId() && $customerId)
			{
				$returnGetAllItems = $this->ws_getAllCartItems($store, $customerId, null, $currentCurrencyCode, null);
				return $returnGetAllItems;
			}
			elseif($quote->getId() && !$customerId)
			{
				$res['status'] = "error";
				$res['message'] = "The customer was not mentioned for the cart merging process.";
				return $res;
			}
			else
			{
				$customerQuote = Mage::getModel('sales/quote')->setStoreId($store)->loadByCustomer($customerId);
				if($customerQuote->getId() && $quote->getId() != $customerQuote->getId())
				{
					$customerQuote->merge($quote)
								  ->collectTotals()
								  ->save();
					$quote->delete();
					if($customerQuote->getId())
						$getAllCartItemsQuote = $customerQuote->getId();
					else
						$getAllCartItemsQuote = null;
					$returnGetAllItems = $this->ws_getAllCartItems($store, $customerId, $getAllCartItemsQuote, $currentCurrencyCode, null);
				}
				else
				{
					$customer = Mage::getModel('customer/customer')->load($customerId);
					$quote->getBillingAddress();
					$quote->getShippingAddress();
					$quote->setCustomer($customer)
						  ->setTotalsCollectedFlag(false)
						  ->collectTotals()
						  ->save();
					if($quote->getId())
						$getAllCartItemsQuote = $quote->getId();
					else
						$getAllCartItemsQuote = null;
					$returnGetAllItems = $this->ws_getAllCartItems($store, $customerId, $getAllCartItemsQuote, $currentCurrencyCode, null);
					
				}
				return $returnGetAllItems;
			}
			
		}
		catch(Exception $e)
		{
			$res['status'] = "error";
			$res['message'] = $e->getMessage();
		}
		return $res;
	}
	/*============================================================================*/
	
	public function getQuoteErrors($quote)
	{
		$cartMessages = [];$cartMsgCount = 0;
		$quoteMessages = $quote->getMessages();
		if ($quoteMessages) {
			foreach ($quoteMessages as $quoteMessage) {
				if($quoteMessage->getType() == 'error')
				{
					$cartMessages[$cartMsgCount]['text'] = $quoteMessage->getCode();
					$cartMessages[$cartMsgCount]['type'] = $quoteMessage->getType();
					$cartMsgCount++;
				}
			}
		}
		if($cartMessages)
			return $cartMessages;
		else
			return false;
	}

	/*------------------- cart sync webservice end ------------------- */
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
        //~ echo '<pre>';print_r($quote->getShippingAddress()->getData());die;
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
                if (isset($shippingAddress[$attribute->getAttributeCode()])) {
                    $quote->getShippingAddress()->setData($attribute->getAttributeCode(), $shippingAddress[$attribute->getAttributeCode()]);
                }
            }
            foreach ($addressForm->getAttributes() as $attribute) {
                if (isset($billingAddress[$attribute->getAttributeCode()])) {
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
		//~ echo '<pre>';print_r(($quote->getShippingAddress()->getData()));die;
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
		if (isset($totals['tax']) && $totals['tax']->getValue()) {
			$tax_amount = number_format(Mage::helper('directory')->currencyConvert($totals['tax']->getValue(), $basecurrencycode, $currency), 2, ".", "");
		} else {
			$tax_amount = 0;
		}
		if (isset($totals['shipping']) && $totals['shipping']->getValue()) {
			$shipping_amount = number_format(Mage::helper('directory')->currencyConvert($totals['shipping']->getValue(), $basecurrencycode, $currency), 2, ".", "");
		} else {
			$shipping_amount = 0;
		}
		if ($shipping_tax_amount) {
			$shipping_amount += $shipping_tax_amount;
		}
		
		//  Find Applied coupon
		if (isset($totals['discount']) && $totals['discount']->getValue()) {
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
    
// =============================== Autorelated Product Implementation on App ================================= //

	public function relatedProductBlock($store, $currentcurrencycode, $blockType, $customerId, $productId, $categoryId, $quoteId, $cartProducts = null, $wishlistProducts = null)
	{
		$res = [];$blocksArray = [];
		$blockCount = 0;
		Mage::app()->setCurrentStore($store);
        $basecurrencycode   = Mage::app()->getStore()->getBaseCurrencyCode();
		$res['status'] = 'success';
		$moduleEnabled = Mage::getStoreConfig('advanced/modules_disable_output/AW_Autorelated');
		if($moduleEnabled != '' && !$moduleEnabled)
		{
			try
			{
				if($customerId)
				{
					$customer = Mage::getModel('customer/customer')->load($customerId);
					$customerGroupId = $customer->getGroupId();
				}
				else
				{
					$customerGroupId = 0;
				}
				$blocksCollection = Mage::getModel('awautorelated/blocks')->getCollection();  //getting the block collection
				$blocksCollection->addStoreFilter()
							//->addPositionFilter($this->getBlockPosition())
							->addFieldToFilter('position', array('neq' => 0))
							->addStatusFilter()
							->addCustomerGroupFilter($customerGroupId)
							->addDateFilter()
							->setPriorityOrder();

				switch($blockType) //getting the block type value from block type name.
				{
					case "product"  : $blockTypeValue = 1;
					break;
					case "category" : $blockTypeValue = 2;
					break;
					case "cart"     : $blockTypeValue = 3;
					break;
					default         : $blockTypeValue = 0;
				}

				if($blockTypeValue){  //setting the block type value
					$blocksCollection->addTypeFilter($blockTypeValue);
				}
				foreach($blocksCollection as $block)
				{
					$blockProductCollection = [];
					if($blockTypeValue == 1)
					{
						$_canShow = null;
						if($productId)
						{
							$product = Mage::getModel('catalog/product')->load($productId);

							//$coll->callAfterLoad();
							$modelRuleView = Mage::getModel('awautorelated/blocks_product_ruleviewed');
							$modelRuleView->setWebsiteIds(Mage::getModel('core/store')->load($store)->getWebsiteId());
							$conditionsRuleView = $block->getCurrentlyViewed()->getConditions();
							if(isset($conditionsRuleView['viewed'])) {
								$modelRuleView->getConditions()->loadArray($conditionsRuleView, 'viewed');
								$matchRuleView = $modelRuleView->getMatchingProductIds();
								if (in_array($productId, $matchRuleView))
									$_canShow = true;
								else
									$_canShow = false;
							}
							else
								$_canShow = true;
						}
						else
						{
							$res['status'] = 'error';
							$res['message'] = 'No product id passed.'; 
						}
						if($_canShow)
						{
							// ------------------------------- init collection ---------------------------------//
							$this->_relatedInitCollection($block,$store);
							// ---------------------------------------------------------------------------------//
							
							// ------------------------ _renderRelatedProductsFilters --------------------------//
							$this->_renderRelatedProductsFilters($block, $store, $product, $cartProducts, $wishlistProducts);
							// ---------------------------------------------------------------------------------//

							// ----------------------------- _postProcessCollection ----------------------------//
							$this->_relatedPostProcessCollection($block, $store);
							// ---------------------------------------------------------------------------------//
							if($this->_relatedCollection && $this->_relatedCollection->getSize())
							{
								$blocksArray[$blockCount]['blockId'] = $block->getId();
								$blocksArray[$blockCount]['blockName'] = $block->getName();
								$blocksArray[$blockCount]['blockType'] = $blockType;
								$blocksArray[$blockCount]['replaceNativeRelatedBlock'] = ($block->position == 2) ? true : false;
								$blocksArray[$blockCount]['replaceCrossSellBlock'] = ($block->position == 5) ? true : false;
								$blocksArray[$blockCount]['blockPosition'] = ($block->position < 4) ? 'afterContent' : 'beforeContent';
								foreach($this->_relatedCollection as $blockProducts)
								{
									$blockProductCollection [] = $this->getRelatedProductData($blockProducts->getId(), $currentcurrencycode, $basecurrencycode);
								}
								$blocksArray[$blockCount]['blockProducts'] = $blockProductCollection;
								$blockCount++;
							}
						}
					}
					
					if($blockTypeValue == 2)
					{
						$_canShow = false;
						if($categoryId)
						{
							$currentCategory = Mage::getModel('catalog/category')->load($categoryId);
						
							$currentlyViewed = $block->getCurrentlyViewed();
							if ($currentlyViewed && $currentlyViewed instanceof Varien_Object && ($currentCategory || $block->position == AW_Autorelated_Model_Source_Position::CUSTOM))
							{
								if ($currentlyViewed->getData('area') == 1) {
									// Categories = ALL
									$_canShow = true;
								}

								if (!$currentCategory || !$categoryIds = $currentlyViewed->getData('category_ids')) {
									$_canShow = false;
								}

								// Block has category IDs
								if (is_string($categoryIds)) {
									$categoryIds = explode(',', $categoryIds);
								}

								if (is_array($categoryIds) && in_array($currentCategory->getId(), $categoryIds)) {
									$_canShow = true;
								}
							}
						}
						else
						{
							$res['status'] = 'error';
							$res['message'] = 'No category id passed.'; 
						}
						
						if($_canShow)
						{
							// ------------------------------- init collection ---------------------------------//
							$this->_relatedInitCollection($block,$store);
							// ---------------------------------------------------------------------------------//
							
							// ------------------------ _renderRelatedProductsFilters --------------------------//
							$this->_renderRelatedProductsFiltersCategory($block, $store, $currentCategory, $cartProducts, $wishlistProducts);
							// ---------------------------------------------------------------------------------//
							
							// ----------------------------- _postProcessCollection ----------------------------//
							$this->_relatedPostProcessCollection($block, $store);
							// ---------------------------------------------------------------------------------//
							if($this->_relatedCollection && $this->_relatedCollection->getSize())
							{
								$blocksArray[$blockCount]['blockId'] = $block->getId();
								$blocksArray[$blockCount]['blockName'] = $block->getName();
								$blocksArray[$blockCount]['blockType'] = $blockType;
								$blocksArray[$blockCount]['replaceNativeRelatedBlock'] = ($block->position == 2) ? true : false;
								$blocksArray[$blockCount]['replaceCrossSellBlock'] = ($block->position == 5) ? true : false;
								$blocksArray[$blockCount]['blockPosition'] = ($block->position == 4) ? 'afterContent' : 'beforeContent';
								foreach($this->_relatedCollection as $blockProducts)
								{
									$blockProductCollection [] = $this->getRelatedProductData($blockProducts->getId(), $currentcurrencycode, $basecurrencycode);
								}
								$blocksArray[$blockCount]['blockProducts'] = $blockProductCollection;
								$blockCount++;
							}
						
						}
					}
					
					if($blockTypeValue == 3)
					{
						$_canShow = false;
						
						if ($quoteId) {
							/** @var $quote Mage_Sales_Model_Quote */
							$quote = Mage::getModel('sales/quote');

							if (method_exists($quote, 'loadByIdWithoutStore')) {
								$quote->loadByIdWithoutStore($quoteId);
							} else {
								$quote->setStoreId($store)->load($quoteId);
							}

							if ($quote->getId()) {
								$checkoutCart = Mage::getModel('checkout/cart')->setQuote($quote);
							}
							
							foreach ($checkoutCart->getQuote()->getAllItems() as $item) {
								/** @var $item Mage_Sales_Model_Quote_Item */
								$item->getProduct()->load($item->getProductId());
							}
							$shoppingCart = $checkoutCart;
						
							if ($shoppingCart->getItemsCount()) {
								/** @var $model AW_Autorelated_Model_Blocks_Shoppingcart_Ruleviewed */
								$model = Mage::getModel('awautorelated/blocks_shoppingcart_ruleviewed');

								$conditions = ($block->getData('currently_viewed' . '/conditions')) ? $block->getData('currently_viewed' . '/conditions') : array();
								$model->setWebsiteIds(Mage::getModel('core/store')->load($store)->getWebsiteId());
								$model->getConditions()->loadArray($conditions, 'viewed');
								$quote = $shoppingCart->getQuote();
								$quote->setTotalsCollectedFlag(false)->collectTotals();
								if ($quote instanceof Mage_Sales_Model_Quote_Address_Item) {
									$address = $quote->getAddress();
								} elseif ($quote instanceof Mage_Sales_Model_Quote) {
									if ($quote->isVirtual()) {
										$address = $quote->getBillingAddress();
									} else {
										$address = $quote->getShippingAddress();
									}
								} elseif ($quote->getQuote()->isVirtual()) {
									$address = $quote->getQuote()->getBillingAddress();
								} else {
									$address = $quote->getQuote()->getShippingAddress();
								}
							
								$_canShow = $model->validate($address);
							} else {
								$_canShow = false;
							}
						}
						else
						{
							$res['status'] = 'error';
							$res['message'] = 'No quote id passed.'; 
						}
						if($_canShow)
						{
							
							// ------------------------------- init collection ---------------------------------//
							$this->_relatedInitCollection($block,$store);
							// ---------------------------------------------------------------------------------//
							
							// ------------------------ _renderRelatedProductsFilters --------------------------//
							$this->_renderRelatedProductsFiltersCart($block, $store, $shoppingCart);
							// ---------------------------------------------------------------------------------//

							// ----------------------------- _postProcessCollection ----------------------------//
							$this->_relatedPostProcessCollection($block, $store);
							// ---------------------------------------------------------------------------------//
							if($this->_relatedCollection && $this->_relatedCollection->getSize())
							{
								$blocksArray[$blockCount]['blockId'] = $block->getId();
								$blocksArray[$blockCount]['blockName'] = $block->getName();
								$blocksArray[$blockCount]['blockType'] = $blockType;
								$blocksArray[$blockCount]['replaceNativeRelatedBlock'] = ($block->position == 2) ? true : false;
								$blocksArray[$blockCount]['replaceCrossSellBlock'] = ($block->position == 5) ? true : false;
								$blocksArray[$blockCount]['blockPosition'] = ($block->position < 4) ? 'afterContent' : 'beforeContent';
								foreach($this->_relatedCollection as $blockProducts)
								{
									$blockProductCollection [] = $this->getRelatedProductData($blockProducts->getId(), $currentcurrencycode, $basecurrencycode);
								}
								$blocksArray[$blockCount]['blockProducts'] = $blockProductCollection;
								$blockCount++;
							}
							
						}
					}
				}
			}
			catch(Exception $ex)
			{
				$res['status'] = 'error';
				$res['message'] = $ex->getMessage();
			}
		}
		$res['total'] = $blockCount;
		$res['relatedBlocks'] = $blocksArray;
		return $res;
	}

	protected function _relatedInitCollection($block, $store)
	{
		$this->_relatedCollection = Mage::getModel('awautorelated/product_collection');
		$this->_relatedCollection->addAttributeToSelect('*');

		$_visibility = array(
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
			Mage_Catalog_Model_Product_Visibility::VISIBILITY_IN_CATALOG
		);

		$this->_relatedCollection->addAttributeToFilter('visibility', $_visibility)
					->addAttributeToFilter('status',
						array(
							'in' => Mage::getSingleton("catalog/product_status")->getVisibleStatusIds()
						));

		if (!$this->_getShowOutOfStock($block)) {
			Mage::getSingleton('cataloginventory/stock')->addInStockFilterToCollection($this->_relatedCollection);
			$this->_relatedCollection->getSelect()
					->join(
						array(
							'inv_stock_status' => $this->_relatedCollection->getTable('cataloginventory/stock_status')
							),'inv_stock_status.product_id = e.entity_id AND inv_stock_status.stock_status = 1',array()
						);
		}

		$this->_relatedCollection->addStoreFilter($store)
					->joinCategoriesByProduct()
					->groupByAttribute('entity_id'); 
	
		return $this->_relatedCollection;
	}

	protected function _renderRelatedProductsFilters($block, $store, $currentProduct, $cartProducts = null, $wishlistProducts = null)
    {
        $modelRuleRelated = Mage::getModel('awautorelated/blocks_product_rulerelated');
        $modelRuleRelated->setWebsiteIds(Mage::getModel('core/store')->load($store)->getWebsiteId());
        $conditionsRuleRelated = $block->getRelatedProducts()->getRelated();
        $mIds = array();
        $gCondition = $block->getRelatedProducts()->getGeneral();
        $limit = $block->getRelatedProducts()->getProductQty();

        if (isset($conditionsRuleRelated['conditions']['related'])) {
            $modelRuleRelated->getConditions()->loadArray($conditionsRuleRelated['conditions'], 'related');
            $mIds = $modelRuleRelated->getMatchingProductIds();

            if (empty($mIds)) {
                unset($this->_relatedCollection);
                return $this;
            } else {
                $mIds = array_diff($mIds, array($currentProduct->getId()));
            }
        }

        if (!empty($gCondition)) {
            $filteredIds = $this->filterByAtts($currentProduct, $gCondition, $mIds);
        } elseif (!empty($mIds)) {
            $filteredIds = $mIds;
        } else {
            $filteredIds = $this->_relatedCollection->getAllIds();
        }

        if (!empty($filteredIds)) {
            $filteredIds = array_diff($filteredIds, array($currentProduct->getId()));
            if($wishlistProducts)
            {
				$wishlistProductsArray = json_decode(base64_decode($wishlistProducts));
				$filteredIds = array_diff($filteredIds, $wishlistProductsArray);
            }
            if($cartProducts)
            {
				$cartProductsArray = json_decode(base64_decode($cartProducts));
				$filteredIds = array_diff($filteredIds, $cartProductsArray);
            }
            $filteredIds = array_intersect($filteredIds, $this->_relatedCollection->getAllIds());
            $itemsCount = count($filteredIds);
            if (!$itemsCount) {
                unset($this->_relatedCollection);
                return $this;
            }
            $this->_relatedInitCollectionForIds($filteredIds, true, $block, $store, $limit);
            $this->_relatedCollection->setPageSize($limit);
            $this->_relatedCollection->setCurPage(1);
        } else {
            unset($this->_relatedCollection);
        }
        return $this;
    }
    
    protected function _renderRelatedProductsFiltersCart($block, $store, $shoppingCart)
    {
        $limit = $block->getRelatedProducts()->getCount();
        $relatedIds = $this->_getRelatedIdsCart($block, $store, $shoppingCart);
        if ($relatedIds) {
            $this->_relatedInitCollectionForIds($relatedIds, true, $block, $store, $limit);
            $this->_relatedCollection->setPageSize($limit);
            $this->_relatedCollection->setCurPage(1);
        } else {
            $this->_relatedCollection = null;
        }
        return $this;
    }
    
    
    
    protected function _getRelatedIdsCart($block, $store, $shoppingCart)
    {
        $relatedIds = array();
        $filteredByOptionsIds = $this->_getFilteredByOptionsIdsCart($block, $store, $shoppingCart);
        if ($filteredByOptionsIds) {
            $filteredIds = $this->_getFilteredIdsByConditionsCart($block,$store);
            $relatedIds = $filteredByOptionsIds;
            if (null !== $filteredIds) {
                $relatedIds = array_intersect($relatedIds, $filteredIds);
            }
        }

        if ($relatedIds) {
            $relatedIds = array_diff($relatedIds, $this->_getCheckoutCartProductIds($shoppingCart));
        }
        return $relatedIds;
    }
    
    protected function _getCheckoutCartProductIds($shoppingCart)
    {
        $productIds = array();
        if ($shoppingCart) {
            foreach ($shoppingCart->getQuote()->getItemsCollection() as $quoteItem) {
                /** @var $quoteItem Mage_Sales_Model_Quote_Item */
                $productIds[] = $quoteItem->getProductId();
            }
        }
        return $productIds;
    }
    
    protected function _getFilteredIdsByConditionsCart($block,$store)
    {
        /** @var $rule AW_Autorelated_Model_Blocks_Shoppingcart_Rulerelated */
        $rule = Mage::getModel('awautorelated/blocks_shoppingcart_rulerelated');
        $rule->setReturnMode(AW_Autorelated_Model_Blocks_Rule::ALL_IDS_ON_NO_CONDITIONS);
        $rule->getConditions()->loadArray($block->getRelatedProducts()->getData('conditions'), 'related');
        $rule->setWebsiteIds(Mage::getModel('core/store')->load($store)->getWebsiteId());
        return $rule->getMatchingProductIds();
    }
    
    protected function _getFilteredByOptionsIdsCart($block, $store, $shoppingCart)
    {
        $options = $block->getRelatedProducts()->getData('options');
        if ($options) {
            /** @var $productCollection AW_Autorelated_Model_Product_Collection */
            $productCollection = Mage::getModel('awautorelated/product_collection');
            $productCollection->setStoreId($store);
            $isFiltered = false;
            foreach ($options as $option) {
                $attributeConditions = $this->_getShoppingCartProductsAttributeConditions(
                    $option['ATTR'], $option['CONDITION'],$shoppingCart
                );
                if ($attributeConditions) {
                    $isFiltered = true;
                    switch ($option['ATTR']) {
                        case 'price':
                            $productCollection->addPriceAttributeToFilter('final_price', $attributeConditions);
                            break;
                        default:
                            $productCollection->addAttributeToFilter($option['ATTR'], $attributeConditions);
                    }
                }
            }
            if (!$isFiltered) {
                return array();
            }
            $filteredIds = array_intersect($this->_relatedCollection->getAllIds(), $productCollection->getAllIds());
        } else {
            $filteredIds = $this->_relatedCollection->getAllIds();
        }
        return $filteredIds;
    }
    
    protected function _getShoppingCartProductsAttributeConditions($attrName, $attrCondition, $shoppingCart)
    {
        $attributeConditions = array();
        foreach ($shoppingCart->getQuote()->getAllItems() as $quoteItem) {
            /** @var $quoteItem Mage_Sales_Model_Quote_Item */
            $product = $quoteItem->getProduct();
            if ($product->getId() && $attrValue = $product->getData($attrName)) {
                if (in_array($attrCondition, array('like', 'nlike'))) {
                    $attrValue = '%' . $attrValue . '%';
                }
                $attributeConditions[] = array($attrCondition => $attrValue);
            }
        }
        return $attributeConditions;
    }
    
    protected function _renderRelatedProductsFiltersCategory($block, $store, $currentCategory, $cartProducts = null, $wishlistProducts = null)
    {
		$limit = $block->getRelatedProducts()->getCount();
        $relatedIds = $this->_getRelatedIdsCategory($block, $store, $currentCategory, $cartProducts, $wishlistProducts);
        if ($relatedIds) {
			$this->_relatedInitCollectionForIds($relatedIds, true, $block, $store, $limit);
            $this->_relatedCollection->setPageSize($limit);
            $this->_relatedCollection->setCurPage(1);
        } else {
            $this->_relatedCollection = null;
        }
        return $this;
    }
    
    protected function _getRelatedIdsCategory($block, $store, $currentCategory, $cartProducts = null, $wishlistProducts = null)
    {
		$filteredIds = AW_Autorelated_Model_Cache::getCategoryBlockMatchedIds($block->getId());
        $intersectedArray = $this->_relatedCollection->getAllIds();
		if (null !== $filteredIds) {
            $intersectedArray = array_intersect($intersectedArray, $filteredIds);
        }
		$limit = $block->getRelatedProducts()->getCount();
        $relatedProducts = $block->getRelatedProducts();
        //$currentCategory = $this->_getCurrentCategory();
        if ($intersectedArray) {
            $this->_relatedInitCollectionForIds($intersectedArray, false, $block, $store, $limit);

            // Setting include filter
            if ($relatedProducts->getData('include') != AW_Autorelated_Model_Source_Block_Category_Include::ALL
                && $currentCategory
            ) {
                $include = true;
                if ($relatedProducts->getData('include')
                    == AW_Autorelated_Model_Source_Block_Category_Include::CURRENT_CATEGORY
                ) {
                    $include = false;
                }
                $this->_relatedCollection->addCategoriesFilter($currentCategory->getId(), $include);
            }

            $relatedIds = array();
            if ($relatedProducts->getData('include') == AW_Autorelated_Model_Source_Block_Category_Include::ALL
                || $currentCategory
            ) {
                $relatedIds = $this->_relatedCollection->getAllIds();
                if($wishlistProducts)
				{
					$wishlistProductsArray = json_decode(base64_decode($wishlistProducts));
					$relatedIds = array_diff($relatedIds, $wishlistProductsArray);
				}
				if($cartProducts)
				{
					$cartProductsArray = json_decode(base64_decode($cartProducts));
					$relatedIds = array_diff($relatedIds, $cartProductsArray);
				}
            }
        }
        return $relatedIds;
    }
    
    protected function _relatedInitCollectionForIds(array $ids, $sort = true, $block, $store, $limit)
    {
        unset($this->_relatedCollection);
        $this->_relatedCollection = Mage::getModel('awautorelated/product_collection');

        //init sort by
        if (true === $sort) {
            $ids = array_unique($ids);
            $orderSettings = $this->_getRelatedProductsOrder($block);
            switch ($orderSettings->getData('type')) {
                case AW_Autorelated_Model_Source_Block_Common_Order::RANDOM:
                    shuffle($ids);
                    //$limit = $block->getRelatedProducts()->getProductQty();
                    if (count($ids) > $limit) {
                        array_splice($ids, $limit);
                    }
                    $this->_relatedCollection->getSelect()->order(new Zend_Db_Expr('RAND()'));
                    break;
                case AW_Autorelated_Model_Source_Block_Common_Order::BY_ATTRIBUTE:
                    $this->_relatedCollection->addAttributeToSort(
                        $orderSettings->getData('attribute'),
                        $orderSettings->getData('direction')
                    );
                    break;
                case AW_Autorelated_Model_Source_Block_Common_Order::NONE:
                    //$limit = $block->getRelatedProducts()->getProductQty();
                    if (count($ids) > $limit) {
                        array_splice($ids, $limit);
                    }
                    break;
            }
        }

        $this->_relatedCollection->addAttributeToSelect('*')->addFilterByIds($ids)->setStoreId($store);
            
        return $this->_relatedCollection;
    }
    
    protected function _relatedPostProcessCollection($block, $store)
    {
        if ($this->_relatedCollection instanceof AW_Autorelated_Model_Product_Collection) {
            $this->_relatedCollection->setStoreId($store)
                ->addMinimalPrice()
                ->groupByAttribute('entity_id')
                ->addUrlRewrite();

            if ($this->_getShowOutOfStock($block) && !Mage::helper('cataloginventory')->isShowOutOfStock()) {
                $fromPart = $this->_relatedCollection->getSelect()->getPart(Zend_Db_Select::FROM);
                if (isset($fromPart['price_index'])
                    && is_array($fromPart['price_index'])
                    && isset($fromPart['price_index']['joinType'])
                    && $fromPart['price_index']['joinType'] === Zend_Db_Select::INNER_JOIN
                ) {
                    $fromPart['price_index']['joinType'] = Zend_Db_Select::LEFT_JOIN;
                    $this->_relatedCollection->getSelect()->setPart(Zend_Db_Select::FROM, $fromPart);
                }
            }
        }
        return $this;
    }

	protected function _getShowOutOfStock($block)
    {
        return $block->getData('related_products') instanceof Varien_Object
            && $block->getData('related_products')->getData('show_out_of_stock');
    }
    /*
     * 
     * filter product by attributes valuesd
     * Mage_Catalog_Model_Product $currentProduct -main product
     * Array $atts - atts list for filter  
     * Array $ids - products id for filter
     */
    public function filterByAtts(Mage_Catalog_Model_Product $currentProduct, $atts, $ids = null, $_collection)
    {

        $_joinedAttributes = array();
        //$collection = $_collection;
        $collection = $this->_relatedCollection;
        $rule = new AW_Autorelated_Model_Blocks_Rule();

        foreach ($atts as $at) {
            /*
            *  collect category ids related to product
            *  If category is anchor we should implode all of its subcategories as value
            *  If it's not we should get only its id
            *  If there is no category in product, get all categories product is in
            */
            if ($at['att'] == 'category_ids') {
                $category = $currentProduct->getCategory();
                if ($category instanceof Varien_Object) {
                    if ($category->getIsAnchor()) {
                        $value = $category->getAllChildren();
                    } else {
                        $value = $category->getId();
                    }
                } else {
                    $value = implode(',', $currentProduct->getCategoryIds());
                    $value = !empty($value) ? $value : null;
                }
            } elseif ($at['att'] == 'price') {
                $value = $currentProduct->getFinalPrice();
            } else {
                $value = $currentProduct->getData($at['att']);
            }
            if (!$value) {
                $collection = NULL;
                return false;
            }
            $sql = $rule->prepareSqlForAtt($at['att'], $this->_joinedAttributes, $collection, $at['condition'], $value);
            if ($sql) {
                $collection->getSelect()->where($sql);
            }
        }
        if ($ids) {
            $collection->getSelect()->where('e.entity_id IN(' . implode(',', $ids) . ')');
        }
        $collection->getSelect()->group('e.entity_id');

        return $collection->getAllIds();
    }
    
    protected function _getRelatedProductsOrder($block)
    {
		$rpOrder = array(
					'type' => AW_Autorelated_Model_Source_Block_Common_Order::NONE
				   );
				
		$relatedProducts = $block->getData('related_products') ? $block->getData('related_products') : null; 
				
		if ($relatedProducts && is_array($order = $relatedProducts->getData('order')))
		{
			$rpOrder = $order;
		}		
               
        return new Varien_Object($rpOrder);
    }
    
    public function getRelatedProductData($productid, $currentcurrencycode, $basecurrencycode)
    {
		$_product = Mage::getModel('catalog/product')->load($productid);
		$productName = $this->getNamePrefix($_product).$_product->getName();
		$productImage = Mage::helper('catalog/image')->init($_product,'small_image')->resize(200,200);
		//$productImage = $_product->getImageUrl();
		//$productImage = Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) . 'catalog/product' . $_product->getimage();
		//echo Mage::getSingleton('catalog/product_media_config')->getBaseMediaUrl(). '/placeholder/default/red_zoom.jpg';
		if($_product->getTypeID() == 'grouped') {
            $actualprice = number_format($this->getGroupedProductPrice($_product->getId(), $currentcurrencycode) , 3, '.', '');
            $specialprice =  number_format($_product->getFinalPrice(), 3, '.', '');
        }
        else
        {
			$actualprice =  number_format($_product->getPrice(), 3, '.', '');
			$specialprice =  number_format($_product->getFinalPrice(), 3, '.', '');
        }

        $ratingValue = '';
        $formatValue = '';
        $productDescription = '';
        if(isset($_product['soko_rating']))
			$ratingValue = $_product->getAttributeText('soko_rating');
        if(isset($_product['soko_format']))
			$formatValue = $_product->getAttributeText('soko_format');

        if($actualprice == $specialprice)
			$specialprice = number_format(0, 3, '.', '');
		
		$relatedProductData = array(
			'id' => $_product->getId(),
			'name' => $productName,
			'image' => (string)$productImage,
			'sku' => $_product->getSku(),
			'description' => $_product->getDescription(),
			'type' => $_product->getTypeID(),
			'price' => number_format($this->convert_currency($actualprice, $basecurrencycode, $currentcurrencycode), 3, '.', ''),
			'special_price' => number_format($this->convert_currency($specialprice, $basecurrencycode, $currentcurrencycode), 3, '.', ''),
			'currency_symbol' => Mage::app()->getLocale()->currency($currentcurrencycode)->getSymbol(),
			'created_date' => $_product->getCreatedAt(),
			'is_stock_status' => $_product->getStockItem()->getIsInStock(),
			'stock_quantity' => Mage::getModel('cataloginventory/stock_item')->loadByProduct($_product->getId())->getQty(),
			'soko_rating_value' => $ratingValue,
			'soko_format_value' => $formatValue

        );
        return $relatedProductData;
	}
	
	public function getNamePrefix($product)
	{
		$name = '';
		if(isset($product['soko_vintage']) && $product->getAttributeText('soko_vintage'))
			$name = $product->getAttributeText('soko_vintage').' ';
		return $name;
	}

// =========================================================================================================== //

// ==================================== Multifees Related api's ============================================== //
	public function getMultifeesTotal($store,$quoteId = null,$customerId = null,$currentCurrencyCode)
	{
		$res = [];$count = 0;
		$res['total']	= $count;
		$res["status"]	= 'success';
		try
		{
			//$customerSession = Mage::getSingleton('customer/session');	//customer session required is for Multifees extension only.
			//$customerSession->unsetAll();								//clearing the earlier customer session.
			
			//$checkoutSession = Mage::getSingleton('checkout/session');	//checkout session required is for Multifees extension only.
			//$checkoutSession->unsetAll();								//clearing the earlier checkout session.

			Mage::app()->setCurrentStore($store);
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			$helper = Mage::helper('multifees');
			if ($helper->isEnabled())
			{
				$quote = Mage::getModel('sales/quote');
				if($customerId)
				{
					$customerObj = Mage::getModel('customer/customer')->load($customerId);	//loading customer object for passing to customer session.
					//if($customerObj && $customerId != $customerSession->getCustomerId())	//default cart sync api does not require use of session.
					//{
					//	$customerSession->setCustomer($customerObj);
					//}
					$quote = $quote->loadByCustomer($customerId);
				}
				else
				{
					if($quoteId)
						$quote = $quote->loadActive($quoteId);
				}
				if($quote->getId())
				{
					//$checkoutSession->setQuoteId($quote->getId());	//setting the quote to the checkout session related to Multifees extension.
					$totals = $quote->getTotals();
					if (Mage::helper('multifees')->getTaxInCart() == 2) 
						$inclTax = true; 
					else 
						$inclTax = false;

					if(isset($totals["tax_multifees"]))
						$inclTax = true;

					if(isset($totals["multifees"]))
					{
						$res['title'] = $totals["multifees"]->getTitle();
						$res['price'] = number_format($this->convert_currency($totals["multifees"]->getValue(), $baseCurrencyCode, $currentCurrencyCode), 2, '.', '');
						$feesData = $totals["multifees"]->getFullInfo();
						$feesData = unserialize($feesData);
						foreach($feesData as $fee)
						{
							$optionCount = 0;
							$options = [];
							foreach ($fee['options'] as $option)
							{
								$optionPrice = $inclTax ? $option['price'] : $option['price'] - $option['tax'];
								$optionPercent = isset($option['percent']) ? ' ('.(float)$option['percent'].'%)' : '';
								$options[$optionCount]['title'] = $option['title'].$optionPercent;
								$options[$optionCount]['price'] = number_format($this->convert_currency($optionPrice, $baseCurrencyCode, $currentCurrencyCode), 2, '.', '');
								$optionCount++;
							}
							$feePrice = $inclTax ? $fee['price']:$fee['price'] - $fee['tax'];
							$res['fees'][$count]['title'] = $fee['title'];
							$res['fees'][$count]['price'] = number_format($this->convert_currency($feePrice, $baseCurrencyCode, $currentCurrencyCode), 2, '.', '');
							$res['fees'][$count]['options'] = $options;
							$count++;
						}
						//echo "<pre>";print_r($feesData);
					}
					$res["total"]	= $count;
				}
				else
				{
					$res['status'] = "error";
					$res['message'] = "The Cart could not be fetched for the respective Quote/Customer";
				}
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $ex->getMessage();
		}
		return $res;
	}

	public function getMultifeesOptions($store,$quoteId = null,$customerId = null,$currentCurrencyCode,$blockType = null,$code = '')
	{
		if(!$code)
			$code = 'Mofluid_Test';
		$res = [];$count = 0;
		$res['total']	= $count;
		$res["status"]	= 'success';
		try
		{
			//$customerSession = Mage::getSingleton('customer/session');	//customer session required is for Multifees extension only.
			//$customerSession->unsetAll();								//clearing the earlier customer session.
			
			//$checkoutSession = Mage::getSingleton('checkout/session');	//checkout session required is for Multifees extension only.
			//$checkoutSession->unsetAll();								//clearing the earlier checkout session.
			
			Mage::app()->setCurrentStore($store);
			$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
			$helper = Mage::helper('multifees');
			if ($helper->isEnabled())
			{
				$quote = Mage::getModel('sales/quote');
				if($customerId)
				{
					$customerObj = Mage::getModel('customer/customer')->load($customerId);	//loading customer object for passing to customer session.
					//if($customerObj && $customerId != $customerSession->getCustomerId())	//default cart sync api does not require use of session.
					//{
					//	$customerSession->setCustomer($customerObj);
					//}
					$quote = $quote->loadByCustomer($customerId);
				}
				else
				{
					if($quoteId)
						$quote = $quote->loadActive($quoteId);
				}
				if($quote->getId())
				{
					//$checkoutSession->setQuoteId($quote->getId());	//setting the quote to the checkout session related to Multifees extension.
					switch($blockType) //getting the block type value from block type name.
					{
						case "shipping":	$blockTypeValue	= 3;
											$blockEnabled	= $helper->isEnableShippingFees(); 
						break;
						case "payment":		$blockTypeValue = 2;
											$blockEnabled	= $helper->isEnablePaymentFees();
						break;
						case "cart":		$blockTypeValue = 1;
											$code = '';
											$blockEnabled	= $helper->isEnableCartFees();
						break;
						default:			$blockTypeValue = 0;
											$blockEnabled	= 0;
					}
					if($blockTypeValue && $blockEnabled)
					{
						$multifees = $helper->getMultifees($blockTypeValue, 0, 2, 0, $code, $quote); // Shipping Fee, no hidden
						//echo "<pre>";print_r($multifees->getData());
						if(count($multifees) > 0)
						{
							foreach ($multifees as $fee)
							{
								$feeType = '';
								$optionCount = 0;
								$feeOpt = [];
								$res['fees'][$count]['fee_id'] = $fee->getFeeId();
								$res['fees'][$count]['title'] = $fee->getTitle();
								$res['fees'][$count]['is_required'] = $fee->getRequired() ? true : false;
								$res['fees'][$count]['description'] = $fee->getDescription();
								$res['fees'][$count]['enable_date_field'] = $fee->getEnableDateField() ? true : false;
								$res['fees'][$count]['date_field_title'] = $fee->getDateFieldTitle();
								$res['fees'][$count]['enable_customer_message'] = $fee->getEnableCustomerMessage() ? true : false;
								$res['fees'][$count]['customer_message_title'] = $fee->getCustomerMessageTitle();
								switch($fee->getInputType())
								{
									case 1:		$feeType = 'dropdown';
									break;
									case 2:		$feeType = 'radio-button';
									break;
									case 3:		$feeType = 'checkbox';
									break;
									case 4:		$feeType = 'hidden';
									break;
									case 5:		$feeType = 'notice';
									break;
									default:	$feeType = '';
								}
								$res['fees'][$count]['input_type'] = $feeType;
								$feeOptions = $fee->getOptions(true);
								if(count($feeOptions) > 0)
								{
									foreach($feeOptions as $option)
									{
										$feeOpt[$optionCount]['id'] = $option->getId();
										$feeOpt[$optionCount]['title'] = $option->getTitle().' - '.$helper->getOptionFormatPrice($option, $fee);
										//$feeOpt[$optionCount]['price'] = $helper->getOptionFormatPrice($option, $fee);
										$feeOpt[$optionCount]['is_default'] = $option->getIsDefault() ? true : false;
										$optionCount++;
									}
								}
								$res['fees'][$count]['options'] = $feeOpt;
								$count++;
							}
						}
						$res['total']	= $count;
					}
				}
				else
				{
					$res['status'] = "error";
					$res['message'] = "The Cart could not be fetched for the respective Quote/Customer";
				}
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $ex->getMessage();
		}
		return $res;
	}

	public function applyMultifeesOptions($store,$quoteId = null,$customerId = null,$blockType = null,$feesData = null)
	{
		$res = [];//$count = 0;
		//$res['total']	= $count;
		$res["status"]	= 'success';
		
		//$feesData = '{"5":{"options":{"0":"10"},"date":"12/12/2019","message":"This is testing"},"7":{"options":{"0":"12"}}}';
		//$feesDataArray = json_decode($feesData,true);
		//echo "<pre>";print_r($feesDataArray);die;
		
		try
		{
			//$customerSession = Mage::getSingleton('customer/session');	//customer session required is for Multifees extension only.
			//$customerSession->unsetAll();								//clearing the earlier customer session.
			
			//$checkoutSession = Mage::getSingleton('checkout/session');	//checkout session required is for Multifees extension only.
			//$checkoutSession->unsetAll();								//clearing the earlier checkout session.

			$helper = Mage::helper('multifees');
			if($helper->isEnabled())
			{
				$quote = Mage::getModel('sales/quote');
				if($customerId)
				{
					$customerObj = Mage::getModel('customer/customer')->load($customerId);	//loading customer object for passing to customer session.
					//if($customerObj && $customerId != $customerSession->getCustomerId())	//default cart sync api does not require use of session.
					//{
					//	$customerSession->setCustomer($customerObj);
					//}
					$quote = $quote->loadByCustomer($customerId);
				}
				else
				{
					if($quoteId)
						$quote = $quote->loadActive($quoteId);
				}
				if($quote->getId())
				{
					//$checkoutSession->setQuoteId($quote->getId());	//setting the quote to the checkout session related to Multifees extension.
					switch($blockType) //getting the block type value from block type name.
					{
						case "shipping":	$blockTypeValue	= 3;
											$blockEnabled	= $helper->isEnableShippingFees(); 
						break;
						case "payment":		$blockTypeValue = 2;
											$blockEnabled	= $helper->isEnablePaymentFees();
						break;
						case "cart":		$blockTypeValue = 1;
											$blockEnabled	= $helper->isEnableCartFees();
						break;
						default:			$blockTypeValue = 0;
											$blockEnabled	= 0;
					}
					if($blockTypeValue && $blockEnabled)
					{
						if($feesData)
						{
							$feesData = json_decode(base64_decode($feesData),true);
							//$address = $this->getSalesAddress($quote);
							$helper->addFeesToCart($feesData, $store, false, $blockTypeValue, 0);

							$quote->getBillingAddress();
							$quote->getShippingAddress()->setCollectShippingRates(true);
							$quote->setTotalsCollectedFlag(false)->collectTotals();
							$quote->save();
						}
						else
						{
							$res['status'] = 'error';
							$res['message'] = 'Fees Type/Data is missing.';
						}
					}
				}
				else
				{
					$res['status'] = "error";
					$res['message'] = "The Cart could not be fetched for the respective Quote/Customer";
				}
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $ex->getMessage();
		}
		return $res;
	}
	
	public function getSalesAddress($sales) {
        $address = $sales->getShippingAddress();
        if ($address->getSubtotal()==0) {
            $address = $sales->getBillingAddress();
        }
        return $address;
    }
// =========================================================================================================== //

// ================================= Authorize Payment Implementation ======================================== //
	public function ws_getAuthorizeCards($store,$customerId,$method,$customerAccount = null)
	{
		$res = [];
		$total = 0;
		$res['total'] = $total;
		$res['status'] = 'success';
		$res['cards'] = [];
		if(!$method)
			$method = Mage::getSingleton('authnetcim/method')->getCode();
		if(!$customerAccount)
			$res['require_ccv'] = intval(Mage::getStoreConfig('payment/'.$method.'/require_ccv',$store));
		try
		{
			Mage::app()->setCurrentStore($store);
			if($customerId)
			{
				$cards = Mage::getModel('tokenbase/card')->getCollection()
						->addFieldToFilter('customer_id', (int)$customerId )
						->addFieldToFilter('active', 1 )
						->addFieldToFilter('method', $method);
				if($cards)
				{
					foreach($cards as $card)
					{
						$card_array = $this->_prepareCard($card);
						$res['cards'][$total]['id'] = $card_array['id'];
						$res['cards'][$total]['customer_id'] = $card_array['customer_id'];
						$res['cards'][$total]['label'] = $card_array['label'];
						$res['cards'][$total]['hash'] = $card_array['hash'];
						//$res['cards'][$total]['cc_type'] = $card_array['cc_type'];
						//$res['cards'][$total]['cc_last4'] = $card_array['cc_last4'];
						if($customerAccount)
						{
							$cardExpired = false;
							$cardInuse = false;
							if($card->getExpires() != '' && strtotime($card->getExpires()) < time())
								$cardExpired = true;
							$res['cards'][$total]['is_expired'] = $cardExpired;
							$res['cards'][$total]['expiry_date'] = $card->getExpires() ? date('m/Y',strtotime($card->getExpires())) : '';
							if($card->isInUse())
								$cardInuse = true;
							$res['cards'][$total]['in_use'] = $cardInuse;
							$res['cards'][$total]['address'] = $card_array['address'];
						}
						$total++;
					}
				}
				$res['total'] = $total;
			}
			else
			{
				$res['status'] = 'error';
				$res['message'] = 'Please mention the customer for fetching the respective card.';
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
    }
    
	public function ws_getAuthorizeCC($store,$method)
	{
		$res = [];
		$total = 0;
		$res['cc_types'] = [];
		$res['status'] = 'success';
		$res['total'] = $total;
		try
		{
			Mage::app()->setCurrentStore($store);
			if(!$method)
				$method = Mage::getSingleton('authnetcim/method')->getCode();
			$ccTypes = Mage::helper('tokenbase')->getCcAvailableTypes($method);
			foreach($ccTypes as $cc_code => $ccType)
			{
				$res['cc_types'][$total]['code']	= $cc_code;
				$res['cc_types'][$total]['label']	= $ccType;
				$total++;
			}
			$res['total'] = $total;
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}
    
    protected function _prepareCard( ParadoxLabs_TokenBase_Model_Card $card )
	{
		$card		= $card->getTypeInstance();
		$address	= $card->getAddress();
		
		$_cardMap	= array( 'id', 'customer_id', 'customer_email', 'customer_ip', 'profile_id', 'payment_id', 'method', 'created_at', 'updated_at', 'last_use', 'expires', 'additional', 'hash' );
		$_addrMap	= array( 'firstname', 'lastname', 'street', 'city', 'region', 'postcode', 'country_id', 'telephone', 'fax', 'region_id' );
	
		/**
		 * Basic payment record data
		 */
		$result		= array();
		foreach( $_cardMap as $key ) {
			$result[ $key ]		= $card->getData( $key );
		}
		
		/**
		 * Address data
		 */
		$result['address']		= array();
		foreach( $_addrMap as $key ) {
			$result['address'][ $key ] = $address[ $key ];
		}
		
		/**
		 * Additional (common) information
		 */
		$result['label']		= $card->getLabel();
		$result['cc_type']		= $card->getAdditional('cc_type');
		$result['cc_last4']		= $card->getAdditional('cc_last4');
		
		return $result;
	}
	
	public function ws_orderAuthorizePayment($quote)
	{
		//for getting the payData value. Can fetch both GET & POST Variables. But for security purpose POST is preferred. 
		$payData = Mage::app()->getRequest()->getParam('payData');
		$postLength = strlen($payData) - 5;			//Calculation done for removing the extra key appended to the paydata
		$payData = substr($payData,3,$postLength);	//for security purposes.
		$payData = json_decode(base64_decode($payData),true);
		$payData['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
		| Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
		| Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
		| Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
		| Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;
		$payment = $quote->getPayment();
		$payment->importData($payData);
	}
	
	public function ws_setAuthorizePayment($store,$quoteId)
	{
		$res = [];
		try
		{
			if($quoteId)
			{
				Mage::app()->setCurrentStore($store);
				$quote = Mage::getModel('sales/quote');
				$quote = $quote->loadActive($quoteId);
				$payment = $quote->getPayment();
				if($quote->getId())
				{
					//for getting the payData value. Can fetch both GET & POST Variables. But for security purpose POST is preferred. 
					$payData = Mage::app()->getRequest()->getParam('payData');
					$postLength = strlen($payData) - 5;			//Calculation done for removing the extra key appended to the paydata
					$payData = substr($payData,3,$postLength);	//for security purposes.
					$payData = json_decode(base64_decode($payData),true);
					if($payData)
					{
						if ($quote->isVirtual()) {
							$quote->getBillingAddress()->setPaymentMethod(isset($payData['method']) ? $payData['method'] : null);
						} else {
							$quote->getShippingAddress()->setPaymentMethod(isset($payData['method']) ? $payData['method'] : null);
						}

						if (!$quote->isVirtual() && $quote->getShippingAddress()) {
							$quote->getShippingAddress()->setCollectShippingRates(true);
						}

						$payData['checks'] = Mage_Payment_Model_Method_Abstract::CHECK_USE_CHECKOUT
						| Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_COUNTRY
						| Mage_Payment_Model_Method_Abstract::CHECK_USE_FOR_CURRENCY
						| Mage_Payment_Model_Method_Abstract::CHECK_ORDER_TOTAL_MIN_MAX
						| Mage_Payment_Model_Method_Abstract::CHECK_ZERO_TOTAL;

						$payment = $quote->getPayment();
						$payment->importData($payData);

						$quote->save();
						$res['status'] = 'success';
					}
					else
					{
						$res['status']	= 'error';
						$res['message']	= 'Please ensure you have passed correct payment data.';
					}
				}
				else
				{
					$res['status']	= 'error';
					$res['message']	= 'It seems cart/quote is already used.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Could not able to fetch the quote/cart.';  
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}

	public function ws_saveAuthorizeCard($store,$customerId = null)
	{
		$res		= [];
		Mage::app()->setCurrentStore($store);
		$quote = Mage::getModel('sales/quote');
		$method		= Mage::getSingleton('authnetcim/method')->getCode();
		try
		{
			$payData = Mage::app()->getRequest()->getParam('payData');
			$postLength = strlen($payData) - 5;			//Calculation done for removing the extra key appended to the paydata
			$payData = substr($payData,3,$postLength);	//for security purposes.
			$payData = json_decode(base64_decode($payData),true);
			
			if($payData && $customerId && isset($payData['billing']) && is_array($payData['billing']) && isset($payData['payment']) && is_array($payData['payment']))
			{
				if(isset($payData['id']))
					$id = intval($payData['id']);
				else
					$id = 0;
				$card		= Mage::getModel($method.'/card')->load($id);
				$customer	= Mage::getModel('customer/customer')->load($customerId);
				if($card && ($id == 0 || ($card->getId() == $id && $card->hasOwner($customerId))))
				{
					if(isset($payData['shipping_address_id']))
						$newAddrId	= intval($payData['shipping_address_id']);
					else
						$newAddrId	= 0;
					// Existing address
					if($newAddrId > 0) {
						$newAddr = Mage::getModel('customer/address')->load($newAddrId);
						if($newAddr->getCustomerId() != $customer->getId())
						{
							throw new Exception('An error occured. Please try again.');
						}
					}
					// New address
					else
					{
						$newAddr = Mage::getModel('customer/address');
						$newAddr->setCustomerId($customer->getId());
						//$data = Mage::app()->getRequest()->getPost('billing', array());
						if(isset($payData['billing']))
							$data = $payData['billing'];
						$addressForm  = Mage::getModel('customer/form');
						$addressForm->setFormCode('customer_address_edit');
						$addressForm->setEntity($newAddr);
						$addressData    = $addressForm->extractData($addressForm->prepareRequest($data));
						$addressErrors  = $addressForm->validateData($addressData);
						if($addressErrors !== true) {
							throw new Exception(implode(' ', $addressErrors));
						}
						$addressForm->compactData($addressData);
						$addressErrors  = $newAddr->validate();
						$newAddr->setSaveInAddressBook(false);
						$newAddr->implodeStreetAddress();
					}
					//$cardData = Mage::app()->getRequest()->getParam('payment');
					$cardData = [];
					if(isset($payData['payment']))
						$cardData = $payData['payment'];
					$cardData['method']		= $method;
					$cardData['card_id']	= $card->getId();
					if(isset($cardData['cc_number'])) {
						$cardData['cc_last4']	= substr($cardData['cc_number'], -4);
					}
					$newPayment = Mage::getModel('sales/quote_payment');
					$newPayment->setQuote($quote->loadByCustomer($customerId));
					$newPayment->getQuote()->getBillingAddress()->setCountryId($newAddr->getCountryId());
					$newPayment->importData($cardData);
					/**
					* Save payment data
					*/
					$card->setMethod($method);
					$card->setCustomer($customer);
					$card->setAddress($newAddr);
					$card->importPaymentInfo($newPayment);
					$card->save();
					$res['status']	= 'success';
					$res['message']	= 'Payment data saved successfully.'; 
					//Mage::getSingleton('customer/session')->unsTokenbaseFormData();
				}
				else {
					$res['status']	= 'error';
					$res['message']	= 'Invalid Request.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. The paydata or customerId passed is incorrect.';
			}
		}
		catch(Exception $e) 
		{
			//Mage::getSingleton('customer/session')->setTokenbaseFormData(Mage::app()->getRequest()->getPost());
			//Mage::helper('tokenbase')->log($method, (string)$e);
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_deleteAuthorizeCard($store,$customerId = null)
	{
		$res	= [];
		try
		{
			$id		= intval(Mage::app()->getRequest()->getParam('id'));
			if($id && $customerId)
			{
				$method	= Mage::getSingleton('authnetcim/method')->getCode();
				$card = Mage::getModel( $method . '/card' )->load($id);
				if($card && $card->getId() == $id && $card->hasOwner($customerId))
				{
					$card->queueDeletion()->save();
					$res['status']	= 'success';
					$res['message']	= 'Payment record deleted.';
				}
				else
				{
					$res['status']	= 'error';
					$res['message']	= 'Invalid Request.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. The Card id or CustomerId passed is incorrect.';
			}
		}
		catch(Exception $e) 
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}
// =========================================================================================================== //

// =================================== Multiple Address Implementation ======================================= //

	public function ws_saveCustomerAddress($store,$customerId = null)
	{
		$res = [];
		try
		{
			$customerData = Mage::app()->getRequest()->getParam('customerData');
			$customerData = json_decode(base64_decode($customerData),true);
			if($customerData && $customerId)
			{
				$customer = Mage::getModel('customer/customer')->load($customerId);
				/* @var $address Mage_Customer_Model_Address */
				$address  = Mage::getModel('customer/address');
				if(isset($customerData['id']))
					$addressId = intval($customerData['id']);
				if ($addressId) {
					$existsAddress = $customer->getAddressById($addressId);
					if ($existsAddress->getId() && $existsAddress->getCustomerId() == $customer->getId()) {
						$address->setId($existsAddress->getId());
					}
				}

				$errors = array();
				/* @var $addressForm Mage_Customer_Model_Form */
				$addressForm = Mage::getModel('customer/form');
				$addressForm->setFormCode('customer_address_edit')
							->setEntity($address);
				$addressData    = $addressForm->extractData($addressForm->prepareRequest($customerData));
				$addressErrors  = $addressForm->validateData($addressData);
				if ($addressErrors !== true) {
					$errors = $addressErrors;
				}

				try
				{
					if(isset($customerData['default_billing']) && $customerData['default_billing'])
						$default_billing = true;
					else
						$default_billing = false;
					
					if(isset($customerData['default_shipping']) && $customerData['default_shipping'])
						$default_shipping = true;
					else
						$default_shipping = false;
					
					$addressForm->compactData($addressData);
					$address->setCustomerId($customer->getId())
							->setIsDefaultBilling($default_billing)
							->setIsDefaultShipping($default_shipping);

					$addressErrors = $address->validate();
					if ($addressErrors !== true) {
						$errors = array_merge($errors, $addressErrors);
					}

					if (count($errors) === 0) {
						$address->save();
						$res['status'] = 'success';
					}
					else
					{
						$error_Message = '';
						foreach ($errors as $errorMessage) {
							$error_Message = $error_Message.' '.$errorMessage;
						}
						$res['status'] = 'error';
						$res['message'] = $error_Message;
					}
				}
				catch(Mage_Core_Exception $e)
				{
					throw $e;
				}
				catch(Exception $e)
				{
					throw $e;
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. The customer data passed is incorrect.';
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}

	public function ws_deleteCustomerAddress($store,$customerId = null)
	{
		$res = [];
		try
		{
			$addressId = Mage::app()->getRequest()->getParam('addressId', false);
			if($addressId && $customerId)
			{
				$address = Mage::getModel('customer/address')->load($addressId);
				// Validate address_id <=> customer_id
				if ($address->getCustomerId() == $customerId) {
					$address->delete();
					$res['status'] = 'success';
				}
				else
				{
					$res['status']	= 'error';
					$res['message']	= 'The address does not belong to this customer.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. The customer data passed is incorrect.';
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}

	public function ws_getAllCustomerAddress($store,$customerId = null)
	{
		$res = [];
		$customeraddress = [];
		$count = 0;
		$res['addresses'] = [];
		$res['total'] = 0;
		try
		{
			if($customerId)
			{
				$customer 	= Mage::getModel('customer/customer')->load($customerId);
				$defaultBilling  = $customer->getDefaultBilling();
				$defaultShipping = $customer->getDefaultShipping();
				$addresses	= $customer->getAddresses();
				foreach($addresses as $address)
				{
					$streetAdd = $address->getStreet();
					$customerAddress[$count]['id']        	= $address->getId();
					$customerAddress[$count]['firstname'] 	= $address->getFirstname() ? $address->getFirstname() : '';
					//$customerAddress[$count]['middlename']= $address->getMiddlename();
					$customerAddress[$count]['lastname']  	= $address->getLastname() ? $address->getLastname() : '' ;
					$customerAddress[$count]['street']    	= $streetAdd;//implode(" ",$streetAdd);//$address->getStreetFull();
					$customerAddress[$count]['city']      	= $address->getCity() ? $address->getCity() : '';
					$customerAddress[$count]['region']    	= $address->getRegion() ? $address->getRegion() : '';
					$customerAddress[$count]['region_id']	= $address->getRegionId();
					$customerAddress[$count]['countryid'] 	= $address->getCountryId();
					$customerAddress[$count]['contactno']	= $address->getTelephone();
					$customerAddress[$count]['pincode']   	= $address->getPostcode();
					$customerAddress[$count]['company']   	= $address->getCompany() ? $address->getCompany() : '';
					$customerAddress[$count]['fax']   		= $address->getFax() ? $address->getFax() : '';
					$customerAddress[$count]['is_defaultbilling']  = 0;
					$customerAddress[$count]['is_defaultshipping'] = 0;
					if($defaultBilling && $address->getId() == $defaultBilling)
						$customerAddress[$count]['is_defaultbilling']  = 1;
					if($defaultShipping && $address->getId() == $defaultShipping)
						$customerAddress[$count]['is_defaultshipping'] = 1;
					$count++;
				}
				$res['status']	= 'success';
				$res['addresses'] = $customerAddress;
				$res['total']	= $count;
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. The customer id passed is incorrect.';
			}
		}
		catch(Exception $e)
		{
			$res['status'] = 'error';
			$res['message'] = $e->getMessage();
		}
		return $res;
	}
// =========================================================================================================== //

// ===================================== Gift Card Implementation ============================================ //

	public function ws_giftCardValidation($store,$customerId,$giftcode)
	{
		$res = [];
		$res['status'] = 'success';
		$res['notice'] = '';
		$res['giftcard_details'] = [];
	   	try
	   	{
			$giftcode = base64_decode($giftcode);
			$quote = Mage::getModel('sales/quote');
			if($customerId)
			{
				$quote = $quote->loadByCustomer($customerId);
			}
			if($customerId && $quote->getId() && $giftcode)
			{
				if(Mage::helper('giftvoucher')->getGeneralConfig('active', $store))
				{
					if ($quote->getCouponCode() && !Mage::helper('giftvoucher')->getGeneralConfig('use_with_coupon'))
					{
						$res['status']	= 'error';
						$res['message']	= 'A coupon code has been used. You cannot apply gift codes or Gift Card credit with the coupon to get discount.';
					}
					else
					{
						$giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($giftcode);
						if($giftVoucher->getId())
						{
							if($giftVoucher->getId() && $giftVoucher->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE && $giftVoucher->getBaseBalance() > 0)   
							{
								if(!$this->canUseCode($giftVoucher,$store,$customerId)) 
								{
									$res['status'] = 'error';
									$res['message'] = 'This gift code limits the number of users '.Mage::helper('giftvoucher')->getHiddenCode($giftcode).'.';
								}
								else 
								{
									$flag = false;
									foreach ($quote->getAllItems() as $item)
									{
										if ($giftVoucher->getActions()->validate($item))
										{
											$flag = true;
										}
									}
									if($flag && $giftVoucher->validate($quote->setQuote($quote)))
									{
										$isNoGiftVoucher = true;
										foreach ($quote->getAllItems() as $item) {
											if($item->getProductType() == 'giftvoucher')
											{
												$isNoGiftVoucher = false;
												break;
											}
										}
										if($isNoGiftVoucher)
										{
											$res['giftcard_details'] = array(
												'gift_code' => $giftVoucher->getGiftCode(),
												'hidden_code' => Mage::helper('giftvoucher')->getHiddenCode($giftVoucher->getGiftCode()),
												'balance_format' => strip_tags($this->getGiftCardBalance($giftVoucher,$store,0)),
												'balance' => strip_tags($this->getGiftCardBalance($giftVoucher,$store,1))
											);
										}
										else
										{
											$res['status'] = 'error';
											$res['message'] = 'Please remove your Gift Card information since you cannot use either gift codes or Gift Card credit balance to purchase other Gift Card products.';
										}
									}
									else
									{
										$res['status'] = 'error';
										$res['message'] = 'You cannot use this gift code since its conditions have not been met.';
                                    }

									if($giftVoucher->getCustomerId() == $customerId && $giftVoucher->getRecipientName() && $giftVoucher->getRecipientEmail() && $giftVoucher->getCustomerId())
									{
										$res['notice'] = 'Please note that gift code '.$giftcode.' has been sent to your friend. When using, both you and your friend will share the same balance in the gift code.';
									}
								}
							}
							else 
							{
								$res['status'] = 'error';
								$res['message'] = 'Gift code '.$giftcode.' is no longer available to use.';
							}
						}
						else
						{
							$res['status']	= 'error';
							$res['message']	= 'Gift card '.$giftcode.' is invalid.';
						}
					}
				}
				else
				{
					$res['status']	= 'error';
					$res['message']	= 'GiftCard functionality is currently Disabled.';
				}
			}
			else
			{
				$res['status']	= 'error';
				if(!$giftcode)
					$res['message']	= 'Invalid Request. Please provide the gift code to validate.';
				else
					$res['message']	= 'Invalid Request. The customer/quote could not be fetched.';
			}
		}
		catch(Exception $e)
		{
				$res['status']	= 'error';
				$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function getGiftCardBalance($item,$store,$returnPrice = 0)
	{
		$cardCurrency = Mage::getModel('directory/currency')->load($item->getCurrency());
		/* @var Mage_Core_Model_Store */
		Mage::app()->setCurrentStore($store);
		$store = Mage::app()->getStore();
		$baseCurrency = $store->getBaseCurrency();
		$currentCurrency = $store->getCurrentCurrency();
		if(!$returnPrice)
		{
			if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
				return $store->formatPrice($item->getBalance());
			}
			if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
				return $store->convertPrice($item->getBalance(), true);
			}
			if ($baseCurrency->convert(100, $cardCurrency)) {
				$amount = $item->getBalance() * $baseCurrency->convert(100, $currentCurrency) 
					/ $baseCurrency->convert(100, $cardCurrency);
				return $store->formatPrice($amount);
			}
			return $cardCurrency->format($item->getBalance(), array(), true);
		}
		else
		{
			if ($cardCurrency->getCode() == $currentCurrency->getCode())
				return number_format($item->getBalance(),2,'.','');
			if ($cardCurrency->getCode() == $baseCurrency->getCode())
				return number_format($item->getBalance(),2,'.','');
			if ($baseCurrency->convert(100, $cardCurrency)) {
				$amount = $item->getBalance() * $baseCurrency->convert(100, $currentCurrency) / $baseCurrency->convert(100, $cardCurrency);
				return number_format($amount,2,'.','');
			}
			return number_format($item->getBalance(),2,'.','');
		}
	}

	public function ws_findGiftCardCredit($store,$customerId,$creditAmount = 0)
	{
		$res = [];
		$res['status'] = 'success';
		$res['giftcredit_details'] = [];
		try
		{
			if($customerId)
			{
				if(Mage::helper('giftvoucher')->getGeneralConfig('active', $store) && Mage::helper('giftvoucher')->getGeneralConfig('enablecredit'))
				{
					$credit = Mage::getModel('giftvoucher/credit')->load($customerId,'customer_id');
					if ($credit->getBalance() > 0.0001) {
						$res['giftcredit_details'] = array(
							'label' => 'Use Gift Card credit to check out',
							'balance_format' => strip_tags($this->formatBalance($credit, true, $store,$creditAmount,0)),
							'balance' => strip_tags($this->formatBalance($credit, true, $store,$creditAmount,1))
						);
					}
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. Could not fetch the customer.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function formatBalance($credit, $showUpdate = false,$store,$creditAmount = 0,$returnPrice = 0)
	{
		if($showUpdate)
		{
			$cardCurrency = Mage::getModel('directory/currency')->load($credit->getCurrency());
			Mage::app()->setCurrentStore($store);
			$store = Mage::app()->getStore();
			$baseCurrency = $store->getBaseCurrency();
			$currentCurrency = $store->getCurrentCurrency();
			if(!$returnPrice)
			{
				if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
					if($creditAmount)
						return $store->formatPrice($credit->getBalance() - $creditAmount); 
					else
						return $store->formatPrice($credit->getBalance());
				}
				if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
					$amount = $store->convertPrice($credit->getBalance(), false);
					if($creditAmount)
						return $store->formatPrice($amount - $creditAmount);
					else
						return $store->formatPrice($amount);
				}
				if ($baseCurrency->convert(100, $cardCurrency)) {
					$amount = $credit->getBalance() * $baseCurrency->convert(100, $currentCurrency) / $baseCurrency->convert(100, $cardCurrency);
					if($creditAmount)
						return $store->formatPrice($amount - $creditAmount);
					else
						return $store->formatPrice($amount);
				}
				return $cardCurrency->format($credit->getBalance(), array(), true);
			}
			else
			{
				if ($cardCurrency->getCode() == $currentCurrency->getCode()) {
					if($creditAmount)
						return number_format(($credit->getBalance() - $creditAmount),2,'.','');
					else
						return number_format($credit->getBalance(),2,'.','');
				}
				if ($cardCurrency->getCode() == $baseCurrency->getCode()) {
					$amount = $store->convertPrice($credit->getBalance(), false);
					if($creditAmount)
						return number_format(($amount - $creditAmount),2,'.','');
					else
						return number_format($amount,2,'.','');
				}
				if ($baseCurrency->convert(100, $cardCurrency)) {
					$amount = $credit->getBalance() * $baseCurrency->convert(100, $currentCurrency) / $baseCurrency->convert(100, $cardCurrency);
					if($creditAmount)
						return number_format(($amount - $creditAmount),2,'.','');
					else
						return number_format($amount,2,'.','');
				}
				return number_format($credit->getBalance(),2,'.','');
			}
		}
		return $this->getGiftCardBalance($credit,$store,$returnPrice);
    }
    
	public function ws_getExistedGiftCodes($store,$customerId,$giftcode)
	{
		$res = [];
		$res['status'] = 'success';
		$res['existed_giftcard_details'] = [];
		try
		{
			$quote = Mage::getModel('sales/quote');
			if($customerId)
			{
				$quote = $quote->loadByCustomer($customerId);
			}
			if($customerId && $quote->getId())
			{
				if(Mage::helper('giftvoucher')->getGeneralConfig('active', $store))
				{
					$customer = Mage::getModel('customer/customer')->load($customerId);
					$collection = Mage::getResourceModel('giftvoucher/customervoucher_collection')
								->addFieldToFilter('main_table.customer_id', $customerId);
					$voucherTable = $collection->getTable('giftvoucher/giftvoucher');
					$collection->getSelect()
								->join(array('v' => $voucherTable), 'main_table.voucher_id = v.giftvoucher_id', array('gift_code', 'balance', 'currency', 'conditions_serialized')
								)->where('v.status = ?', Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE)
								->where("v.recipient_name IS NULL OR v.recipient_name = '' OR (v.customer_id <> '" .
								$customerId . "' AND v.customer_email <> ?)", $customer->getEmail()
					);
					$addedCodes = json_decode(base64_decode($giftcode),true);
					$helper = Mage::helper('giftvoucher');
					$conditions = Mage::getSingleton('giftvoucher/giftvoucher')->getConditions();
					$quote->setQuote($quote);
					foreach ($collection as $item) {
						if(in_array($item->getGiftCode(), $addedCodes)) {
							continue;
						}
						if ($item->getConditionsSerialized()) {
							$conditionsArr = unserialize($item->getConditionsSerialized());
							if (!empty($conditionsArr) && is_array($conditionsArr)) {
								$conditions->setConditions(array())->loadArray($conditionsArr);
								if (!$conditions->validate($quote))
								{
									continue;
								}
							}
						}
						$res['existed_giftcard_details'] = array(
							'gift_code' => $item->getGiftCode(),
							'hidden_code' => $helper->getHiddenCode($item->getGiftCode()),
							'balance_format' => strip_tags($this->getGiftCardBalance($item,$store,0)),
							'balance' => strip_tags($this->getGiftCardBalance($item,$store,1))
						);
					}
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. Customer/Quote could not be fetched.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_redeemGiftCard($store,$customerId,$giftcode)
	{
		$res = [];
		$res['status'] = 'success';
		try
		{
			if(Mage::helper('giftvoucher')->getGeneralConfig('enablecredit') && Mage::helper('giftvoucher')->getGeneralConfig('active', $store))
			{
				Mage::app()->setCurrentStore($store);
				$code = base64_decode($giftcode);
				if($code && $customerId)
				{
					$giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);
					if(!$giftVoucher->getId())
					{
						$res['status']	= 'error';
						$res['message']	= 'Gift card '.$code.' is invalid.';
						return $res;
					}
					else
					{
						$conditions = $giftVoucher->getConditionsSerialized();
						if(!empty($conditions))
						{
							$conditions = unserialize($conditions);
							if(is_array($conditions) && !empty($conditions))
							{
								if(!Mage::helper('giftvoucher')->getGeneralConfig('credit_condition') && $conditions['conditions'])
								{
									$res['status']	= 'error';
									$res['message']	= 'Gift code '.$code.' has usage conditions, you cannot redeem it to Gift Card credit.';
									return $res;
								}
							}
						}
						$actions = $giftVoucher->getActionsSerialized();
						if(!empty($actions))
						{
							$actions = unserialize($actions);
							if(is_array($actions) && !empty($actions))
							{
								if(!Mage::helper('giftvoucher')->getGeneralConfig('credit_condition') && $actions['conditions'])
								{
									$res['status']	= 'error';
									$res['message']	= 'Gift code '.$code.' has usage conditions, you cannot redeem it to Gift Card credit.';
									return $res;
								}
							}
						}
						if(!$this->canUseCode($giftVoucher,$store,$customerId))
						{
							$res['status']	= 'error';
							$res['message']	= 'The gift code usage has exceeded the number of users allowed.';
							return $res;
						}
						$customer = Mage::getModel('customer/customer')->load($customerId);
						if ($giftVoucher->getBalance() == 0)
						{
							$res['status']	= 'error';
							$res['message']	= $code.' - The current balance of this gift code is 0.';
							return $res;
						}
						if($giftVoucher->getStatus() != 2 && $giftVoucher->getStatus() != 4)
						{
							$res['status']	= 'error';
							$res['message']	= 'Gift code '.$code.' is not avaliable.';
							return $res;
						}
						else
						{
							$balance = $giftVoucher->getBalance();
							$credit = Mage::getModel('giftvoucher/credit');

							$collection = $credit->getCollection()->addFieldToFilter('customer_id',$customerId);
							if($collection->getSize()){
								$id = $collection->getFirstItem()->getId();
								$credit->load($id);
							}

							$creditCurrencyCode = $credit->getCurrency();
							$baseCurrencyCode = Mage::app()->getStore()->getBaseCurrencyCode();
							if(!$creditCurrencyCode)
							{
								$creditCurrencyCode = $baseCurrencyCode;
								$credit->setCurrency($creditCurrencyCode);
								$credit->setCustomerId($customerId);
							}
							$voucherCurrency = Mage::getModel('directory/currency')->load($giftVoucher->getCurrency());
							$baseCurrency = Mage::getModel('directory/currency')->load($baseCurrencyCode);
							$creditCurrency = Mage::getModel('directory/currency')->load($creditCurrencyCode);

							$amount_temp = $balance * $balance / $baseCurrency->convert($balance, $voucherCurrency);
							$amount = $baseCurrency->convert($amount_temp, $creditCurrency);

							$credit->setBalance($credit->getBalance() + $amount);

							$credithistory = Mage::getModel('giftvoucher/credithistory')
											->setCustomerId($customer->getId())
											->setAction('Redeem')
											->setCurrencyBalance($credit->getBalance())
											->setGiftcardCode($giftVoucher->getGiftCode())
											->setBalanceChange($balance)
											->setCurrency($giftVoucher->getCurrency())
											->setCreatedDate(now());
							$history = Mage::getModel('giftvoucher/history')->setData(array(
										'order_increment_id' => '',
										'giftvoucher_id' => $giftVoucher->getId(),
										'created_at' => now(),
										'action' => Magestore_Giftvoucher_Model_Actions::ACTIONS_REDEEM,
										'amount' => $balance,
										'balance' => 0.0,
										'currency' => $giftVoucher->getCurrency(),
										'status' => $giftVoucher->getStatus(),
										'order_amount' => '',
										'comments' => Mage::helper('giftvoucher')->__('Redeem to Gift Card credit balance'),
										'extra_content' => Mage::helper('giftvoucher')->__('Redeemed by %s', $customer->getName()),
										'customer_id' => $customer->getId(),
										'customer_email' => $customer->getEmail(),
							));
							try
							{
								$giftVoucher->setBalance(0)->setStatus(Magestore_Giftvoucher_Model_Status::STATUS_USED)->save();
							}
							catch (Exception $e) {
								throw $e;
							}
							try
							{
								$credit->save();
							}catch(Exception $e)
							{
								$giftVoucher->setBalance($balance)->setStatus(Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE)->save();
								throw $e;
							}
							try
							{
								$history->save();
								$credithistory->save();
							}
							catch (Exception $e) {
								$giftVoucher->setBalance($balance)->setStatus(Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE)->save();
								$credit->setBalance($credit->getBalance() - $amount)->save();
								throw $e;
							}
						}
					}
				}
				else
				{
					$res['status']	= 'error';
					$res['message']	= 'Invalid Request. Giftcode or customer data not provided.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Could not redeem the gift card as Gift Card functionality is currently not available.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function canUseCode($code,$store = null,$customerId = null)
	{
		if (!$code) {
			return false;
		}
		if (is_string($code)) {
			$code = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);
		}
		if (!($code instanceof Magestore_Giftvoucher_Model_Giftvoucher)) {
			return false;
		}
		if (!$code->getId()) {
			return false;
		}
        //~ if (Mage::app()->getStore()->isAdmin()) {
            //~ return true;
        //~ }
		$shareCard = intval(Mage::getStoreConfig('giftvoucher/general/share_card', $store));
		if ($shareCard < 1) {
			return true;
		}
		$customersUsed = $code->getCustomerIdsUsed();
		if ($shareCard > count($customersUsed) || in_array($customerId, $customersUsed))
		{
			return true;
		}
		return false;
    }
    
	public function ws_getGiftCardConfiguration($store)
	{
		$res = [];
		$res['status'] = 'success';
		$res['giftcard_configuration'] = [];
		try
		{
			$res['giftcard_configuration']['is_enabled'] = Mage::helper('giftvoucher')->getGeneralConfig('active', $store) ? Mage::helper('giftvoucher')->getGeneralConfig('active', $store) : 0;
			$res['giftcard_configuration']['is_enabledCredit'] = Mage::helper('giftvoucher')->getGeneralConfig('enablecredit',$store) ? Mage::helper('giftvoucher')->getGeneralConfig('enablecredit',$store) : 0;
			$res['giftcard_configuration']['max_try'] = Mage::helper('giftvoucher')->getGeneralConfig('maximum',$store) ? Mage::helper('giftvoucher')->getGeneralConfig('maximum',$store) : -1;
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_applyGiftCard($store,$customerId,$session,$quote,$giftcodes)
	{
		$res['giftcodes'] = [];
		$res['status'] = 'success';
		try
		{
			$giftcodes = json_decode(base64_decode($giftcodes),true);
			if($giftcodes)
			{
				$session->setUseGiftCard(1);
				foreach($giftcodes as $code)
				{
					$giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code['gift_code']);
					if ($giftVoucher->getId() && $giftVoucher->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE && $giftVoucher->getBaseBalance() > 0 && $giftVoucher->validate($quote->setQuote($quote)))
					{
						if($this->canUseCode($giftVoucher,$store,$customerId))
						{
							$giftVoucher->addToSession($session);
						}
					}
				}
				foreach($giftcodes as $code)
				{
					if((isset($code['update']) && isset($code['update_amount'])) && $code['update'] && $code['update_amount'])
					{
						$giftMaxUseAmount = unserialize($session->getGiftMaxUseAmount());
						if(!is_array($giftMaxUseAmount))
						{
							$giftMaxUseAmount = array();
						}
						$giftMaxUseAmount[$code['gift_code']] = $code['update_amount'];
						$session->setGiftMaxUseAmount(serialize($giftMaxUseAmount));
					}
				}
			}
			else
			{
				$res['status'] = 'error';
				$res['message'] = 'Invalid Request! Please ensure you have passed correct gift code value.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

// =========================================================================================================== //

	public function ws_applyCoupon($store,$quoteId = null,$couponCode = null,$couponRemoveFlag = null)
	{
		$res = [];
		$res['status'] = 'success';
        try 
        {
			if(!$couponCode)
			{
				$res['status']  = 'error';
				$res['message'] = 'Invalid Request! Please provide the coupon code to apply/remove.';
				return $res;
			}
			Mage::app()->setCurrentStore($store);
			$quote = Mage::getModel('sales/quote');
			if($quoteId)
				$quote = $quote->loadActive($quoteId);
			if($quote->getId())
			{
				if ($couponRemoveFlag) {
					$couponCode = '';
				}
				$codeLength = strlen($couponCode);
				$isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;
				//$quote->getShippingAddress()->setCollectShippingRates(true); //Commenting this code due to api time consumption issue.
				$quote->setCouponCode($isCodeLengthValid ? $couponCode : '')	//Need to verify with frontend team for any issues.
					  ->collectTotals()
                      ->save();
				if ($codeLength) {
					if ($isCodeLengthValid && $couponCode == $quote->getCouponCode()) {
						$res["valid"] = 1;
						$res["refreshData"] = 1;
						$res['message'] = 'Coupon code '.Mage::helper('core')->escapeHtml($couponCode).' was applied.';
					} 
					else {
						$res["valid"] = 0;
						$res['message'] = 'Coupon code '.Mage::helper('core')->escapeHtml($couponCode).' is not valid.';
					}
				} 
				else {
					$res["refreshData"] = 1;
					$res['message'] = 'Coupon code was canceled.';
				}
			}
			else
			{
				$res['status']  = 'error';
				$res['message'] = 'Invalid Request the cart could not be fetched.';
			}
        }
        catch (Exception $e) {
			$res['status']  = 'error';
            $res['message'] = 'Cannot apply the coupon code.';        
        }
        return $res;		
	}


	public function getQuoteMultifeesTotal($store,$quote = null,$currentCurrencyCode)
	{
		$res = [];$count = 0;
		$res['total']	= $count;
		$res["status"]	= 'success';
		$res['price'] 	= '';
		$res['title'] 	= '';
		$res['fees'] 	= [];
		Mage::app()->setCurrentStore($store);
		$baseCurrencyCode   = Mage::app()->getStore()->getBaseCurrencyCode();
		$helper = Mage::helper('multifees');
		if ($helper->isEnabled())
		{
			if($quote && $quote->getId())
			{
				$totals = $quote->getTotals();
				if (Mage::helper('multifees')->getTaxInCart() == 2) 
					$inclTax = true; 
				else 
					$inclTax = false;

				if(isset($totals["tax_multifees"]))
					$inclTax = true;
				if(isset($totals["multifees"]))
				{
					$res['title'] = $totals["multifees"]->getTitle();
					$res['price'] = number_format($this->convert_currency($totals["multifees"]->getValue(), $baseCurrencyCode, $currentCurrencyCode), 2, '.', '');
					$feesData = $totals["multifees"]->getFullInfo();
					$feesData = unserialize($feesData);
					foreach($feesData as $fee)
					{
						$optionCount = 0;
						$options = [];
						foreach ($fee['options'] as $option)
						{
							$optionPrice = $inclTax ? $option['price'] : $option['price'] - $option['tax'];
							$optionPercent = isset($option['percent']) ? ' ('.(float)$option['percent'].'%)' : '';
							$options[$optionCount]['title'] = $option['title'].$optionPercent;
							$options[$optionCount]['price'] = number_format($this->convert_currency($optionPrice, $baseCurrencyCode, $currentCurrencyCode), 2, '.', '');
							$optionCount++;
						}
						$feePrice = $inclTax ? $fee['price']:$fee['price'] - $fee['tax'];
						$res['fees'][$count]['title'] = $fee['title'];
						$res['fees'][$count]['price'] = number_format($this->convert_currency($feePrice, $baseCurrencyCode, $currentCurrencyCode), 2, '.', '');
						$res['fees'][$count]['options'] = $options;
						$count++;
					}
						//echo "<pre>";print_r($feesData);
				}
				$res["total"]	= $count;
			}
			else
			{
				$res['status'] = "error";
				$res['message'] = "The Cart could not be fetched for the respective Quote/Customer";
			}
		}
		return $res;
	}

	public function ws_getNewCategoryFilter($store,$categoryId,$filterData,$getCollection = 0){
		$filterCount 	= 0;
		$actvCount		= 0;
		$res = $activeFilters = $filter = $params = [];
		$res['status'] 	= 'success';
		$res['total']	= 0;
		$res['activeFilters'] = $res['filters'] = [];
		if($categoryId)
		{
			try
			{
				Mage::app()->setCurrentStore($store);
				$layer = Mage::getModel("catalog/layer");
				$category = Mage::getModel("catalog/category")->load($categoryId);
				$layer->setCurrentCategory($category);
				$attributes = $layer->getFilterableAttributes();
				$filterData = json_decode($filterData, true);
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
					return $layer->getProductCollection(); //returning the product collection after applying the filters to the getLayerCollection api.
				/* Commented the code for fetching the updated category filters as category filter is commented on the website. */
				//~ if(!array_key_exists('cat',$params))
					//~ $params['cat'] = $categoryId;
				//~ if(array_key_exists('cat',$params))
				//~ {
					//~ $catId	= $params['cat'];
					//~ $appliedCategory = Mage::getModel('catalog/category')->load($catId);
					//~ $subCategories = $appliedCategory->getChildrenCategories();
					//~ $layer->getProductCollection()->addCountToCategories($subCategories);
					//~ $catCount	= 0;
					//~ $catData 	= [];
					//~ foreach ($subCategories as $categ) {
						//~ if ($categ->getIsActive() && $categ->getProductCount()) {
							//~ $catData[$catCount]["label"] = strip_tags($categ->getName());
							//~ $catData[$catCount]["value"] = $categ->getId();
							//~ $catData[$catCount]["count"] = $categ->getProductCount();
							//~ $catCount++;
						//~ }
					//~ }
					//~ if($catData)
					//~ {
						//~ $filter[$filterCount]["code"]	= 'cat';
						//~ $filter[$filterCount]["type"]	= 'Select';
						//~ $filter[$filterCount]["label"]	= 'Category';
						//~ $filter[$filterCount]["values"] = $catData;
						//~ $filterCount++;
					//~ }
				//~ }

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
				$res['status'] 	= 'error';
				$res['message']	= $e->getMessage();
			}
		}
		else
		{
			$res['status'] 	= 'error';
			$res['message']	= 'Invalid Request! Please specify the category id.';
		}
		return $res;
	}

	public function ws_getLayerCollection($store, $categoryId, $filterData, $curr_page = null, $page_size = null, $sortType = null, $sortOrder = null, $currentcurrencycode){
		$res = [];
		$res['status']	= 'success';
		$res['total'] 	= 0;
		$res['data']	= [];
		if($categoryId)
		{
			try
			{
				Mage::app()->setCurrentStore($store);
				$basecurrencycode	= Mage::app()->getStore()->getBaseCurrencyCode();
				if(!isset($curr_page))
				{
					$curr_page	= 1;
				}
				if(!isset($page_size))
				{
					$page_size	= 20;
				}
				if(!isset($sortType))
				{
					$sortType	= 'name';
				}
				if(!isset($sortOrder))
				{
					$sortOrder	= 'asc';
				}
				$collection	= $this->ws_getNewCategoryFilter($store,$categoryId,$filterData,1); //getting the product collection based on the filters applied.
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
					$collection->addAttributeToSort($sortType, $sortOrder); //applying the sorting on the collection.
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
						//---------------- Custom variable related changes -----------------//
						$_helper = Mage::helper('catalog/output');
						$availability = '';
						if (strpos($_product->getAttributeText('soko_format'),'Kit') !== false)
							$availability	= 'In Stock';
						else
						{
							if($_product->isSaleable())
							{
								$availabilityValue = explode("</strong>",Mage::helper('hpmodules')->crazy($_helper->productAttribute($_product, $_product->getSKU(), 'sku')));
								if(count($availabilityValue) > 1)
									$availability = $availabilityValue[1];
								else
									$availability = $availabilityValue[0];
							}
							else
								$availability 	= 'Out of Stock';
						}
						//------------------------------------------------------------------//
						if($gflag)
						{
							$res["data"][] = array(
								"id" => $_product->getId(),
								"name" => $this->getNamePrefix($_product).$_product->getName(),
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
								"availability" => $availability, //custom variable
								"soko_rating_value" => $_product->getAttributeText('soko_rating') ? $_product->getAttributeText('soko_rating') : '' , //custom variable
								'soko_format_value' => $_product->getAttributeText('soko_format') ? $_product->getAttributeText('soko_format') : '',  //custom variable
								"description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) : '' //custom variable
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
			$res['message']	= 'Invalid Request! Please specify the category id.';
		}
		return $res;
	}

	public function ws_noShipStates()
	{
		$res = $states = [];
		$res['status'] = 'success';
		$res['total'] = $count = 0;
		try
		{
			$resource = Mage::getSingleton('core/resource');
			$readConnection = $resource->getConnection('core_read');
			$query = "Select state from soko_noship_states";
			if($readConnection->isTableExists('soko_noship_states'))
			{
				$results = $readConnection->fetchAll($query);
				foreach($results as $row)
				{
					$states[$count++] = $row['state'];
				}
			}
			$res['total'] 	= $count;
			$res['states'] 	= $states;
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_addGiftCard($store,$customerId,$giftcode)
	{
		$res = [];
		$res['status'] = 'success';
		$res['message'] = 'The gift code has been added to your list successfully.';
		try
		{
			Mage::app()->setCurrentStore($store);
			$code = base64_decode($giftcode);
			if($code && $customerId) {
				$giftVoucher = Mage::getModel('giftvoucher/giftvoucher')->loadByCode($code);
				if($giftVoucher->getId())
				{
					if(!$this->canUseCode($giftVoucher,$store,$customerId))
					{
						$res['status']	= 'error';
						$res['message']	= 'The gift code usage has exceeded the number of users allowed.';
						return $res;
					}
					$collection = Mage::getModel('giftvoucher/customervoucher')->getCollection();
					$collection->addFieldToFilter('customer_id', $customerId)->addFieldToFilter('voucher_id', $giftVoucher->getId());
					if($collection->getSize())
					{
						$res['status']	= 'error';
						$res['message']	= 'This gift code already exist\'s in your list.';
						return $res;
					}
					elseif($giftVoucher->getStatus() != 1 && $giftVoucher->getStatus() != 2 && $giftVoucher->getStatus() != 4)
					{
						$res['status']	= 'error';
						$res['message']	= 'Gift code '.$code.' is not avaliable';
						return $res;
					}
					else
					{
						$model = Mage::getModel('giftvoucher/customervoucher')
								->setCustomerId($customerId)
								->setVoucherId($giftVoucher->getId())
								->setAddedDate(now());
						$model->save();
					}
				}
				else
				{
					$res['status'] = 'error';
					$res['message'] = 'Gift card '.$code.' is invalid.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request. Giftcode or customer data not provided.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_removeGiftCard($store,$customerId,$giftCardId)
	{
		$res = [];
		$res['status'] = 'success';
		$res['message'] = 'Gift card was successfully removed';
		try
		{
			if($giftCardId && $customerId)
			{
				Mage::app()->setCurrentStore($store);
				$voucher = Mage::getModel('giftvoucher/customervoucher')->load($giftCardId);
				if($voucher && $voucher->getCustomerId() == $customerId)
					$voucher->delete();
				else
				{
					$res['status'] = 'error';
					if($voucher->getId())
						$res['message'] = 'Cannot remove gift card. This gift card is not associated with the customer.';
					else
						$res['message'] = 'Unable to be find gift card associated with provided id.';
				}
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request! Unable to fetch customer/gift card id.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_getGiftCardList($store,$customerId,$curr_page = null, $page_size = null)
	{
		$res = [];
		$res['status'] = 'success';
		$count = 0;
		$giftCodes = [];
		try
		{
			if($customerId)
			{
				$curr_page = isset($curr_page) ? $curr_page : 1;
				$page_size = isset($page_size) ? $page_size : 10;
				Mage::app()->setCurrentStore($store);
				$storeData = Mage::app()->getStore($store);
				$credit = Mage::getModel('giftvoucher/credit')->load($customerId,'customer_id');
				$currency = $storeData->getCurrentCurrency();
				$timezoneValue = Mage::app()->getStore()->getConfig('general/locale/timezone');
				$timezone = ((Mage::app()->getLocale()->date()->get(Zend_Date::TIMEZONE_SECS)) / 3600);
				$statuses = Mage::getSingleton('giftvoucher/status')->getOptionArray();
				$customer = Mage::getModel('customer/customer')->load($customerId);        
				$collection = Mage::getModel('giftvoucher/customervoucher')->getCollection()->addFieldToFilter('main_table.customer_id', $customerId);
				$voucherTable = Mage::getModel('core/resource')->getTableName('giftvoucher');
				$collection->getSelect()
						->joinleft(array('voucher_table' => $voucherTable), 'main_table.voucher_id = voucher_table.giftvoucher_id', array('recipient_name', 'gift_code', 'balance', 'currency', 'status', 'expired_at', 'customer_check_id' => 'voucher_table.customer_id', 'recipient_email', 'customer_email'))
						->where('voucher_table.status <> ?', Magestore_Giftvoucher_Model_Status::STATUS_DELETED);
				$collection->getSelect()
						->columns(array(
							'added_date' => new Zend_Db_Expr("SUBDATE(added_date,INTERVAL " . $timezone . " HOUR)"),
				));
				$collection->getSelect()
						->columns(array(
							'expired_at' => new Zend_Db_Expr("SUBDATE(expired_at,INTERVAL " . $timezone . " HOUR)"),
				));
				$collection->setOrder('customer_voucher_id', 'DESC');
				$collection->getSelect()->limit($page_size,(($curr_page - 1) * $page_size));
				$res['enable_giftcard']	= Mage::helper('giftvoucher')->getGeneralConfig('active', $store) ? true : false;
				$res['enable_credit']	= Mage::helper('giftvoucher')->getGeneralConfig('enablecredit',$store) ? true : false;
				$res['credit_amount']	= strip_tags($currency->format($storeData->convertPrice($credit->getBalance())));
				$res['timezone_data']	= 'Date is set in timezone '.$timezoneValue;
				if($collection->getSize())
				{
					foreach($collection as $row)
					{
						$giftCodes[$count]['customer_voucher_id']	= $row->getId();
						$giftCodes[$count]['voucher_id'] = $row->getVoucherId();
						$giftCodes[$count]['code']	= $row->getGiftCode();
						$giftCodes[$count]['hidden_code']	= Mage::helper('giftvoucher')->getHiddenCode($row->getGiftCode());
						$giftCodes[$count]['balance']	= strip_tags($this->getGiftCardBalance($row,$store,0));
						$giftCodes[$count]['status']	= $statuses[$row->getStatus()];
						$giftCodes[$count]['added_date'] = Mage::helper('core')->formatDate($row->getAddedDate(),'medium',false);
						$giftCodes[$count]['expire_date'] = Mage::helper('core')->formatDate($row->getExpiredAt(),'medium',false);
						$giftCodes[$count]['recipient_name'] = $row->getRecipientName() ? $row->getRecipientName() : '';
						$giftCodes[$count]['recipient_email'] = $row->getRecipientEmail() ? $row->getRecipientEmail() : '';
						$giftCodes[$count]['customer_email'] = $row->getCustomerEmail() ? $row->getCustomerEmail() : '';
						$giftCodes[$count]['is_redeem'] = false;
						$giftCodes[$count]['can_email'] = false;
						$available = $this->canUseCode(Mage::getModel('giftvoucher/giftvoucher')->load($row->getVoucherId()));
						if (Mage::helper('giftvoucher')->getGeneralConfig('enablecredit') && $available) {
							if ($row->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE || ($row->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_USED && $row->getBalance() > 0)) {
								$giftCodes[$count]['is_redeem'] = true;
							}
						}
						if($row->getStatus() < Magestore_Giftvoucher_Model_Status::STATUS_DISABLED) {
							if($row->getRecipientName() && $row->getRecipientEmail() && ($row->getCustomerId() == $customerId || $row->getCustomerEmail() == $customer->getEmail())) {
								$giftCodes[$count]['can_email'] = true;
							}
						}
						$count++;
					}
				}
				$res['total'] = $collection->getSize();
				$res['giftcodes'] = $giftCodes;
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request! Please provide customer id.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_sendGiftCardEmail($store,$customerId,$giftCardDetails)
	{
		$res = [];
		$res['status']	= 'success';
		$res['message']	= 'The Gift Card email has been sent successfully.';
		try
		{
			Mage::app()->setCurrentStore($store);
			$giftCardDetails = json_decode(base64_decode($giftCardDetails),true);
			if($customerId && (isset($giftCardDetails['name']) && $giftCardDetails['name'] && isset($giftCardDetails['email']) && $giftCardDetails['email'] && isset($giftCardDetails['id']) && $giftCardDetails['id'] && isset($giftCardDetails['message'])
			&& $giftCardDetails['message']))
			{
				$giftCard = Mage::getModel('giftvoucher/giftvoucher')->load($giftCardDetails['id']);
				$customer = Mage::getModel('customer/customer')->load($customerId);        

				$params = Mage::app()->getRequest()->getParams();
				$params['giftcard_id'] = $giftCardDetails['id'];
				$params['recipient_name'] = $giftCardDetails['name'];
				$params['recipient_email'] = $giftCardDetails['email'];
				$params['message'] = $giftCardDetails['message'];
				Mage::app()->getRequest()->setParams($params);

				if (!$customer || ($giftCard->getCustomerId() != $customer->getId() && $giftCard->getCustomerEmail() != $customer->getEmail())) 
				{
					$res['status']	= 'error';
					$res['message']	= 'The Gift Card email has been failed to send.';
					return $res;
				}
				$translate = Mage::getSingleton('core/translate');
				$translate->setTranslateInline(false);
				if (!$giftCard->sendEmailToRecipient()) {
					$res['status']	= 'error';
					$res['message']	= 'The Gift Card email cannot be sent to your friend!';
					return $res;
				}
				$translate->setTranslateInline(true);
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request! Insufficient data passed to process the request.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
	}

	public function ws_getGiftCardDetails($store,$customerId,$giftCardId)
	{
		$res = [];
		$res['status'] = 'success';
		try
		{
			if($customerId && $giftCardId)
			{
				$customerGift = Mage::getModel('giftvoucher/customervoucher')->load($giftCardId);
				$giftVoucher  = Mage::getModel('giftvoucher/giftvoucher')->load($customerGift->getVoucherId());
				if ($customerGift->getCustomerId() != $customerId) {
					throw new Exception('Access Denied for current customer');
				}
				$statuses = Mage::getSingleton('giftvoucher/status')->getOptionArray();
				$res['customer_voucher_id']	= $customerGift->getCustomerVoucherId();
				$res['voucher_id'] = $customerGift->getVoucherId();
				$res['code'] 	= $giftVoucher->getGiftCode();
				$res['hidden_code']	= Mage::helper('giftvoucher')->getHiddenCode($giftVoucher->getGiftCode());
				$res['balance']	= strip_tags($this->getGiftCardBalance($giftVoucher,$store,0));
				$res['gift_status']	= $statuses[$giftVoucher->getStatus()];
				$res['added_date'] = Mage::helper('core')->formatDate($giftVoucher->getAddedDate(),'medium',false);
				$res['expire_date'] = Mage::helper('core')->formatDate($giftVoucher->getExpiredAt(),'medium',false);
				$res['description'] = '';
				if($giftVoucher->getDescription())
					$res['description'] = $giftVoucher->getDescription();
				$res['comment'] = '';
				if($giftVoucher->getRecipientName() && $giftVoucher->getRecipientEmail() && $giftVoucher->getCustomerId() == $customerId)
					$res['comment'] = 'This is your gift to give for '. $giftVoucher->getRecipientName() .' ( '. $giftVoucher->getRecipientEmail() .')';
				$res['recipient_name'] = $giftVoucher->getRecipientName() ? $giftVoucher->getRecipientName() : '';
				$res['recipient_email'] = $giftVoucher->getRecipientEmail() ? $giftVoucher->getRecipientEmail() : '';
				$res['customer_email'] = $giftVoucher->getCustomerEmail() ? $giftVoucher->getCustomerEmail() : '';
				$res['is_redeem'] = false;
				$res['can_email'] = false;
				if ($giftVoucher->getStatus() == Magestore_Giftvoucher_Model_Status::STATUS_ACTIVE && Mage::helper('giftvoucher')->getGeneralConfig('enablecredit')) {
					$res['is_redeem'] = true;
				}
				if($giftVoucher->getRecipientName() && $giftVoucher->getRecipientEmail() && ($giftVoucher->getCustomerId() == $customerId)) {
					$res['can_email'] = true;
				}
				$historyCollection = Mage::getResourceModel('giftvoucher/history_collection')->addFieldToFilter('main_table.giftvoucher_id', $giftVoucher->getId());
				if ($giftVoucher->getCustomerId() != $customerId)
				{
					$historyCollection->addFieldToFilter('main_table.customer_id', $customerId);
				}
				$historyCollection->getSelect()->order('main_table.created_at DESC');
				$historyCollection->getSelect()
						->joinLeft(array('o' => $historyCollection->getTable('sales/order')), 'main_table.order_increment_id = o.increment_id', array('order_id' => 'entity_id')
				);
				$history = [];
				$histCount = 0;
				if(count($historyCollection))
				{
					foreach($historyCollection as $_item)
					{
						$currency = Mage::getModel('directory/currency')->load($_item->getCurrency());
						$actions = Mage::getSingleton('giftvoucher/actions')->getOptionArray();
						if (isset($actions[$_item->getAction()])) {
							$history[$histCount]['action'] = $actions[$_item->getAction()];
						}
						reset($actions);
						$history[$histCount]['action']	 = current($actions);
						if(is_null($_item->getBalance()))
							$history[$histCount]['balance'] = 'N/A';
						else
						{
							$history[$histCount]['balance']	 = strip_tags($currency->format($_item->getBalance()));
						}
						$history[$histCount]['date'] = Mage::helper('core')->formatDate($_item->getCreatedAt(),'medium',false);
						$history[$histCount]['balance_change'] = strip_tags($currency->format($giftVoucher->getAmount()));
						$history[$histCount]['order_id'] = '';
						if($_item->getOrderIncrementId())
						{
							$history[$histCount]['order_id'] = (string)$_item->getOrderIncrementId();
						}
						$history[$histCount]['comment'] = '';
						if ($_item->getCustomerId() == Mage::getSingleton('customer/session')->getCustomerId()) {
							$history[$histCount]['comment'] = strip_tags($_item->getComments());
						} else {
							$email_history = $_item->getCustomerEmail();
							if ($email_history)
								$history[$histCount]['comment'] = strip_tags($_item->getExtraContent()) . ' (' . strip_tags($email_history) . ')';
							else
								$history[$histCount]['comment'] = $_item->getExtraContent() != null ? strip_tags($_item->getExtraContent()) : strip_tags($_item->getComments());
						}
						$histCount++;
					}
				}
				$res['history'] = $history;
			}
			else
			{
				$res['status']	= 'error';
				$res['message']	= 'Invalid Request! Insufficient data passed to process the request.';
			}
		}
		catch(Exception $e)
		{
			$res['status']	= 'error';
			$res['message']	= $e->getMessage();
		}
		return $res;
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
		$res['status'] 	= 'success';
		$res['total']	= 0;
		$res['activeFilters'] = $res['filters'] = [];
		$res['redirect_category_id'] = '';
		$res['redirect_product_id'] = '';
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
					if ($query->getId()) {
						if($getCollection)
							$query->setPopularity($query->getPopularity()+1); //Updating the popularity only in case of loading product collection,
					}														  //otherwise popularity would be incremented twice per search on the app.
					else {
						if($getCollection)
							$query->setPopularity(1);
					}
					if ($query->getRedirect()){
						$query->save();
						/* ------------ Additional code for finding redirects of search term in case of products and category only ------------ */
						$redirectPath = strtok(str_replace(Mage::getBaseUrl(),'',$query->getRedirect()),"?"); //removing get parameters if any from the url.
						$oRewrite = Mage::getModel('core/url_rewrite')->setStoreId($store)->loadByRequestPath($redirectPath);
						if(isset($oRewrite['product_id']) && $oRewrite->getProductId())
							$res['redirect_product_id'] = $oRewrite->getProductId();
						else
						{
							if(isset($oRewrite['category_id']) && $oRewrite->getCategoryId())
								$res['redirect_category_id'] = $oRewrite->getCategoryId();
						}
						/* -------------------------------------------------------------------------------------------------------------------- */
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
					}
					$res['total']	= $filterCount;
					$res['filters']	= $filter; //setting the updated filters to the data. 
					$res['activeFilters'] = $activeFilters; //setting the applied filters to the return data.
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

	public function canShowBlock($layer)
	{
		$_isLNAllowedByEngine = Mage::helper('catalogsearch')->getEngine()->isLeyeredNavigationAllowed();
		if (!$_isLNAllowedByEngine) {
			return false;
		}
		$availableResCount = (int) Mage::app()->getStore()
			->getConfig(Mage_CatalogSearch_Model_Layer::XML_PATH_DISPLAY_LAYER_COUNT);
		if (!$availableResCount
			|| ($availableResCount > $layer->getProductCollection()->getSize())) {
			return true;//return parent::canShowBlock();
		}
		return false;
	}


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
						//---------------- Custom variable related changes -----------------//
						$_helper = Mage::helper('catalog/output');
						$availability = '';
						if (strpos($_product->getAttributeText('soko_format'),'Kit') !== false)
							$availability	= 'In Stock';
						else
						{
							if($_product->isSaleable())
							{
								$availabilityValue = explode("</strong>",Mage::helper('hpmodules')->crazy($_helper->productAttribute($_product, $_product->getSKU(), 'sku')));
								if(count($availabilityValue) > 1)
									$availability = $availabilityValue[1];
								else
									$availability = $availabilityValue[0];
							}
							else
								$availability 	= 'Out of Stock';
						}
						//------------------------------------------------------------------//
						if($gflag)
						{
							$res["data"][] = array(
								"id" => $_product->getId(),
								"name" => $this->getNamePrefix($_product).$_product->getName(),
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
								"availability" => $availability, //custom variable
								"soko_rating_value" => $_product->getAttributeText('soko_rating') ? $_product->getAttributeText('soko_rating') : '' , //custom variable
								'soko_format_value' => $_product->getAttributeText('soko_format') ? $_product->getAttributeText('soko_format') : '',  //custom variable
								"description" => $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) ? $_product->getResource()->getAttributeRawValue($_product->getId(),'description',$store_id) : '' //custom variable
							);
						}
					}
				}
				/* ------------ Additional code for finding redirects of search term in case of products and category only ------------ */
				$query = Mage::helper('catalogsearch')->getQuery();
				$query->setStoreId($store);
				if($query->getQueryText() != '') {
					if ($query->getRedirect()){
						$redirectPath = strtok(str_replace(Mage::getBaseUrl(),'',$query->getRedirect()),"?"); //removing get parameters if any from the url.
						$oRewrite = Mage::getModel('core/url_rewrite')->setStoreId($store)->loadByRequestPath($redirectPath);
						if(isset($oRewrite['product_id']) && $oRewrite->getProductId())
							$res['redirect_product_id'] = $oRewrite->getProductId();
						else
						{
							if(isset($oRewrite['category_id']) && $oRewrite->getCategoryId())
								$res['redirect_category_id'] = $oRewrite->getCategoryId();
						}
					}
				}
				/* -------------------------------------------------------------------------------------------------------------------- */
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

	public function ws_getSearchSuggestion($store,$searchData)
	{
		$res = [];
		$res['status'] = 'success';
		$res['total'] = $counter = 0;
		$res['suggest_terms'] = $data = [];
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
				if(!Mage::helper('catalogsearch')->isMinQueryLength())
				{
					$suggestCollection = $query->getSuggestCollection();
					foreach ($suggestCollection as $item) {
						$_data = array(
							'title' => $item->getQueryText(),
							'num_of_results' => $item->getNumResults()
						);
						if ($item->getQueryText() == $query->getQueryText()) {
							array_unshift($data, $_data);
						}
						else {
							$data[] = $_data;
						}
					}
					$res['total'] = $suggestCollection->getSize();
					$res['suggest_terms'] = $data;
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
	
	
	/***** Sokolin Chat system starts here ****/
	
	public function create_new_request($customerid, $message, $sender, $receiver, $file, $video) {
		
		try {
			
			// remove this this is not part of code
			$customername = "JUNK";
			
			$requestid = 1;
			
			$productid = '';
			
			if($message == null) $message = '';
			
			/**** incrementing the total request ****/
			$reqmodel = Mage::getModel('mofluid_chat/totalcounter')->load($customerid);
			if($reqmodel->getId()) {
				$requestid = $reqmodel->getRequestId()+1;
				$reqmodel->setRequestId($requestid);
				$reqmodel->save();
			}else {
				$reqmodel = Mage::getModel('mofluid_chat/totalcounter');
				$reqmodel->setId($customerid);
				$reqmodel->setCustomerId($customerid);
				$reqmodel->setRequestId(1);
				$reqmodel->save();
			}
			
			/**** uploading the image and getting image path ****/
			$imgpath = '';
			$productName = '';
			$videopath ='';
			
			if($file) {
				$uploadres = $this->uploadImage($customerid, $requestid, $file);
				if($uploadres["status"] == 0) {return $uploadres;};
				$imgpath = $uploadres["imgpath"];
			}
			
			if($video) {
				$uploadres = $this->uploadImage($customerid, $requestid, $video);
				if($uploadres["status"] == 0) {return $uploadres;};
				$videopath = $uploadres["imgpath"];
			}
			
			/**** creating the json format to save in the table ****/
			
			$messagearr = array("sender"=>$sender, "receiver"=>$receiver, "imagepath"=>$imgpath ,
								"created_at"=>Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
								"message"=>$message, "productid"=>$productid, "productname"=>$productName,
								"videopath"=>$videopath);
			$msgArr[] = $messagearr;
			$messagejson = json_encode($msgArr);
			
			/**** saving the data in admin table ****/
			$adminmodel = Mage::getModel('mofluid_chat/adminchat');
			$adminmodel->setCustomerId($customerid);
			$adminmodel->setRequestId($requestid);
			$adminmodel->setCreatedAt(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
			$adminmodel->setUpdatedAt(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
			$adminmodel->setMessage($messagejson);
			$adminmodel->setMessageCount(1);
			$adminmodel->setCustomerName($customername); // remaining to set the customername
			$adminmodel->save();
			
			$id = $adminmodel->getId();
			
			$res["status"] = 1;
			$res["message"] = "success";
			$res["id"] = $id;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Error in creating new request".$e->getMessage();
		}
		return $res;
	}
	
	public function update_existing_request($id, $message, $sender, $receiver, $file, $productid, $video) {
		
		try {
			
			if($message == null) $message = '';
			
			/**** uploading the image and getting image path ****/
			$adminmodel = Mage::getModel('mofluid_chat/adminchat')->load($id);
			$requestid = $adminmodel->getRequestId();
			$customerid = $adminmodel->getCustomerId();
			$prevMsg = json_decode($adminmodel->getMessage(), true);
			
			// specialVar to handle both situation 
			$specialVar = 0;
			// if product id is not sent then
			if(!$productid) $productid = '';
			
			$imgpath = '';
			$productName = '';
			$videopath ='';
			
			if($video) {
				$uploadres = $this->uploadImage($customerid, $requestid, $video);
				if($uploadres["status"] == 0) {return $uploadres;};
				$videopath = $uploadres["imgpath"];
			}
			
			if($file) {
				/*** file is sent ****/
				$uploadres = $this->uploadImage($customerid, $requestid, $file);
				if($uploadres["status"] == 0) {return $uploadres;};
				$imgpath = $uploadres["imgpath"];
			}
			
			if($productid) {
				/**** only product is sent ***/
				$productImage = $this->query($productid);
				if($productImage["status"] == 1){
					$imgpath =  $productImage["image_path"];
					$productName = $productImage["product_name"];
					/*** IMP :: use it only for testing purpose uncomment it ***/
					$imgpath = "http://sokolin.ebizontech.biz/media/catalog/product/5/9/59739_zoom.jpg";
				}
			}
			
			/**** creating the json format to save in the table ****/
			
			$messagearr = array("sender"=>$sender, "receiver"=>$receiver, "imagepath"=>$imgpath ,
								"created_at"=>Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'),
								"message"=>$message, "productid"=>$productid, "productname"=>$productName,
								"videopath"=>$videopath);
								
								
			array_push($prevMsg, $messagearr);
			$prevMsg = json_encode($prevMsg);
			
			/**** saving the data in admin table ****/
			
			$adminmodel->setMessage($prevMsg);
			$adminmodel->setUpdatedAt(Mage::getModel('core/date')->gmtDate('Y-m-d H:i:s'));
			$adminmodel->save();
			
			$id = $adminmodel->getId();
			
			$res["status"] = 1;
			$res["message"] = "success";
			$res["id"] = $id;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Error in updating request".$e->getMessage();
		}
		return $res;
		
	}
	
	public function get_all_requests($customerid) {
		
		try {
			$customerid = ''.$customerid.'';
			$requests = array();
			
			/**** getting the collection of id, requestid and customerid ****/
			$collection = Mage::getModel('mofluid_chat/adminchat')->getCollection()->addFieldToSelect(array('id','customer_id','request_id','created_at','message')); 
			
			/*** filtering the collection first by customerid and then by requestid ****/
			$collection = $collection->addFieldToFilter('customer_id', array('eq'=>$customerid)); 
			
			/**** getting all the requests_id and their corresponding id ****/
			foreach($collection as $col) {
				$temp = json_decode($col->getMessage(), true);
				$temp = $temp[0];
				array_push($requests, array("requests_id" => $col->getRequestId(), "id" => $col->getId(), "message"=> $temp));
				//$requests[$col->getRequestId()] =  $col->getId();
			}
			
			$res["customer_id"] = $customerid;
			$res["requests_id"] = $requests;
			$res["status"] = 1;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Failed to list <br>".$e->getMessage();
		}
		return $res;
	}
		
	
	public function get_all_message($id) {
		
		try {
			$adminmodel = Mage::getModel('mofluid_chat/adminchat')->load($id);
			$res["status"] = 1;
			$res["customerName"] = $adminmodel->getCustomerName();
			$res["customerId"] = $adminmodel->getCustomerId();
			$res["id"] = $id;
			$res["message"] = json_decode($adminmodel->getMessage());
		}catch(Exception $e) {
			$res["message"] = "Failed in get_all message <br> ". $e->getMessage();
			$res["status"] = 0;
		}
		return $res;
	}

	
	public function processDir($customerid, $requestid) {
		
		/**** For processing the directory ****/
		
		$res = array();
		
		try {
			/**** getting varien object *****/
			$io = new Varien_Io_File();
			$basedir = Mage::getBaseDir('media');
			$baseUrl = Mage::getBaseUrl('media');
			
			/**** Create Chat dir ****/
			$chatbasedir = $basedir.'/Chat';
			$chatbaseurl = $baseUrl.'/Chat';
			if (!$io->fileExists($chatbasedir, false)) {
				$io->mkdir($chatbasedir);
			}
			
			/**** Create Customerid dir ****/
			$idbasedir = $chatbasedir.'/cust_'.$customerid;
			$idbaseurl = $chatbaseurl.'/cust_'.$customerid;
			if (!$io->fileExists($idbasedir, false)) {
				$io->mkdir($idbasedir);
			}
			
			/**** Create Request dir *****/
			$requestbasedir = $idbasedir.'/req_'.$requestid;
			$requestbaseurl = $idbaseurl.'/req_'.$requestid;
			if (!$io->fileExists($requestbasedir, false)) {
				$io->mkdir($requestbasedir);
			}
			
			/**** returning curr dir ****/
			$res["status"] = 1;
			$res["message"] = "success";
			$res["dir"] = $requestbasedir.'/';
			$res["imgurl"] = $requestbaseurl.'/';
		}catch(Exception $e) {
			/*** status = 0 for failure ***/
			$res["status"] = 0;
			$res["message"] = $e->getMessage();
		}
		
		return $res;
	}
	
	public function uploadImage($customerid, $requestid, $file) {
		
		$res = array();
			
		/**** getting the file parameters ****/
		$fileName = $file['name'];
		$fileTmpName = $file['tmp_name'];
		$fileSize = $file['size'];
		$fileError = $file['error'];
		$fileType = $file['type'];
		
		/**** separate the file by to get extension ****/
		$fileExt = explode('.', $fileName);
		$fileActualExt = strtolower(end($fileExt));
		
		if($fileError == 0) {
			/**** getting the unique name for the customer ****/
			$fileNameNew = uniqid().".".$fileActualExt;
			
			/**** getting the name for the required directory ****/
			$tempDir = $this->processDir($customerid, $requestid);
			
			if($tempDir["status"] == 0 ) {
				$res["message"] = $tempDir["message"];
				$res["status"] = 0;
			}else {
				$fileDestination = $tempDir["dir"].$fileNameNew;
				$imgurl = $tempDir["imgurl"].$fileNameNew;
				/**** moving the required file to required folder ****/
				move_uploaded_file($fileTmpName, $fileDestination);
				$res["message"] = "success";
				$res["status"] = 1;
				$res["imgpath"] = $imgurl;
			}
		}else {
			$res["message"] = "There was error uploading file Maybe file format not supported.";
			$res["status"] = 0;
		}
		return $res;
	}
	
	/**** specifically of no use till now ****/
	
	public function getcustpkid($customerid, $requestid) {
		try {
			
			/**** getting the id of the given customerid and requestid ****/
			
			$customerid = ''.$customerid.'';
			$requestid = ''.$requestid.'';
			
			/**** getting the collection of id, requestid and customerid ****/
			$collection = Mage::getModel('mofluid_chat/adminchat')->getCollection()->addFieldToSelect(array('id','customer_id','request_id'));  
			
			/*** filtering the collection first by customerid and then by requestid ****/
			$collection = $collection->addFieldToFilter('customer_id', array('eq'=>$customerid));
			$collection = $collection->addFieldToFilter('request_id', array('eq'=>$requestid));
			
			/**** Finally getting the id, it will be unique understand ****/
			$res["id"] = $collection->getId();
			$res["message"] = "success";
			$res["status"] = 1;
		}catch(Exception $e) {
			$res["status"] = 0;
			$res["message"] = "Failed to list <br>".$e->getMessage();
		}
		return $res;
	}
	
	public function query($proid) {
		
		$res = array();
		if(!is_numeric($proid) || $proid <= 0) {
			$res["status"] = 0;
			$res["message"] = "You have entered wrong input for id";
		}
		else {
			try {
				/**** getting the path of product image ****/
				$baseUrl = Mage::getBaseUrl('media');
				$image = Mage::getResourceModel('catalog/product')->getAttributeRawValue($proid, 'image', Mage::app()->getStore()->getId());
				$imagePath = $baseUrl.'catalog/'.'product'.$image;
				
				/**** getting the name of the product ****/
				$product_name = Mage::getResourceModel('catalog/product')->getAttributeRawValue($proid, 'name', Mage::app()->getStore()->getId());
				
				/**** getting the description of the product ****/
				$product_desc = Mage::getResourceModel('catalog/product')->getAttributeRawValue($proid, 'description', Mage::app()->getStore()->getId());

				if($product_name) {
					$res["status"] = 1;
					$res["message"] = 'success';
					$res["product_name"] = $product_name;
					$res["product_description"] = !$product_desc ? '': $product_desc;
					$res["image_path"] = $imagePath;
				}else {
					$res["status"] = 0;
					$res["message"] = "No Product Present for this Id.";
				}
			}catch(Exception $e) {
				$res["status"] = 0;
				$res["message"] = "Error in query ".$e->getMessage();
			}
		}
		
		return $res;
	}
	
}

