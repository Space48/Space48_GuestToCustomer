<?php

class Space48_GuestToCustomer_Adminhtml_Space48_Guesttocustomer_IndexController
    extends Mage_Adminhtml_Controller_Action
{

    protected function _getOrder()
    {
        $order = Mage::getModel('sales/order');

        if ( $id = $this->getRequest()->getParam('order_id') ) {
            $order->load($id);
        }

        return $order;
    }

    public function createCustomerFromOrderAction()
    {
        $order = $this->_getOrder();
        $store = $order->getStore();
        $website = $store->getWebsite();

        try {

            $customer = Mage::getModel('customer/customer');
            $customer->setWebsiteId($website->getId());
            $customer->loadByEmail($order->getCustomerEmail());

            if ($customer->getId()) {
                Mage::throwException('Customer already exists.');
            } else {
                $customer = Mage::getModel('customer/customer');
                $customer->setWebsiteId($website->getId());
            }

            if ( ! $order->getId() ) {
                Mage::throwException('Unable to load order.');
            }

            if ($billingAddress = $order->getBillingAddress()) {

                $billing['firstname'] = $billingAddress->getFirstname();
                $billing['lastname'] = $billingAddress->getLastname();
                $billing['street'] = $billingAddress->getStreet();
                $billing['city '] = $billingAddress->getCity();
                $billing['region'] = $billingAddress->getRegion();
                $billing['postcode'] = $billingAddress->getPostcode();
                $billing['telephone'] = $billingAddress->getTelephone();
                $billing['country_id'] = $billingAddress->getCountryId();

            } else {
                Mage::throwException('Unable to find billing address.');
            }

            if ($shippingAddress = $order->getShippingAddress()) {

                $shipping['firstname'] = $shippingAddress->getFirstname();
                $shipping['lastname'] = $shippingAddress->getLastname();
                $shipping['street'] = $shippingAddress->getStreet();
                $shipping['city'] = $shippingAddress->getCity();
                $shipping['region'] = $shippingAddress->getRegion();
                $shipping['postcode'] = $shippingAddress->getPostcode();
                $shipping['telephone'] = $shippingAddress->getTelephone();
                $shipping['country_id'] = $shippingAddress->getCountryId();

            } else {
                $shipping = $billing;
            }

            $customer->setEmail($order->getCustomerEmail());
            $customer->setFirstname($order->getBillingAddress()->getFirstname());
            $customer->setLastname($order->getBillingAddress()->getLastname());
            $customer->setPassword($customer->generatePassword());
            $customer->setWebsiteId($website->getId());
            $customer->setStoreId($store->getId());

            $customer->setConfirmation(null);
            $customer->save();

            $customer->sendNewAccountEmail('registered', '', $store->getId());

            $customerBillingAddress = Mage::getModel('customer/address');
            $customerBillingAddress
                ->setData($billing)
                ->setCustomerId($customer->getId())
                ->setIsDefaultBilling(1)
                ->setSaveInAddressBook(1)
                ->save();

            $customerShippingAddress = Mage::getModel('customer/address');
            $customerShippingAddress
                ->setData($shipping)
                ->setCustomerId($customer->getId())
                ->setIsDefaultShipping(1)
                ->setSaveInAddressBook(1)
                ->save();

            $order->setCustomerId($customer->getId())->save();

            $this->_getSession()->addSuccess(
                $this->__('Customer [ID: %s] successfully created from order.', $customer->getId())
            );

        } catch (Exception $e) {
            $message = $e->getMessage();

            $this->_getSession()->addError(
                $this->__($message)
            );
        }

        $this->_redirect('adminhtml/sales_order/view', array(
            'order_id' => $order->getId(),
        ));

        return;

//        $ship_street[0] = str_replace("&"," ", $ship_street[0]);
//        $ship_street[1] = str_replace(","," ", $ship_street[0]);
//        $ship_name = str_replace("&"," ", $ship_name);
//        $ship_name = str_replace(","," ", $ship_name);
//        $ship_region = str_replace("&"," ", $ship_region);
//        $ship_region = str_replace(","," ", $ship_region);
//        $ship_city = str_replace("&"," ", $ship_city);
//        $ship_city = str_replace(","," ", $ship_city);
//        $ship_postcode = str_replace("&"," ", $ship_postcode);
//        $ship_postcode = str_replace(","," ", $ship_postcode);

    }
}