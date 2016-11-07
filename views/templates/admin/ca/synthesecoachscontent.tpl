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

<hr>
<div class="row synthesecontent">
    <div class="col-xs-3">
        <div class="row group">
            <div class="col-xs-12">CA Code Action {$filterCodeAction['name']|escape:'htmlall':'UTF-8'}
                <span class="pull-right">{displayPrice price=$caCoachsTotal}</span></div>
        </div>
        {if !empty({$coach->lastname})}
            <div class="row">
                <div class="col-xs-12">CA Code Action {$filterCodeAction['name']|escape:'htmlall':'UTF-8'} {$coach->lastname|escape:'htmlall':'UTF-8'}
                    <span class="pull-right">{displayPrice price=$caCoach}</span></div>
            </div>
        {/if}
        <div class="row group">
            <div class="col-xs-12">CA Déduit Total (- {$caDeduitJours|escape:'htmlall':'UTF-8'} j.)
                <span class="pull-right">{displayPrice price=$caDeduitTotal}</span></div>
        </div>
        {if !empty({$coach->lastname|escape:'htmlall':'UTF-8'})}
            <div class="row">
                <div class="col-xs-12">CA Déduit {$coach->lastname|escape:'htmlall':'UTF-8'} (- {$caDeduitJours|escape:'htmlall':'UTF-8'} j.)
                    <span class="pull-right">{displayPrice price=$caDeduitCoach}</span></div>
            </div>
        {/if}
        <div class="row group">
            <div class="col-xs-12">CA FID (Prospects déjà inscrit)
                <span class="pull-right">{displayPrice price=$caFidTotal}</span></div>
        </div>
        {if !empty({$coach->lastname})}
            <div class="row">
                <div class="col-xs-12">CA FID (Prospects déjà inscrit {$coach->lastname|escape:'htmlall':'UTF-8'})
                    <span class="pull-right">{displayPrice price=$caFidCoach}</span></div>
            </div>
        {/if}
        <div class="row group">
            <div class="col-xs-12">CA Prospect Total
                <span class="pull-right">{displayPrice price=($caTotal - $caFidTotal)}</span></div>
        </div>
        {if !empty({$coach->lastname|escape:'htmlall':'UTF-8'})}
            <div class="row">
                <div class="col-xs-12">CA Prospect {$coach->lastname|escape:'htmlall':'UTF-8'}
                    <span class="pull-right">{displayPrice price=($caTotalCoach - $caFidCoach)}</span></div>
            </div>
        {/if}
        <div class="row group">
            <div class="col-xs-12">Prime fichier total
                <span class="pull-right">{displayPrice price=$primeFichierTotal}</span></div>
        </div>
        {if !empty({$coach->lastname|escape:'htmlall':'UTF-8'})}
            <div class="row">
                <div class="col-xs-12">Prime Fichier {$coach->lastname}
                    <span class="pull-right">{displayPrice price=$primeFichierCoach}</span></div>
            </div>
        {/if}
    </div>
    <div class="col-xs-push-1 col-xs-8">
        <h2>Ajustements</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th style="width: 15%">Nom</th>
                <th style="width: 10%">Date</th>
                <th style="width: 10%"><span class="pull-right">Somme</span></th>
                <th style="width: 60%">Commentaire</th>
                <th style="width: 5%"></th>
            </tr>
            </thead>
            <tbody>
            {foreach item=ajoutSomme from=$ajoutSommes}
                <tr>
                    <td>{$ajoutSomme['lastname']|escape:'htmlall':'UTF-8'}</td>
                    <td>{$ajoutSomme['date_add']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                    <td><span class="pull-right">{displayPrice price=$ajoutSomme['somme']}</span></td>
                    <td>{$ajoutSomme['commentaire']|wordwrap:50:"\n":true|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        {if isset($coachs)}
                            <a href="{$linkFilter|escape:'htmlall':'UTF-8'}&mod_as&id_as={$ajoutSomme['id_ajout_somme']}">
                                <i class="icon-edit text-success"></i>
                            </a>
                            <a href="{$linkFilter|escape:'htmlall':'UTF-8'}&del_as&id_as={$ajoutSomme['id_ajout_somme']}"
                               onclick="if(confirm('Etes-vous sur de vouloir supprimer ce cet ajout ?')) {} else return false">
                                <i class="icon-cut text-danger"></i>
                            </a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <h2>Objectif Coach</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th style="width: 15%">Nom</th>
                <th style="width: 10%">Date début</th>
                <th style="width: 10%">Date fin</th>
                <th style="width: 10%"><span class="pull-right">Objectif</span></th>
                <th style="width: 10%"><span class="pull-right">CA</span></th>
                <th style="width: 10%"><span class="pull-right">% Objectif</span></th>
                <th style="width: 30%">Commentaire</th>
                <th style="width: 5%"></th>
            </tr>
            </thead>
            <tbody>
            {foreach item=objectif from=$objectifCoachs}
                {if $objectif['somme'] != 0}
                    <tr class="{$objectif['class']|escape:'htmlall':'UTF-8'}">
                        <td>{$objectif['lastname']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_start']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_end']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td><span class="pull-right">{displayPrice price=$objectif['somme']}</span></td>
                        <td><span class="pull-right">{displayPrice price=$objectif['caCoach']}</span></td>
                        <td><span class="pull-right">{$objectif['pourcentDeObjectif']|escape:'htmlall':'UTF-8'} %</span></td>
                        <td>{$objectif['commentaire']|wordwrap:50:"\n":true|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            {if isset($coachs)}
                                <a href="{$linkFilter|escape:'htmlall':'UTF-8'}&mod_oc&id_oc={$objectif['id_objectif_coach']}">
                                    <i class="icon-edit text-success"></i>
                                </a>
                                <a href="{$linkFilter|escape:'htmlall':'UTF-8'}&del_oc&id_oc={$objectif['id_objectif_coach']}"
                                   onclick="if(confirm('Etes-vous sur de vouloir supprimer cet objectif ?')) {} else return false">
                                    <i class="icon-cut text-danger"></i>
                                </a>
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
        <h2>Horaire Coach</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th style="width: 15%">Nom</th>
                <th style="width: 10%">Date début</th>
                <th style="width: 10%">Date fin</th>
                <th style="width: 10%">Heure</th>
                <th style="width: 10%">Jour</th>
                <th style="width: 10%">Jours ouvrés</th>
                <th style="width: 40%">Commentaire</th>
                <th style="width: 5%"></th>
            </tr>
            </thead>
            <tbody>
            {foreach item=objectif from=$objectifCoachs}
                {if $objectif['heure_absence'] != 0 || $objectif['jour_absence'] != 0 || $objectif['jour_ouvre'] != 0}
                    <tr>
                        <td>{$objectif['lastname']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_start']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['date_end']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['heure_absence']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['jour_absence']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['jour_ouvre']|escape:'htmlall':'UTF-8'}</td>
                        <td>{$objectif['commentaire']|wordwrap:50:"\n":true|escape:'htmlall':'UTF-8'}</td>
                        <td>
                            {if isset($coachs)}
                                <a href="{$linkFilter}&mod_oc&id_oc={$objectif['id_objectif_coach']|escape:'htmlall':'UTF-8'}">
                                    <i class="icon-edit text-success"></i>
                                </a>
                                <a href="{$linkFilter}&del_oc&id_oc={$objectif['id_objectif_coach']|escape:'htmlall':'UTF-8'}"
                                   onclick="if(confirm('Etes-vous sur de vouloir supprimer cette ligne ?')) {} else return false">
                                    <i class="icon-cut text-danger"></i>
                                </a>
                            {/if}
                        </td>
                    </tr>
                {/if}
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
