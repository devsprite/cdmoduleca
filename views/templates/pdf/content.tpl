<table style="width: 100%; font-size: 10pt; line-height: 0.8pt;">
    <tr>
        <td>
            <table style="width: 100%;border-bottom:3px solid #448B01;">
                <tr style="line-height: 2pt;">
                    <td style="width: 30%"><span style="font-size: 24pt;color: #448B01;">L&Sens </span></td>
                    <td style="width: 70%; text-align: right"><span
                                style="font-size: 12pt;">{$smarty.now|date_format:'%A %e %B %Y à %H:%M'|capitalize}</span></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%;border-bottom:1px solid #448B01;">
                <tr>
                    <td>
                        <table style="width: 100%;border-bottom:1px solid #448B01;">
                            <tr style="line-height: 1.5pt;">
                                <td style="width: 100%;">
                                    <span>Du {$datepickerFrom|escape|date_format:'%A %e %B %Y'}
                                        au {$datepickerTo|escape|date_format:'%A %e %B %Y'}</span>
                                </td>
                            </tr>
                            <tr style="line-height: 1.5pt;">
                                <td style="width: 100%;">
                                    <span>Code action : {$filterCodeAction['name']}</span>
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr style="line-height: 1.5pt;">
                    <td>
                        <span>Tous les coachs : {displayPrice price=$caCoachsTotal}</span>
                    </td>
                </tr>
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>Coach {$coach->lastname} : {displayPrice price=$caCoach}</span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                {/if}
                <tr style="line-height: 1.5pt;">
                    <td>
                        <span>CA Déduit Total (- {$caDeduitJours} j.) : {displayPrice price=$caDeduitTotal}</span>
                    </td>
                </tr>
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA Déduit {$coach->lastname} (- {$caDeduitJours}
                                j.) : {displayPrice price=$caDeduitCoach}</span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                {/if}
                <tr style="line-height: 1.5pt;">
                    <td>
                        <span>CA FID Total : {displayPrice price=$caFidTotal}</span>
                    </td>
                </tr>
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA FID {$coach->lastname} : {displayPrice price=$caFidCoach}</span>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                    </tr>
                {/if}
                <tr style="line-height: 1.5pt;">
                    <td>
                        <span>CA Prospects Total : {displayPrice price=($caCoachsTotal - $caFidTotal)}</span>
                    </td>
                </tr>
                {if $coach->lastname}
                    <tr style="line-height: 1.5pt;">
                        <td>
                            <span>CA Prospect {$coach->lastname}
                                 : {displayPrice price=($caTotalCoach - $caFidCoach)}</span>
                        </td>
                    </tr>
                {/if}
                    <tr>
                        <td></td>
                    </tr>
            </table>
        </td>
    </tr>
</table>

<table style="100%">
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Ajout manuel</span>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%; font-size: 8pt;;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="background-color: #AAAAAA">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%">Date</th>
                    <th style="width: 10%; text-align: right">Somme</th>
                    <th style="width: 70%">Commentaire</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=ajoutSomme from=$ajoutSommes}
                    <tr>
                        <td>{$ajoutSomme['lastname']}</td>
                        <td>{$ajoutSomme['date_add']|date_format:'%d/%m/%Y'}</td>
                        <td style="text-align: right">{displayPrice price=$ajoutSomme['somme']}</td>
                        <td>{$ajoutSomme['commentaire']|wordwrap:50:"\n":true}</td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
</table>

<table style="100%">
    <tr>
        <td>
            <span style="font-size: 18pt;color: #448B01;">Objectif Coach</span>
        </td>
    </tr>
    <tr>
        <td></td>
    </tr>
    <tr>
        <td>
            <table style="width: 100%; font-size: 8pt;border-bottom:1px solid #448B01;">
                <thead>
                <tr style="background-color: #AAAAAA">
                    <th style="width: 10%">Nom</th>
                    <th style="width: 10%">Date début</th>
                    <th style="width: 10%">Date fin</th>
                    <th style="width: 10%;text-align: right">Objectif</th>
                    <th style="width: 10%;text-align: right">CA</th>
                    <th style="width: 10%;text-align: right">% Objectif</th>
                    <th style="width: 40%">Commentaire</th>
                </tr>
                </thead>
                <tbody>
                {foreach item=objectif from=$objectifCoachs}
                    <tr class="{$objectif['class']}">
                        <td>{$objectif['lastname']}</td>
                        <td>{$objectif['date_start']|date_format:'%d/%m/%Y'}</td>
                        <td>{$objectif['date_end']|date_format:'%d/%m/%Y'}</td>
                        <td style="text-align: right">{displayPrice price=$objectif['somme']}</td>
                        <td style="text-align: right">{displayPrice price=$objectif['caCoach']}</td>
                        <td style="text-align: right">{$objectif['pourcentDeObjectif']} %</td>
                        <td>{$objectif['commentaire']|wordwrap:50:"\n":true}</td>
                    </tr>
                {/foreach}
            </table>
        </td>
    </tr>
</table>



