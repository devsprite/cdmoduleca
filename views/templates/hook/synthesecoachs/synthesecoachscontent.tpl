<div class="row synthesecontent">
    <div class="row">
        <div class="col-xs-3">CA Total : <span class="pull-right">{displayPrice price=$caCoachsTotal}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA {$coach->lastname} :
            <span class="pull-right">{displayPrice price=$caCoach}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA FID Total : <span class="pull-right">{displayPrice price=$caFidTotal}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA FID {$coach->lastname} :
            <span class="pull-right">{displayPrice price=$caFidCoach}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Prospect Total :
            <span class="pull-right">{displayPrice price=($caCoachsTotal - $caFidTotal)}</span></div>
    </div>
    <div class="row">
        <div class="col-xs-3">CA Prospect Coach {$coach->lastname} :
            <span class="pull-right">{displayPrice price=($caCoach - $caFidCoach)}</span></div>
    </div>
</div>
