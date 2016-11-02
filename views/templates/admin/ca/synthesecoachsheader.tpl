{if $confirmation}
    <p class="alert alert-success">{$confirmation}</p>
{/if}
{if !empty($errors)}
    {foreach item=error from=$errors}
        {if $error}<p class="alert alert-danger">{$error}</p>{/if}
    {/foreach}
{/if}
<div class="row panel">
    <div class="col-lg-1"><a class="btn btn-default export-csv" href="{$LinkFile}&export_csv=1">
            <i class="icon-cloud-upload"></i>CSV</a>
        <a class="btn btn-default export-csv" href="{$LinkFile}&export_pdf=1">
            <i class="icon-cloud-upload"></i>PDF</a></div>
</div>
<div class="panel">
    <div class="panel-heading">
        {l s='Synthèse Coachs' mod='cdmoduleca'}

    </div>
    <div class="row">

