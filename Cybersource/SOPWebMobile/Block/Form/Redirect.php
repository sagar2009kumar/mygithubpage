<?php
/**
* © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
* “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
* (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
* Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
* You should read the Agreement carefully before using the code.
*/

class Cybersource_SOPWebMobile_Block_Form_Redirect extends Mage_Core_Block_Abstract
{
    /**
     * Holds all cybersource config fields
     * @var array
     */
    public $_configvalues = false;

    /**
     * Main constructor
     */
    protected function _construct()
	{
		parent::_construct();

        $this->getConfig();
	}

	/**
	 * Force cache to be disabled
	 * @see Mage_Core_Block_Abstract::getCacheLifetime()
	 */
	public function getCacheLifetime()
	{

		return null;

	}

    /**
     * Improve layout and add to system config
     * @return string
     */
    protected function _toHtml()
    {

        if($this->_configvalues == false)
        {
            $returnlink = sprintf('<br/><a href="%s">%s</a>', Mage::helper('checkout/cart')->getCartUrl(), $this->__('Please click here to go back to your cart.'));
            $html = $this->getTemplateHtml($this->__('There has been an error with your payment'). $returnlink,'','');

        }else {

            $form = new Varien_Data_Form();
            $form->setAction($this->getCyberUrl())
                ->setId('cybersourceform')
                ->setName('cybersourceform')
                ->setMethod('POST')
                ->setUseContainer(true);

            $fields = $this->getFields();
			$storeId = Mage::app()->getStore()->getId();
			$mobileEnabled = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('mobile_enabled',$storeId);
			if (isset($mobileEnabled) && $mobileEnabled) {
                unset($fields['card_cvn']);
                unset($fields['card_number']);
                unset($fields['card_expiry_date']);
                unset($fields['card_type']);
            }
            foreach($fields as $name => $value) {
                    $form->addField($name, 'hidden', array('name'=>$name, 'value'=>$value));
                }

            //add signed signature
            $form->addField('signature', 'hidden', array('name'=>'signature', 'value'=>$this->getSignature()));

            $redirectcode = '<script type="text/javascript">document.getElementById("cybersourceform").submit();</script>';

            //bg colour, div bg colour, text colour, text, form, redirect
            $staticBlockId = Mage::getStoreConfig('payment/cybersourcesop/message_block');
            $staticBlockHtml = Mage::app()->getLayout()->createBlock('cms/block')->setBlockId($staticBlockId)->toHtml();
            $html = $this->getTemplateHtml($this->__($staticBlockHtml),$form->toHtml(),$redirectcode);

        }

        return $html;
    }

	/**
	 * Get the config or bail on error
	 *
	 * @return boolean
	 */
	protected function getConfig()
	{
		if($this->_configvalues == false)
		{

			$this->_configvalues = Mage::getModel('cybersourcesop/config')->assignAllData();


		}

		return $this->_configvalues;
	}

    /**
     * Retrieve form template
     * @param $displaytext
     * @param $form
     * @param $redirect
     * @return string
     */
    protected function getTemplateHtml($displaytext, $form, $redirect)
    {
        $str = <<<EOD
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
		<html xmlns="http://www.w3.org/1999/xhtml">
		<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>Processing.</title>
	<style type="text/css">

	p {font-weight: bold; font-size: 12px;}

	div {text-align: center; font-size: 0.9em; line-height: 1.5; color: #797C7F;}

	.generate-book-shelf-code{
	font-family: Arial, Helvetica, sans-serif;
		font-size:20px;
		text-align: center;
		color: #797C7F;
		position: absolute;
		top: 35%;
		left: 5%;
		width: 85%;
	}
	.generate-book-shelf-code p {
		font-weight: bold;
		font-size: 12px;
	}

	@media only screen and (min-device-width : 320px) and (max-device-width : 977px) and (orientation : portrait) or (orientation : landscape)  {
		.generate-book-shelf-code{
			font-size:20px;
			text-align: center;
			color: #797C7F;
			position: absolute;
			top: 40%;
			left: 5%;
			width: 90%;
		}

		.generate-book-shelf-code p {
			font-weight: bold;
			font-size: 36px;
		}
	}
</style>
		</head>
		<body>
		<div class="generate-book-shelf-code">
		<p>
		$displaytext
		<br/>
		</p>
		</div>
		<div>
		$form
		</div>
		$redirect
		</body>
		</html>
EOD;

        return $str;
    }


    /**
     * Retrieves background color
     * @return mixed|string
     */
    protected function getBgColour()
	{
		$val = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('style_bgcolour');
		$default = '#fff';

		if(empty($val))
		{

			$val = $default;

		}

		if($val[0] != '#')
		{
			$val = $default;
		}

		return $val;
	}

    /**
     * Retrieves foreground color
     * @return mixed|string
     */
    protected function getTxtColour()
	{
		$val = Cybersource_SOPWebMobile_Model_Source_Consts::getSystemConfig('style_txtcolour');
		$default = '#000';

		if(empty($val))
		{

			$val = $default;

		}

		if($val[0] != '#')
		{
			$val = $default;
		}

		return $val;
	}

    /**
     * Retrieves cybersource url
     * @return mixed
     */
    public function getCyberUrl()
	{

		return $this->getConfig()->getCyberUrl();

	}

    /**
     * Return the code based on the payment code
     * @param $ccin
     * @return mixed
     */
    public function getCyberCCCode($ccin)
	{
		return $this->getConfig()->getCCVals($ccin);
	}

    /**
     * Return signature
     * @return mixed
     */
    public function getSignature()
	{
		return $this->getConfig()->getSignature();
	}

    /**
     * Returns checkout form fields.
     * @return mixed
     */
    public function getFields()
	{

		return $this->getConfig()->getCheckoutFormFields();

	}


}
