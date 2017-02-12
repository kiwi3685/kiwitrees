<?php
// View for the fan chart.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA

define('WT_SCRIPT_NAME', 'fanchart.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$controller = new WT_Controller_Fanchart();
$controller
	->pageHeader()
	->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
	->addExternalJavascript(WT_D3_JS)
	->addExternalJavascript(WT_STATIC_URL . WT_MODULES_DIR . 'chart_fanchart/js/ancestral-fan-chart.js');

	// Encode chart parameters to json string
	$chartParams = json_encode(
		array(
			'fanDegree'    => $controller->fanDegree,
			'generations'  => $controller->generations,
			'defaultColor' => $controller->getColor(),
			'fontScale'    => $controller->fontScale,
			'fontColor'    => $controller->getChartFontColor(),
			'data'         => $controller->buildJsonTree($controller->root),
		)
	);

	$controller
		->addInlineJavascript('autocomplete();')
		->addInlineJavascript('
			jQuery(function () {
				" use strict" ;
				var fanChart = jQuery("#fan_chart" );
				if (typeof jQuery().ancestralFanChart === "function" ) {
					fanChart.ancestralFanChart(' . $chartParams . ');
				}
			});
		');
	?>
	<div id="fanchart-page">
		<h2><?php echo $controller->getPageTitle(); ?></h2>
		<form name="people" id="people" method="get" action="?">
			<div class="chart_options">
				<label for="rootid"><?php echo WT_I18N::translate('Individual'); ?></label>
				<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="rootid" id="rootid" value="<?php echo $controller->root->getXref(); ?>">
			</div>
			<div class="chart_options">
				<label for="generations"><?php echo WT_I18N::translate('Generations'); ?></label>
				<?php echo edit_field_integers('generations', $controller->generations, 2, 9); ?>
			</div>
			<div class="chart_options">
				<label for="fanDegree"><?php echo WT_I18N::translate('Degrees'); ?></label>
				<?php echo select_edit_control('fanDegree', $controller->getFanDegrees(), null, $controller->fanDegree); ?>
			</div>
			<div class="chart_options">
				<label for="fontScale"><?php echo WT_I18N::translate('Font size'); ?></label>
				<input class="fontScale" type="text" name="fontScale" id="fontScale" value="<?php echo $controller->fontScale; ?>"> %
			</div>
			<button class="btn btn-primary show" type="submit">
				<i class="fa fa-eye"></i>
				<?php echo WT_I18N::translate('Show'); ?>
			</button>
		</form>
		<hr style="clear:both;">
		<!-- end of form -->
		<div id="fan_chart"></div>
	</div>
	<?php
