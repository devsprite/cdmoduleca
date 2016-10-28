{if isset($listProspects)}
<div class="row panel">
    <div class="panel-heading">
        Prospects
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Coach</th>
            <th>Traité</th>
            <th>Injoignable</th>
            <th>Date Prospection</th>
        </tr>
        </thead>
        <tbody>
        {foreach item=prosp from=$listProspects}
            <tr>
                <td>{$prosp['id_customer']}</td>
                <td>{$prosp['lastname']|upper} {$prosp['firstname']|lower|capitalize}</td>
                <td>{$prosp['coach']}</td>
                <td>{$prosp['traite']}</td>
                <td>{$prosp['injoignable']}</td>
                <td>{$prosp['date_debut']|date_format:'%d/%m/%Y'}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{/if}