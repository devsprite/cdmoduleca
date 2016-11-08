{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Dominique <dominique@chez-dominique.fr>
 * @copyright 2007-2016 PrestaShop SA / 2011-2016 Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 *}

{if isset($listProspects)}
<div class="row panel">
    <div class="panel-heading">
        Prospects
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Coach</th>
            <th>Traité</th>
            <th>Injoignable</th>
            <th>Date Prospection</th>
        </tr>
        </thead>
        <tbody>
        {foreach item=prosp from=$listProspects}
            <tr>
                <td>{$prosp['id_customer']|escape:'htmlall':'UTF-8'}</td>
                <td>{$prosp['lastname']|upper} {$prosp['firstname']|lower|capitalize|escape:'htmlall':'UTF-8'}</td>
                <td>{$prosp['coach']|escape:'htmlall':'UTF-8'}</td>
                <td>{$prosp['traite']|escape:'htmlall':'UTF-8'}</td>
                <td>{$prosp['injoignable']|escape:'htmlall':'UTF-8'}</td>
                <td>{$prosp['date_debut']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{/if}