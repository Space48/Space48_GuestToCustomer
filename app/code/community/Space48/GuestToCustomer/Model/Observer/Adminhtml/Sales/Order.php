<?php

class Space48_GuestToCustomer_Model_Observer_Adminhtml_Sales_Order
{
    /**
     * add submit enquiry button to initiate a
     * proposal enquiry
     *
     * @param Varien_Event_Observer $observer
     */
    public function addButton(Varien_Event_Observer $observer)
    {
        // get block
        $block = $observer->getEvent()->getBlock();

        // should be instance of "Mage_Adminhtml_Block_Sales_Order_View"
        if (!($block instanceof Mage_Adminhtml_Block_Sales_Order_View)) {
            return $this;
        }

        // get request, controller action
        $request = Mage::app()->getRequest();
        $controller = $request->getControllerName();
        $action = $request->getActionName();

        // controller should be "sales_order"
        if ($controller != 'sales_order') {
            return $this;
        }

        // action should be "view"
        if ($action != 'view') {
            return $this;
        }

        // get current order
        $order = Mage::registry('current_order');

        if (!($order instanceof Mage_Sales_Model_Order)) {
            return $this;
        }

        if ($order->getCustomerId()) {
            return $this;
        }

        // confirm message
        $message = Mage::helper('space48_guesttocustomer')->__('Are you sure you want to perform this action?');

        // url
        $url = $block->getUrl('adminhtml/space48_guesttocustomer_index/createCustomerFromOrder');

        // add button
        $block->addButton('guesttocustomer_button', array(
            'label' => Mage::helper('space48_guesttocustomer')->__('Create a new customer from Guest Order'),
            'onclick' => "confirmSetLocation('{$message}', '{$url}');",
            'class' => 'go',
        ), 0, 50);

        return $this;
    }
}