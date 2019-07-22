<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
 *
 * Derived from webtrees (www.webtrees.net)
 * Copyright (C) 2010 to 2012 webtrees development team
 *
 * Derived from PhpGedView (phpgedview.sourceforge.net)
 * Copyright (C) 2002 to 2010 PGV Development Team
 *
 * Kiwitrees is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class chart_statistics_KT_Module extends KT_Module implements KT_Module_Chart {

	// Extend class KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Statistics');
	}

	// Extend class KT_Module
	public function getDescription() {
		return /* I18N: Description of “Statistics chart” module */ KT_I18N::translate('An individual\'s statistics chart');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'show':
				$this->show();
				break;
			default:
				header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class KT_Module
	public function defaultAccessLevel() {
		return KT_PRIV_PUBLIC;
	}

	// Implement KT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref	= $controller->getSignificantIndividual()->getXref();
		$menus		= array();
		$menu		= new KT_Menu(
			$this->getTitle(),
			'module.php?mod=' . $this->getName() . '&amp;mod_action=show&amp;ged=' . KT_GEDURL,
			'menu-chart-statistics'
		);
		$menus[] = $menu;
		return $menus;
	}

	// Display list
	public function show() {
		global $controller, $GEDCOM, $iconStyle, $KT_STATS_CHART_COLOR1, $KT_STATS_CHART_COLOR2, $KT_STATS_CHART_COLOR3;
		$controller = new KT_Controller_Page;
		$stats	= new KT_Stats($GEDCOM);
		$tab	= KT_Filter::get('tab', KT_REGEX_NOSCRIPT, 0);

		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_D3_JS)

			->addInlineJavascript('
				jQuery("#statistics_chart").css("visibility", "visible");
//				jQuery("#statistics_chart").tabs();

				pieChart("chartSex");
				pieChart("chartMortality");
				pieChart("chartCommonSurnames");
				pieChart("chartCommonGiven");
				barChart("chartStatsBirth");
				barChart("chartStatsDeath");
				groupChart("chartStatsAge");
		    ');

		include_once 'statistics.js.php';
		?>

		<!-- Start page layout  -->
		<style> /* set the CSS */
			.bar-m { fill: <?php echo $KT_STATS_CHART_COLOR1; ?>; }
			.bar-f { fill: <?php echo $KT_STATS_CHART_COLOR2; ?>; }
			.bar-u { fill: <?php echo $KT_STATS_CHART_COLOR3; ?>; }
			.bar-l { fill: #9fff80!important; }
			.bar-d { fill: #4682b4!important;; }
			[class^="bar-"] {stroke: #fff;}
			.pieLabel {font-size: 0.5rem;}
			.axisLabel {font-size: 0.9rem;}
			.axisLabel, .barLabel {text-anchor: middle;}
			.tick text, .tick text {color: #555; font-size: 0.8rem;}
			#chartStatsBirth, #chartStatsDeath, #chartStatsAge {height: 21rem; width: 100%;}
			#chartStatsBirth svg, #chartStatsDeath svg {margin-top: 2rem; max-height: 20rem; max-width: 50%;}
			#chartSex, #chartMortality, #chartCommonSurnames, #chartCommonGiven svg {height: 21rem;}
			#chartSex svg, #chartMortality svg, #chartCommonSurnames svg, #chartCommonGiven svg {height: 18rem; max-width: 95%;}
		</style>
		<div id="statistics-page">
			<h2><?php echo KT_I18N::translate('Statistics'); ?></h2>
			<div id="statistics_chart" class="ui-tabs ui-corner-all ui-widget ui-widget-content">
				<ul class="ui-tabs-nav ui-corner-all ui-helper-reset ui-helper-clearfix ui-widget-header">
					<li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab">
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>&amp;tab=0" class="ui-tabs-anchor">
							<span id="stats-indi"><?php echo KT_I18N::translate('Individuals'); ?></span>
						</a>
					</li>
					<li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab">
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>&amp;tab=1" class="ui-tabs-anchor">
							<span id="stats-fam"><?php echo KT_I18N::translate('Families'); ?></span>
						</a>
					</li>
					<li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab">
						<a href="statistics.php?ged=<?php echo KT_GEDURL; ?>&amp;ajax=1&amp;tab=2" class="ui-tabs-anchor">
							<span id="stats-other"><?php echo KT_I18N::translate('Others'); ?></span>
						</a>
					</li>
					<li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab">
						<a href="statistics.php?ged=<?php echo KT_GEDURL; ?>&amp;ajax=1&amp;tab=3" class="ui-tabs-anchor">
							<span id="stats-own"><?php echo KT_I18N::translate('Own charts'); ?></span>
						</a>
					</li>
				</ul>
				<?php if ($tab == 0) { ?>
					<fieldset>
						<legend>
							<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis" target="_blank">
								<?php echo KT_I18N::translate('Total individuals: %s', $stats->totalIndividuals()); ?>
							</a>
						</legend>
					<table class="facts_table" style="table-layout:fixed;">
						<tr>
							<td class="facts_label" style="width:13.3%;"><?php echo KT_I18N::translate('Total males'); ?></td>
							<td class="facts_label" style="width:13.3%;"><?php echo KT_I18N::translate('Total females'); ?></td>
							<td class="facts_label" style="width:13.3%;"><?php echo KT_I18N::translate('Total unknown'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Total living'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Total dead'); ?></td>
						</tr>
						<tr>
							<td class="facts_value" align="center">
								<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
									<?php echo $stats->totalSexMales(); ?>
								</a>
								 (<?php echo $stats->totalSexMalesPercentage(); ?>)
							</td>
							<td class="facts_value" align="center">
								<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=female" target="_blank">
									<?php echo $stats->totalSexFemales(); ?>
								</a>
								 (<?php echo $stats->totalSexFemalesPercentage(); ?>)
							</td>
							<td class="facts_value" align="center">
								<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=unknown" target="_blank">
									<?php echo $stats->totalSexUnknown(); ?>
								</a>
							 	 (<?php echo $stats->totalSexUnknownPercentage(); ?>)
							</td>
							<td class="facts_value" align="center">
								<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=living" target="_blank">
									<?php echo $stats->totalLiving(); ?>
								</a>
								 (<?php echo $stats->totalLivingPercentage(); ?>)
							 </td>
							<td class="facts_value" align="center">
								<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=deceased" target="_blank">
									<?php echo $stats->totalDeceased(); ?>
								</a>
								 (<?php echo $stats->totalDeceasedPercentage(); ?>)
							 </td>
						</tr>
						<tr>
							<td class="facts_value statistics-page" colspan="3" style="width:50%;"><div id="chartSex"></div></td>
							<td class="facts_value statistics-page" colspan="2"><div id="chartMortality"></div></td>
						</tr>
					</table>
					<br>
					<b><?php echo KT_I18N::translate('Events'); ?></b>
					<table class="facts_table" style="table-layout:fixed;">
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Total births'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Total deaths'); ?></td>
						</tr>
						<tr>
							<td class="facts_value" align="center"><?php echo KT_I18n::number($stats->totalBirths()); ?></td>
							<td class="facts_value" align="center"><?php echo KT_I18n::number($stats->totalDeaths()); ?></td>
						</tr>
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Births by century'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Deaths by century'); ?></td>
						</tr>
						<tr>
							<td class="facts_value statistics-page" style="width:50%;"><div id="chartStatsBirth"></div></td>
							<td class="facts_value statistics-page"><div id="chartStatsDeath"></div></td>
						</tr>
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Earliest birth'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Earliest death'); ?></td>
						</tr>
						<tr>
							<td class="facts_value"><?php echo $stats->firstBirth(); ?></td>
							<td class="facts_value"><?php echo $stats->firstDeath(); ?></td>
						</tr>
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Latest birth'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Latest death'); ?></td>
						</tr>
						<tr>
							<td class="facts_value"><?php echo $stats->lastBirth(); ?></td>
							<td class="facts_value"><?php echo $stats->lastDeath(); ?></td>
						</tr>
					</table>
					<br>
					<b><?php echo KT_I18N::translate('Lifespan'); ?></b>
					<table class="facts_table" style="table-layout:fixed;">
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Average age at death'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Males'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Females'); ?></td>
						</tr>
						<tr>
							<td class="facts_value" align="center"><?php echo $stats->averageLifespan(true); ?></td>
							<td class="facts_value" align="center"><?php echo $stats->averageLifespanMale(true); ?></td>
							<td class="facts_value" align="center"><?php echo $stats->averageLifespanFemale(true); ?></td>
						</tr>
						<tr>
							<td class="facts_value statistics-page" colspan="3" style="width:50%;"><div id="chartStatsAge"></div></td>
						</tr>
					</table>
					<br>
					<b><?php echo KT_I18N::translate('Greatest age at death'); ?></b>
					<table class="facts_table" style="table-layout:fixed;">
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Males'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Females'); ?></td>
						</tr>
						<tr>
							<td class="facts_value"><?php echo $stats->topTenOldestMaleList(); ?></td>
							<td class="facts_value"><?php echo $stats->topTenOldestFemaleList(); ?></td>
						</tr>
					</table>
					<br>
					<?php if (KT_USER_ID) { ?>
						<b><?php echo KT_I18N::translate('Oldest living people'); ?></b>
						<table class="facts_table" style="table-layout:fixed;">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Males'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Females'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->topTenOldestMaleListAlive(); ?></td>
								<td class="facts_value"><?php echo $stats->topTenOldestFemaleListAlive(); ?></td>
							</tr>
						</table>
						<br>
					<?php } ?>
					<b><?php echo KT_I18N::translate('Names'); ?></b>
					<table class="facts_table" style="table-layout:fixed;">
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Total surnames'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Total given names'); ?></td>
						</tr>
						<tr>
							<td class="facts_value" align="center">
								<a href="indilist.php?show_all=yes&ged=<?php echo $GEDCOM; ?>"><?php echo $stats->totalSurnames(); ?></a>
							</td>
							<td class="facts_value" align="center"><?php echo $stats->totalGivennames(); ?></td>
						</tr>
						<tr>
							<td class="facts_label"><?php echo KT_I18N::translate('Top surnames'); ?></td>
							<td class="facts_label"><?php echo KT_I18N::translate('Top given names'); ?></td>
						</tr>
						<tr>
							<td class="facts_value statistics-page" style="width:50%;"><div id="chartCommonSurnames"></div></td>
							<td class="facts_value statistics-page"><div id="chartCommonGiven"></div></td>
						</tr>
					</table>
					</fieldset>
				<?php } ?>
			</div>
		</div>
		<?php
	}

}
