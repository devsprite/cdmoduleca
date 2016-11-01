{if $confirmation}
    <p class="alert alert-success">{$confirmation}</p>
{/if}
{if !empty($errors)}
    {foreach item=error from=$errors}
        {if $error}<p class="alert alert-danger">{$error}</p>{/if}
    {/foreach}
{/if}
<div class="panel">
    <div class="panel-heading">
        {l s='Synthèse Coachs' mod='cdmoduleca'}

    </div>
    <div class="row">
