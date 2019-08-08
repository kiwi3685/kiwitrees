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
		$controller	= new KT_Controller_Page;
		$stats		= new KT_Stats($GEDCOM);
		$tab		= KT_Filter::get('tab', KT_REGEX_NOSCRIPT, 0);

		$controller
			->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
			->pageHeader()
			->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
			->addExternalJavascript(KT_D3_JS)
			->addInlineJavascript('
				jQuery("[id^=tab]").css("visibility", "hidden");
			');

		include_once 'statistics.js.php';
		?>

		<!-- Start page layout  -->
		<style>
			.statistics-page h4 {margin: 0 auto 10px;}
			div.legend {padding: 20px;}
			[id^="tab"] .facts_label {text-transform: unset;}
			[id^="chart"] svg {max-height: 20rem; width: 75%;}
			.bar-m { fill: <?php echo $KT_STATS_CHART_COLOR1; ?>; }
			.bar-f { fill: <?php echo $KT_STATS_CHART_COLOR2; ?>; }
			.bar-u { fill: <?php echo $KT_STATS_CHART_COLOR3; ?>; }
			.bar-l { fill: #9fff80!important; }
			.bar-d { fill: #4682b4!important;; }
			[class^="bar-"] {stroke: #fff;}
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
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>&amp;tab=2" class="ui-tabs-anchor">
							<span id="stats-other"><?php echo KT_I18N::translate('Others'); ?></span>
						</a>
					</li>
					<li class="ui-tabs-tab ui-corner-top ui-state-default ui-tab">
						<a href="module.php?mod=<?php echo $this->getName(); ?>&amp;mod_action=show&amp;ged=<?php echo KT_GEDURL; ?>&amp;tab=3" class="ui-tabs-anchor">
							<span id="stats-own"><?php echo KT_I18N::translate('Own charts'); ?></span>
						</a>
					</li>
				</ul>
				<?php
				// Individual tab
				if ($tab == 0) {
					$controller->addInlineJavascript('
						jQuery("li.ui-tab").removeClass("ui-tabs-active ui-state-active");
						jQuery("#stats-indi").closest("li").addClass("ui-tabs-active ui-state-active");

						pieChart("chartSex");
						pieChart("chartMortality");
						horizontalChart("chartCommonSurnames");
						horizontalChart("chartCommonGiven");
						barChart("chartStatsBirth");
						barChart("chartStatsDeath");
						groupChart("chartStatsAge");

						jQuery("#tab0").css("visibility", "visible");
					'); ?>

					<fieldset id="tab0">
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
								<td class="facts_value statistics-page" colspan="3" style="width:50%;">
									<h4><?php echo KT_I18N::translate('Individuals, by gender'); ?></h4>
									<div id="chartSex"></div>
								</td>
								<td class="facts_value statistics-page" colspan="2">
									<h4><?php echo KT_I18N::translate('Individuals, by living / deceased status'); ?></h4>
									<div id="chartMortality"></div>
								</td>
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
								<td class="facts_value statistics-page" style="width:50%;">
									<h4><?php echo KT_I18N::translate('Number of births in each century'); ?></h4>
									<div id="chartStatsBirth"></div>
								</td>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Number of deaths in each century'); ?></h4>
									<div id="chartStatsDeath"></div>
								</td>
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
								<td class="facts_value statistics-page" colspan="3" style="width:50%;">
									<h4><?php echo KT_I18N::translate('Average age at death date, by century'); ?></h4>
									<div id="chartStatsAge"></div>
								</td>
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
								<td class="facts_value statistics-page" style="width:50%;">
									<h4><?php echo KT_I18N::translate('Top surnames'); ?></h4>
									<div id="chartCommonSurnames"></div>
								</td>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Top given names'); ?></h4>
									<div id="chartCommonGiven"></div>
								</td>
							</tr>
						</table>
					</fieldset>
				<?php }
				// Family tab
				if ($tab == 1) {
					$controller->addInlineJavascript('
						jQuery("li.ui-tab").removeClass("ui-tabs-active ui-state-active");
						jQuery("#stats-fam").closest("li").addClass("ui-tabs-active ui-state-active");

						barChart("chartMarr");
						barChart("chartDiv");
						groupChart("chartMarrAge");
						barChart("chartChild");
						barChart("chartNoChild");

						jQuery("#tab1").css("visibility", "visible");
					'); ?>

					<fieldset id="tab1">
						<legend>
							<?php echo KT_I18N::translate('Total families: %s', $stats->totalFamilies()); ?>
						</legend>
						<b><?php echo KT_I18N::translate('Events'); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Total marriages'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Total divorces'); ?></td>
							</tr>
							<tr>
								<td class="facts_value" align="center"><?php echo KT_I18n::number($stats->totalMarriages()); ?></td>
								<td class="facts_value" align="center"><?php echo KT_I18n::number($stats->totalDivorces()); ?></td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Marriages by century'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Divorces by century'); ?></td>
							</tr>
							<tr>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Number of marriages in each century'); ?></h4>
									<div id="chartMarr"></div>
								</td>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Number of divorces in each century'); ?></h4>
									<div id="chartDiv"></div>
								</td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Earliest marriage'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Earliest divorce'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->firstMarriage(); ?></td>
								<td class="facts_value"><?php echo $stats->firstDivorce(); ?></td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Latest marriage'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Latest divorce'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->lastMarriage(); ?></td>
								<td class="facts_value"><?php echo $stats->lastDivorce(); ?></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Length of marriage'); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Longest marriage'); ?> - <?php echo $stats->topAgeOfMarriage(); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Shortest marriage'); ?> - <?php echo $stats->minAgeOfMarriage(); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->topAgeOfMarriageFamily(); ?></td>
								<td class="facts_value"><?php echo $stats->minAgeOfMarriageFamily(); ?></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Age in year of marriage'); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Youngest male'); ?> - <?php echo $stats->youngestMarriageMaleAge(true); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Youngest female'); ?> - <?php echo $stats->youngestMarriageFemaleAge(true); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->youngestMarriageMale(); ?></td>
								<td class="facts_value"><?php echo $stats->youngestMarriageFemale(); ?></td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Oldest male'); ?> - <?php echo $stats->oldestMarriageMaleAge(true); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Oldest female'); ?> - <?php echo $stats->oldestMarriageFemaleAge(true); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->oldestMarriageMale(); ?></td>
								<td class="facts_value"><?php echo $stats->oldestMarriageFemale(); ?></td>
							</tr>
							<tr>
								<td class="facts_value statistics-page" colspan="2">
									<h4><?php echo KT_I18N::translate('Maximum age at marriage date, by century'); ?></h4>
									<div id="chartMarrAge"></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Age at birth of child'); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Youngest father'); ?> - <?php echo $stats->youngestFatherAge(true); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Youngest mother'); ?> - <?php echo $stats->youngestMotherAge(true); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->youngestFather(); ?></td>
								<td class="facts_value"><?php echo $stats->youngestMother(); ?></td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Oldest father'); ?> - <?php echo $stats->oldestFatherAge(true); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Oldest mother'); ?> - <?php echo $stats->oldestMotherAge(true); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->oldestFather(); ?></td>
								<td class="facts_value"><?php echo $stats->oldestMother(); ?></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Children in family'); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Average number of children per family'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Number of families without children'); ?></td>
							</tr>
							<tr>
								<td class="facts_value" align="center"><?php echo $stats->averageChildren(); ?></td>
								<td class="facts_value" align="center"><?php echo $stats->noChildrenFamilies(); ?></td>
							</tr>
							<tr>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Number of children per family, by century'); ?></h4>
									<div id="chartChild">
								</td>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Number of families without children, by century'); ?></h4>
									<div id="chartNoChild">
								</td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Largest families'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Largest number of grandchildren'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->topTenLargestFamilyList(); ?></td>
								<td class="facts_value"><?php echo $stats->topTenLargestGrandFamilyList(); ?></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Age difference'); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Between siblings'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Greatest age between siblings'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->topAgeBetweenSiblingsList(); ?></td>
								<td class="facts_value"><?php echo $stats->topAgeBetweenSiblingsFullName(); ?></td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Between husband and wife, husband older'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Between wife and husband, wife older'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->ageBetweenSpousesMFList(); ?></td>
								<td class="facts_value"><?php echo $stats->ageBetweenSpousesFMList(); ?></td>
							</tr>
						</table>
					</fieldset>
				<?php }
				// Other tab
				if ($tab == 2) {
					$controller->addInlineJavascript('
						jQuery("li.ui-tab").removeClass("ui-tabs-active ui-state-active");
						jQuery("#stats-other").closest("li").addClass("ui-tabs-active ui-state-active");

						barChart("chartMedia");
						pieChart("chartIndisWithSources");
						pieChart("chartFamsWithSources");
						mapChart("chartDistribution");

						jQuery("#tab2").css("visibility", "visible");
					'); ?>

					<fieldset id="tab2">
						<br>
						<b><?php echo KT_I18N::translate('Records'); ?>: <?php echo $stats->totalRecords(); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Media objects'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Sources'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Notes'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Repositories'); ?></td>
							</tr>
							<tr>
								<td class="facts_value" align="center"><?php echo $stats->totalMedia(); ?></td>
								<td class="facts_value" align="center"><?php echo $stats->totalSources(); ?></td>
								<td class="facts_value" align="center"><?php echo $stats->totalNotes(); ?></td>
								<td class="facts_value" align="center"><?php echo $stats->totalRepositories(); ?></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Total events'); ?>: <?php echo $stats->totalEvents(); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('First event'); ?> - <?php echo $stats->firstEventType(); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Last event'); ?> - <?php echo $stats->lastEventType(); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->firstEvent(); ?></td>
								<td class="facts_value"><?php echo $stats->lastEvent(); ?></td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Media objects'); ?>: <?php echo $stats->totalMedia(); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Media objects by type'); ?></td>
							</tr>
							<tr>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Media'); ?></h4>
									<div id="chartMedia"></div>
								</td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Sources'); ?>: <?php echo $stats->totalSources(); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Individuals with sources'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Families with sources'); ?></td>
							</tr>
							<tr>
								<td class="facts_value" align="center">
									<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
										<?php echo $stats->totalIndisWithSources(); ?>
									</a>
									 (<?php echo $stats->totalIndividualsPercentage(); ?>)
								</td>
								<td class="facts_value" align="center">
									<a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalIndis&amp;option=male" target="_blank">
										<?php echo $stats->totalFamsWithSources(); ?>
									</a>
									 (<?php echo $stats->totalFamiliesPercentage(); ?>)
								</td>
							</tr>
							<tr>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Individuals with sources'); ?></h4>
									<div id="chartIndisWithSources"></div>
								</td>
								<td class="facts_value statistics-page">
									<h4><?php echo KT_I18N::translate('Families with sources'); ?></h4>
									<div id="chartFamsWithSources"></div>
								</td>
							</tr>
						</table>
						<br>
						<b><?php echo KT_I18N::translate('Places'); ?>: <?php echo $stats->totalPlaces(); ?></b>
						<table class="facts_table">
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Birth places'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Death places'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->commonBirthPlacesList(); ?></td>
								<td class="facts_value"><?php echo $stats->commonDeathPlacesList(); ?></td>
							</tr>
							<tr>
								<td class="facts_label"><?php echo KT_I18N::translate('Marriage places'); ?></td>
								<td class="facts_label"><?php echo KT_I18N::translate('Events in countries'); ?></td>
							</tr>
							<tr>
								<td class="facts_value"><?php echo $stats->commonMarriagePlacesList(); ?></td>
								<td class="facts_value"><?php echo $stats->commonCountriesList(); ?></td>
							</tr>
							<tr>
								<td class="facts_value statistics-page" colspan="2">
									<h4><?php echo KT_I18N::translate('Individual distribution chart'); ?></h4>
									<div id="chartDistribution"></div>
								</td>
							</tr>
						</table>
					</fieldset>
				<?php } ?>
			</div>
		</div>
		<?php
	}

}
