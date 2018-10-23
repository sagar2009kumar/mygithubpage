<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Security_Encryption extends Mage_Core_Model_Encryption
{
    /**
     * Sets the encryption model.
     * @param string $keyin
     * @param bool $saveinsession
     * @param bool $getfromsession
     * @return $this
     * @throws Exception
     */
    public function setCryptWithKey($keyin = null, $saveinsession = false, $getfromsession=false)
    {
    	if($getfromsession)
    	{
	    	$keyin = $this->getSessionKey();
	    	//now remove from the session
	    	$this->clearSessionKey();
    	}

    	if(empty($keyin))
    	{
	    	$keyin = $this->generateKey();
    	}

	    if($keyin)
	    {
	    	$this->_crypt = Varien_Crypt::factory()->init($keyin);

	    }else{

		    Throw new Exception('Encryption key incorrect');

	    }

	    if($saveinsession)
	    {
		    $this->setSessionKey($keyin);
	    }

	    return $this;
    }

    /**
     * Generates the encryption key
     * @return string
     */
    protected function generateKey()
	{

		return $this->getSessionId() . $this->generateRandomString(10);

	}

    /**
     * Generates a radom string
     * @param int $length
     * @return string
     */
    protected function generateRandomString($length = 10) {

    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    	$randomString = '';

    	for ($i = 0; $i < $length; $i++) {
        	$randomString .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $randomString;

    }

    /**
     * Returns the encrypted session id
     * @return string
     */
    protected function getSessionId()
    {
	    $session = Mage::getSingleton("core/session");
		if($session->getEncryptedSessionId())
		{
			return $session->getEncryptedSessionId();
		}else
		{
			return '';
		}

    }

    /**
     * Clears the session key
     */
    protected function clearSessionKey()
    {
	    Mage::getSingleton("checkout/session")->unsCybersopencryptionkey();
    }

    /**
     * Sets the session key
     * @param string $keyin
     */
    protected function setSessionKey($keyin)
    {
	   	Mage::getSingleton("checkout/session")->setCybersopencryptionkey($keyin);
    }

    /**
     * Retrieves the session key
     * @return mixed
     * @throws Exception
     */
    protected function getSessionKey()
    {
    	$sessionkey = Mage::getSingleton("checkout/session")->getCybersopencryptionkey();
    	if(empty($sessionkey))
    	{
	    	Throw new Exception('Cannot retrieve session variable');
    	}

	   return $sessionkey;

    }


}
