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

{if $confirmation}{$confirmation|escape:'htmlall':'UTF-8'}{/if}
{if $isAllow}
    <div class="row panel">
        <div class="panel-heading">
            Attribution des prospects
        </div>
        <form action="{$linkForm}" method="post" name="employes">
            <div class="row panel">
                <div class="col-lg-2">
                    <p>Il y a {$nbr_prospects|escape:'htmlall':'UTF-8'} nouveau(x) prospect(s)</p>
                    <p>et {$nbr_ancien_prospects|escape:'htmlall':'UTF-8'} ancien(s) prospect(s)</p>
                </div>
                <div class="col-lg-3">
                    <label class="control-label" for="">Nombre de jours d'ancienneté des prospects
                        <input type="text" name="p_nbr_jour_max_prospects" id="p_nbr_jour_max_prospects"
                               value="{$jour_max_prospects|escape:'htmlall':'UTF-8'}"
                               class="form-control" ></label>
                </div>
                <div class="col-lg-2">
                    <label class="control-label" for="p_date_index">Date index prospects
                        <input type="text" name="p_date_index" id="p_date_index"
                               value="{$date_index|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}"
                               class="datepicker form-control"></label>
                </div>
                <div class="col-lg-2">
                    <br>
                    <button type="submit" name="submitDateIndex" id="submitDateIndex" class="btn btn-default"><i
                                class="icon-save"></i> {l s='Save'}</button>
                </div>
            </div>
            <div class="row panel">
                <div class="col-xs-6 col-md-2 col-xs-12">
                    <label class="control-label" for="p_date_start">Date début
                        <input type="text" name="p_date_start" id="p_date_start"
                               value="{if isset($smarty.post.p_date_start)
                               }{$smarty.post.p_date_start|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}{elseif
                               isset($pa)}{$pa->date_debut|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}{else}{$smarty.now|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}{/if}"
                               class="datepicker form-control"></label>
                </div>
                <div class="col-xs-6 col-md-2 col-xs-12">
                    <label class="control-label" for="p_date_end">Date fin
                        <input type="text" name="p_date_end" id="p_date_end"
                               value="{if isset($smarty.post.p_date_end)
                               }{$smarty.post.p_date_end|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}{elseif
                               isset($pa)}{$pa->date_fin|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}{/if}"
                               class="datepicker form-control"></label>
                </div>
                {if isset($pa)}
                    <div class="col-lg-3 col-md-3 col-xs-12">
                        <label class="control-label">
                            Coach :
                            <select name="pa_id_employee" class="" id="pa_id_employee">
                                {foreach item=coach from=$coachs}
                                    {if !empty($coach['id_group'])}
                                        <option value="{$coach['id_employee']}" {if $pa->id_employee == $coach['id_employee']}
                                            selected="selected"{/if}>{$coach['lastname']|escape:'htmlall':'UTF-8'} -
                                            ({$coach['firstname']|escape:'htmlall':'UTF-8'})
                                        </option>
                                    {/if}
                                {/foreach}
                            </select>
                        </label>
                    </div>
                    <div class="col-lg-3 col-md-3 col-xs-12">
                        <label class="control-label" for="">Nbre Prosprects Attribués
                            <input type="text" name="" id=""
                                   value="{$pa->nbr_prospect_attribue|escape:'htmlall':'UTF-8'}"
                                   class="form-control" disabled></label>
                    </div>
                    <input type="hidden" name="pa_id_pa" value="{$pa->id_prospect_attribue|escape:'htmlall':'UTF-8'}">
                {elseif !empty($nbrProspectsIsoles)}
                    <div class="col-lg-3 col-md-3 col-xs-12">
                        <label class="control-label">
                            Coach :
                            <select name="pi_id_employee" class="" id="pi_id_employee">
                                {foreach item=coach from=$coachs}
                                    {if !empty($coach['id_group'])}
                                        <option value="{$coach['id_employee']|escape:'htmlall':'UTF-8'}">{$coach['lastname']|escape:'htmlall':'UTF-8'}
                                            -
                                            ({$coach['firstname']|escape:'htmlall':'UTF-8'})
                                        </option>
                                    {/if}
                                {/foreach}
                            </select>
                        </label>
                    </div>
                    <div class="col-lg-3 col-md-3 col-xs-12">
                        <label class="control-label" for="">{$nbrProspectsIsoles|escape:'htmlall':'UTF-8'} Prospect(s)
                            sans coach
                            <input type="text" name="pi_nbr_pr" id="pi_nbr_pr"
                                   value="{$nbrProspectsIsoles|escape:'htmlall':'UTF-8'}"
                                   class="form-control"></label>
                    </div>
                {/if}
                {if !isset($pa)}
                    <div class="col-xs-1">
                        <div>
                            <label for="employeActif">Employés actif</label>
                        </div>
                        <div>
                            <input type="checkbox" name="employeActif" id="employeActif" onchange="this.form.submit();"
                                    {if $employeActif}{$employeActif|escape:'htmlall':'UTF-8'}{/if}>
                            <input type="hidden" name="submitEmployeActif" value="1"/>
                        </div>
                    </div>
                {/if}
                <script type="text/javascript">
                    {literal}
                    $(document).ready(function () {
                        if ($("#p_date_start").length > 0)
                            $("#p_date_start").datepicker({
                                prevText: '',
                                nextText: '',
                                dateFormat: 'yy-mm-dd'
                            });
                        if ($("#p_date_end").length > 0)
                            $("#p_date_end").datepicker({
                                prevText: '',
                                nextText: '',
                                dateFormat: 'yy-mm-dd'
                            });
                        if ($("#p_date_index").length > 0)
                            $("#p_date_index").datepicker({
                                prevText: '',
                                nextText: '',
                                dateFormat: 'yy-mm-dd'
                            });
                    });
                    {/literal}
                </script>

                <div class="col-xs-12">

                    {if isset($pa) || !empty($nbrProspectsIsoles)}{else}
                        <hr>
                        {foreach item=employe from=$employes}
                            {if !empty($employe['id_group'])}
                                <div class="col-lg-4 col-md-6 col-xs-12">
                                    <label class="control-label"
                                           for="{$employe['lastname']|escape:'htmlall':'UTF-8'}{$employe['firstname']|escape:'htmlall':'UTF-8'}"><strong>{$employe['lastname']|escape:'htmlall':'UTF-8'}</strong>
                                        {if !empty({$employe['total_prospect']})} - {$employe['total_prospect']|escape:'htmlall':'UTF-8'} Prospects{/if}
                                        <p>({$employe['firstname']|escape:'htmlall':'UTF-8'}
                                            - {$employe['id_group']|escape:'htmlall':'UTF-8'}
                                            )</p></label>
                                    <input type="text" name="em_{$employe['id_employee']|escape:'htmlall':'UTF-8'}"
                                           id="{$employe['lastname']|escape:'htmlall':'UTF-8'}{$employe['firstname']|escape:'htmlall':'UTF-8'}"
                                           value="">

                                </div>
                            {/if}
                        {/foreach}
                    {/if}
                    <div class="col-xs-12">
                        {if isset($pa)}
                            <div class="col-lg-1">
                                <hr>
                                <a class="btn btn-primary" href="{$link->getAdminLink('AdminProspects')}">Annulé
                                </a>
                            </div>
                        {/if}
                        <div class="col-lg-1">
                            <hr>
                            <button type="submit" name="submitEmployes" class="btn btn-success fixed-width-lg">Ajouter
                            </button>
                        </div>
                    </div>
                </div>
        </form>
    </div>
    </div>
{/if}