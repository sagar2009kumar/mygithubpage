<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */
class Cybersource_SOPWebMobile_Model_Order extends Mage_Sales_Model_Order
{
    /**
     * Guest email template System config path
     * @access protected
     * @var string
     */
    protected $_emailGuestTemplate = 'payment/cybersourcesop/email_cancel_template';
    /**
     * Customer email template System config path
     * @access protected
     * @var string
     */
    protected $_emailTemplate = 'payment/cybersourcesop/email_cancel_template';
    /**
     * Used when cancelling orders
     * @access protected
     * @var bool
     */
    protected $_isCronRunning=false;

    /**
     * Sets isCronRunning to true.
     */
    public function cronRunning(){
        $this->_isCronRunning=true;
    }

    /**
     * Sends the order update email.
     * @param bool $notifyCustomer
     * @param string $comment
     * @return $this
     */
    public function sendOrderUpdateEmail($notifyCustomer = true, $comment = '')
    {
        if($this->_isCronRunning){
            $storeId = $this->getStore()->getId();

            if (!Mage::helper('sales')->canSendOrderCommentEmail($storeId)) {
                return $this;
            }
            // Get the destination email addresses to send copies to
            $copyTo = $this->_getEmails(self::XML_PATH_UPDATE_EMAIL_COPY_TO);
            $copyMethod = Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_COPY_METHOD, $storeId);
            // Check if at least one recepient is found
            if (!$notifyCustomer && !$copyTo) {
                return $this;
            }

            // Retrieve corresponding email template id and customer name
            if ($this->getCustomerIsGuest()) {
                $templateId = Mage::getStoreConfig($this->_emailGuestTemplate, $storeId);
                $customerName = $this->getBillingAddress()->getName();
            } else {
                $templateId = Mage::getStoreConfig($this->_emailTemplate, $storeId);
                $customerName = $this->getCustomerName();
            }
            if(is_null($templateId)||$templateId<1){
                parent::sendOrderUpdateEmail($notifyCustomer, $comment);
            }
            else{
                $mailer = Mage::getModel('core/email_template_mailer');
                if ($notifyCustomer) {
                    $emailInfo = Mage::getModel('core/email_info');
                    $emailInfo->addTo($this->getCustomerEmail(), $customerName);
                    if ($copyTo && $copyMethod == 'bcc') {
                        // Add bcc to customer email
                        foreach ($copyTo as $email) {
                            $emailInfo->addBcc($email);
                        }
                    }
                    $mailer->addEmailInfo($emailInfo);
                }

                // Email copies are sent as separated emails if their copy method is
                // 'copy' or a customer should not be notified
                if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
                    foreach ($copyTo as $email) {
                        $emailInfo = Mage::getModel('core/email_info');
                        $emailInfo->addTo($email);
                        $mailer->addEmailInfo($emailInfo);
                    }
                }

                // Set all required params and send emails
                $mailer->setSender(Mage::getStoreConfig(self::XML_PATH_UPDATE_EMAIL_IDENTITY, $storeId));
                $mailer->setStoreId($storeId);
                $mailer->setTemplateId($templateId);
                $mailer->setTemplateParams(array(
                        'order'   => $this,
                        'comment' => $comment,
                        'billing' => $this->getBillingAddress()
                    )
                );
                $mailer->send();

                return $this;
            }
        }
        else{
            parent::sendOrderUpdateEmail($notifyCustomer, $comment);
        }
    }

}
