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

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once dirname(__FILE__) . '/classes/CodFee.php';

use PrestaShop\PrestaShop\Adapter\Presenter\Cart\CartPresenter;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;

/**
 * COD Fee module class.
 *
 * @category Module
 * @package  Pittica/PrestaShop/CodFee
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-codfee/blob/main/pitticacodfee.php
 * @since    1.0.0
 */
class PitticaCodFee extends PaymentModule
{
    /**
     * {@inheritDoc}
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->name             = 'pitticacodfee';
        $this->tab              = 'payments_gateways';
        $this->version          = '1.0.0';
        $this->author           = 'Pittica';
        $this->controllers      = array(
            'validation'
        );
        $this->is_eu_compatible = 1;
        $this->bootstrap        = true;
        $this->currencies       = true;
        $this->currencies_mode  = 'checkbox';
        
        parent::__construct();
        
        $this->displayName = $this->l('C.O.D. With Fee');
        $this->description = $this->l('C.O.D. payment with fees.');
        
        $this->ps_versions_compliancy = array(
            'min' => '1.7.7.0',
            'max' => _PS_VERSION_
        );
    }
    
    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function install()
    {
        require_once dirname(__FILE__) . '/sql/install.php';
        
        return parent::install() && $this->registerHook('paymentOptions') && $this->registerHook('actionFrontControllerSetMedia');
    }
    
    /**
     * {@inheritDoc}
     *
     * @return boolean
     * @since  1.0.0
     */
    public function uninstall()
    {
        require_once dirname(__FILE__) . '/sql/uninstall.php';

        return parent::uninstall();
    }
    
    /**
     * Processes the POST action in module configuration.
     *
     * @return string
     * @since  1.0.0
     */
    protected function _postProcess()
    {
        if (Tools::isSubmit('btnSubmit')) {
            CodFee::truncate();

            foreach (Carrier::getCarriers($this->context->language->id) as $carrier) {
                $fee = new CodFee($carrier['id_carrier']);

                $fee->id     = (int) $carrier['id_carrier'];
                $fee->fee    = (float) Tools::getValue('PITTICA_CODFEE_' . $carrier['id_carrier'] . '_FEE');
                $fee->limit  = (float) Tools::getValue('PITTICA_CODFEE_' . $carrier['id_carrier'] . '_LIMIT');
                $fee->active = (bool) Tools::getValue('PITTICA_CODFEE_' . $carrier['id_carrier'] . '_ACTIVE');

                $fee->add();
            }
        }
        
        return $this->displayConfirmation($this->trans('Settings updated', array(), 'Admin.Global'));
    }
    
    /**
     * {@inheritDoc}
     *
     * @return string
     * @since  1.0.0
     */
    public function getContent()
    {
        $output = '';
        
        if (Tools::isSubmit('btnSubmit')) {
            $output .= $this->_postProcess();
        } else {
            $output .= '<br />';
        }
        
        return $output . $this->renderForm();
    }
    
