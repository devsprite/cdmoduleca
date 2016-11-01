<hr>
<div class="row synthesecontent">
    <div class="col-xs-3">
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
    <div class="col-xs-push-1 col-xs-8">
        <h2>Ajustements</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Date</th>
                <th><span class="pull-right">Somme</span></th>
                <th>Commentaire</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach item=ajoutSomme from=$ajoutSommes}
                <tr>
                    <td>{$ajoutSomme['lastname']}</td>
                    <td>{$ajoutSomme['date_add']|date_format:'%d/%m/%Y'}</td>
                    <td><span class="pull-right">{displayPrice price=$ajoutSomme['somme']}</span></td>
                    <td>{$ajoutSomme['commentaire']|wordwrap:50:"\n":true}</td>
                    <td>
                        {if isset($coachs)}
                            <a href="{$linkFilter}&mod_as&id_as={$ajoutSomme['id_ajout_somme']}">
                                <i class="icon-edit text-success"></i>
                            </a>
                            <a href="{$linkFilter}&del_as&id_as={$ajoutSomme['id_ajout_somme']}"
                               onclick="if(confirm('Etes-vous sur de vouloir supprimer ce cet ajout ?')) {} else return false">
                                <i class="icon-cut text-danger"></i>
                            </a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
        <h2>Objectif Coach</h2>
        <table class="table table-hover">
            <thead>
            <tr>
                <th>Nom</th>
                <th>Date début</th>
                <th>Date fin</th>
                <th><span class="pull-right">Objectif</span></th>
                <th><span class="pull-right">CA</span></th>
                <th><span class="pull-right">% Objectif</span></th>
                <th>Commentaire</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach item=objectif from=$objectifCoachs}
                <tr class="{$objectif['class']}">
                    <td>{$objectif['lastname']}</td>
                    <td>{$objectif['date_start']|date_format:'%d/%m/%Y'}</td>
                    <td>{$objectif['date_end']|date_format:'%d/%m/%Y'}</td>
                    <td><span class="pull-right">{displayPrice price=$objectif['somme']}</span></td>
                    <td><span class="pull-right">{displayPrice price=$objectif['caCoach']}</span></td>
                    <td><span class="pull-right">{$objectif['pourcentDeObjectif']} %</span></td>
                    <td>{$objectif['commentaire']|wordwrap:50:"\n":true}</td>
                    <td>
                        {if isset($coachs)}
                            <a href="{$linkFilter}&mod_oc&id_oc={$objectif['id_objectif_coach']}">
                                <i class="icon-edit text-success"></i>
                            </a>
                            <a href="{$linkFilter}&del_oc&id_oc={$objectif['id_objectif_coach']}"
                               onclick="if(confirm('Etes-vous sur de vouloir supprimer cet objectif ?')) {} else return false">
                                <i class="icon-cut text-danger"></i>
                            </a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
</div>
