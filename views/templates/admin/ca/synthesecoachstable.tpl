<hr>
<h2>Coach</h2>
<table class="table table-hover">
    <thead>
    <tr style="width: 100%">
        <th style="width: 8%">Nom</th>
        <th style="width: 8%;text-align: right">CA TOTAL</th>
        <th style="width: 7%;text-align: right">Ajustement</th>
        <th style="width: 7%;text-align: right">CA Ajusté</th>
        <th style="width: 5%;text-align: center">Nbre de ventes TOTAL</th>
        <th style="width: 5%;text-align: center">Nbre de prospects</th>
        <th style="width: 5%;text-align: center">Prime Fichier</th>
        <th style="width: 5%;text-align: right">Panier Moyen</th>
        <th style="width: 5%;text-align: center">CA/Contact</th>
        <th style="width: 5%;text-align: right">% Taux de transfo. prospect</th>
        <th style="width: 5%;text-align: right">CA prospect</th>
        <th style="width: 5%;text-align: center">% CA prospect</th>
        <th style="width: 5%;text-align: right">CA FID</th>
        <th style="width: 5%;text-align: center">% CA FID</th>
        <th style="width: 5%;text-align: right">CA Remb/Avoir</th>
        <th style="width: 5%;text-align: center">% CA Remb/Avoir</th>
        <th style="width: 5%;text-align: right">CA Impayé</th>
        <th style="width: 5%;text-align: center">% Impayé</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
    <tr>
        <td>{$coach['lastname']} ({$coach['firstname']|substr:0:1}.)</td>
        <td class="text-right">{displayPrice price=$coach['caTotal']}</td>
        <td class="text-right">{displayPrice price=$coach['ajustement']}</td>
        <td class="text-right">{displayPrice price=$coach['caAjuste']}</td>
        <td class="text-center">{$coach['NbreVentesTotal']}</td>
        <td class="text-center">{$coach['NbreDeProspects']}</td>
        <td class="text-right">{displayPrice price=$coach['primeFichierCoach']}</td>
        <td class="text-right">{displayPrice price=$coach['panierMoyen']}</td>
        <td class="text-center">{$coach['CaContact']}</td>
        <td class="text-right">{$coach['tauxTransfo']}</td>
        <td class="text-right">{displayPrice price=$coach['CaProsp']}</td>
        <td class="text-right">{$coach['PourcCaProspect']}</td>
        <td class="text-right">{displayPrice price=$coach['caDejaInscrit']}</td>
        <td class="text-right">{$coach['PourcCaFID']}</td>
        <td class="text-right">{displayPrice price=$coach['caRembAvoir']}
        <td class="text-right">{$coach['pourCaRembAvoir']}</td>
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
    <tr style="width: 100%">
        <th style="width: 10%">Nom</th>
        <th style="width: 10%;text-align: right">Prime Abo = 10%</th>
        <th style="width: 10%;text-align: center">Nbre d'abos</th>
        <th style="width: 10%;text-align: center">Nbre de désabo</th>
        <th style="width: 10%;text-align: right">% de désabo</th>
        <th style="width: 10%;text-align: right">CA Parrainage</th>
        <th style="width: 40%;text-align: left">% CA Parrainage</th>
    </tr>
    </thead>
    <tbody>
    {foreach item=coach from=$datasEmployees}
        <tr>
            <td>{$coach['lastname']} ({$coach['firstname']})</td>
            <td class="text-right">{displayPrice price=$coach['primeVenteGrAbo']}</td>
            <td class="text-center">{$coach['nbrVenteGrAbo']}</td>
            <td class="text-center">{$coach['nbrVenteGrDesaAbo']}</td>
            <td class="text-right">{$coach['pourcenDesabo']}</td>
            <td class="text-right">{displayPrice price=$coach['totalVenteGrPar']}</td>
            <td class="text-left">{$coach['pourVenteGrPar']}</td>
            <td></td>
        </tr>
    {/foreach}
    </tbody>
</table>