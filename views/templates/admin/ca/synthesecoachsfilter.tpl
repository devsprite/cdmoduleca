<div class="row">
    <div class="col-xs-12">
        {if isset($coachs)}
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
                                       onchange="this.form.submit();" {if $filterCoachActif}checked{/if}>
                                Actif</label>
                        </div>
                    </div>
                </div>
            </form>
        {/if}
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
    </div>
    <hr>
    {if isset($coachs) && $filterActif != 0}
        <div class="col-xs-12 hidden-print">
            <br>
            <h3>Ajout manuel</h3>
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
                            <input type="text" id="as_somme" name="as_somme" value="{if isset($smarty.post.as_somme)
                            }{$smarty.post.as_somme}{/if}">
                            <span class="input-group-addon">€</span>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <label for="as_commentaire">Commentaire</label>
                        <input type="text" id="as_commentaire" value="{if isset($smarty.post.as_commentaire)
                        }{$smarty.post.as_commentaire}{/if}" name="as_commentaire">
                    </div>
                    <div class="col-lg-1">
                        <label for="">Enregistrer</label>
                        <button class="btn btn-success" type="submit" id="as_submit" name="as_submit">Enregistrer
                        </button>
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
            <h3>Objectif coach</h3>
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
                            <input type="text" id="oc_somme" name="oc_somme" value="{if isset($smarty.post.oc_somme)
                            }{$smarty.post.oc_somme}{/if}">
                            <span class="input-group-addon">€</span>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <label for="oc_commentaire">Commentaire</label>
                        <input type="text" id="oc_commentaire" value="{if isset($smarty.post.oc_commentaire)
                        }{$smarty.post.oc_commentaire}{/if}" name="oc_commentaire">
                    </div>
                    <div class="col-lg-1">
                        <label for="">Enregistrer</label>
                        <button class="btn btn-success" type="submit" id="oc_submit" name="oc_submit">Enregistrer
                        </button>
                        <input type="hidden" id="oc_id_employee" name="oc_id_employee"
                               value="{if isset($smarty.post.oc_id_employee)
                               }{$smarty.post.oc_id_employee}{else}{$filterActif}{/if}">
                        <input type="hidden" id="oc_id" name="oc_id"
                               value="{if isset($smarty.post.oc_id)
                               }{$smarty.post.oc_id}{/if}">
                    </div>
                </div>
            </form>
        </div>
    {/if}
</div>