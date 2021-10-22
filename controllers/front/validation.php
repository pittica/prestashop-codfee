<?php

/**
 * PrestaShop Module - pitticacodfee
 *
 * Copyright 2021 Pittica S.r.l.
 *
 * @category  Module
 * @package   Pittica/PrestaShop/CodFee
 * @author    Lucio Benini <info@pittica.com>
 * @copyright 2021 Pittica S.r.l.
 * @license   http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link      https://github.com/pittica/prestashop-codfee
 */

require_once dirname(__FILE__) . '../../../classes/CodFee.php';

/**
 * Validation front controller.
 * 
 * @category Controller
 * @package  Pittica/PrestaShop/CodFee
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-codfee/blob/main/controllers/front/validation.php
 * @since    1.0.0
 */
class pitticacodfeevalidationModuleFrontController extends ModuleFrontController
{
    /**
     * {@inheritDoc}
     * 
     * @return void
     * @since  1.0.0
     */
    public function postProcess()
    {
        $cart = $this->context->cart;
        $fee  = new CodFee($cart->id_carrier);

        if (!$fee->isValid($cart)) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, array(
                'step' => '1'
            )));
        }
        
        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, array(
                'step' => '1'
            )));
        }
        
        $authorized = false;
        
        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'pitticacodfee') {
                $authorized = true;
                break;
            }
        }
        
        if (!$authorized) {
            die($this->module->getTranslator()->l('This payment method is not available.'));
        }
        
        $customer = new Customer($cart->id_customer);
        
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect($this->context->link->getPageLink('order', null, null, array(
                'step' => '1'
            )));
        }
        
        $currency = $this->context->currency;
        $total    = (float) $cart->getOrderTotal(true, Cart::BOTH);
        
        $this->module->validateOrder($cart->id, Configuration::get('PS_OS_PREPARATION'), $total, $this->module->l('C.O.D.'), null, array(), (int) $currency->id, false, $customer->secure_key);
        
        Tools::redirect($this->context->link->getPageLink('order-confirmation', null, null, array(
            'id_cart' => $cart->id,
            'id_module' => $this->module->id,
            'id_order' => $this->module->currentOrder,
            'key' => $customer->secure_key
        )));
    }
}
