<hr>
<table class="table table-hover">
    <thead>
    <tr>
        <th>Nom</th>
        <th>CA</th>
        <th>CA/Contact Prosp</th>
        <th>Nbr Ventes</th>
        <th>Nbr Prospects</th>
        <th>Taux de Transf.</th>
        <th>CA Prosp</th>
        <th>CA FID</th>
        <th>% CA Prospect</th>
        <th>% CA FID</th>
        {*<th>Nbr Ventes FID</th>*}
        {*<th>Nbr Parrainage</th>*}
        {*<th>Panier moyen</th>*}
        {*<th>Prime Abo €</th>*}
        {*<th>Nbr Abos</th>*}
        {*<th>Nbr Désabo</th>*}
        {*<th>% Désabo</th>*}
        {*<th>Nbr retours</th>*}
        {*<th>CA retour</th>*}
        {*<th>% Retour CA</th>*}
        {*<th>CA Impayés</th>*}
        {*<th>Objectif CA €</th>*}
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
    <tr>
        <td>{$coach['lastname']} ({$coach['firstname']})</td>
        <td>{displayPrice price=$coach['caTotal']}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>
        <td>{displayPrice price=$coach['CaProsp']}</td>
        <td>{displayPrice price=$coach['caDejaInscrit']}</td>
        <td>{$coach['PourcCaProspect']}</td>
        <td>{$coach['PourcCaFID']}</td>
    </tr>
    {/foreach}
    </tbody>
</table>