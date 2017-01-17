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

<hr>
<h2>Coach</h2>
<table class="table table-hover">
    <thead>
    <tr style="width: 100%">
        <th style="width: 8%">Nom</th>
        <th style="width: 8%;text-align: right">CA TOTAL FINAL</th>
        <th style="width: 5%;text-align: center">CA/Contact Prospect</th>
        <th style="width: 5%;text-align: right">% de Transfo Prospect</th>
        <th style="width: 5%;text-align: center">Nbre de Fichiers</th>
        <th style="width: 5%;text-align: center">Nbre de Ventes TOTAL</th>
        <th style="width: 5%;text-align: right">CA Prospection</th>
        <th style="width: 5%;text-align: center">Panier Moyen Prospect</th>
        <th style="width: 5%;text-align: center">% CA Prospect</th>
        <th style="width: 5%;text-align: right">CA FID</th>
        <th style="width: 5%;text-align: center">Panier Moyen FID</th>
        <th style="width: 5%;text-align: center">% CA FID</th>
        <th style="width: 5%;text-align: right">CA Retour</th>
        <th style="width: 5%;text-align: center">% CA Retour</th>
        <th style="width: 5%;text-align: right">CA Impayé</th>
        <th style="width: 5%;text-align: center">% Impayé</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
    <tr>
        <td>{$coach['lastname']|escape:'htmlall':'UTF-8'} ({$coach['firstname']|escape:'htmlall':'UTF-8'})</td>{*Nom*}
        <td class="text-right">{displayPrice price=$coach['caAjuste']}</td>{*CA TOTAL FINAL*}
        <td class="text-center">{displayPrice price=$coach['CaContact']|escape:'htmlall':'UTF-8'}</td>{*CA/Contact Prospect*}
        <td class="text-right">{$coach['tauxTransfo']|escape:'htmlall':'UTF-8'}</td>{*% de Transfo Prospect*}
        <td class="text-center">{$coach['NbreDeProspects']|escape:'htmlall':'UTF-8'}</td>{*Nbre de Fichiers*}
        <td class="text-center">{$coach['NbreVentesTotal']|escape:'htmlall':'UTF-8'}</td>{*Nbre de Ventes TOTAL*}
        <td class="text-right">{displayPrice price=$coach['CaProsp']}</td>{*CA Prospection*}
        <td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>{*Panier Moyen Prospect*}
        <td class="text-right">{$coach['PourcCaProspect']|escape:'htmlall':'UTF-8'}</td>{*% CA Prospect*}
        <td class="text-right">{displayPrice price=$coach['caFidTotal']}</td>{*CA FID*}
        <td class="text-right">{displayPrice price=$coach['panierMoyenFid']}</td>{*Panier Moyen FID*}
        <td class="text-right">{$coach['PourcCaFID']|escape:'htmlall':'UTF-8'}</td>{*% CA FID*}
        <td class="text-right">{displayPrice price=$coach['caAvoir']}</td>{*CA Retour*}
        <td class="text-right">{$coach['pourCaAvoir']|escape:'htmlall':'UTF-8'}</td>{*% CA Retour*}
        <td class="text-right">{displayPrice price=$coach['caImpaye']}</td>{*CA Impayé*}
        <td class="text-right">{$coach['pourCaImpaye']}</td>{*% Impayé*}
    </tr>
    {/foreach}
    {if !empty($total)}
            <tr>
                <td>Total</td>
                <td class="text-right">{displayPrice price=$total['caAjuste']}</td>
                <td class="text-center">{displayPrice price=$total['CaContact']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-right">{$total['tauxTransfo']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-center">{$total['NbreDeProspects']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-center">{$total['NbreVentesTotal']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-right">{displayPrice price=$total['CaProsp']}</td>
                <td class="text-right">{displayPrice price=$total['panierMoyen']}</td>
                <td class="text-right">{$total['PourcCaProspect']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-right">{displayPrice price=$total['caDejaInscrit']}</td>
                <td class="text-right">{displayPrice price=$total['panierMoyenFid']}</td>
                <td class="text-right">{$total['PourcCaFID']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-right">{displayPrice price=$total['caAvoir']}
                <td class="text-right">{$total['pourCaAvoir']|escape:'htmlall':'UTF-8'}</td>
                <td class="text-right">{displayPrice price=$total['caImpaye']}</td>
                <td class="text-right">{$total['pourCaImpaye']}</td>
            </tr>
    {/if}

    </tbody>
</table>
<hr>
<h2>Groupe </h2>
<table class="table table-hover">
    <thead>
    <tr style="width: 100%">
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
            <td>{$coach['lastname']|escape:'htmlall':'UTF-8'} ({$coach.firstname|escape:'htmlall':'UTF-8'})</td>
            <td class="text-right">{displayPrice price=$coach.totalVenteGrAbo}</td>
            <td class="text-center">{$coach.nbrVenteGrAbo|escape:'htmlall':'UTF-8'}</td>
            <td class="text-center">{$coach.nbrVenteGrDesaAbo|escape:'htmlall':'UTF-8'}</td>
            <td class="text-right">{$coach.pourcenDesabo|escape:'htmlall':'UTF-8'}</td>
            <td class="text-right">{displayPrice price=$coach.totalVenteGrPar}</td>
            <td class="text-center">{$coach.nbrVenteGrPar|escape:'htmlall':'UTF-8'}</td>
            <td class="text-left">{$coach.pourVenteGrPar|escape:'htmlall':'UTF-8'}</td>
        </tr>
    {/foreach}
    {if !empty($total)}
        <tr>
            <td>Total</td>
            <td class="text-right">{displayPrice price=$total['totalVenteGrAbo']}</td>
            <td class="text-center">{$total['nbrVenteGrAbo']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-center">{$total['nbrVenteGrDesaAbo']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-right">{$total['pourcenDesabo']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-right">{displayPrice price=$total['totalVenteGrPar']}</td>
            <td class="text-center">{$total['nbrVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-left">{$total['pourVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
        </tr>
    {/if}
    </tbody>
</table>