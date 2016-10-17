<hr>
<h2>Coach</h2>
<table class="table table-hover">
    <thead>
    <tr>
        <th>Nom</th>
        <th class="text-right">CA</th>
        <th class="text-right">CA/Contact Prosp</th>
        <th>Nbr Propects</th>
        <th>Taux de Transf.</th>
        <th>Nbr Ventes Prospects</th>
        <th class="text-right">CA Prosp</th>
        <th class="text-right">% CA Prospect</th>
        <th class="text-right">CA FID</th>
        <th class="text-right">Nbr Vente FID</th>
        <th class="text-right">% CA FID</th>
        <th class="text-right">Panier moyen</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
    <tr>
        <td>{$coach['lastname']} ({$coach['firstname']})</td>
        <td class="text-right">{displayPrice price=$coach['caTotal']}</td>
        <td></td>
        <td></td>
        <td></td>
        <td class="text-center">{$coach['NbrCommandes']}</td>
        <td class="text-right">{displayPrice price=$coach['CaProsp']}</td>
        <td class="text-right">{$coach['PourcCaProspect']}</td>
        <td class="text-right">{displayPrice price=$coach['caDejaInscrit']}</td>
        <td class="text-center">{$coach['nbrVenteFid']}</td>
        <td class="text-right">{$coach['PourcCaFID']}</td>
        <td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>
    </tr>
    {/foreach}
    </tbody>
</table>
<hr>
<h2>Groupe </h2>
<table class="table table-hover">
    <thead>
    <tr>
        <th class="fixed-width-md">Nom</th>
        {*<th class="text-right fixed-width-md">CA</th>*}
        {*<th class="text-right">Nbr Ventes</th>*}
        <th class="text-right">Nbr Ventes ABO</th>
        <th class="text-right">Total Ventes ABO</th>
        <th class="text-right">Désabonnement</th>
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
            {*<td class="text-right">{displayPrice price=$coach['caTotal']}</td>*}
            {*<td class="text-right">{$coach['NbrCommandes']}</td>*}
            <td class="text-right">{$coach['nbrVenteGrAbo']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrAbo']}</td>
            <td class="text-right">{$coach['nbrVenteGrDesaAbo']}</td>
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