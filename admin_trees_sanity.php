<?php
// Check a family tree for structural errors.
//
// Note that the tests and error messages are not yet finalised.  Wait until the code has stabilised before
// adding I18N.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2006-2009 Greg Roach
//
// This program is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License or,
// at your discretion, any later version.
//
// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with this program; if not, write to the Free Software
// Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA
//
//$Id$

define('WT_SCRIPT_NAME', 'admin_trees_sanity.php');
require './includes/session.php';
require WT_ROOT.'includes/functions/functions_edit.php';
require WT_ROOT.'includes/functions/functions_print_facts.php';

$controller = new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Sanity check'))
	->pageHeader();
?>

<div id="sanity_check">
	<a class="current faq_link" href="http://kiwitrees.net/faqs/general/sanity-check/" target="_blank" title="<?php echo WT_I18N::translate('View FAQ for this page.'); ?>"><?php echo WT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<p class="warning">
		<?php echo WT_I18N::translate('This process can be slow. If you have a large family tree or suspect large numbers of errors you should only select a few checks each time.'); ?>
	</p>
	<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="go" value="1">
		<div class="admin_options">
			<div class="input">
				<label><?php echo WT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>
			</div>
		</div>
		<div id="sanity_options">
			<h4><?php echo WT_I18N::translate('Date discrepancies'); ?></h4>
			<ul>
				<li class="facts_value" name="baptised" id="baptised">
					<input type="checkbox" name="baptised" value="baptised"
						<?php if (WT_Filter::post('baptised')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth after baptism or christening'); ?>
				</li>
				<li class="facts_value" name="died" id="died">
					<input type="checkbox" name="died" value="died"
						<?php if (WT_Filter::post('died')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth after death or burial'); ?>
				</li>
				<li class="facts_value" name="buri" id="buri">
					<input type="checkbox" name="buri" value="buri"
						<?php if (WT_Filter::post('buri')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Burial before death'); ?>
				</li>
			</ul>
			<h4><?php echo WT_I18N::translate('Missing data'); ?></h4>
			<ul>
				<li class="facts_value" name="sex" id="sex" >
					<input type="checkbox" name="sex" value="sex"
						<?php if (WT_Filter::post('sex')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('No gender recorded'); ?>
				</li>
			</ul>
			<h4><?php echo WT_I18N::translate('Duplicated data'); ?></h4>
			<ul>
				<li class="facts_value" name="dupe_birt" id="dupe_birt" >
					<input type="checkbox" name="dupe_birt" value="dupe_birt"
						<?php if (WT_Filter::post('dupe_birt')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Birth'); ?>
				</li>
				<li class="facts_value" name="dupe_deat" id="dupe_deat" >
					<input type="checkbox" name="dupe_deat" value="dupe_deat"
						<?php if (WT_Filter::post('dupe_deat')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Death'); ?>
				</li>
				<li class="facts_value" name="dupe_sex" id="dupe_sex" >
					<input type="checkbox" name="dupe_sex" value="dupe_sex"
						<?php if (WT_Filter::post('dupe_sex')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Gender'); ?>
				</li>
				<li class="facts_value" name="dupe_name" id="dupe_name" >
					<input type="checkbox" name="dupe_name" value="dupe_name"
						<?php if (WT_Filter::post('dupe_name')) echo ' checked="checked"'?>
					>
					<?php echo WT_I18N::translate('Name'); ?>
				</li>
			</ul>
		</div>
		<button type="submit" class="btn btn-primary" >
			<i class="fa fa-check"></i>
			<?php echo $controller->getPageTitle(); ?>
		</button>
	</form>

	<?php
	if (!WT_Filter::post('go')) {
		exit;
	}
		?>

	<div id="sanity_accordion">
		<?php
			if (WT_Filter::post('baptised')) {
				$data = birth_comparisons(array('BAPM', 'CHR'));
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s born after baptism or christening', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('died')) {
				$data = birth_comparisons(array('DEAT'));
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s born after death or burial', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('buri')) {
				$data = death_comparisons(array('BURI'));
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s buried before death', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
					</div>';
			}
			if (WT_Filter::post('sex')) {
				$data = missing_tag('SEX');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with no gender recorded', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_deat')) {
				$data = duplicate_tag('DEAT');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated death record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_birt')) {
				$data = duplicate_tag('BIRT');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated birth record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_sex')) {
				$data = duplicate_tag('SEX');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with duplicated gender record', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
			if (WT_Filter::post('dupe_name')) {
				$data = identical_name();
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with identical name records', $data['count']) . '</h5>
						<ul>' . $data['html'] . '</ul>
					</div>';
			}
		?>
	</div>
</div> <!-- close sanity_check page div -->

<?php

// sanity functions
function birth_comparisons($tag_array) {
	$html = '';
	$count = 0;
	$tag_count = count($tag_array);
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = WT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%') AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')"
		)->execute(array(WT_GED_ID, $tag_array[$i], $tag_array[$i]))->fetchAll();
		foreach ($rows as $row) {
			$person			= WT_Person::getInstance($row->xref);
			$birth_date 	= $person->getBirthDate();
			$event			= $person->getFactByType($tag_array[$i]);
			if ($event) {
				$event_date = $event->getDate();
				$age_diff	= WT_Date::Compare($event_date, $birth_date);
				if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
					$html .= '
						<p>
							<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $person->getFullName(). '</a></div>
							<div class="second"><span class="label">' . WT_Gedcom_Tag::getLabel($tag_array[$i]) . '</span>' . $event_date->Display() . '</div>
							<div class="third"><span class="label">' . WT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
						</p>';
					$count ++;
				}
			}
		}
	}
	return array('html' => $html, 'count' => $count);
}

function death_comparisons($tag_array) {
	$html = '';
	$count = 0;
	$tag_count = count($tag_array);
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = WT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%') AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')"
		)->execute(array(WT_GED_ID, $tag_array[$i], $tag_array[$i]))->fetchAll();
		foreach ($rows as $row) {
			$person			= WT_Person::getInstance($row->xref);
			$death_date 	= $person->getDeathDate();
			$event			= $person->getFactByType($tag_array[$i]);
			if ($event) {
				$event_date = $event->getDate();
				$age_diff	= WT_Date::Compare($event_date, $death_date);
				if ($event_date->MinJD() && $death_date->MinJD() && ($age_diff < 0)) {
					$html .= '
						<p>
							<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $person->getFullName(). '</a></div>
							<div class="second"><span class="label">' . WT_Gedcom_Tag::getLabel($tag_array[$i]) . '</span>' . $event_date->Display() . '</div>
							<div class="third"><span class="label">' . WT_Gedcom_Tag::getLabel('DEAT') . '</span>' . $death_date->Display() . '</div>
						</p>';
					$count ++;
				}
			}
		}
	}
	return array('html' => $html, 'count' => $count);
}

function missing_tag($tag) {
	$html = '';
	$count = 0;
	$rows = WT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` NOT REGEXP CONCAT('\n[0-9] ' , ?)"
	)->execute(array(WT_GED_ID, $tag))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '<li><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $person->getFullName(). '</a></li>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);
}

function duplicate_tag($tag) {
	$html = '';
	$count = 0;
	$rows = WT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file`= ? AND `i_gedcom` REGEXP '(\n1 " . $tag . ")((.*\n.*)*)(\n1 " . $tag . ")(.*)'"
 	)->execute(array(WT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '<li><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $person->getFullName(). '</a></li>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);
}

function identical_name() {
	$html = '';
	$count = 0;
	$rows = WT_DB::prepare(
		"SELECT n_id AS xref, COUNT(*) as count  FROM `##name` WHERE `n_file`= ? AND `n_type`= 'NAME' GROUP BY `n_id`, `n_sort` HAVING COUNT(*) > 1 "
 	)->execute(array(WT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '<li><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $person->getFullName(). '</a></li>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);
}
