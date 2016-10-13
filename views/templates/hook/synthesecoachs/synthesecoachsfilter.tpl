<div class="col-xs-12">
    <form action="{$linkFilter}" method="post">
        <div class="form-group">
            <label class="control-label col-lg-1" for="filterCoach">
                Coach :
            </label>
            <div class="col-lg-2 ">
                <select name="filterCoach" class="fixed-width-xl" id="filterCoach" onchange="this.form.submit();">
                {foreach item=coach from=$coachs}
                    <option value="{$coach['id_employee']}" {if $filterActif == $coach['id_employee']}
                        selected="selected"{/if}>{$coach['lastname']} - ({$coach['firstname']})</option>
                {/foreach}
                </select>
                <input type="hidden" name="submitFilterCoachs" value="1" />
            </div>
        </div>
    </form>
</div>