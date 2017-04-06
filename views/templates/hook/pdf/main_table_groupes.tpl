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
 * @copyright 2007-2015 PrestaShop SA / 2011-2015 Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 *}

<table>
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Groupe(s)</span>
            {if isset($histo)}
            <span style="font-size: 10pt">Du {$datasEmployeesTotal['datepickerFrom']|escape|date_format:'%A %e %B %Y'|escape:'htmlall':'UTF-8'}
                                        au {$datasEmployeesTotal['datepickerTo']|escape|date_format:'%A %e %B %Y'|escape:'htmlall':'UTF-8'}</span>
            {else}
            <span style="font-size: 10pt">Du {$datepickerFrom|escape|date_format:'%A %e %B %Y'|escape:'htmlall':'UTF-8'}
                au {$datepickerTo|escape|date_format:'%A %e %B %Y'|escape:'htmlall':'UTF-8'}</span>
            {/if}
        </td>
    </tr>
    <tr>
        <td>
        </td>
    </tr>
    <tr>
        <td>
            <table style="100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="width: 100%;background-color: #DDDDDD">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%;text-align: right">CA Total Abo</th>
                    <th style="width: 10%;text-align: center">Nbre d'abos</th>
                    <th style="width: 10%;text-align: center">Nbre de désabo</th>
                    <th style="width: 10%;text-align: right">% de désabo</th>
                    <th style="width: 10%;text-align: right">CA Parrainage</th>
                    <th style="width: 10%;text-align: center">Nbre de Parrainage</th>
                    <th style="width: 30%;text-align: left">% de Parrainage</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=coach from=$datasEmployees}
                    <tr>
                        <td>{$coach['lastname']|escape:'htmlall':'UTF-8'} ({$coach['firstname']|escape:'htmlall':'UTF-8'})</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['totalVenteGrAbo']}</td>
                        <td style="text-align: center">{$coach['nbrVenteGrAbo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['nbrVenteGrDesaAbo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center">{$coach['pourcenDesabo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['totalVenteGrPar']}</td>
                        <td style="text-align: center">{$coach['nbrVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: left; background-color: #DDDDDD">{$coach['pourVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}

                {if !empty($total)}
                    <tr>
                        <td>Total</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['totalVenteGrAbo']}</td>
                        <td style="text-align: center">{$total['nbrVenteGrAbo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$total['nbrVenteGrDesaAbo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center">{$total['pourcenDesabo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['totalVenteGrPar']}</td>
                        <td style="text-align: center">{$total['nbrVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: left; background-color: #DDDDDD">{$total['pourVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </td>
    </tr>
</table>