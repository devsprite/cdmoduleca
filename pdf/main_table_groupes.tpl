<table>
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Groupe(s)</span>
            <span style="font-size: 10pt">Du {$datepickerFrom|escape|date_format:'%A %e %B %Y'}
                au {$datepickerTo|escape|date_format:'%A %e %B %Y'}</span>
        </td>
    </tr>
    <tr>
        <td>
        </td>
    </tr>
    <tr>
        <td>
            <table style="100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="width: 100%;background-color: #DDDDDD">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 15%;text-align: center; background-color: #DDDDDD">Prime Abo = 10%</th>
                    <th style="width: 15%;text-align: center;">Nbre d'abos</th>
                    <th style="width: 15%;text-align: center; background-color: #DDDDDD">Nbre de désabo</th>
                    <th style="width: 15%;text-align: center;">% de désabo</th>
                    <th style="width: 15%;text-align: center; background-color: #DDDDDD">CA Parrainage</th>
                    <th style="width: 15%;text-align: center;">% CA Parrainage</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=coach from=$datasEmployees}
                    <tr>
                        <td>{$coach['lastname']} ({$coach['firstname']})</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['primeVenteGrAbo']}</td>
                        <td style="text-align: center">{$coach['nbrVenteGrAbo']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{$coach['nbrVenteGrDesaAbo']}</td>
                        <td style="text-align: center">{$coach['pourcenDesabo']}</td>
                        <td style="text-align: center; background-color: #DDDDDD">{displayPrice price=$coach['totalVenteGrPar']}</td>
                        <td style="text-align: center"></td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </td>
    </tr>
</table>