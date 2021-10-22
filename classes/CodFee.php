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

/**
 * Fee object model.
 * 
 * @category ObjectModel
 * @package  Pittica/PrestaShop/CodFee
 * @author   Lucio Benini <info@pittica.com>
 * @license  http://opensource.org/licenses/LGPL-3.0  The GNU Lesser General Public License, version 3.0 ( LGPL-3.0 )
 * @link     https://github.com/pittica/prestashop-codfee/blob/main/classes/CodFee.php
 * @since    1.0.0
 */
class CodFee extends ObjectModel
{
    const TABLE_NAME = 'pittica_codfee_fee';

    /**
     * {@inheritDoc}
     *
     * @var boolean
     */
    public $force_id = true;

    public $fee;
    public $limit;
    public $active;

    /**
     * {@inheritDoc}
     *
     * @var array
     */
    public static $definition = array(
        'table' => self::TABLE_NAME,
        'primary' => 'id_carrier',
        'multilang' => false,
        'multishop' => true,
        'fields' => array(
            'fee' => array(
                'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false
            ),
            'limit' => array(
                'type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => false
            ),
            'active' => array(
                'type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => false
            )
        )
    );

    /**
     * Gets the prices including taxes.
     * 
     * @return float
     * @since  1.0.0
     */
    public function getPrice()
    {
        return $this->fee ? $this->fee * (1 + ((float) Tax::getCarrierTaxRate($this->id) / 100)) : 0;
    }

    /**
     * Determines whether the C.O.D. is valid.
     *
     * @param Cart $cart The cart to check.
     * 
     * @return boolean
     * @since  1.0.0
     */
    public function isValid($cart)
    {
        if ($this->active) {
            return (!$this->limit || ($cart->getOrderTotal(true, Cart::BOTH) + $this->getPrice()) < $this->limit) && !$cart->isVirtualCart();
        }
        
        return false;
    }

    /**
     * Gets the fees.
     *
     * @return array
     * @since  1.0.0
     */
    public static function getFees()
    {
        $result = Db::getInstance()->executeS('SELECT o.* FROM `' . _DB_PREFIX_ . self::TABLE_NAME . '` o');
        $fees = array();

        foreach ($result as $row) {
            $fees[$row['id_carrier']] = $row;
        }

        return $fees;
    }

    /**
     * Truncates the table.
     *
     * @return boolean
     * @since  1.0.0
     */
    public static function truncate()
    {
        return Db::getInstance()->execute('TRUNCATE `' . _DB_PREFIX_ . self::TABLE_NAME . '`');
    }
}
