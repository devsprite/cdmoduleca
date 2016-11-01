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
        <td></td>
        <td></td>
        <td></td>
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
        </tr>
    {/foreach}
    </tbody>
</table>