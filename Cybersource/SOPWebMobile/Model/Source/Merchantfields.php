<?php
/**
 * © 2016 CyberSource Corporation. All rights reserved. CyberSource Corporation (including its subsidiaries,
 * “CyberSource”) furnishes this code under the applicable agreement between the reader of this document
 * (“You”) and CyberSource (“Agreement”). You may use this code only in accordance with the terms of the
 * Agreement. The copyrighted code is licensed to You for use only in strict accordance with the Agreement.
 * You should read the Agreement carefully before using the code.
 */

class Cybersource_SOPWebMobile_Model_Source_Merchantfields
{
    /**
     * Return merchant fields array
     * @return array
     */
    public function toOptionArray()
    {
        $options = array();

        $customerAttributes = Mage::getResourceModel('customer/attribute_collection');

        $resource = Mage::getSingleton('core/resource');
        $conn = $resource->getConnection('core_read');
        //Get column names in sales_flat_order table.
        $table = "sales_flat_order";
        //if table prefix is defined it will be added with the table name
        $tablename = Mage::getSingleton("core/resource")->getTableName($table);
        $results = $conn->query("DESCRIBE $tablename")->fetchAll();

        //Add 'none' value to array.
        $merchantFields = array('-- none --');

        //Add order fields to array.
        foreach ($results as $result) {
            $merchantFields[] = $result['Field'];
        }

        //Add customer fields to array if they do not exist in array yet.
        foreach($customerAttributes as $attribute){
            if(!in_array($attribute->getAttributeCode(),$merchantFields) && !in_array('customer_'.$attribute->getAttributeCode(),$merchantFields)){
                $merchantFields[] = 'customer_'.$attribute->getAttributeCode();
            }
        }

        $removeFields = array(
            'adjustment_negative',
            'adjustment_positive',
            'applied_rule_ids',
            'can_ship_partially',
            'can_ship_partially_item',
            'coupon_rule_name',
            'customer_confirmation',
            'customer_default_shipping',
            'customer_disable_auto_group_change',
            'customer_email',
            'customer_firstname',
            'customer_lastname',
            'customer_lastname',
            'customer_middlename',
            'customer_note_notify',
            'customer_password_hash',
            'customer_prefix',
            'customer_reward_update_notification',
            'customer_reward_warning_notification',
            'customer_rp_token',
            'customer_rp_token_created_at',
            'customer_suffix',
            'customer_taxvat',
            'edit_increment',
            'entity_id',
            'ext_customer_id',
            'ext_order_id',
            'forced_shipment_with_invoice',
            'global_currency_code',
            'hidden_tax_amount',
            'hold_before_state',
            'hold_before_status',
            'original_increment_id',
            'paypal_ipn_customer_notified',
            'protect_code',
            'reward_points_balance_refund',
            'reward_salesrule_points',
            'shipping_address_id',
            'shipping_hidden_tax_amount',
            'store_to_base_rate',
            'store_to_order_rate',
            'x_forwarded_for',
        );

        //Sorts array in alphabetical order.
        sort($merchantFields);

        //Add Merchant Fields to options array.
        foreach($merchantFields as $field){
            if (!in_array($field,$removeFields)){
                if (!strpos($field,'refunded') && !strpos($field,'canceled') && !strpos($field,'invoiced') && !strpos($field,'base') &&
                    !strpos($field,'relation') && !strpos($field,'billing') && !strpos($field,'gw'))
                {
                    $options[] = array(
                        'value' => $field,
                        'label' => $field
                    );
                }
            }
        }

        return $options;
    }
}
