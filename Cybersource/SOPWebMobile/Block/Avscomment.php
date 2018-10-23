<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Block_Avscomment extends Mage_Core_Block_Template {

    /**
     * Main constructor
     */
    protected function  _construct()
    {
        parent::_construct();
        $this->setTemplate('cybersourcesop/avscomment.phtml');
    }

    /**
     * Loads template as a html
     * @return mixed
     */
    public function getCommentHtml()
    {
        return $this->toHtml();
    }
}
