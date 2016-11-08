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
        <th style="width: 8%;text-align: right">CA TOTAL</th>
        <th style="width: 7%;text-align: right">Ajustement</th>
        <th style="width: 7%;text-align: right">CA Ajusté</th>
        <th style="width: 5%;text-align: center">Nbre de ventes TOTAL</th>
        <th style="width: 5%;text-align: center">Nbre de prospects</th>
        <th style="width: 5%;text-align: center">Prime Fichier</th>
        <th style="width: 5%;text-align: right">Panier Moyen</th>
        <th style="width: 5%;text-align: center">CA/Contact</th>
        <th style="width: 5%;text-align: right">% Taux de transfo. prospect</th>
        <th style="width: 5%;text-align: right">CA prospect</th>
        <th style="width: 5%;text-align: center">% CA prospect</th>
        <th style="width: 5%;text-align: right">CA FID</th>
        <th style="width: 5%;text-align: center">% CA FID</th>
        <th style="width: 5%;text-align: right">CA Remb/Avoir</th>
        <th style="width: 5%;text-align: center">% CA Remb/Avoir</th>
        <th style="width: 5%;text-align: right">CA Impayé</th>
        <th style="width: 5%;text-align: center">% Impayé</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
    <tr>
        <td>{$coach['lastname']|escape:'htmlall':'UTF-8'} ({$coach['firstname']|substr:0:1|escape:'htmlall':'UTF-8'}.)</td>
        <td class="text-right">{displayPrice price=$coach['caTotal']}</td>
        <td class="text-right">{displayPrice price=$coach['ajustement']}</td>
        <td class="text-right">{displayPrice price=$coach['caAjuste']}</td>
        <td class="text-center">{$coach['NbreVentesTotal']|escape:'htmlall':'UTF-8'}</td>
        <td class="text-center">{$coach['NbreDeProspects']|escape:'htmlall':'UTF-8'}</td>
        <td class="text-right">{displayPrice price=$coach['primeFichierCoach']}</td>
        <td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>
        <td class="text-center">{$coach['CaContact']|escape:'htmlall':'UTF-8'}</td>
        <td class="text-right">{$coach['tauxTransfo']|escape:'htmlall':'UTF-8'}</td>
        <td class="text-right">{displayPrice price=$coach['CaProsp']}</td>
        <td class="text-right">{$coach['PourcCaProspect']|escape:'htmlall':'UTF-8'}</td>
        <td class="text-right">{displayPrice price=$coach['caDejaInscrit']}</td>
        <td class="text-right">{$coach['PourcCaFID']|escape:'htmlall':'UTF-8'}</td>
        <td class="text-right">{displayPrice price=$coach['caRembAvoir']}
        <td class="text-right">{$coach['pourCaRembAvoir']|escape:'htmlall':'UTF-8'}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
    </tr>
    {/foreach}
    </tbody>
</table>
<hr>
<h2>Groupe </h2>
<table class="table table-hover">
    <thead>
    <tr style="width: 100%">
        <th style="width: 10%">Nom</th>
        <th style="width: 10%;text-align: right">Prime Abo = 10%</th>
        <th style="width: 10%;text-align: center">Nbre d'abos</th>
        <th style="width: 10%;text-align: center">Nbre de désabo</th>
        <th style="width: 10%;text-align: right">% de désabo</th>
        <th style="width: 10%;text-align: right">CA Parrainage</th>
        <th style="width: 40%;text-align: left">% CA Parrainage</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
        <tr>
            <td>{$coach['lastname']|escape:'htmlall':'UTF-8'} ({$coach['firstname']|escape:'htmlall':'UTF-8'})</td>
            <td class="text-right">{displayPrice price=$coach['primeVenteGrAbo']}</td>
            <td class="text-center">{$coach['nbrVenteGrAbo']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-center">{$coach['nbrVenteGrDesaAbo']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-right">{$coach['pourcenDesabo']|escape:'htmlall':'UTF-8'}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrPar']}</td>
            <td class="text-left">{$coach['pourVenteGrPar']|escape:'htmlall':'UTF-8'}</td>
            <td></td>
        </tr>
    {/foreach}
    </tbody>
</table>