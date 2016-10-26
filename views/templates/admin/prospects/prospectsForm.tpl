{if $confirmation}{$confirmation}{/if}
{if $errors}{$errors}{/if}


<div class="row panel">
    <div class="panel-heading">
        Configuration
    </div>
    <form action="{$linkForm}" method="post">
        <div class="col-xs-2">
            <label for="nbrProspects">Nombre de prospects à distribuer
                <input type="text" name="nbr_prospect_distribuer" id="nbrProspects">
            </label>
        </div>
        <div class="col-xs-1">
            <div>
                <label for="employeActif">Employés actif</label>
            </div>
            <div>
                <input type="checkbox" name="employeActif" id="employeActif" onchange="this.form.submit();"
                        {if $employeActif}{$employeActif}{/if}>
                <input type="hidden" name="submitEmployeActif" value="1"/>
            </div>
        </div>
        <div class="col-xs-2">
            {if $nbr_prospects <= 0}
                <p>Pas de nouveau prospect disponible.</p>
                <p>L'attribution se ferra sur des prospects plus ancien.</p>
            {else}
                <p>Il y a {$nbr_prospects} nouveaux prospect(s)</p>
            {/if}
        </div>
    </form>
</div>
<div class="row panel">
    <div class="panel-heading">
        Employés
    </div>
    <form action="{$linkForm}" method="post" name="employes">
        <div class="row panel">
            <div class="col-xs-2">
                <label  class="control-label" for="p_date_start">Date début
                    <input type="text" name="p_date_start" id="p_date_start"
                           value="{if isset($smarty.post.p_date_start)
                           }{$smarty.post.p_date_start|date_format:'%Y-%m-%d'}{elseif
                           isset($pa)}{$pa->date_debut|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}"
                           class="datepicker form-control"></label>
            </div>
            <div class="col-xs-2">
                <label  class="control-label" for="p_date_end">Date fin
                    <input type="text" name="p_date_end" id="p_date_end"
                           value="{if isset($smarty.post.p_date_end)
                           }{$smarty.post.p_date_end|date_format:'%Y-%m-%d'}{elseif
                           isset($pa)}{$pa->date_fin|date_format:'%Y-%m-%d'}{else}{$smarty.now|date_format:'%Y-%m-%d'}{/if}"
                           class="datepicker form-control"></label>
            </div>
            {if isset($coachs)}
                <div class="col-lg-2 ">
                    <label class="control-label">
                        Coach :
                        <select name="filterCoach" class="fixed-width-xl" id="filterCoach">
                            {foreach item=coach from=$coachs}
                                <option value="{$coach['id_employee']}" {if $pa->id_employee == $coach['id_employee']}
                                    selected="selected"{/if}>{$coach['lastname']} - ({$coach['firstname']})
                                </option>
                            {/foreach}
                        </select>
                    </label>
                </div>
                <div class="col-xs-2">
                    <label  class="control-label" for="">Nbre Prosprects Attribués
                        <input type="text" name="" id=""
                               value="{$pa->nbr_prospect_attribue}"
                               class="form-control" disabled></label>
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
                });
                {/literal}
            </script>
            <div class="col-xs-12">
                <button type="submit" name="submitEmployes" class="btn btn-success">Valider</button>
            </div>
        </div>
        <div class="col-xs-12">
            {foreach item=employe from=$employes}
                <div class="col-md-2 col-xs-3">
                    <label  class="control-label" for="{$employe['lastname']}{$employe['firstname']}">{$employe['lastname']}
                        ({$employe['firstname']}
                        ){if !empty({$employe['total_prospect']})} - {$employe['total_prospect']} Prospects{/if}
                        <input type="text" name="em_{$employe['id_employee']}"
                               id="{$employe['lastname']}{$employe['firstname']}" value="">
                    </label>
                </div>
            {/foreach}
    </form>
</div>
</div>