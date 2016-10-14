<div class="row synthesecontent">
    <div class="row group">
        <div class="col-xs-3">CA {$filterCodeAction['name']} : <span class="pull-right">{displayPrice price=$caCoachsTotal}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA {$filterCodeAction['name']} {$coach->lastname} :
            <span class="pull-right">{displayPrice price=$caCoach}</span></div>
    </div>
    <div class="row group">
        <div class="col-xs-3">CA FID Total : <span class="pull-right">{displayPrice price=$caFidTotal}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA FID {$coach->lastname} :
            <span class="pull-right">{displayPrice price=$caFidCoach}</span></div>
    </div>
    <div class="row group">
        <div class="col-xs-3">CA Prospect Total :
            <span class="pull-right">{displayPrice price=($caTotal - $caFidTotal)}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Prospect Coach {$coach->lastname} :
            <span class="pull-right">{displayPrice price=($caTotalCoach - $caFidCoach)}</span></div>
    </div>
</div>
