<hr>
<div class="row synthesecontent">
    <div class="col-xs-4">
        <div class="row group">
            <div class="col-xs-12">CA Code Action {$filterCodeAction['name']}
                <span class="pull-right">{displayPrice price=$caCoachsTotal}</span></div>
        </div>
        <div class="row">
            <div class="col-xs-12">CA Code Action {$filterCodeAction['name']} {$coach->lastname}
                <span class="pull-right">{displayPrice price=$caCoach}</span></div>
        </div>
        <div class="row group">
            <div class="col-xs-12">CA Déduit Total (- {$caDeduitJours} j.)
                <span class="pull-right">{displayPrice price=$caDeduitTotal}</span></div>
        </div>
        <div class="row">
            <div class="col-xs-12">CA Déduit {$coach->lastname} (- {$caDeduitJours} j.)
                <span class="pull-right">{displayPrice price=$caDeduitCoach}</span></div>
        </div>
        <div class="row group">
            <div class="col-xs-12">CA FID (Prospects déjà inscrit)
                <span class="pull-right">{displayPrice price=$caFidTotal}</span></div>
        </div>
        <div class="row">
            <div class="col-xs-12">CA FID (Prospects déjà inscrit {$coach->lastname})
                <span class="pull-right">{displayPrice price=$caFidCoach}</span></div>
        </div>
        <div class="row group">
            <div class="col-xs-12">CA Prospect Total
                <span class="pull-right">{displayPrice price=($caTotal - $caFidTotal)}</span></div>
        </div>
        <div class="row">
            <div class="col-xs-12">CA Prospect {$coach->lastname}
                <span class="pull-right">{displayPrice price=($caTotalCoach - $caFidCoach)}</span></div>
        </div>
    </div>
    <div class="col-xs-push-1 col-xs-6">
        <h2>Ajout manuel</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th class="fixed-width-md">Nom</th>
                <th class="fixed-width-md">Date</th>
                <th class="fixed-width-md">Somme</th>
                <th class="fixed-width-md">Commentaire</th>
            </tr>
            </thead>
            <tbody>
            {foreach item=ajoutSomme from=$ajoutSommes}
                <tr>
                    <td>{$ajoutSomme['lastname']}</td>
                    <td>{$ajoutSomme['date_add']|date_format:'%D'}</td>
                    <td>{displayPrice price=$ajoutSomme['somme']}</td>
                    <td>{$ajoutSomme['commentaire']}</td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
