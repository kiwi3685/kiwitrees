<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

define('KT_SCRIPT_NAME', 'ancestry.php');
require './includes/session.php';
require_once KT_ROOT.'includes/functions/functions_print_lists.php';

$controller = new KT_Controller_Ancestry();
$controller
	->pageHeader()
	->addExternalJavascript(KT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');

?>
<div id="ancestry-page">
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<form name="people" id="people" method="get" action="?">
		<input type="hidden" name="ged" value="<?php echo KT_GEDCOM; ?>">
		<input type="hidden" name="show_full" value="<?php echo $controller->show_full; ?>">
		<input type="hidden" name="show_cousins" value="<?php echo $controller->show_cousins; ?>">
		<table class="list_table">
			<tr>
				<td class="descriptionbox">
					<?php echo KT_I18N::translate('Individual'); ?>
				</td>
				<td class="optionbox">
				<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="rootid" id="rootid" size="3" value="<?php echo $controller->rootid; ?>">
					<?php echo print_findindi_link('rootid'); ?>
				</td>
				<td class="descriptionbox">
					<?php echo KT_I18N::translate('Box width'); ?>
				</td>
				<td class="optionbox">
					<input type="text" size="3" name="box_width" value="<?php echo htmlspecialchars((string) $box_width); ?>"> <b>%</b>
				</td>
				<td rowspan="2" class="descriptionbox">
					<?php echo KT_I18N::translate('Layout'); ?>
				</td>
				<td rowspan="2" class="optionbox">
					<input type="radio" name="chart_style" value="0"
					<?php
						if ($controller->chart_style==0) {
							echo ' checked="checked"';
						}
						echo ' onclick="statusDisable(\'cousins\');';
						echo '">', KT_I18N::translate('List');
						echo '<br><input type="radio" name="chart_style" value="1"';
						if ($controller->chart_style==1) {
							echo ' checked="checked"';
						}
						echo ' onclick="statusEnable(\'cousins\');';
						echo '">', KT_I18N::translate('Booklet');
					?>
					<br>
					<?php
						echo '<input ';
						if ($controller->chart_style==0) {
							echo 'disabled="disabled" ';
						}
						echo 'id="cousins" type="checkbox" value="';
						if ($controller->show_cousins) {
							echo '1" checked="checked" onclick="document.people.show_cousins.value=\'0\';"';
						} else {
							echo '0" onclick="document.people.show_cousins.value=\'1\';"';
						}
						echo '>';
						echo KT_I18N::translate('Show cousins');
						echo '<br><input type="radio" name="chart_style" value="2"';
						if ($controller->chart_style==2) {
							echo ' checked="checked" ';
						}
						echo ' onclick="statusDisable(\'cousins\');"';
						echo '>', KT_I18N::translate('Individuals');
						echo '<br><input type="radio" name="chart_style" value="3"';
						echo ' onclick="statusDisable(\'cousins\');"';
						if ($controller->chart_style==3) {
							echo ' checked="checked" ';
						}
						echo '>', KT_I18N::translate('Families');
					?>
				</td>
				<td rowspan="2" class="facts_label03">
					<input type="submit" value="<?php echo KT_I18N::translate('View'); ?>">
				</td>
			</tr>
			<tr>
				<td class="descriptionbox">
					<?php echo KT_I18N::translate('Generations'); ?>
				</td>
				<td class="optionbox">
					<select name="PEDIGREE_GENERATIONS">
						<?php
							for ($i=2; $i<=$MAX_PEDIGREE_GENERATIONS; $i++) {
								echo '<option value="', $i, '"';
								if ($i==$OLD_PGENS) {
									echo ' selected="selected"';
								}
									echo '>', KT_I18N::number($i), '</option>';
								}
						?>
					</select>
				</td>
				<td class="descriptionbox">
					<?php echo KT_I18N::translate('Show Details'); ?>
				</td>
				<td class="optionbox">
					<input type="checkbox" value="<?php if ($controller->show_full) { echo '1" checked="checked" onclick="document.people.show_full.value=\'0\';'; } else { echo '0" onclick="document.people.show_full.value=\'1\';'; } ?>">
				</td>
			</tr>
		</table>
	</form>
<?php

if ($controller->error_message) {
	echo '<p class="ui-state-error">', $controller->error_message, '</p>';
	exit;
}

switch ($controller->chart_style) {
case 0:
	// List
	$pidarr=array();
	echo '<ul id="ancestry_chart">';
	$controller->print_child_ascendancy($controller->root, 1, $OLD_PGENS-1);
	echo '</ul>';
	echo '<br>';
	break;
case 1:
	// TODO: this should be a parameter to a function, not a global
	$show_cousins=$controller->show_cousins;
	echo '<div id="ancestry_chart">';
	// Booklet
	// first page : show indi facts
	print_pedigree_person($controller->root, 1, 1);
	// process the tree
	$treeid=ancestry_array($controller->root->getXref(), $PEDIGREE_GENERATIONS-1);
	foreach ($treeid as $i=>$pid) {
		if ($pid) {
			$person=KT_Person::getInstance($pid);
			if ($person) {
				foreach ($person->getChildFamilies() as $family) {
					print_sosa_family($family->getXref(), $pid, $i);
				}
			}
		}
	}
	echo '</div>';
	break;
case 2:
	// Individual list
	$treeid=ancestry_array($controller->root->getXref(), $PEDIGREE_GENERATIONS);
	echo '<div id="ancestry-list">';
	echo format_indi_table($treeid, 'sosa');
	echo '</div>';
	break;
case 3:
	// Family list
	$treeid=ancestry_array($controller->root->getXref(), $PEDIGREE_GENERATIONS-1);
	$famlist=array();
	foreach ($treeid as $pid) {
		$person=KT_Person::getInstance($pid);
		if (is_null($person)) {
			continue;
		}
		foreach ($person->getChildFamilies() as $famc) {
			$famlist[$famc->getXref()]=$famc;
		}
	}
	echo '<div id="ancestry-list">';
	echo format_fam_table($famlist, $controller->getPageTitle());
	echo '</div>';
	break;
}
echo '</div>'; // close #ancestry-page
