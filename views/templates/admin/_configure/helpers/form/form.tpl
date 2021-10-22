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

{extends file="helpers/form/form.tpl"}

{block name="input"}
{if $input.type == 'number'}
{if isset($input.prefix) || isset($input.suffix)}
<div class="input-group{if isset($input.class)} {$input.class}{/if}">
    {if isset($input.prefix)}
    <span class="input-group-addon">
        {$input.prefix}
    </span>
    {/if}
{/if}
<input type="number" name="{$input.name}" id="{if isset($input.id)}{$input.id}{else}{$input.name}{/if}" min="{if isset($input.min)}{$input.min}{/if}" max="{if isset($input.max)}{$input.max}{/if}" step="{if isset($input.step)}{$input.step}{/if}" class="form-control{if isset($input.class)} {$input.class}{/if}" value="{if isset($fields_value[$input.name])}{$fields_value[$input.name]}{/if}"{if isset($input.required) && $input.required} required="required" {/if} />
{if isset($input.prefix) || isset($input.suffix)}
    {if isset($input.suffix)}
    <span class="input-group-addon">
        {$input.suffix}
    </span>
    {/if}
</div>
{/if}
{else}
{$smarty.block.parent}
{/if}
{/block}