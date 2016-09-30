<?php
 // Calculates the relationship between two individuals in the gedcom
 //
 // Kiwitrees: Web based Family History software
 // Copyright (C) 2016 kiwitrees.net
 //
 // Derived from webtrees
 // Copyright (C) 2016 webtrees development team
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

define('WT_SCRIPT_NAME', 'relationship.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$max_recursion	= get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION');
$ancestors_only	= get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_ANCESTORS');

$controller = new WT_Controller_Relationship();
$pid1       = WT_Filter::get('pid1', WT_REGEX_XREF);
$pid2       = WT_Filter::get('pid2', WT_REGEX_XREF);
$show_full  = WT_Filter::getInteger('show_full', 0, 1, get_gedcom_setting(WT_GED_ID, 'PEDIGREE_FULL_DETAILS'));
$recursion  = WT_Filter::getInteger('recursion', 0, $max_recursion, 0);
$ancestors  = WT_Filter::getInteger('ancestors', 0, 1, 0);
$person1	= WT_Person::getInstance($pid1, $WT_TREE);
$person2	= WT_Person::getInstance($pid2, $WT_TREE);

$controller
	->addExternalJavascript(WT_STATIC_URL . 'js/autocomplete.js')
	->addInlineJavascript('autocomplete();');

if ($person1 && $person2) {
	$controller
		->setPageTitle(WT_I18N::translate(/* I18N: %s are individual’s names */ 'Relationships between %1$s and %2$s', $person1->getFullName(), $person2->getFullName()))
		->pageHeader();
	$paths = $controller->calculateRelationships($person1, $person2, $recursion, (bool) $ancestors);
} else {
	$controller
		->setPageTitle(WT_I18N::translate('Relationships'))
		->pageHeader();
	$paths = array();
}

?>

