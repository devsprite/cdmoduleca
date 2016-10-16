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
</div>