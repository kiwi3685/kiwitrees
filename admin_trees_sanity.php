<?php
// Check a family tree for structural errors.
//
// Note that the tests and error messages are not yet finalised.  Wait until the code has stabilised before
// adding I18N.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2015 kiwitrees.net
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

$controller=new WT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(WT_I18N::translate('Sanity check'))
	->pageHeader();
//	->addInlineJavascript('
//		jQuery("#sanity_accordion").accordion({
//			active: 0,
//			collapsible: true,
//			heightStyle: "content"
//		});
//	');
?>

<style>
	#sanity_check a {color: #75ABFF;}
	#sanity_check h5 {font-size: 1.0em;   margin: 0;}
	#sanity_check li {list-style-type: none;}
	#sanity_check .first  {display: inline-block; width: 300px;}
	#sanity_check .second {display: inline-block; width: 300px;}
	#sanity_check .third  {display: inline-block; width: 300px;}
	#sanity_check span.label {font-weight: 900; padding: 0 20px;}
	#sanity_accordion {border-top: 1px inset; margin: 10px auto; padding: 20px 0; width: 98%;}
	#sanity_accordion .result {  border: 1px inset #D3D3D3;   margin: 20px 10px; padding: 5px;}
</style>

<div id="sanity_check">
	<a class="current faq_link" href="http://kiwitrees.net/faqs/modules-faqs/sanity_check/" target="_blank" title="<?php echo WT_I18N::translate('View FAQ for this page.'); ?>"><?php echo WT_I18N::translate('View FAQ for this page.'); ?></a>
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<p class="warning">
		<?php echo WT_I18N::translate('This process can be slow. If you have a large family tree or suspect large numbers of errors you should only select a few checks each time.'); ?>
	</p>
	<form method="post" action="<?php echo WT_SCRIPT_NAME; ?>">
		<input type="hidden" name="go" value="1">
		<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM); ?>

		<ul>
			<li class="facts_value" name="baptised" id="baptised" >
				<input type="checkbox" name="baptised" value="baptised" 
					<?php if (WT_Filter::post('baptised')) echo ' checked="checked"'?>
				>
				<?php echo WT_I18N::translate('Birth after baptism or christening'); ?>
			</li>
			<li class="facts_value" name="died" id="died" >
				<input type="checkbox" name="died" value="died" 
					<?php if (WT_Filter::post('died')) echo ' checked="checked"'?>
				>
				<?php echo WT_I18N::translate('Birth after death or burial'); ?>
			</li>
			<li class="facts_value" name="sex" id="sex" >
				<input type="checkbox" name="sex" value="sex" 
					<?php if (WT_Filter::post('sex')) echo ' checked="checked"'?>
				>
				<?php echo WT_I18N::translate('No gender recorded (Note: this does not include gender recorded as unknown)'); ?>
			</li>
		</ul>

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
			if (WT_Filter::post('sex')) {
				$data = missing_tag('SEX');
				echo '
					<div class="result">
						<h5>' . WT_I18N::translate('%s with no gender recorded', $data['count']) . '</h5>
						<div>' . $data['html'] . '</div>
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
	foreach ($tag_array as $val) {
		$tags[]  = "'%1 " . $val . "%'";
		$tags2[] = "'%1 " . $val . " Y%'";
	}
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = WT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file`=? AND `i_gedcom` LIKE " . $tags[$i] . " AND `i_gedcom` NOT LIKE " . $tags2[$i] . ""
		)->execute(array(WT_GED_ID))->fetchAll();
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

function missing_tag($tag) {
	$html = '';
	$count = 0;
	$tags  = "'%1 " . $tag . "%'";
	$rows = WT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file`=? AND `i_gedcom` NOT LIKE " . $tags . ""
	)->execute(array(WT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person = WT_Person::getInstance($row->xref);
		$html .= '
			<p>
				<div class="first"><a href="'. $person->getHtmlUrl(). '" target="_blank">'. $person->getFullName(). '</a></div>
				<div class="second"><span class="label">' . WT_I18N::translate('Missing %s', WT_Gedcom_Tag::getLabel($tag)) . '</span></div>
			</p>';
		$count ++;
	}
	return array('html' => $html, 'count' => $count);

}