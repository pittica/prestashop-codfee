{**
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
 *}

<section>
  <div>
    <p><strong>{l s='Cash on Delivery' mod='pitticacodfee'}</strong></p>
    <p>{l s='Pay the goods on delivery by cash.' mod='pitticacodfee'}</p>
    {if $fee}
    <p>{l s='Surcharge: %s' sprintf=[$fee] mod='pitticacodfee'}</p>
    {/if}
  </div>
</section>
