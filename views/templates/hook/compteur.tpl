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
 * @copyright 2007-2015 PrestaShop SA / 2011-2015 Dominique
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 * International Registred Trademark & Property of PrestaShop SA
 *}

{if isset($objectif['somme']) || isset($objectif['appels'])}
<div class="compteur">
    <table>
        <tbody class="compteur_{if isset($objectif['class'])}{$objectif['class']|escape:'htmlall':'UTF-8'}{/if}">
            <tr>
                <td><span id="compteurAppels"></span> <i id="iconCompteurAppels" class="icon-phone"></i></td>
                <td>{if !empty($objectif['projectif'])}P {displayPrice price=$objectif['projectif']}{/if}</td>
            </tr>
            <tr>
                <td colspan="2">{if !empty($objectif['caCoach'])}{displayPrice price=$objectif['caCoach']}{/if}
                    {if !empty($objectif['somme'])} / {displayPrice price=$objectif['somme']}{/if}</td>
            </tr>
        </tbody>
    </table>
</div>
{/if}