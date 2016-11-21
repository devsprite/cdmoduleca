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

<table style="width: 100%; font-size: 10pt; line-height: 0.8pt;">
    <tr>
        <td>
            <table style="width: 100%;border-bottom:3px solid #448B01;">
                <tr style="line-height: 2pt;">
                    <td style="width: 30%"><span style="font-size: 24pt;color: #448B01;">L&Sens </span></td>
                    <td style="width: 70%; text-align: right"><span
                                style="font-size: 12pt;">{$smarty.now|date_format:'%A %e %B %Y à %H:%M'|capitalize|escape:'htmlall':'UTF-8'}</span>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%;border-bottom:1px solid #448B01;">
                <tr>
                    <td>
                        <table style="width: 100%;border-bottom:1px solid #448B01;">
                            <tr style="line-height: 1.5pt;">
                                <td style="width: 100%;">
                                    <span>Du {$datepickerFrom|escape|date_format:'%A %e %B %Y'|escape:'htmlall':'UTF-8'}
                                        au {$datepickerTo|escape|date_format:'%A %e %B %Y'|escape:'htmlall':'UTF-8'}</span>
                                </td>
                            </tr>
                            <tr style="line-height: 1.5pt;">
                                <td style="width: 100%;">
                                    <span>Code action : {$filterCodeAction['name']|escape:'htmlall':'UTF-8'}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td></td>
                </tr>

                {if isset($coachs) && $filterActif == 0 && !empty($datasEmployeesTotal)}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA TOTAL FINAL : {displayPrice price=$datasEmployeesTotal['caAjuste']}</span>
                        </td>
                    </tr>
                {/if}




                {if isset($coachs) && $filterActif == 0}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>Tous les coachs : {displayPrice price=$caCoachsTotal}</span>
                        </td>
                    </tr>
                {/if}
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>Coach {$coach->lastname|escape:'htmlall':'UTF-8'}
                                : {displayPrice price=$caCoach}</span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                {/if}
                {if isset($coachs) && $filterActif == 0}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA Déduit Total (- {$caDeduitJours|escape:'htmlall':'UTF-8'}
                                j.) : {displayPrice price=$caDeduitTotal}</span>
                        </td>
                    </tr>
                {/if}
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA Déduit {$coach->lastname|escape:'htmlall':'UTF-8'}
                                (- {$caDeduitJours|escape:'htmlall':'UTF-8'}
                                j.) : {displayPrice price=$caDeduitCoach}</span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                {/if}
                {if isset($coachs) && $filterActif == 0}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA FID Total : {displayPrice price=$caFidTotal}</span>
                        </td>
                    </tr>
                {/if}
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA FID {$coach->lastname|escape:'htmlall':'UTF-8'}
                                : {displayPrice price=$caFidCoach}</span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                {/if}
                {if isset($coachs) && $filterActif == 0}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA Prospects Total : {displayPrice price=($caCoachsTotal - $caFidTotal)}</span>
                        </td>
                    </tr>
                {/if}
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA Prospect {$coach->lastname|escape:'htmlall':'UTF-8'}
                                : {displayPrice price=($caTotalCoach - $caFidCoach)}</span>
                        </td>
                    </tr>
                {/if}
                <tr>
                    <td></td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<table style="100%">
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Ajustement</span>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%; font-size: 8pt;;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="background-color: #AAAAAA">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%">Date</th>
                    <th style="width: 10%; text-align: right">Somme</th>
                    <th style="width: 60%">Commentaire</th>
                    <th style="width: 10%">Numéro de commande</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=ajoutSomme from=$ajoutSommes}
                    <tr>
                        <td>{$ajoutSomme['lastname']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$ajoutSomme['date_ajout_somme']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: right">{displayPrice price=$ajoutSomme['somme']}</td>
                        <td>{$ajoutSomme['commentaire']|wordwrap:50:"\n":true|escape:'htmlall':'UTF-8'}</td>
                        <td>{$ajoutSomme['id_order']|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>

<table style="100%">
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Objectif Coachs</span>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="background-color: #AAAAAA">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%">Date début</th>
                    <th style="width: 10%">Date fin</th>
                    <th style="width: 10%;text-align: right">Objectif</th>
                    <th style="width: 10%;text-align: right">CA</th>
                    <th style="width: 10%;text-align: right">% Objectif</th>
                    <th style="width: 40%">Commentaire</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=objectif from=$objectifCoachs}
                    <tr class="{$objectif['class']|escape:'htmlall':'UTF-8'}">
                        <td>{$objectif['lastname']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_start']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_end']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: right">{displayPrice price=$objectif['somme']}</td>
                        <td style="text-align: right">{displayPrice price=$objectif['caCoach']}</td>
                        <td style="text-align: right">{$objectif['pourcentDeObjectif']|escape:'htmlall':'UTF-8'} %</td>
                        <td>{$objectif['commentaire']|wordwrap:50:"\n":true|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
            </table>
        </td>
    </tr>
</table>

<table style="100%">
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Temps de travail Coachs</span>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="background-color: #AAAAAA">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%">Date début</th>
                    <th style="width: 10%">Date fin</th>
                    <th style="width: 10%;text-align: right">Heure d'absence</th>
                    <th style="width: 10%;text-align: center">Jour de congé</th>
                    <th style="width: 10%;text-align: center">Jours ouvrés</th>
                    <th style="width: 40%">Commentaire</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=objectif from=$objectifCoachs}
                    <tr class="{$objectif['class']|escape:'htmlall':'UTF-8'}">
                        <td>{$objectif['lastname']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_start']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_end']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td style="text-align: center">{if !empty({$objectif['heure_absence']})}{$objectif['heure_absence']}{/if}</td>
                        <td style="text-align: center">{if !empty({$objectif['jour_absence']})}{$objectif['jour_absence']}{/if}</td>
                        <td style="text-align: center">{if !empty({$objectif['jour_ouvre']})}{$objectif['jour_ouvre']|escape:'htmlall':'UTF-8'} %{/if}</td>
                        <td>{$objectif['commentaire']|wordwrap:50:"\n":true|escape:'htmlall':'UTF-8'}</td>
                    </tr>
                {/foreach}
            </table>
        </td>
    </tr>
</table>



