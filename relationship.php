<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2017 kiwitrees.net
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
 * along with Kiwitrees.  If not, see <http://www.gnu.org/licenses/>.
 */

define('WT_SCRIPT_NAME', 'relationship.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';

$max_recursion = intval(get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION'));

$controller = new WT_Controller_Relationship();
$pid1       = WT_Filter::get('pid1', WT_REGEX_XREF);
$pid2       = WT_Filter::get('pid2', WT_REGEX_XREF);
$show_full  = WT_Filter::getInteger('show_full', 0, 1, get_gedcom_setting(WT_GED_ID, 'PEDIGREE_FULL_DETAILS'));
$recursion  = WT_Filter::getInteger('recursion', 0, $max_recursion, 0);
$find		= WT_Filter::getInteger('find', 1, 7, 1);
$person1	= WT_Person::getInstance($pid1, $WT_TREE);
$person2	= WT_Person::getInstance($pid2, $WT_TREE);

$controller
	->addExternalJavascript(WT_AUTOCOMPLETE_JS_URL)
	->addInlineJavascript('autocomplete();');

if ($person1 && $person2) {
	$controller
		->setPageTitle(WT_I18N::translate(/* I18N: %s are individual’s names */ 'Relationships between %1$s and %2$s', $person1->getFullName(), $person2->getFullName()))
		->pageHeader();
	$paths = $controller->calculateRelationships($person1, $person2, $find, $recursion);

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
			<a href="module.php?mod=chart_relationship&amp;mod_action=admin_config" target="_blank" rel="noopener noreferrer">
				<i class="fa fa-cog"></i>
			</a>
		<?php } ?>
	</h2>
	<form name="people" method="get" action="?">
		<input type="hidden" name="ged" value="<?php echo WT_GEDCOM; ?>">
		<div id="row1">
			<div class="chart_options">
				<label for = "pid1"><?php echo WT_I18N::translate('Individual 1') ?></label>
				<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="pid1" id="pid1" value="<?php echo $pid1 ?>">
			</div>
			<div class="chart_options">
				<label for = "pid2"><?php echo WT_I18N::translate('Individual 2') ?></label>
				<input class="pedigree_form" data-autocomplete-type="INDI" type="text" name="pid2" id="pid2" value="<?php echo $pid2 ?>">
			</div>
			<div class="chart_options swap">
				<a href="#" onclick="var x = jQuery('#pid1').val(); jQuery('#pid1').val(jQuery('#pid2').val()); jQuery('#pid2').val(x); return false;"><?php echo /* I18N: Reverse the order of two individuals */ WT_I18N::translate('Swap individuals') ?></a>
			</div>
			<div class="chart_options">
				<label for = "show_full"><?php echo WT_I18N::translate('Show details') ?></label>
				<?php echo two_state_checkbox('show_full', $show_full); ?>
			</div>
			<button class="btn btn-primary show" type="submit">
				<i class="fa fa-eye"></i>
				<?php echo /* I18N: A button label. */ WT_I18N::translate('Show'); ?>
			</button>
		</div>
		<div id="row2">
			<?php if (boolval(get_gedcom_setting(WT_GED_ID, 'CHART_1'))): ?>
				<div class="chart_options block">
					<input type="radio" class="inline" id="chart1" name="find" value="1" <?php echo ($find === 1) ? 'checked' : ''; ?>>
					<label for = "chart1" class="inline"><?php echo WT_I18N::translate('Find a closest relationship via common ancestors'); ?></label>
				</div>
			<?php endif; ?>
			<?php if (boolval(get_gedcom_setting(WT_GED_ID, 'CHART_2'))): ?>
				<div class="chart_options block">
					<input type="radio" class="inline" id="chart2" name="find" value="2"<?php echo ($find === 2) ? 'checked' : ''; ?>>
					<label for = "chart2" class="inline"><?php echo WT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?></label>
				</div>
			<?php endif; ?>
			<?php if (get_gedcom_setting(WT_GED_ID, 'CHART_3')): ?>
				<div class="chart_options block">
					<input type="radio" class="inline" id="chart3" name="find" value="3"<?php echo ($find === 3) ? 'checked' : ''; ?>>
					<label for = "chart3" class="inline"><?php echo WT_I18N::translate('Find all relationships via lowest common ancestors'); ?></label>
				</div>
			<?php endif; ?>
			<?php if (get_gedcom_setting(WT_GED_ID, 'CHART_4')): ?>
				<div class="chart_options block">
					<input type="radio" class="inline" id="chart4" name="find" value="4"<?php echo ($find === 4) ? 'checked' : ''; ?>>
					<label for = "chart4" class="inline"><?php echo WT_I18N::translate('Find the closest overall connections (preferably via common ancestors)'); ?></label>
				</div>
			<?php endif; ?>
			<?php if (get_gedcom_setting(WT_GED_ID, 'CHART_7')): ?>
				<div class="chart_options block">
					<input type="radio" class="inline" id="chart7" name="find" value="7"<?php echo ($find === 7) ? 'checked' : ''; ?>>
					<label for = "chart7" class="inline"><?php echo WT_I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'); ?></label>
				</div>
			<?php endif; ?>
			<?php if ($max_recursion == 0): ?>
				<?php if (get_gedcom_setting(WT_GED_ID, 'CHART_5')): ?>
					<div class="chart_options block">
						<input type="radio" class="inline" id="chart5" name="find" value="5"<?php echo ($find === 5) ? 'checked' : ''; ?>>
						<label for = "chart5" class="inline"><?php echo WT_I18N::translate('Find the closest overall connections') ?>
					</div>
				<?php endif; ?>
			<?php else: ?>
				<?php if (get_gedcom_setting(WT_GED_ID, 'CHART_5')): ?>
					<div class="chart_options block">
						<input type="radio" class="inline" id="chart5" name="find" value="5"<?php echo ($find === 5) ? 'checked' : ''; ?>>
						<label for = "chart5" class="inline"><?php echo WT_I18N::translate('Find the closest overall connections') ?></label>
					</div>
				<?php endif; ?>
				<?php if (get_gedcom_setting(WT_GED_ID, 'CHART_6')): ?>
					<div class="chart_options block">
						<input type="radio" class="inline" id="chart6" name="find" value="6"<?php echo ($find === 6) ? 'checked' : ''; ?>>
						<input type="hidden" name="recursion" value="<?php echo $max_recursion ?>">
						<label for = "chart6" class="inline">
							<?php if ($max_recursion == '99'): ?>
								<?php echo WT_I18N::translate('Find all overall connections') ?>
							<?php else: ?>
								<?php echo WT_I18N::translate('Find other overall connections') ?>
							<?php endif; ?>
						</label>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		</div>
	</form>
	<hr>
	<?php

	if ($person1 && $person2) {
		if ($TEXT_DIRECTION=='ltr') {
			$horizontal_arrow = '<br><i class="icon-rarrow"></i>';
			$diagonal1        = $WT_IMAGES['dline'];
			$diagonal2        = $WT_IMAGES['dline2'];

		} else {
			$horizontal_arrow = '<br><i class="icon-larrow"></i>';
			$diagonal1        = $WT_IMAGES['dline2'];
			$diagonal2        = $WT_IMAGES['dline'];
		}
		$up_arrow   = ' <i class="icon-uarrow"></i>';
		$down_arrow = ' <i class="icon-darrow"></i>';

		if ($find == 3) {
			$cor = $controller->getCor($paths);
			echo '<h3>', WT_I18N::translate('Uncorrected CoR (Coefficient of Relationship): %s', WT_I18n::percentage($cor, 2)); ?>
			<div class="helpcontent">
				<?php echo /* I18N: Configuration option */ WT_I18N::translate('All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: <a href = "http://www.genetic-genealogy.co.uk/Toc115570135.html" target="_blank" rel="noopener noreferrer">Coefficient of Relationship</a>'); ?>
			</div>
			<?php
			echo WT_I18N::translate('(Number of relationships: %s)', count($paths)), '</h3>';
		}

		$num_paths = 0;
		foreach ($paths as $path) {
			// Extract the relationship names between pairs of individuals
			$relationships = $controller->oldStyleRelationshipPath($path);
			if (empty($relationships)) {
				// Cannot see one of the families/individuals, due to privacy;
				continue;
			}
			$num_paths++;
			echo '<h3>';
				if (count($paths) > 1) {
					echo '<a href="#" onclick="return expand_layer(\'rel_'.$num_paths.'\');" class="top">
						<i id="rel_'.$num_paths.'_img" class="icon-minus" title="', WT_I18N::translate('View Relationship'), '"></i>
					</a> ';
				}
				echo WT_I18N::translate('Relationship: %s', get_relationship_name_from_path(implode('', $relationships), $person1, $person2)), '
			</h3>';

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
							$table[$x + 1][$y - 1] = '<div style="background:url(' . $diagonal2 . ') center; width: 64px; height: 64px; text-align: center;"><div style="height: 32px; text-align: end;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="height: 32px; text-align: start;">' . $down_arrow . '</div></div>';
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
							$table[$x + 1][$y + 1] = '<div style="background:url(' . $diagonal1 . ') center; width: 64px; height: 64px; text-align: center;"><div style="height: 32px; text-align: start;">' . get_relationship_name_from_path($relationships[$n], WT_Person::getInstance($path[$n - 1], $WT_TREE), WT_Person::getInstance($path[$n + 1], $WT_TREE)) . '</div><div style="height: 32px; text-align: end;">' . $up_arrow . '</div></div>';
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
			echo '<table id="rel_'.$num_paths.'">';
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
			echo '</table><hr>';
		}

		if (!$num_paths) {
			echo '<p>', WT_I18N::translate('No link between the two individuals could be found.'), '</p>';
		}
	echo '</div>'; // close page
}
