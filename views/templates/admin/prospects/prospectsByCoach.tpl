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

{if !empty($prospects_by_coach)}
    <div class="panel">
        <div class="panel-heading">
            Prospects attribués
        </div>
        <table class="table table-hover" id="simpleTable">
            <thead>
            <tr>
                <th class="handler" data-sort="string">Du</th>
                <th class="handler" data-sort="string">Au</th>
                <th class="handler" data-sort="string">Coach</th>
                <th class="handler" data-sort="int" >Nbre Prospects</th>
                <th></th>
            </tr>
            </thead>
            <tbody>
            {foreach item=coach from=$prospects_by_coach}
                <tr>
                    <td>{$coach['date_debut']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                    <td>{$coach['date_fin']|date_format:'%d/%m/%Y'|escape:'htmlall':'UTF-8'}</td>
                    <td>{$coach['lastname']|escape:'htmlall':'UTF-8'} ({$coach['firstname']|escape:'htmlall':'UTF-8'})</td>
                    <td>{$coach['nbr_prospect_attribue']|escape:'htmlall':'UTF-8'}</td>
                    <td>
                        <a href="{$linkFilter}&view_pa&id_pa={$coach['id_prospect_attribue']|intval}">
                            <i class="icon-eye text-info"></i>
                        </a>
                        {if $isAllow}
                            <a href="{$linkFilter}&mod_pa&id_pa={$coach['id_prospect_attribue']|intval}">
                                <i class="icon-edit text-success"></i>
                            </a>
                            <a href="{$linkFilter}&del_pa&id_pa={$coach['id_prospect_attribue']|intval}"
                               onclick="if(confirm('Etes-vous sur de vouloir supprimer cette ligne ?')) {} else return false">
                                <i class="icon-cut text-danger"></i>
                            </a>
                        {/if}
                    </td>
                </tr>
            {/foreach}
            </tbody>
        </table>
    </div>
{/if}