<div id="relationship-page">
	<h2>
		<?php echo $controller->getPageTitle() ?>
		<?php if (WT_USER_IS_ADMIN) { ?>
			<a href="<?php echo WT_STATIC_URL; ?>admin_trees_config.php?view=layout-options&amp;ged=<?php echo WT_GEDCOM; ?>#relationships_bookmark" target="_blank">
				<i class="fa fa-cog"></i>
			</a>
		<?php } ?>
	</h2>
	<form name="people" method="get" action="?">
		<input type="hidden" name="ged" value="<?php echo WT_GEDCOM; ?>">
		<div class="chart_options">
			<label for = "pid1"><?php echo WT_I18N::translate('Individual 1') ?></label>
			<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="pid1" id="pid1" size="3" value="<?php echo $pid1 ?>">
		</div>
		<div class="chart_options">
			<label for = "pid2"><?php echo WT_I18N::translate('Individual 2') ?></label>
			<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="pid2" id="pid2" size="3" value="<?php echo $pid2 ?>">
		</div>
		<div class="chart_options swap">
			<a href="#" onclick="var x = jQuery('#pid1').val(); jQuery('#pid1').val(jQuery('#pid2').val()); jQuery('#pid2').val(x); return false;"><?php echo /* I18N: Reverse the order of two individuals */ WT_I18N::translate('Swap individuals') ?></a>
		</div>
		<div class="chart_options">
			<label for = "show_full"><?php echo WT_I18N::translate('Show details') ?></label>
			<?php echo two_state_checkbox('show_full', $show_full); ?>
		</div>
		<div class="chart_options">
			<?php if ($ancestors_only === '1'): ?>
				<label for = "ancestors" class="inline"><?php echo WT_I18N::translate('Find relationships via ancestors') ?></label>
				<input type="hidden" name="ancestors" value="1">
			<?php else: ?>
				<input class="inline" type="radio" name="ancestors" value="0" <?php echo $ancestors == 0 ? 'checked' : '' ?>>
				<label class="inline"><?php echo WT_I18N::translate('Find any relationship') ?></label>
				<br><br>
				<input class="inline" type="radio" name="ancestors" value="1" <?php echo $ancestors == 1 ? 'checked' : '' ?>>
				<label class="inline"><?php echo WT_I18N::translate('Find relationships via ancestors') ?></label>
			<?php endif; ?>
		</div>
		<div class="chart_options">
			<?php if ($max_recursion == 0): ?>
				<label for = "ancestors" class="inline"><?php echo WT_I18N::translate('Find the closest relationships') ?></label>
				<input type="hidden" name="recursion" value="0">
			<?php else: ?>
				<label>
					<input class="inline" type="radio" name="recursion" value="0" <?php echo $recursion == 0 ? 'checked' : '' ?>>
					<?php echo WT_I18N::translate('Find the closest relationships') ?>
				</label>
				<br>
				<input class="inline" type="radio" name="recursion" value="<?php echo $max_recursion ?>" <?php echo $recursion > 0 ? 'checked' : '' ?>>
				<?php if ($max_recursion == 99): ?>
					<label class="inline"><?php echo WT_I18N::translate('Find all possible relationships') ?></label>
				<?php else: ?>
					<label class="inline"><?php echo WT_I18N::translate('Find other relationships') ?></label>
				<?php endif; ?>
			<?php endif; ?>
		</div>
		<button class="btn btn-primary show" type="submit">
			<i class="fa fa-eye"></i>
			<?php echo /* I18N: A button label. */ WT_I18N::translate('Show'); ?>
		</button>
	</form>
	<hr>
	<?php

	if ($person1 && $person2) {
		if ($TEXT_DIRECTION=='ltr') {
			$horizontal_arrow = '<br><i class="icon-rarrow"></i>';
			$diagonal1        = $WT_IMAGES['dline'];
			$diagonal1        = $WT_IMAGES['dline2'];

		} else {
			$horizontal_arrow = '<br><i class="icon-larrow"></i>';
			$diagonal1        = $WT_IMAGES['dline2'];
			$diagonal2        = $WT_IMAGES['dline'];
		}
		$up_arrow   = ' <i class="icon-uarrow"></i>';
		$down_arrow = ' <i class="icon-darrow"></i>';

		$num_paths = 0;
		foreach ($paths as $path) {
			// Extract the relationship names between pairs of individuals
			$relationships = $controller->oldStyleRelationshipPath($path);
			if (empty($relationships)) {
				// Cannot see one of the families/individuals, due to privacy;
				continue;
			}
			echo '<h3>', WT_I18N::translate('Relationship: %s', get_relationship_name_from_path(implode('', $relationships), $person1, $person2)), '</h3>';
			$num_paths++;

			// Use a table/grid for layout.
			$table = array();
			// Current position in the grid.
			$x     = 0;
			$y     = 0;
			// Extent of the grid.
			$min_y = 0;
			$max_y = 0;
			$max_x = 0;
			// For each node in the path.
			foreach ($path as $n => $xref) {
				if ($n % 2 === 1) {
					switch ($relationships[$n]) {
					case 'hus':
					case 'wif':
					case 'spo':
					case 'bro':
					case 'sis':
					case 'sib':
						$table[$x + 1][$y] = '<div style="background:url(' . $WT_IMAGES['hline'] . ') repeat-x center;  width: 94px; text-align: center"><div class="hline-text" style="height: 32px;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="height: 32px;">' . $horizontal_arrow . '</div></div>';
						$x += 2;
						break;
					case 'son':
					case 'dau':
					case 'chi':
						if ($n > 2 && preg_match('/fat|mot|par/', $relationships[$n - 2])) {
							$table[$x + 1][$y - 1] = '<div style="background:url(' . $diagonal2 . '); width: 64px; height: 64px; text-align: center;"><div style="height: 32px; text-align: end;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="height: 32px; text-align: start;">' . $down_arrow . '</div></div>';
							$x += 2;
						} else {
							$table[$x][$y - 1] = '<div style="background:url(' . $WT_IMAGES['vline'] . ') repeat-y center; height: 64px; text-align: center;"><div class="vline-text" style="display: inline-block; width:50%; line-height: 64px;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="display: inline-block; width:50%; line-height: 64px;">' . $down_arrow . '</div></div>';
						}
						$y -= 2;
						break;
					case 'fat':
					case 'mot':
					case 'par':
						if ($n > 2 && preg_match('/son|dau|chi/', $relationships[$n - 2])) {
							$table[$x + 1][$y + 1] = '<div style="background:url(' . $diagonal1 . '); background-position: top right; width: 64px; height: 64px; text-align: center;"><div style="height: 32px; text-align: start;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="height: 32px; text-align: end;">' . $up_arrow . '</div></div>';
							$x += 2;
						} else {
							$table[$x][$y + 1] = '<div style="background:url(' . $WT_IMAGES['vline'] . ') repeat-y center; height: 64px; text-align:center; "><div class="vline-text" style="display: inline-block; width: 50%; line-height: 32px;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="display: inline-block; width: 50%; line-height: 32px">' . $up_arrow . '</div></div>';
						}
						$y += 2;
						break;
					}
					$max_x = max($max_x, $x);
					$min_y = min($min_y, $y);
					$max_y = max($max_y, $y);
				} else {
					$individual = WT_Person::getInstance($xref, $WT_TREE);
					ob_start();
					print_pedigree_person($individual, $show_full);
					$table[$x][$y] = ob_get_clean();
				}
			}
			echo '<table id="relationship_chart">';
			for ($y = $max_y; $y >= $min_y; --$y) {
				echo '<tr>';
				for ($x = 0; $x <= $max_x; ++$x) {
					echo '<td style="padding: 0;">';
					if (isset($table[$x][$y])) {
						echo $table[$x][$y];
					}
					echo '</td>';
				}
				echo '</tr>';
			}
			echo '</table>';
		}

		if (!$num_paths) {
			echo '<p>', WT_I18N::translate('No link between the two individuals could be found.'), '</p>';
		}
	echo '</div>'; // close page
}
