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

<div class="row">
    <div class="col-xs-12">
        {if (isset($coachs) && !isset($histo))}
            <form action="{$linkFilter}" method="post">
                <div class="form-group">
                    <div class="col-lg-3 ">
                        <label class="control-label" for="filterCoach">
                            Coach :
                        </label>
                        <select name="filterCoach" class="fixed-width-xl" id="filterCoach"
                                onchange="this.form.submit();">
                            {foreach item=coach from=$coachs}
                                <option value="{$coach['id_employee']}" {if $filterActif == $coach['id_employee']}
                                    selected="selected"{/if}>{$coach['lastname']} - ({$coach['firstname']})
                                </option>
                            {/foreach}
                        </select>
                        <input type="hidden" name="submitFilterCoachs" value="1"/>
                        <div class="checkbox">
                            <label for="filterCoachActif">
                                <input type="checkbox" name="filterCoachActif" id="filterCoachActif"
                                       onchange="this.form.submit();" {if $filterCoachActif == "checked"}checked{/if}>
                                Actif</label>
                        </div>
                    </div>
                </div>
            </form>
        {/if}
        {if !isset($histo)}
        <form action="{$linkFilter}" method="post">
            <div class="form-group">
                <div class="col-lg-3">
                    <label class="control-label" for="filterCodeAction">
                        Code Action :
                    </label>
                    <select name="filterCodeAction" class="fixed-width-xl" id="filterCoach"
                            onchange="this.form.submit();">
                        {foreach item=code from=$codesAction}
                            <option value="{$code['id_code_action']}" {if $filterCodeAction == $code['id_code_action']}
                                selected="selected"{/if}>{$code['name']}</option>
                        {/foreach}
                    </select>
                    <input type="hidden" name="submitFilterCodeAction" value="1"/>
                </div>
            </div>
        </form>
        <form action="{$linkFilter}" method="post">
            <div class="form-group">
                <div class="col-lg-2 ">
                    <label class="control-label" for="filterCommande">
                        Commande Valide :
                    </label>
                    <select name="filterCommande" class="fixed-width-xl" id="filterCommande"
                            onchange="this.form.submit();">
                        {foreach item=code from=$commandeActive}
                            <option value="{$code['value']}" {if $filterCommandeActive == $code['value']}
                                selected="selected"{/if}>{$code['key']}</option>
                        {/foreach}
                    </select>
                    <input type="hidden" name="submitFilterCommande" value="1"/>
                </div>
            </div>
        </form>
        {/if}
    </div>
        <button id="toggle_ca" class="btn btn-success"><i class="icon-chevron-down"></i> </button>
        <div class="toggle">
            <div class="content_ca">
                <div class="row panel">
                    <div class="col-lg-2">
                        {if !isset($histo)}
                        <a class="btn btn-default export-csv" href="{$LinkFile}&export_csv=1">
                            <i class="icon-cloud-upload"></i> CSV</a>
                        {/if}
                        <a class="btn btn-default export-csv" href="{$LinkFile}&export_pdf=1">
                            <i class="icon-cloud-upload"></i> PDF</a>
                    </div>
                    {if isset($coachs) && $filterActif != 0}
                    {if isset($allow) && !isset($histo)}
                        <form enctype="multipart/form-data" action="{$LinkFile}" method="post">
                            <div class="col-lg-3">
                                <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
                                <input type="file" name="uploadFile" class="btn btn-default">
                            </div>
                            <div class="col-lg-1">
                                <button class="btn btn-default" type="submit" name="submitUpload">
                                    <i class="icon-cloud-upload"></i> Envoyer</button>
                            </div>
                        </form>
                    {/if}
                    {/if}
                    {if !isset($histo)}
                        <div class="col-lg-2">
                            <a href="{$linkFilter}&histo=1" class="btn btn-info"><strong>Sauvegarde Historique</strong></a>
                        </div>
                    {/if}
                </div>

                {if isset($coachs) && $filterActif != 0 && !isset($histo)}
                <div class="col-xs-12 hidden-print">
                    <br>
                    <h2>Ajustement</h2>
                    <form id="form_as_date" action="{$linkFilter}" method="post">
                        <div class="form-group">
                            <div class="col-lg-1">
                                <label for="as_date">Date</label>
                                <input type="text" name="as_date" id="as_date" value="{if isset($smarty.post.as_date)
                                }{$smarty.post.as_date|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}"
                                       class="datepicker  form-control">
                                <script type="text/javascript">
                                    {literal}
                                    $(document).ready(function () {
                                        if ($("form#form_as_date .datepicker").length > 0)
                                            $("form#form_as_date .datepicker").datepicker({
                                                prevText: '',
                                                nextText: '',
                                                dateFormat: 'yy-mm-dd'
                                            });
                                    });
                                    {/literal}
                                </script>
                            </div>
                            <div class="col-lg-1">
                                <label for="as_somme">Somme</label>
                                <div class="input input-group">
                                    <input type="text" id="as_somme" name="as_somme"
                                           value="{if isset($smarty.post.as_somme)
                                           }{$smarty.post.as_somme}{/if}">
                                    <span class="input-group-addon">€</span>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <label for="as_order">Numéro commande</label>
                                <div class="input input-group">
                                    <input type="text" id="as_order" name="as_order"
                                           value="{if isset($smarty.post.as_order)
                                           }{$smarty.post.as_order}{/if}">
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label for="as_commentaire">Commentaire</label>
                                <input type="text" id="as_commentaire" value="{if isset($smarty.post.as_commentaire)
                                }{$smarty.post.as_commentaire}{/if}" name="as_commentaire">
                            </div>
                            <div class="col-lg-12">
                                {if isset($smarty.post.as_id)}
                                    <div class="col-lg-1">
                                        <br>
                                        <a class="btn btn-primary" href="{$link->getAdminLink('AdminCaLetSens')}">Annulé
                                        </a>
                                    </div>
                                {/if}
                                <div class="col-lg-1">
                                    <br>
                                    <button class="btn btn-success" type="submit" id="as_submit" name="as_submit">
                                        Enregistrer
                                    </button>
                                </div>
                                <input type="hidden" id="as_id_employee" name="as_id_employee"
                                       value="{if isset($smarty.post.as_id_employee)
                                       }{$smarty.post.as_id_employee}{else}{$filterActif}{/if}">
                                <input type="hidden" id="as_id" name="as_id"
                                       value="{if isset($smarty.post.as_id)
                                       }{$smarty.post.as_id}{/if}">

                            </div>
                        </div>
                    </form>
                </div>
                <div class="col-xs-12 hidden-print">
                    <br>
                    <h2>Coach</h2>
                    <form id="form_oc_objectif" action="{$linkFilter}" method="post">
                        <div class="form-group">
                            <div class="col-lg-1">
                                <label for="oc_date_start">Date début</label>
                                <input type="text" name="oc_date_start" id="oc_date_start"
                                       value="{if isset($smarty.post.oc_date_start)
                                       }{$smarty.post.oc_date_start|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}"
                                       class="datepicker form-control">
                            </div>
                            <div class="col-lg-1">
                                <label for="oc_date_end">Date fin</label>
                                <input type="text" name="oc_date_end" id="oc_date_end"
                                       value="{if isset($smarty.post.oc_date_end)
                                       }{$smarty.post.oc_date_end|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}"
                                       class="datepicker form-control">
                            </div>
                            <script type="text/javascript">
                                {literal}
                                $(document).ready(function () {
                                    if ($("#oc_date_start").length > 0)
                                        $("#oc_date_start").datepicker({
                                            prevText: '',
                                            nextText: '',
                                            dateFormat: 'yy-mm-dd'
                                        });
                                    if ($("#oc_date_end").length > 0)
                                        $("#oc_date_end").datepicker({
                                            prevText: '',
                                            nextText: '',
                                            dateFormat: 'yy-mm-dd'
                                        });
                                });
                                {/literal}
                            </script>
                            <div class="col-lg-1">
                                <label for="oc_somme">Objectif</label>
                                <div class="input input-group">
                                    <input type="text" id="oc_somme" name="oc_somme"
                                           value="{if isset($smarty.post.oc_somme)
                                           }{$smarty.post.oc_somme}{/if}">
                                    <span class="input-group-addon">€</span>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <label for="oc_somme">Heure d'absence</label>
                                <div class="input input-group">
                                    <input type="text" id="oc_heure" name="oc_heure"
                                           value="{if isset($smarty.post.oc_heure)
                                           }{$smarty.post.oc_heure}{/if}">
                                    <span class="input-group-addon">Heure</span>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <label for="oc_somme">Jour de congé</label>
                                <div class="input input-group">
                                    <input type="text" id="oc_jour" name="oc_jour"
                                           value="{if isset($smarty.post.oc_jour)
                                           }{$smarty.post.oc_jour}{/if}">
                                    <span class="input-group-addon">Jour</span>
                                </div>
                            </div>
                            <div class="col-lg-1">
                                <label for="oc_somme">Nbre de jours ouvrés</label>
                                <div class="input input-group">
                                    <input type="text" id="oc_jour_ouvre" name="oc_jour_ouvre"
                                           value="{if isset($smarty.post.oc_jour_ouvre)
                                           }{$smarty.post.oc_jour_ouvre}{/if}">
                                    <span class="input-group-addon">Jour</span>
                                </div>
                            </div>
                            <div class="col-lg-12">
                                <div class="col-lg-7">
                                    <label for="oc_commentaire">Commentaire</label>
                                    <input type="text" id="oc_commentaire" value="{if isset($smarty.post.oc_commentaire)
                                    }{$smarty.post.oc_commentaire}{/if}" name="oc_commentaire">
                                </div>
                            </div>
                            <div class="col-lg-12">
                                {if isset($smarty.post.oc_id)}
                                    <div class="col-lg-1">
                                        <br>
                                        <a class="btn btn-primary" href="{$link->getAdminLink('AdminCaLetSens')}">Annulé
                                        </a>
                                    </div>
                                {/if}
                                <div class="col-lg-1">
                                    <br>
                                    <button class="btn btn-success" type="submit" id="oc_submit" name="oc_submit">
                                        Enregistrer
                                    </button>
                                    <input type="hidden" id="oc_id_employee" name="oc_id_employee"
                                           value="{if isset($smarty.post.oc_id_employee)
                                           }{$smarty.post.oc_id_employee}{else}{$filterActif}{/if}">
                                    <input type="hidden" id="oc_id" name="oc_id"
                                           value="{if isset($smarty.post.oc_id)
                                           }{$smarty.post.oc_id}{/if}">
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    {/if}
</div>