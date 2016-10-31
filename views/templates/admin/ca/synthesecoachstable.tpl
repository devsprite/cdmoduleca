<hr>
<h2>Coach</h2>
<table class="table table-hover">
    <thead>
    <tr>
        <th class="fixed-width-md">Nom</th>
        <th class="fixed-width-md text-right">CA TOTAL</th>
        <th>Nbre de ventes TOTAL</th>
        <th>Nbre de prospects</th>
        <th>Panier Moyen</th>
        <th>CA/Contact</th>
        <th>% Taux de transfo. prospect</th>
        <th>CA prospect</th>
        <th>% CA prospect</th>
        <th>CA FID</th>
        <th>% CA FID</th>
        <th>CA Retour</th>
        <th>% CA Retour</th>
        <th>CA Impayé</th>
        <th>% Impayé</th>
        {*<th class="text-right">CA</th>*}
        {*<th class="text-right">CA/Contact Prosp</th>*}
        {*<th>Nbr Propects</th>*}
        {*<th>Taux de Transf.</th>*}
        {*<th>Nbr Ventes Prospects</th>*}
        {*<th class="text-right">CA Prosp</th>*}
        {*<th class="text-right">% CA Prospect</th>*}
        {*<th class="text-right">CA FID</th>*}
        {*<th class="text-right">Nbr Vente FID</th>*}
        {*<th class="text-right">% CA FID</th>*}
        {*<th class="text-right">Panier moyen</th>*}
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
    <tr>
        <td>{$coach['lastname']} ({$coach['firstname']})</td>
        <td class="text-right">{displayPrice price=$coach['caTotal']}</td>
        <td class="text-center">{$coach['NbreVentesTotal']}</td>
        <td class="text-center">{$coach['NbreDeProspects']}</td>
        <td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>
        <td class="text-center">{$coach['CaContact']}</td>
        <td class="text-right">{$coach['tauxTransfo']}</td>
        <td class="text-right">{displayPrice price=$coach['CaProsp']}</td>
        <td class="text-right">{$coach['PourcCaProspect']}</td>
        <td class="text-right">{displayPrice price=$coach['caDejaInscrit']}</td>
        <td class="text-right">{$coach['PourcCaFID']}</td>
        <td></td>
        <td></td>
        <td></td>
        <td></td>

        {*<td>{$coach['lastname']} ({$coach['firstname']})</td>*}
        {*<td class="text-right">{displayPrice price=$coach['caTotal']}</td>*}
        <td></td>
        <td></td>
        <td></td>
        {*<td class="text-center">{$coach['NbrCommandes']}</td>*}
        {*<td class="text-right">{displayPrice price=$coach['CaProsp']}</td>*}
        {*<td class="text-right">{$coach['PourcCaProspect']}</td>*}
        {*<td class="text-right">{displayPrice price=$coach['caDejaInscrit']}</td>*}
        {*<td class="text-center">{$coach['nbrVenteFid']}</td>*}
        {*<td class="text-right">{$coach['PourcCaFID']}</td>*}
        {*<td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>*}
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
        <th class="fixed-width-md text-right">Prime Abo = 10%</th>
        <th class="text-center">Nbre d'abos</th>
        <th class="text-center">Nbre de désabo</th>
        <th class="text-right">% de désabo</th>
        <th class="text-right">CA Parrainage</th>
        <th>% CA Parrainage</th>
        {*<th class="text-right fixed-width-md">CA</th>*}
        {*<th class="text-right">Nbr Ventes</th>*}
        {*<th class="text-right">Nbr Ventes ABO</th>*}
        {*<th class="text-right">Total Ventes ABO</th>*}
        {*<th class="text-right">Désabonnement</th>*}
        {*<th class="text-right">Nbr Ventes FID</th>*}
        {*<th class="text-right">Total Ventes FID</th>*}
        {*<th class="text-right">Nbr Ventes PROSP</th>*}
        {*<th class="text-right">Total Ventes PROSP</th>*}
        {*<th class="text-right">Nbr Ventes PAR</th>*}
        {*<th class="text-right">Total Ventes PAR</th>*}
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
        <tr>
            <td>{$coach['lastname']} ({$coach['firstname']})</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrAbo']}</td>
            <td class="text-center">{$coach['nbrVenteGrAbo']}</td>
            <td class="text-center">{$coach['nbrVenteGrDesaAbo']}</td>
            <td class="text-right">{$coach['pourcenDesabo']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrPar']}</td>
            <td></td>
            {*<td class="text-right">{displayPrice price=$coach['caTotal']}</td>*}
            {*<td class="text-right">{$coach['NbrCommandes']}</td>*}
            {*<td class="text-right">{$coach['nbrVenteGrFid']}</td>*}
            {*<td class="text-right">{displayPrice price=$coach['totalVenteGrFid']}</td>*}
            {*<td class="text-right">{$coach['nbrVenteGrProsp']}</td>*}
            {*<td class="text-right">{displayPrice price=$coach['totalVenteGrProsp']}</td>*}
            {*<td class="text-right">{$coach['nbrVenteGrPar']}</td>*}

        </tr>
    {/foreach}
    </tbody>
</table>