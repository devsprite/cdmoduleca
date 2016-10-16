<hr>
<table class="table table-hover">
    <thead>
    <tr>
        <th>Nom</th>
        <th class="text-right">CA</th>
        <th class="text-right">CA/Contact Prosp</th>
        <th>Nbr Ventes</th>
        <th>Nbr Prospects</th>
        <th>Taux de Transf.</th>
        <th class="text-right">CA Prosp</th>
        <th class="text-right">CA FID</th>
        <th class="text-right">% CA Prospect</th>
        <th class="text-right">% CA FID</th>
        <th class="text-right">Panier moyen</th>
        {*<th>Nbr Parrainage</th>*}
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
        <td class="text-right">{displayPrice price=$coach['caTotal']}</td>
        <td></td>
        <td class="text-center">{$coach['NbrCommandes']}</td>
        <td></td>
        <td></td>
        <td class="text-right">{displayPrice price=$coach['CaProsp']}</td>
        <td class="text-right">{displayPrice price=$coach['caDejaInscrit']}</td>
        <td class="text-right">{$coach['PourcCaProspect']}</td>
        <td class="text-right">{$coach['PourcCaFID']}</td>
        <td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>
    </tr>
    {/foreach}
    </tbody>
</table>
<hr>
<table class="table table-hover">
    <thead>
    <tr>
        <th class="fixed-width-md">Nom</th>
        <th class="text-right fixed-width-md">CA</th>
        <th class="text-right">Nbr Ventes</th>
        <th class="text-right">Nbr Ventes ABO</th>
        <th class="text-right">Total Ventes ABO</th>
        <th class="text-right">Nbr Ventes FID</th>
        <th class="text-right">Total Ventes FID</th>
        <th class="text-right">Nbr Ventes PROSP</th>
        <th class="text-right">Total Ventes PROSP</th>
        <th class="text-right">Nbr Ventes PAR</th>
        <th class="text-right">Total Ventes PAR</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
        <tr>
            <td>{$coach['lastname']} ({$coach['firstname']})</td>
            <td class="text-right">{displayPrice price=$coach['caTotal']}</td>
            <td class="text-right">{$coach['NbrCommandes']}</td>
            <td class="text-right">{$coach['nbrVenteGrAbo']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrAbo']}</td>
            <td class="text-right">{$coach['nbrVenteGrFid']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrFid']}</td>
            <td class="text-right">{$coach['nbrVenteGrProsp']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrProsp']}</td>
            <td class="text-right">{$coach['nbrVenteGrPar']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrPar']}</td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
    {/foreach}
    </tbody>
</table>