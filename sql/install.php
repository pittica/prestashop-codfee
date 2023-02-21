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

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CodFee::TABLE_NAME . '` (
	`id_carrier` INT(10) UNSIGNED NOT NULL PRIMARY KEY,
    `fee` DECIMAL(20,6) NULL,
    `limit` DECIMAL(20,6) NULL,
    `active` TINYINT NOT NULL DEFAULT 0
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . CodFee::TABLE_NAME . '_shop` (
	`id_carrier` INT(10) UNSIGNED NOT NULL PRIMARY KEY,
    `id_shop` INT(10) UNSIGNED NOT NULL,
    `fee` DECIMAL(20,6) NULL,
    `limit` DECIMAL(20,6) NULL,
    `active` TINYINT NOT NULL DEFAULT 0
) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        return false;
    }
}
