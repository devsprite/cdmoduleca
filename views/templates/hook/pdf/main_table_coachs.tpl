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
            <span style="font-size: 18pt;color: #448B01;">Coach(s)</span>
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
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="width: 100%;background-color: #DDDDDD">
                    <th style="width: 9%">Nom</th>
                    <th style="width: 9%;text-align: center">CA TOTAL FINAL</th>
                    <th style="width: 7%;text-align: center">CA/Contact Prospect</th>
                    <th style="width: 7%;text-align: center">% de Transfo Prospect</th>
                    <th style="width: 5%;text-align: center">Nbre de Fichiers</th>
                    <th style="width: 5%;text-align: center">Nbre de Ventes TOTAL</th>
                    <th style="width: 7%;text-align: center">CA Prospection</th>
                    <th style="width: 7%;text-align: center">Panier Moyen Prospect</th>
                    <th style="width: 5%;text-align: center">% CA Prospect</th>
                    <th style="width: 7%;text-align: center">CA FID</th>
                    <th style="width: 7%;text-align: center">Panier Moyen FID</th>
                    <th style="width: 5%;text-align: center">% CA FID</th>
                    <th style="width: 7%;text-align: center">CA Retour</th>
                    <th style="width: 6%;text-align: center">% CA Retour</th>
                    <th style="width: 6%;text-align: center">CA Impayé</th>
                    <th style="width: 6%;text-align: center">% Impayé</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=coach from=$datasEmployees}
                    <tr>
                        <td style="text-align: left; background-color: #DDDDDD">{$coach['lastname']|escape:'htmlall':'UTF-8'}
                            ({$coach['firstname']|substr:0:1|escape:'htmlall':'UTF-8'}.)</td>
                        <td style="text-align: center; background-color: #FFFFFF">{displayPrice price=$coach['caAjuste']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['CaContact']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$coach['tauxTransfo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['NbreDeProspects']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$coach['NbreVentesTotal']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['CaProsp']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['panierMoyen']}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$coach['PourcCaProspect']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['caDejaInscrit']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['panierMoyenFid']}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$coach['PourcCaFID']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{displayPrice price=$coach['caAvoir']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['pourCaAvoir']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{displayPrice price=$coach['caImpaye']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['pourCaImpaye']}</td>
                    </tr>
                {/foreach}
                {if !empty($total)}
                    <tr>
                        <td style="text-align: left; background-color: #DDDDDD">Total</td>
                        <td style="text-align: center; background-color: #FFFFFF">{displayPrice price=$total['caAjuste']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['CaContact']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$total['tauxTransfo']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$total['NbreDeProspects']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$total['NbreVentesTotal']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['CaProsp']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['panierMoyen']}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$total['PourcCaProspect']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['caDejaInscrit']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$total['panierMoyenFid']}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{$total['PourcCaFID']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{displayPrice price=$total['caAvoir']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$total['pourCaAvoir']|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center; background-color: #FFFFFF">{displayPrice price=$total['caImpaye']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$total['pourCaImpaye']}</td>
                    </tr>
                {/if}
                </tbody>
            </table>
        </td>
    </tr>
</table>