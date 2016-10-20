<div class="row panel">
    <div class="panel-heading">
        Prospects
    </div>
    <table class="table table-striped">
        <thead>
        <tr>
            <th>Id</th>
            <th>Nom</th>
            <th>Groupe</th>
            <th>Contacté</th>
            <th>Traité</th>
            <th>Injoignable</th>
            <th>Date Inscription</th>
        </tr>
        </thead>
        <tbody>
        {foreach item=prosp from=$prosGr1}
            <tr>
                <td>{$prosp['id_customer']}</td>
                <td>{$prosp['nom']}</td>
                <td>{$prosp['id_group']}</td>
                <td></td>
                <td></td>
                <td></td>
                <td>{$prosp['date_add']}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>
</div>