    /**
     * Renders settings form.
     *
     * @return void
     * @since  1.0.0
     */
    protected function renderForm()
    {
        $config = array();
        $input  = array();
        $fees   = CodFee::getFees();
        
        foreach (Carrier::getCarriers($this->context->language->id) as $carrier) {
            $id = $carrier['id_carrier'];
            $config['PITTICA_CODFEE_' . $id . '_FEE'] = Tools::getValue('PITTICA_CODFEE_' . $id . '_FEE', !empty($fees[$id]['fee']) ? (float) $fees[$id]['fee'] : '');
            $config['PITTICA_CODFEE_' . $id . '_LIMIT'] = Tools::getValue('PITTICA_CODFEE_' . $id . '_LIMIT', !empty($fees[$id]['limit']) ? (float) $fees[$id]['limit'] : 0);
            $config['PITTICA_CODFEE_' . $id . '_ACTIVE'] = Tools::getValue('PITTICA_CODFEE_' . $id . '_ACTIVE', !empty($fees[$id]['active']) ? (float) $fees[$id]['active'] : false);
            $input[] = array(
                'form' => array(
                    'legend' => array(
                        'title' => $carrier['name'],
                        'icon' => 'icon-truck'
                    ),
                    'input' => array(
                        array(
                            'type' => 'number',
                            'label' => $this->l('Fee'),
                            'name' => 'PITTICA_CODFEE_' . $id . '_FEE',
                            'desc' => $this->l('Taxes excluded.'),
                            'min' => 0,
                            'step' => 0.1,
                            'suffix' => $this->context->currency->sign
                        ),
                        array(
                            'type' => 'switch',
                            'label' => $this->l('Active'),
                            'name' => 'PITTICA_CODFEE_' . $id . '_ACTIVE',
                            'values' => array(
                                array(
                                    'value' => true
                                ),
                                array(
                                    'value' => false
                                )
                            )
                        ),
                        array(
                            'type' => 'number',
                            'label' => $this->l('Limit'),
                            'name' => 'PITTICA_CODFEE_' . $id . '_LIMIT',
                            'desc' => $this->l('Taxes included.'),
                            'min' => 0,
                            'step' => 0.1,
                            'suffix' => $this->context->currency->sign
                        )
                    ),
                    'submit' => array(
                        'title' => $this->trans('Save', array(), 'Admin.Actions')
                    )
                )
            );
        }
        
        $helper                           = new HelperForm();
        $helper->show_toolbar             = false;
        $helper->table                    = $this->table;
        $helper->module                   = $this;
        $lang                             = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $helper->default_form_language    = $lang->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG') ?: 0;
        $this->fields_form                = array();
        $helper->id                       = (int) Tools::getValue('id_carrier');
        $helper->identifier               = $this->identifier;
        $helper->submit_action            = 'btnSubmit';
        $helper->currentIndex             = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name . '&tab_module= ' . $this->tab . '&module_name=' . $this->name;
        $helper->token                    = Tools::getAdminTokenLite('AdminModules');
        $helper->tpl_vars                 = array(
            'fields_value' => $config,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id
        );
        
        return $helper->generateForm($input);
    }

