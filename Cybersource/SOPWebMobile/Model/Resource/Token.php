<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Resource_Token extends
    Mage_Core_Model_Resource_Db_Abstract
{
    /**
     * Main constructor
     */
    protected function _construct()
    {
        // Specify the DB table name and primary key
        $this->_init('cybersourcesop/token', 'id');
    }

    /**
     * Will run before the token is saved
     * @param Mage_Core_Model_Abstract $object
     * @return mixed
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        $time = Varien_Date::now();
        $object->setModifiedAt($time);

        if ($object->isObjectNew())
        {
            $object->setCreatedAt($time);
        }
        return parent::_beforeSave($object);
    }

    /**
     * Imports tokens by e-mail
     * @param Varien_Object $object
     * @return $this
     */
    public function uploadAndImport(Varien_Object $object) {
        //imports tokens by e-mail

        //check if a file has been uploaded, otherwise return back to system config.
        //this represents the temporary file name uploaded to the server
        if (empty($_FILES['groups']['tmp_name']['cybersourcesop']['fields']['import']['value'])) {
            return $this;
        }

        $csvFile = $_FILES['groups']['tmp_name']['cybersourcesop']['fields']['import']['value'];

        $linecount = count(file($csvFile));

        if ($linecount > 101) {
            Mage::throwException(Mage::helper('cybersourcesop')->__('Your CSV File contains more than 100 tokens. Please ensure only 100 tokens are imported at a time.'));
        }

        $website = Mage::app()->getWebsite($object->getScopeId());

        $this->_importWebsiteId     = (int)$website->getId();
        $this->_importErrors        = array();
        $this->_importedRows        = 0;

        $io     = new Varien_Io_File();
        $info   = pathinfo($csvFile);
        $io->open(array('path' => $info['dirname']));
        $io->streamOpen($info['basename'], 'r');

        // check and skip headers
        $headers = $io->streamReadCsv();

        //limit CSV File to 100 import rows at a time.
        if ($headers === false || count($headers) > 4) {
            $io->streamClose();
            Mage::throwException(Mage::helper('cybersourcesop')->__('Invalid File Format. Please specify the following columns: "Profile ID","Currency","Originator MID","Merchant Reference Code"'));
        }

        try {
            $rowNumber  = 1;
            $importData = array();

            while (false !== ($csvLine = $io->streamReadCsv())) {
                $rowNumber ++;

                if (empty($csvLine)) {
                    continue;
                }

                if (!empty($csvLine[0])) {
                    //create a row to be processed
                    $csvRow = $this->_getImportRow($csvLine, $rowNumber);
                    //do soap call
                    $row = Mage::getModel('cybersourcesop/soapapi_token')->getCyberToken($csvRow);

                    if ($row !== false) {
                        //importData is the big SOAP request we are sending to CYBS.
                        $response[] = $row;

                    }
                }
            }
            $io->streamClose();
        } catch (Mage_Core_Exception $e) {
            $io->streamClose();
            Mage::throwException($e->getMessage());
        } catch (Exception $e) {
            $io->streamClose();
            Mage::logException($e);
            Mage::throwException(Mage::helper('cybersourcesop')->__('An error occurred while importing tokens.'.$e->getMessage()));
        }
        //check if there's responses.
        if ($response) {

            //check if the response from CYBS matches up to customer DB for the current website scope.
            foreach ($response as $reply) {
                $email = $reply->email;
                $customer = '';
                //when introducing website scope - the Import Tokens is not made available to the user
                //Import tokens is only made available to the user on a Website Level, thus the Website
                //is passed into config data in the save events.
                if (strlen($code = Mage::getSingleton('adminhtml/config_data')->getWebsite()))
                {
                    $website_id = Mage::getModel('core/website')->load($code)->getId();
                    $customer = Mage::getModel('customer/customer');
                    $customer->setWebsiteId($website_id);
                    $customer->loadByEmail($email);
                }

                if ($customer_id = $customer->getId()) {
                    $return[] = array(
                        'token_id' => $reply->subscriptionID,    // token_id
                        'cc_number' => $reply->cardAccountNumber,
                        'cc_expiration' => $reply->cardExpirationMonth .'-'.$reply->cardExpirationYear,
                        'cc_type' => $reply->cardType,
                        'customer_id' => $customer_id  // customer_id
                    );
                }
            }
        }
        //done collecting data from CSV and prepping data for save.
        try {
            //save the tokens individually.
            foreach ($return as $token) {
              $this->_saveImportData($token);
            }

        } catch (Exception $e) {
            $error = Mage::helper('cybersourcesop')->__('Token (%s) has not been imported.', $token->token_id);
            Mage::throwException($error);
            Mage::log($e->getMessage(),null,'cybersource_import.log',false);
        }
        if ($this->_importErrors) {
            $error = Mage::helper('cybersourcesop')->__('File has not been completely imported. See the following list of errors: %s', implode(" \n", $this->_importErrors));
            Mage::throwException($error);
        }

        return $this;

    }

    /**
     * Validate row for import and return table rate array or false
     * Error will be add to _importErrors array
     *
     * @param array $row
     * @param int $rowNumber
     * @return array|false
     */
    protected function _getImportRow($row, $rowNumber = 0)
    {
        // validate row
        if (count($row) > 4) {
            $this->_importErrors[] = Mage::helper('cybersourcesop')->__('Invalid CSV format in the Row #%s', $rowNumber);
            return false;
        }
        //reconstruct row to pass to CYBS
        $newRow = array(
                  'merchant_ID' => $row[2], //merchant ID
                  'paySubscriptionRetrieveService_run' => 'true', //retrieve subscription service
                  'recurringSubscriptionInfo_subscriptionID' => $row[0],  //token ID
                  'clientLibraryVersion' => phpversion(),
                  'clientEnvironment' => php_uname(),
                  'clientLibrary' => "PHP",
                  'merchantReferenceCode' => $row[3], //merchant ref code
                  'purchaseTotals_currency' => $row[1], //currency
        );

        return $newRow;
    }

    /**
     * Adds new tokens to token table
     * @param array $importData
     * @return bool
     */
    protected function _saveImportData(array $importData) {
        $tokenModel = Mage::getModel('cybersourcesop/token');
        //if token already exists - skip importing this token
        if (!$tokenModel->getCollection()->addFieldToFilter('token_id',$importData['token_id'])->getData()) {
            //add new token to token table.
            $tokenModel->setTokenId($importData['token_id'])
                       ->setCustomerId($importData['customer_id'])
                       ->setCcNumber($importData['cc_number'])
                       ->setCcExpiration($importData['cc_expiration'])
                       ->setCcType($importData['cc_type'])
                       ->save();
            return true;
        }
        return false;
    }

}
