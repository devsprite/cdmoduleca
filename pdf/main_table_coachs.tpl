<table>
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Coach(s)</span>
            <span style="font-size: 10pt">Du {$datepickerFrom|escape|date_format:'%A %e %B %Y'}
                au {$datepickerTo|escape|date_format:'%A %e %B %Y'}</span>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="width: 100%;background-color: #DDDDDD">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%;text-align: center">CA TOTAL</th>
                    <th style="width: 5%;text-align: center">Nbre de ventes TOTAL</th>
                    <th style="width: 5%;text-align: center">Nbre de prospects</th>
                    <th style="width: 5%;text-align: center">Panier Moyen</th>
                    <th style="width: 5%;text-align: center">CA/Contact</th>
                    <th style="width: 5%;text-align: center">% Taux de transfo. prospect</th>
                    <th style="width: 10%;text-align: center">CA prospect</th>
                    <th style="width: 5%;text-align: center">% CA prospect</th>
                    <th style="width: 10%;text-align: center">CA FID</th>
                    <th style="width: 5%;text-align: center">% CA FID</th>
                    <th style="width: 5%">CA Retour</th>
                    <th style="width: 5%">% CA Retour</th>
                    <th style="width: 10%">CA Impayé</th>
                    <th style="width: 5%">% Impayé</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=coach from=$datasEmployees}
                    <tr>
                        <td style="">{$coach['lastname']} ({$coach['firstname']})</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['caTotal']}</td>
                        <td style="text-align: center">{$coach['NbreVentesTotal']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['NbreDeProspects']}</td>
                        <td style="text-align: center">{displayPrice price=$coach['panierMoyen']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['CaContact']}</td>
                        <td style="text-align: center">{$coach['tauxTransfo']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['CaProsp']}</td>
                        <td style="text-align: center">{$coach['PourcCaProspect']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['caDejaInscrit']}</td>
                        <td style="text-align: center">{$coach['PourcCaFID']}</td>
                        <td style="background-color: #DDDDDD"></td>
                        <td style=""></td>
                        <td style="background-color: #DDDDDD"></td>
                        <td style=""></td>
                        <td style="background-color: #DDDDDD"></td>
                        <td style=""></td>
                        <td style="background-color: #DDDDDD"></td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </td>
    </tr>
</table>