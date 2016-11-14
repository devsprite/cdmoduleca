{**
 * 2007-2016 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Dominique <dominique@chez-dominique.fr>
 * @copyright 2007-2016 PrestaShop SA / 2011-2016 Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 *}

{if $confirmation}
    <p class="alert alert-success">{$confirmation|escape:'htmlall':'UTF-8'}</p>
{/if}
<div class="row panel">
    <div class="col-lg-2">
        <a class="btn btn-default export-csv" href="{$LinkFile}&export_csv=1">
            <i class="icon-cloud-upload"></i> CSV</a>
        <a class="btn btn-default export-csv" href="{$LinkFile}&export_pdf=1">
            <i class="icon-cloud-upload"></i> PDF</a>
    </div>

    {if isset($allow)}
        <form enctype="multipart/form-data" action="{$LinkFile}" method="post">
            <div class="col-lg-2">
            <input type="hidden" name="MAX_FILE_SIZE" value="3000000" />
            <input type="file" name="uploadFile" class="btn btn-default">
            </div>
            <div class="col-lg-1">
                <button class="btn btn-default" type="submit" name="submitUpload">
                    <i class="icon-cloud-upload"></i> Envoyer</button>
            </div>
        </form>
    {/if}
</div>
<div class="panel">
    <div class="panel-heading">
        {l s='Synthèse Coachs' mod='cdmoduleca'}

    </div>
    <div class="row">

