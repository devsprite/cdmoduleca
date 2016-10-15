<hr>
<div class="row synthesecontent">
    <div class="row group">
        <div class="col-xs-3">CA Code Action  {$filterCodeAction['name']}
            <span class="pull-right">{displayPrice price=$caCoachsTotal}</span></div>
        <div class="col-xs-3">Nombre de commandes totale
            <span class="pull-right">{$caTotalNbrCommandes}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Code Action {$filterCodeAction['name']} {$coach->lastname}
            <span class="pull-right">{displayPrice price=$caCoach}</span></div>
        <div class="col-xs-3">Nombre de commandes totale {$coach->lastname}
            <span class="pull-right">{$caCoachNbrCommandes}</span></div>
    </div>
    <div class="row group">
        <div class="col-xs-3">CA Déduit Total (- {$caDeduitJours} j.)
            <span class="pull-right">{displayPrice price=$caDeduitTotal}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Déduit {$coach->lastname} (- {$caDeduitJours} j.)
            <span class="pull-right">{displayPrice price=$caDeduitCoach}</span></div>
    </div>
    <div class="row group">
        <div class="col-xs-3">CA Prospects déjà inscrit
            <span class="pull-right">{displayPrice price=$caFidTotal}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Prospects déjà inscrit {$coach->lastname}
            <span class="pull-right">{displayPrice price=$caFidCoach}</span></div>
    </div>
    <div class="row group">
        <div class="col-xs-3">CA Prospect Total
            <span class="pull-right">{displayPrice price=($caTotal - $caFidTotal)}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Prospect {$coach->lastname}
            <span class="pull-right">{displayPrice price=($caTotalCoach - $caFidCoach)}</span></div>
    </div>
</div>
