<div class="row">
    <div class="col-xs-12">
        {if isset($coachs)}
            <form action="{$linkFilter}" method="post">
                <div class="form-group">
                    <label class="control-label col-lg-1" for="filterCoach">
                        Coach :
                    </label>
                    <div class="col-lg-3 ">
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
                <label class="control-label col-lg-1" for="filterCodeAction">
                    Code Action :
                </label>
                <div class="col-lg-2 ">
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
    </div>
</div>