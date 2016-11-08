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

<div id="calendar" class="panel">
	<form action="{$action|escape:'htmlall':'UTF-8'}" method="post" id="calendar_form" name="calendar_form" class="form-inline">
		<div class="row">
			<div class="col-lg-5">
				<div class="btn-group">
					<button type="submit" name="submitDateDay" class="btn btn-default submitDateDay">{$translations.Day|escape:'htmlall':'UTF-8'}</button>
					<button type="submit" name="submitDateMonth" class="btn btn-default submitDateMonth">{$translations.Month|escape:'htmlall':'UTF-8'}</button>
					<button type="submit" name="submitDateYear" class="btn btn-default submitDateYear">{$translations.Year|escape:'htmlall':'UTF-8'}</button>
					<button type="submit" name="submitDateDayPrev" class="btn btn-default submitDateDayPrev">{$translations.Day|escape:'htmlall':'UTF-8'}-1</button>
					<button type="submit" name="submitDateMonthPrev" class="btn btn-default submitDateMonthPrev">{$translations.Month|escape:'htmlall':'UTF-8'}-1</button>
					<button type="submit" name="submitDateYearPrev" class="btn btn-default submitDateYearPrev">{$translations.Year|escape:'htmlall':'UTF-8'}-1</button>
				</div>					
			</div>
			<div class="col-lg-6">
				<div class="row">
					<div class="col-md-8">
						<div class="row">
							<div class="col-xs-6">
								<div class="input-group">
									<label class="input-group-addon">{if isset($translations.From)}{$translations.From|escape:'htmlall':'UTF-8'}{else}{l s='From:'}{/if}</label>
									<input type="text" name="datepickerFrom" id="datepickerFrom" value="{$datepickerFrom|escape:'htmlall':'UTF-8'}" class="datepicker  form-control">
								</div>
							</div>
							<div class="col-xs-6">
								<div class="input-group">
									<label class="input-group-addon">{if isset($translations.To)}{$translations.To|escape:'htmlall':'UTF-8'}{else}{l s='From:'}{/if}</label>
									<input type="text" name="datepickerTo" id="datepickerTo" value="{$datepickerTo|escape:'htmlall':'UTF-8'}" class="datepicker  form-control">
								</div>
							</div>
						</div>
					</div>
					<div class="col-md-4">
						<div class="row">
							<button type="submit" name="submitDatePicker" id="submitDatePicker" class="btn btn-default"><i class="icon-save"></i> {l s='Save'}</button>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
</div>
<script type="text/javascript">
	$(document).ready(function() {
		if ($("form#calendar_form .datepicker").length > 0)
			$("form#calendar_form .datepicker").datepicker({
				prevText: '',
				nextText: '',
				dateFormat: 'yy-mm-dd'
			});
	});
</script>