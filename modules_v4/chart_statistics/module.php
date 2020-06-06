<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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
        $indi_xref   = $controller->getSignificantIndividual()->getXref();
        $menus       = array();
        $menu        = new KT_Menu(
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
        $controller    = new KT_Controller_Page;
        $stats        = new KT_Stats($GEDCOM);
        $tab        = KT_Filter::get('tab', KT_REGEX_NOSCRIPT, 0);

        $controller
            ->restrictAccess(KT_Module::isActiveChart(KT_GED_ID, $this->getName(), KT_USER_ACCESS_LEVEL))
            ->setPageTitle($this->getTitle())
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
            .statistics-page h4 { font-weight: normal; font-size: 0.8rem; margin: 0 auto 10px;}
            div.legend {padding: 20px;}
            [id^="tab"] .facts_label {text-transform: unset;}
            [id^="chart"] svg {max-height: 20rem; width: 80%;}
            .bar-m {fill: <?php echo $KT_STATS_CHART_COLOR1; ?>; }
            .bar-f {fill: <?php echo $KT_STATS_CHART_COLOR2; ?>; }
            .bar-u {fill: <?php echo $KT_STATS_CHART_COLOR3; ?>; }
            .bar-l {fill: #9fff80!important;}
            .bar-d {fill: #4682b4!important;}
            .horizontalChart .domain {stroke : #ffffff;} /* hide y axis on horizontal chart */
            [class^="bar-"] {stroke: #ffffff;}
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
                                    <?php echo $stats->totalSurnames(); ?>
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
                                <td class="facts_value" align="center">
                                    <a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams&amp;tag=marr" target="_blank">
                                        <?php echo $stats->totalMarriages(); ?>
                                    </a>
                                </td>
                                <td class="facts_value" align="center">
                                    <a href="statisticsTables.php?ged=<?php echo $GEDCOM; ?>&amp;table=totalFams&amp;tag=div" target="_blank">
                                        <?php echo $stats->totalDivorces(); ?>
                                    </a>
                                </td>
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
                                    <h4><?php echo KT_I18N::translate('Average age at marriage date, by century'); ?></h4>
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
                <?php }
                // Own charts tab
                if ($tab == 3) {
                    echo '<fieldset>
                    <legend>', KT_I18N::translate('Create your own chart'), '</legend>';
                    ?>
                    <script>
                        function statusHide(sel) {
                            var box = document.getElementById(sel);
                            box.style.display = 'none';
                            var box_m = document.getElementById(sel+'_m');
                            if (box_m) box_m.style.display = 'none';
                            if (sel=='map_opt') {
                                var box_axes = document.getElementById('axes');
                                if (box_axes) box_axes.style.display = '';
                                var box_zyaxes = document.getElementById('zyaxes');
                                if (box_zyaxes) box_zyaxes.style.display = '';
                            }
                        }
                        function statusShow(sel) {
                            var box = document.getElementById(sel);
                            box.style.display = '';
                            var box_m = document.getElementById(sel+'_m');
                            if (box_m) box_m.style.display = 'none';
                            if (sel=='map_opt') {
                                var box_axes = document.getElementById('axes');
                                if (box_axes) box_axes.style.display = 'none';
                                var box_zyaxes = document.getElementById('zyaxes');
                                if (box_zyaxes) box_zyaxes.style.display = 'none';
                            }
                        }
                        function statusShowSurname(x) {
                            if (x.value == 'surname_distribution_chart') {
                                var box = document.getElementById('surname_opt');
                                box.style.display = '';
                            }
                            else if (x.value !== 'surname_distribution_chart') {
                                var box = document.getElementById('surname_opt');
                                box.style.display = 'none';
                            }
                        }
                        function statsModalDialog(url, title) {
                            var form = jQuery('#own-stats-form');
                            jQuery.post(form.attr('action'), form.serialize(), function(response) {
                                jQuery(response).dialog({
                                    modal: true,
                                    width: 964,
                                    closeText: ""
                                });
                                // Close the window when we click outside it.
                                jQuery(".ui-widget-overlay").on("click", function () {
                                    jQuery("div:ui-dialog:visible").dialog("close");
                                });
                            });
                            return false;
                        }
                    </script>
                    <?php
                    echo '<div id="own-stats"><form method="post" id="own-stats-form" name="form" action="statisticsplot.php" onsubmit="statsModalDialog(\'statisticsplot.php?action=newform\', \'', KT_I18N::translate('Statistics plot'), '\'); return false;">';
                    echo '<input type="hidden" name="action" value="update">';
                    echo '<table width="100%">';
                    if (!isset($plottype)) $plottype = 11;
                    if (!isset($charttype)) $charttype = 1;
                    if (!isset($plotshow)) $plotshow = 302;
                    if (!isset($plotnp)) $plotnp = 201;
                    if (isset($KT_SESSION->statTicks[$GEDCOM])) {
                        $xasGrLeeftijden = $KT_SESSION->statTicks[$GEDCOM]['xasGrLeeftijden'];
                        $xasGrMaanden = $KT_SESSION->statTicks[$GEDCOM]['xasGrMaanden'];
                        $xasGrAantallen = $KT_SESSION->statTicks[$GEDCOM]['xasGrAantallen'];
                        $zasGrPeriode = $KT_SESSION->statTicks[$GEDCOM]['zasGrPeriode'];
                    } else {
                        $xasGrLeeftijden = '1,5,10,20,30,40,50,60,70,80,90,100';
                        $xasGrMaanden = '-24,-12,0,8,12,18,24,48';
                        $xasGrAantallen = '1,2,3,4,5,6,7,8,9,10';
                        $zasGrPeriode = '1700,1750,1800,1850,1900,1950,2000';
                    }
                    if (isset($KT_SESSION->statTicks1[$GEDCOM])) {
                        $chart_shows = $KT_SESSION->statTicks1[$GEDCOM]['chart_shows'];
                        $chart_type = $KT_SESSION->statTicks1[$GEDCOM]['chart_type'];
                        $surname = $KT_SESSION->statTicks1[$GEDCOM]['surname'];
                    } else {
                        $chart_shows = 'world';
                        $chart_type = 'indi_distribution_chart';
                        $surname = $stats->getCommonSurname();
                    }

                    echo '<tr>
                        <td class="descriptionbox width25 wrap">', KT_I18N::translate('Select chart type:'), '</td>
                        <td class="optionbox">
                        <input type="radio" id="stat_11" name="x-as" value="11"';
                        if ($plottype == '11') echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_11">', KT_I18N::translate('Month of birth'), '</label><br>';
                        echo '<input type="radio" id="stat_12" name="x-as" value="12"';
                        if ($plottype == '12') echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_12">', KT_I18N::translate('Month of death'), '</label><br>';
                        echo '<input type="radio" id="stat_13" name="x-as" value="13"';
                        if ($plottype == "13") echo ' checked="checked"';
                        echo " onclick=\"{statusChecked('z_none'); statusDisable('z_sex'); statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_13">', KT_I18N::translate('Month of marriage'), '</label><br>';
                        echo '<input type="radio" id="stat_15" name="x-as" value="15"';
                        if ($plottype == "15") echo ' checked="checked"';
                        echo " onclick=\"{statusChecked('z_none'); statusDisable('z_sex'); statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_15">', KT_I18N::translate('Month of first marriage'), '</label><br>';
                        echo '<input type="radio" id="stat_14" name="x-as" value="14"';
                        if ($plottype == "14") echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_14">', KT_I18N::translate('Month of birth of first child in a relation'), '</label><br>';
                        //echo '<input type="radio" id="stat_16" name="x-as" value="16"';
                        //if ($plottype == "16") echo ' checked="checked"';
                        //echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusShow('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        //echo '"><label for="stat_16">', KT_I18N::translate('Months between marriage and first child'), '</label><br>';
                        echo '<input type="radio" id="stat_17" name="x-as" value="17"';
                        if ($plottype == "17") echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusShow('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_17">', KT_I18N::translate('Age related to birth year'), '</label><br>';
                        echo '<input type="radio" id="stat_18" name="x-as" value="18"';
                        if ($plottype == "18") echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusShow('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_18">', KT_I18N::translate('Age related to death year'), '</label><br>';
                        echo '<input type="radio" id="stat_19" name="x-as" value="19"';
                        if ($plottype == "19") echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusShow('x_years_m'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_19">', KT_I18N::translate('Age in year of marriage'), '</label><br>';
                        echo '<input type="radio" id="stat_20" name="x-as" value="20"';
                        if ($plottype == "20") echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusShow('x_years_m'); statusHide('x_months'); statusHide('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_20">', KT_I18N::translate('Age in year of first marriage'), '</label><br>';
                        echo '<input type="radio" id="stat_21" name="x-as" value="21"';
                        if ($plottype == "21") echo ' checked="checked"';
                        echo " onclick=\"{statusEnable('z_sex'); statusHide('x_years'); statusHide('x_months'); statusShow('x_numbers'); statusHide('map_opt');}";
                        echo '"><label for="stat_21">', KT_I18N::translate('Number of children'), '</label><br>';
                        echo '<input type="radio" id="stat_1" name="x-as" value="1"';
                        if ($plottype == "1") echo ' checked="checked"';
                        echo " onclick=\"{statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusShow('map_opt'); statusShow('chart_type'); statusHide('axes');}";
                        echo '"><label for="stat_1">', KT_I18N::translate('Individual distribution'), '</label><br>';
                        echo '<input type="radio" id="stat_2" name="x-as" value="2"';
                        if ($plottype == "2") echo ' checked="checked"';
                        echo " onclick=\"{statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusShow('map_opt'); statusHide('chart_type'); statusHide('surname_opt');}";
                        echo '"><label for="stat_2">', KT_I18N::translate('Birth by country'), '</label><br>';
                        echo '<input type="radio" id="stat_4" name="x-as" value="4"';
                        if ($plottype == "4") echo ' checked="checked"';
                        echo " onclick=\"{statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusShow('map_opt'); statusHide('chart_type'); statusHide('surname_opt');}";
                        echo '"><label for="stat_4">', KT_I18N::translate('Marriage by country'), '</label><br>';
                        echo '<input type="radio" id="stat_3" name="x-as" value="3"';
                        if ($plottype == "3") echo ' checked="checked"';
                        echo " onclick=\"{statusHide('x_years'); statusHide('x_months'); statusHide('x_numbers'); statusShow('map_opt'); statusHide('chart_type'); statusHide('surname_opt');}";
                        echo '"><label for="stat_3">', KT_I18N::translate('Death by country'), '</label><br>';
                        echo '<br><div id="x_years" style="display:none;">';
                        echo KT_I18N::translate('Select the desired age interval');
                        echo '<br><select id="xas-grenzen-leeftijden" name="xas-grenzen-leeftijden">
                            <option value="1,5,10,20,30,40,50,60,70,80,90,100" selected="selected">',
                            KT_I18N::plural('interval %s year', 'interval %s years', 10, KT_I18N::number(10)), '</option>
                            <option value="5,20,40,60,75,80,85,90">',
                            KT_I18N::plural('interval %s year', 'interval %s years', 20, KT_I18N::number(20)), '</option>
                            <option value="10,25,50,75,100">',
                            KT_I18N::plural('interval %s year', 'interval %s years', 25, KT_I18N::number(25)), '</option>
                        </select><br>
                        </div>
                        <div id="x_years_m" style="display:none;">';
                        echo KT_I18N::translate('Select the desired age interval');
                        echo '<br><select id="xas-grenzen-leeftijden_m" name="xas-grenzen-leeftijden_m">
                            <option value="16,18,20,22,24,26,28,30,32,35,40,50" selected="selected">',
                            KT_I18N::plural('interval %s year', 'interval %s years', 2, KT_I18N::number(2)), '</option>
                            <option value="20,25,30,35,40,45,50">',
                            KT_I18N::plural('interval %s year', 'interval %s years', 5, KT_I18N::number(5)), '</option>
                        </select><br>
                        </div>
                        <div id="x_months" style="display:none;">';
                        echo KT_I18N::translate('Select the desired age interval');
                        echo '<br><select id="xas-grenzen-maanden" name="xas-grenzen-maanden">
                            <option value="0,8,12,15,18,24,48" selected="selected">', KT_I18N::translate('months after marriage'), '</option>
                            <option value="-24,-12,0,8,12,18,24,48">', KT_I18N::translate('months before and after marriage'), '</option>
                            <option value="0,6,9,12,15,18,21,24">', KT_I18N::translate('quarters after marriage'), '</option>
                            <option value="0,6,12,18,24">', KT_I18N::translate('half-year after marriage'), '</option>
                        </select><br>
                        </div>
                        <div id="x_numbers" style="display:none;">';
                        echo KT_I18N::translate('Select the desired count interval');
                        echo '<br><select id="xas-grenzen-aantallen" name="xas-grenzen-aantallen">
                            <option value="1,2,3,4,5,6,7,8,9,10" selected="selected">', KT_I18N::translate('interval one child'), '</option>
                            <option value="2,4,6,8,10,12">', KT_I18N::translate('interval two children'), '</option>
                        </select>
                        <br>
                        </div>
                        <div id="map_opt" style="display:none;">
                        <div id="chart_type">';
                        echo KT_I18N::translate('Chart type');
                        echo '<br><select name="chart_type" onchange="statusShowSurname(this);">
                            <option value="indi_distribution_chart" selected="selected">', KT_I18N::translate('Individual distribution chart'), '</option>
                            <option value="surname_distribution_chart">', KT_I18N::translate('Surname distribution chart'), '</option>
                        </select>
                        <br>
                        </div>
                        <div id="surname_opt" style="display:none;">';
                        echo KT_Gedcom_Tag::getLabel('SURN'), help_link('google_chart_surname'), '<br><input data-autocomplete-type="SURN" type="text" name="SURN" size="20">';
                        echo '<br>
                        </div>';
                        echo KT_I18N::translate('Geographical area');
                        echo '<br><select id="chart_shows" name="chart_shows">
                            <option value="world" selected="selected">', KT_I18N::translate('World'), '</option>
                            <option value="europe">', KT_I18N::translate('Europe'), '</option>
                            <option value="usa">', KT_I18N::translate('United States'), '</option>
                            <option value="south_america">', KT_I18N::translate('South America'), '</option>
                            <option value="asia">', KT_I18N::translate('Asia'), '</option>
                            <option value="middle_east">', KT_I18N::translate('Middle East'), '</option>
                            <option value="africa">', KT_I18N::translate('Africa'), '</option>
                        </select>
                        </div>
                        </td>
                        <td class="descriptionbox width20 wrap" id="axes">', KT_I18N::translate('Categories:'), '</td>
                        <td class="optionbox width30" id="zyaxes">
                        <input type="radio" id="z_none" name="z-as" value="300"';
                        if ($plotshow == "300") echo ' checked="checked"';
                        echo " onclick=\"statusDisable('zas-grenzen-periode');";
                        echo '"><label for="z_none">', KT_I18N::translate('overall'), '</label><br>';
                        echo '<input type="radio" id="z_sex" name="z-as" value="301"';
                        if ($plotshow == "301") echo ' checked="checked"';
                        echo " onclick=\"statusDisable('zas-grenzen-periode');";
                        echo '"><label for="z_sex">', KT_I18N::translate('gender'), '</label><br>';
                        echo '<input type="radio" id="z_time" name="z-as" value="302"';
                        if ($plotshow == "302") echo ' checked="checked"';
                        echo " onclick=\"statusEnable('zas-grenzen-periode');";
                        echo '"><label for="z_time">', KT_I18N::translate('date periods'), '</label><br><br>';
                        echo KT_I18N::translate('Date range'), '<br>';
                        echo '<select id="zas-grenzen-periode" name="zas-grenzen-periode">
                            <option value="1700,1750,1800,1850,1900,1950,2000" selected="selected">',
                                /* I18N: from 1700 interval 50 years */ KT_I18N::plural('from %1$s interval %2$s year', 'from %1$s interval %2$s years', 50, KT_I18N::digits(1700), KT_I18N::number(50)), '</option>
                            <option value="1800,1840,1880,1920,1950,1970,2000">',
                                KT_I18N::plural('from %1$s interval %2$s year', 'from %1$s interval %2$s years', 40, KT_I18N::digits(1800), KT_I18N::number(40)), '</option>
                            <option value="1800,1850,1900,1950,2000">',
                                KT_I18N::plural('from %1$s interval %2$s year', 'from %1$s interval %2$s years', 50, KT_I18N::digits(1800), KT_I18N::number(50)), '</option>
                            <option value="1900,1920,1940,1960,1980,1990,2000">',
                                KT_I18N::plural('from %1$s interval %2$s year', 'from %1$s interval %2$s years', 20, KT_I18N::digits(1900), KT_I18N::number(20)), '</option>
                            <option value="1900,1925,1950,1975,2000">',
                                KT_I18N::plural('from %1$s interval %2$s year', 'from %1$s interval %2$s years', 25, KT_I18N::digits(1900), KT_I18N::number(25)), '</option>
                            <option value="1940,1950,1960,1970,1980,1990,2000">',
                                KT_I18N::plural('from %1$s interval %2$s year', 'from %1$s interval %2$s years', 10, KT_I18N::digits(1940), KT_I18N::number(10)), '</option>
                        </select>
                        <br><br>';
                        echo KT_I18N::translate('results:'), '<br>';
                        echo '<input type="radio" id="y_num" name="y-as" value="201"';
                        if ($plotnp == "201") echo ' checked="checked"';
                        echo '><label for="y_num">', KT_I18N::translate('numbers'), '</label><br>';
                        echo '<input type="radio" id="y_perc" name="y-as" value="202"';
                        if ($plotnp == "202") echo ' checked="checked"';
                        echo '><label for="y_perc">', KT_I18N::translate('percentage'), '</label><br>';
                        echo '</td>
                        </tr>
                        </table>
                        <table width="100%">
                        <tr align="center"><td>
                            <br>
                            <input type="submit" value="', KT_I18N::translate('show'), ' ">
                            <input type="reset"  value=" ', KT_I18N::translate('Reset'), ' " onclick="{statusEnable(\'z_sex\'); statusHide(\'x_years\'); statusHide(\'x_months\'); statusHide(\'x_numbers\'); statusHide(\'map_opt\');}"><br>
                        </td>
                        </tr>
                    </table>
                    </form></div>
                    </fieldset>';
                } ?>
            </div>
        </div>
        <?php
    }

}