    /**
     * Hook "actionFrontControllerSetMedia".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookActionFrontControllerSetMedia($params)
    {
        if ($this->context->controller->php_self === 'order') {
            $cart        = $params['cart'];
            $fee         = new CodFee($cart->id_carrier);
            
            if ($fee->isValid($cart)) {
                $price          = $fee->getPrice();
                $order_total    = $cart->getOrderTotal(true, Cart::BOTH) + $price;
                $total          = Tools::displayPrice($order_total);
                $presenter      = new CartPresenter();
                $presented_cart = $presenter->present($cart);

                $presented_cart['totals']['total']['value'] = $total;
                $presented_cart['totals']['total_including_tax']['value'] = $total;
            
                if (!empty($presented_cart['subtotals']['tax'])) {
                    $presented_cart['subtotals']['tax']['value'] = Tools::displayPrice($order_total - ($cart->getOrderTotal(false, Cart::BOTH) + $price));
                }

                $this->smarty->assign(
                    array(
                        'cart' => $presented_cart,
                        'configuration' => array(
                            'display_prices_tax_incl' => (bool) (new TaxConfiguration())->includeTaxes(),
                            'taxes_enabled' => (bool) Configuration::get('PS_TAX')
                        )
                    )
                );

                Media::addJsDef(array(
                    'pittica_codfee_label' => $this->l('C.O.D.'),
                    'pittica_codfee_fee' => Tools::displayPrice($price),
                    'pittica_codfee_total' => $total,
                    'pittica_codfee_totals' => $this->fetch('checkout/_partials/cart-summary-totals.tpl'),
                    'pittica_cart_lock' => false
                ));

                $this->context->controller->registerJavascript(
                    $this->name,
                    'modules/' . $this->name . '/views/js/lib.js',
                    array(
                        'position' => 'bottom'
                    )
                );
            }
        }
    }
    
    /**
     * Hook "paymentOptions".
     *
     * @param array $params Hook parameters.
     *
     * @return string
     * @since  1.0.0
     */
    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return array();
        }
        
        $cart = $params['cart'];
        
        if (!$this->checkCurrency($cart) || !$cart->id_carrier || $cart->isVirtualCart()) {
            return array();
        }

        $fee = new CodFee($cart->id_carrier);

        if (!$fee->isValid($cart)) {
            return array();
        }

        $price = $fee->getPrice();

        $this->smarty->assign('fee', $price ? Tools::displayPrice($price) : '');
        
        $option = new PaymentOption();
        $option
            ->setModuleName($this->name)
            ->setCallToActionText($price ? sprintf($this->l('Pay by C.O.D. (+%1$s)'), Tools::displayPrice($price)) : $this->l('Pay by C.O.D.'))
            ->setAction($this->context->link->getModuleLink($this->name, 'validation', array(), true))
            ->setAdditionalInformation($this->fetch('module:' . $this->name . '/views/templates/hook/paymentOptions.tpl'));
        
        return array(
            $option
        );
    }
    
    /**
     * Checks the currency of the given cart.
     *
     * @param Cart $cart
     *
     * @return boolean
     * @since  1.0.0
     */
    public function checkCurrency($cart)
    {
        $currency_order    = new Currency($cart->id_currency);
        $currencies_module = $this->getCurrency($cart->id_currency);
        
        if (is_array($currencies_module)) {
            foreach ($currencies_module as $currency_module) {
                if ($currency_order->id == $currency_module['id_currency']) {
                    return true;
                }
            }
        }
        
        return false;
    }
    
    /**
     * {@inheritDoc}
     *
     * @return void
     * @since  1.0.0
     */
    protected function createOrderFromCart(Cart $cart, Currency $currency, $productList, $addressId, $context, $reference, $secure_key, $payment_method, $name, $dont_touch_amount, $amount_paid, $warehouseId, $cart_total_paid, $debug, $order_status, $id_order_state, $carrierId = null)
    {
        $order               = new Order();
        $order->product_list = $productList;
        
        $computingPrecision = Context::getContext()->getComputingPrecision();
        
        if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_delivery') {
            $address          = new Address((int) $addressId);
            $context->country = new Country((int) $address->id_country, (int) $cart->id_lang);
            
            if (!$context->country->active) {
                throw new PrestaShopException('The delivery address country is not active.');
            }
        }
        
        $carrier      = null;
        $fee          = new CodFee($cart->id_carrier);
        $fee_tax_excl = $fee->fee;
        $fee_tax_incl = $fee->getPrice();
        
        if (!$cart->isVirtualCart() && isset($carrierId)) {
            $carrier           = new Carrier((int) $carrierId, (int) $cart->id_lang);
            $order->id_carrier = (int) $carrier->id;
            $carrierId         = (int) $carrier->id;
        } else {
            $order->id_carrier = 0;
            $carrierId         = 0;
        }
        
        $order->id_customer         = (int) $cart->id_customer;
        $order->id_address_invoice  = (int) $cart->id_address_invoice;
        $order->id_address_delivery = (int) $addressId;
        $order->id_currency         = $currency->id;
        $order->id_lang             = (int) $cart->id_lang;
        $order->id_cart             = (int) $cart->id;
        $order->reference           = $reference;
        $order->id_shop             = (int) $context->shop->id;
        $order->id_shop_group       = (int) $context->shop->id_shop_group;
        
        $order->secure_key = ($secure_key ? pSQL($secure_key) : pSQL($context->customer->secure_key));
        $order->payment    = $this->l('C.O.D.');
        
        if (isset($name)) {
            $order->module = $this->name;
        }
        
        $order->recyclable      = $cart->recyclable;
        $order->gift            = (int) $cart->gift;
        $order->gift_message    = $cart->gift_message;
        $order->mobile_theme    = $cart->mobile_theme;
        $order->conversion_rate = $currency->conversion_rate;
        $amount_paid            = !$dont_touch_amount ? Tools::ps_round((float) $amount_paid, $computingPrecision) : $amount_paid;
        $order->total_paid_real = 0;
        
        $order->total_products           = Tools::ps_round((float) $cart->getOrderTotal(false, Cart::ONLY_PRODUCTS, $order->product_list, $carrierId), $computingPrecision);
        $order->total_products_wt        = Tools::ps_round((float) $cart->getOrderTotal(true, Cart::ONLY_PRODUCTS, $order->product_list, $carrierId), $computingPrecision);
        $order->total_discounts_tax_excl = Tools::ps_round((float) abs($cart->getOrderTotal(false, Cart::ONLY_DISCOUNTS, $order->product_list, $carrierId)), $computingPrecision);
        $order->total_discounts_tax_incl = Tools::ps_round((float) abs($cart->getOrderTotal(true, Cart::ONLY_DISCOUNTS, $order->product_list, $carrierId)), $computingPrecision);
        $order->total_discounts          = $order->total_discounts_tax_incl;
        $order->total_shipping_tax_excl  = Tools::ps_round((float) $cart->getPackageShippingCost($carrierId, false, null, $order->product_list) + $fee_tax_excl, $computingPrecision);
        $order->total_shipping_tax_incl  = Tools::ps_round((float) $cart->getPackageShippingCost($carrierId, true, null, $order->product_list) + $fee_tax_incl, $computingPrecision);
        $order->total_shipping           = $order->total_shipping_tax_incl;
        
        if (null !== $carrier && Validate::isLoadedObject($carrier)) {
            $order->carrier_tax_rate = $carrier->getTaxesRate(new Address((int) $cart->{Configuration::get('PS_TAX_ADDRESS_TYPE')}));
        }
        
        $order->total_wrapping_tax_excl = Tools::ps_round((float) abs($cart->getOrderTotal(false, Cart::ONLY_WRAPPING, $order->product_list, $carrierId)), $computingPrecision);
        $order->total_wrapping_tax_incl = Tools::ps_round((float) abs($cart->getOrderTotal(true, Cart::ONLY_WRAPPING, $order->product_list, $carrierId)), $computingPrecision);
        $order->total_wrapping          = $order->total_wrapping_tax_incl;
        
        $order->total_paid_tax_excl = Tools::ps_round((float) $cart->getOrderTotal(false, Cart::BOTH, $order->product_list, $carrierId) + $fee_tax_excl, $computingPrecision);
        $order->total_paid_tax_incl = Tools::ps_round((float) $cart->getOrderTotal(true, Cart::BOTH, $order->product_list, $carrierId) + $fee_tax_incl, $computingPrecision);
        $order->total_paid          = $order->total_paid_tax_incl;
        $order->round_mode          = Configuration::get('PS_PRICE_ROUND_MODE');
        $order->round_type          = Configuration::get('PS_ROUND_TYPE');
        
        $order->invoice_date  = '0000-00-00 00:00:00';
        $order->delivery_date = '0000-00-00 00:00:00';
        
        if ($debug) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - Order is about to be added', 1, null, 'Cart', (int) $cart->id, true);
        }
        
        $result = $order->add();
        
        if (!$result) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - Order cannot be created', 3, null, 'Cart', (int) $cart->id, true);
            throw new PrestaShopException('Can\'t save Order');
        }
        
        if ($order_status->logable && number_format($cart_total_paid + $fee_tax_incl, $computingPrecision) != number_format($amount_paid, $computingPrecision)) {
            $id_order_state = Configuration::get('PS_OS_ERROR');
        }
        
        if ($debug) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - OrderDetail is about to be added', 1, null, 'Cart', (int) $cart->id, true);
        }
        
        $order_detail = new OrderDetail(null, null, $context);
        $order_detail->createList($order, $cart, $id_order_state, $order->product_list, 0, true, $warehouseId);
        
        if ($debug) {
            PrestaShopLogger::addLog('PaymentModule::validateOrder - OrderCarrier is about to be added', 1, null, 'Cart', (int) $cart->id, true);
        }
        
        if (null !== $carrier) {
            $order_carrier                         = new OrderCarrier();
            $order_carrier->id_order               = (int) $order->id;
            $order_carrier->id_carrier             = $carrierId;
            $order_carrier->weight                 = (float) $order->getTotalWeight();
            $order_carrier->shipping_cost_tax_excl = (float) $order->total_shipping_tax_excl;
            $order_carrier->shipping_cost_tax_incl = (float) $order->total_shipping_tax_incl;
            $order_carrier->add();
        }
        
        return array(
            'order' => $order,
            'orderDetail' => $order_detail
        );
    }
}
