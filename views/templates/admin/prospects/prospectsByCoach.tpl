{if !empty($prospects_by_coach)}
<table class="table table-hover">
    <thead>
        <tr>
            <th>Du</th>
            <th>Au</th>
            <th>Coach</th>
            <th>Nbre Prospects</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$prospects_by_coach}
        <tr>
            <td>{$coach['date_debut']|date_format:'%d/%m/%Y'}</td>
            <td>{$coach['date_fin']|date_format:'%d/%m/%Y'}</td>
            <td>{$coach['lastname']} ({$coach['firstname']})</td>
            <td>{$coach['nbr_prospect_attribue']}</td>
            <td>
            {if $isAllow}
                <a href="{$linkFilter}&mod_pa&id_pa={$coach['id_prospect_attribue']}">
                    <i class="icon-edit text-success"></i>
                </a>
                <a href="{$linkFilter}&del_pa&id_pa={$coach['id_prospect_attribue']}"
                   onclick="if(confirm('Etes-vous sur de vouloir supprimer cette ligne ?')) {} else return false">
                    <i class="icon-cut text-danger"></i>
                </a>
            {/if}
            </td>
        </tr>
    {/foreach}
    </tbody>
</table>
{/if}