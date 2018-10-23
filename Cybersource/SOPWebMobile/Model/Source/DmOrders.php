<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */


class Cybersource_SOPWebMobile_Model_Source_DmOrders
{
    /**
     * Returns array containing the area where the Decision manager has to be applied. eg:
     * * Backend
     * * Frontend
     *
     * @return array
     */
	public function toOptionArray()
    {

        return array(
            array(
                'value' => Cybersource_SOPWebMobile_Model_Source_Consts::BACKEND_ORDERS,
                'label' => 'Backend orders Only'
            ),
            array(
                'value' => Cybersource_SOPWebMobile_Model_Source_Consts::FRONTEND_ORDERS,
                'label' => 'Frontend orders Only'
            ),
            array(
                'value' => Cybersource_SOPWebMobile_Model_Source_Consts::ALL_ORDERS,
                'label' => 'Backend and Frontend orders'
            ),

        );
    }
}
