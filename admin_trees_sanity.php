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
 * along with Kiwitrees. If not, see <http://www.gnu.org/licenses/>.
 */

define('KT_SCRIPT_NAME', 'admin_trees_sanity.php');
require './includes/session.php';
require KT_ROOT.'includes/functions/functions_edit.php';
require KT_ROOT.'includes/functions/functions_print_facts.php';
global $MAX_ALIVE_AGE;

$controller = new KT_Controller_Page();
$controller
	->requireManagerLogin()
	->setPageTitle(KT_I18N::translate('Sanity check'))
	->pageHeader()
	->addInlineJavascript('
		jQuery("#sanity_accordion").accordion({heightStyle: "content", collapsible: true, active: false, header: "h5"});
		jQuery("#sanity_accordion").css("visibility", "visible");
		jQuery(".loading-image").css("display", "none");
	');

	// default ages
	$bap_age	= 5;
	$oldage		= $MAX_ALIVE_AGE;
	$marr_age	= 14;
	$spouseage	= 30;
	$child_y	= 15;
	$child_o	= 50;

	if (KT_Filter::postBool('reset')) {
		set_gedcom_setting(KT_GED_ID, 'SANITY_BAPTISM', $bap_age);
		set_gedcom_setting(KT_GED_ID, 'SANITY_OLDAGE', $oldage);
		set_gedcom_setting(KT_GED_ID, 'SANITY_MARRIAGE', $marr_age);
		set_gedcom_setting(KT_GED_ID, 'SANITY_SPOUSE_AGE', $spouseage);
		set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_Y', $child_y);
		set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_O', $child_o);

		AddToLog($controller->getPageTitle() .' set to default values', 'config');
	}

	// save new values
	if (KT_Filter::postBool('save')) {
		set_gedcom_setting(KT_GED_ID, 'SANITY_BAPTISM',		KT_Filter::post('NEW_SANITY_BAPTISM', KT_REGEX_INTEGER, $bap_age));
		set_gedcom_setting(KT_GED_ID, 'SANITY_OLDAGE',		KT_Filter::post('NEW_SANITY_OLDAGE', KT_REGEX_INTEGER, $oldage));
		set_gedcom_setting(KT_GED_ID, 'SANITY_MARRIAGE',	KT_Filter::post('NEW_SANITY_MARRIAGE', KT_REGEX_INTEGER, $marr_age));
		set_gedcom_setting(KT_GED_ID, 'SANITY_SPOUSE_AGE',	KT_Filter::post('NEW_SANITY_SPOUSE_AGE', KT_REGEX_INTEGER, $spouseage));
		set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_Y',		KT_Filter::post('NEW_SANITY_CHILD_Y', KT_REGEX_INTEGER, $child_y));
		set_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_O',		KT_Filter::post('NEW_SANITY_CHILD_O', KT_REGEX_INTEGER, $child_o));

		AddToLog($controller->getPageTitle() .' set to new values', 'config');
	}

	// settings to use
	$bap_age	= get_gedcom_setting(KT_GED_ID, 'SANITY_BAPTISM');
	$oldage		= get_gedcom_setting(KT_GED_ID, 'SANITY_OLDAGE');
	$marr_age	= get_gedcom_setting(KT_GED_ID, 'SANITY_MARRIAGE');
	$spouseage	= get_gedcom_setting(KT_GED_ID, 'SANITY_SPOUSE_AGE');
	$child_y	= get_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_Y');
	$child_o	= get_gedcom_setting(KT_GED_ID, 'SANITY_CHILD_O');

?>

<div id="sanity_check">
	<a class="current faq_link" href="http://kiwitrees.net/faqs/general/sanity-check/" target="_blank" rel="noopener noreferrer" title="<?php echo KT_I18N::translate('View FAQ for this page.'); ?>"><?php echo KT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
	<h2><?php echo $controller->getPageTitle(); ?></h2>
	<p class="warning">
		<?php echo KT_I18N::translate('This process can be slow. If you have a large family tree or suspect large numbers of errors you should only select a few checks each time.<br>Options marked <span color"#ff0000">**</span> are often very slow.'); ?>
	</p>
	<form method="post" action="<?php echo KT_SCRIPT_NAME; ?>">
		<input type="hidden" name="save" value="1">
		<div class="admin_options">
			<div class="input">
				<label><?php echo KT_I18N::translate('Family tree'); ?></label>
				<?php echo select_edit_control('ged', KT_Tree::getNameList(), null, KT_GEDCOM); ?>
			</div>
		</div>
		<div id="sanity_options">
			<ul>
				<h3><?php echo KT_I18N::translate('Date discrepancies'); ?></h3>
				<li class="facts_value" name="baptised" id="baptised">
					<input type="checkbox" name="baptised" value="baptised"
						<?php if (KT_Filter::post('baptised')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Birth after baptism or christening'); ?>
				</li>
				<li class="facts_value" name="died" id="died">
					<input type="checkbox" name="died" value="died"
						<?php if (KT_Filter::post('died')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Birth after death or burial'); ?>
				</li>
				<li class="facts_value" name="birt_marr" id="birt_marr">
					<input type="checkbox" name="birt_marr" value="birt_marr"
						<?php if (KT_Filter::post('birt_marr')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Birth after marriage'); ?>
				</li>
				<li class="facts_value" name="birt_chil" id="birt_chil">
					<input type="checkbox" name="birt_chil" value="birt_chil"
						<?php if (KT_Filter::post('birt_chil')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Birth after their children'); ?>
				</li>
				<li class="facts_value" name="buri" id="buri">
					<input type="checkbox" name="buri" value="buri"
						<?php if (KT_Filter::post('buri')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Burial before death'); ?>
				</li>
			</ul>
			<ul>
				<h3><?php echo KT_I18N::translate('Age related queries'); ?></h3>
				<li class="facts_value" name="bap_late" id="bap_late">
					<input type="checkbox" name="bap_late" value="bap_late"
						<?php if (KT_Filter::post('bap_late')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Baptised after a certain age'); ?>
					<input name="NEW_SANITY_BAPTISM" id="bap_age" type="text" value="<?php echo $bap_age; ?>" >
				</li>
				<li class="facts_value" name="old_age" id="old_age">
					<input type="checkbox" name="old_age" value="old_age"
						<?php if (KT_Filter::post('old_age')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Alive after a certain age'); ?>
					<input name="NEW_SANITY_OLDAGE" id="oldage" type="text" value="<?php echo $oldage; ?>">
				</li>
				<li class="facts_value" name="marr_yng" id="marr_yng">
					<input type="checkbox" name="marr_yng" value="marr_yng"
						<?php if (KT_Filter::post('marr_yng')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Married before a certain age'); ?><span class="error">**</span>
					<input name="NEW_SANITY_MARRIAGE" id="marr_age" type="text" value="<?php echo $marr_age; ?>">
				</li>
				<li class="facts_value" name="spouse_age" id="spouse_age">
					<input type="checkbox" name="spouse_age" value="spouse_age"
						<?php if (KT_Filter::post('spouse_age')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Being much older than spouse'); ?>
					<input name="NEW_SANITY_SPOUSE_AGE" id="spouseage" type="text" value="<?php echo $spouseage; ?>">
				</li>
				<li class="facts_value" name="child_yng" id="child_yng">
					<input type="checkbox" name="child_yng" value="child_yng"
						<?php if (KT_Filter::post('child_yng')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Mothers having children before a certain age'); ?>
					<input name="NEW_SANITY_CHILD_Y" id="child_y" type="text" value="<?php echo $child_y; ?>">
				</li>
				<li class="facts_value" name="child_old" id="child_old">
					<input type="checkbox" name="child_old" value="child_old"
						<?php if (KT_Filter::post('child_old')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Mothers having children past a certain age'); ?>
					<input name="NEW_SANITY_CHILD_O" id="child_o" type="text" value="<?php echo $child_o; ?>">
				</li>
			</ul>
			<ul>
				<h3><?php echo KT_I18N::translate('Duplicated individual data'); ?></h3>
				<li class="facts_value" name="dupe_birt" id="dupe_birt" >
					<input type="checkbox" name="dupe_birt" value="dupe_birt"
						<?php if (KT_Filter::post('dupe_birt')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Birth'); ?>
				</li>
				<li class="facts_value" name="dupe_bapm" id="dupe_bapm" >
					<input type="checkbox" name="dupe_bapm" value="dupe_bapm"
						<?php if (KT_Filter::post('dupe_bapm')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Baptism or christening'); ?>
				</li>
				<li class="facts_value" name="dupe_deat" id="dupe_deat" >
					<input type="checkbox" name="dupe_deat" value="dupe_deat"
						<?php if (KT_Filter::post('dupe_deat')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Death'); ?>
				</li>
				<li class="facts_value" name="dupe_crem" id="dupe_crem" >
					<input type="checkbox" name="dupe_crem" value="dupe_crem"
						<?php if (KT_Filter::post('dupe_crem')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Cremation'); ?>
				</li>
				<li class="facts_value" name="dupe_buri" id="dupe_buri" >
					<input type="checkbox" name="dupe_buri" value="dupe_buri"
						<?php if (KT_Filter::post('dupe_buri')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Burial'); ?>
				</li>
				<li class="facts_value" name="dupe_sex" id="dupe_sex" >
					<input type="checkbox" name="dupe_sex" value="dupe_sex"
						<?php if (KT_Filter::post('dupe_sex')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Gender'); ?>
				</li>
				<li class="facts_value" name="dupe_name" id="dupe_name" >
					<input type="checkbox" name="dupe_name" value="dupe_name"
						<?php if (KT_Filter::post('dupe_name')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Name'); ?>
				</li>
			</ul>
			<ul>
				<h3><?php echo KT_I18N::translate('Duplicated family data'); ?></h3>
				<li class="facts_value" name="dupe_marr" id="dupe_marr" >
					<input type="checkbox" name="dupe_marr" value="dupe_marr"
						<?php if (KT_Filter::post('dupe_marr')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Marriage'); ?>
				</li>
				<li class="facts_value" name="dupe_child" id="dupe_child" >
					<input type="checkbox" name="dupe_child" value="dupe_child"
						<?php if (KT_Filter::post('dupe_child')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Families with duplicately named children'); ?>
				</li>
			</ul>
			<ul>
				<h3><?php echo KT_I18N::translate('Missing or invalid data'); ?></h3>
				<li class="facts_value" name="sex" id="sex" >
					<input type="checkbox" name="sex" value="sex"
						<?php if (KT_Filter::post('sex')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('No gender recorded'); ?>
				</li>
				<li class="facts_value" name="age" id="age" >
					<input type="checkbox" name="age" value="age"
						<?php if (KT_Filter::post('age')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Invalid age recorded'); ?>
				</li>
				<li class="facts_value" name="empty_tag" id="empty_tag" >
					<input type="checkbox" name="empty_tag" value="empty_tag"
						<?php if (KT_Filter::post('empty_tag')) echo ' checked="checked"'?>
					>
					<?php echo KT_I18N::translate('Empty individual fact or event'); ?><span class="error">**</span>
				</li>
			</ul>
		</div>
		<button type="submit" class="btn btn-primary clearfloat" >
			<i class="fa fa-check"></i>
			<?php echo $controller->getPageTitle(); ?>
		</button>
	</form>
	<form method="post" name="rela_form" action="#">
		<input type="hidden" name="reset" value="1">
		<button class="btn btn-primary reset" type="submit">
			<i class="fa fa-refresh"></i>
			<?php echo KT_I18N::translate('Reset'); ?>
		</button>
	</form>
	<hr class="clearfloat">

	<?php if (KT_Filter::post('save')) { ?>

		<div class="loading-image"></div>

		<div id="sanity_accordion" style="visibility: hidden;">
			<h3><?php echo KT_I18N::translate('Results'); ?></h3>
			<?php
			if (KT_Filter::post('baptised')) {
				$data = birth_comparisons(array('BAPM', 'CHR'));
				echo '<h5>' . KT_I18N::translate('%s born after baptism or christening', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('died')) {
				$data = birth_comparisons(array('DEAT'));
				echo '<h5>' . KT_I18N::translate('%s born after death or burial', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('birt_marr')) {
				$data = birth_comparisons(array('FAMS'), 'MARR');
				echo '<h5>' . KT_I18N::translate('%s born after marriage', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('birt_chil')) {
				$data = birth_comparisons(array('FAMS'), 'CHIL');
				echo '<h5>' . KT_I18N::translate('%s born after their children', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('buri')) {
				$data = death_comparisons(array('BURI'));
				echo '<h5>' . KT_I18N::translate('%s buried before death', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('bap_late')) {
				$data = query_age(array('BAPM', 'CHR'), $bap_age);
				echo '<h5>' . KT_I18N::translate('%1s baptised more than %2s years after birth', $data['count'], $bap_age) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('old_age')) {
				$data = query_age(array('DEAT'), $oldage);
				echo '<h5>' . KT_I18N::translate('%1s living and older than %2s years', $data['count'], $oldage) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('marr_yng')) {
				$data = query_age(array('MARR'), $marr_age);
				echo '<h5>' . KT_I18N::translate('%1s married younger than %2s years', $data['count'], $marr_age) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('spouse_age')) {
				$data = query_age(array('FAMS'), $spouseage);
				echo '<h5>' . KT_I18N::translate('%1s spouses with more than %2s years age difference', $data['count'], $spouseage) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('child_yng')) {
				$data = query_age(array('CHIL_1'), $child_y);
				echo '<h5>' . KT_I18N::translate('%1s women having children before age %2s years', $data['count'], $child_y) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('child_old')) {
				$data = query_age(array('CHIL_2'), $child_o);
				echo '<h5>' . KT_I18N::translate('%1s women having children after age %2s years', $data['count'], $child_o) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_birt')) {
				$data = duplicate_tag('BIRT');
				echo '<h5>' . KT_I18N::translate('%s with duplicate births recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_bapm')) {
				$data = duplicate_tag('BAPM');
				echo '<h5>' . KT_I18N::translate('%s with duplicate baptism or christenings recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_deat')) {
				$data = duplicate_tag('DEAT');
				echo '<h5>' . KT_I18N::translate('%s with duplicate deaths recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_crem')) {
				$data = duplicate_tag('CREM');
				echo '<h5>' . KT_I18N::translate('%s with duplicate cremations recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_buri')) {
				$data = duplicate_tag('BURI');
				echo '<h5>' . KT_I18N::translate('%s with duplicate burial or cremations recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_sex')) {
				$data = duplicate_tag('SEX');
				echo '<h5>' . KT_I18N::translate('%s with duplicate genders recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_name')) {
				$data = identical_name();
				echo '<h5>' . KT_I18N::translate('%s with duplicate names recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_marr')) {
				$data = duplicate_famtag('MARR');
				echo '<h5>' . KT_I18N::translate('%s with duplicate marriages recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('dupe_child')) {
				$data = duplicate_child();
				echo '<h5>' . KT_I18N::translate('%s with duplicately named children', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('sex')) {
				$data = missing_tag('SEX');
				echo '<h5>' . KT_I18N::translate('%s have no gender recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('age')) {
				$data = invalid_tag('AGE');
				echo '<h5>' . KT_I18N::translate('%s individuals or families have age incorrectly recorded', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
			if (KT_Filter::post('empty_tag')) {
				$data = empty_tag();
				echo '<h5>' . KT_I18N::translate('%s individuals with empty fact or event tags', $data['count']) . '
					<span>' . KT_I18N::translate('query time: %1s secs', $data['time']) . '</span>
				</h5>
				<div>' . $data['html'] . '</div>';
			}
		?>
	</div>
	<?php } ?>
</div> <!-- close sanity_check page div -->

<?php

// sanity functions
function birth_comparisons($tag_array, $tag2 = '') {
	$html = '';
	$count = 0;
	$tag_count = count($tag_array);
	$start = microtime(true);
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = KT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%') AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')"
		)->execute(array(KT_GED_ID, $tag_array[$i], $tag_array[$i]))->fetchAll();
		foreach ($rows as $row) {
			$person		= KT_Person::getInstance($row->xref);
			$birth_date = $person->getBirthDate();
			switch ($tag_array[$i]) {
				case ('FAMS'):
					switch ($tag2) {
						case 'MARR':
							foreach ($person->getSpouseFamilies() as $family) {
								$event_date	= $family->getMarriageDate();
								$age_diff	= KT_Date::Compare($event_date, $birth_date);
								if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
									$html .= '
										<p>
											<div class="first"><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></div>
											<div class="second"><span class="label">' . KT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
											<div class="third"><span class="label">' . KT_Gedcom_Tag::getLabel($tag2) . '</span>' . $event_date->Display() . '</div>
										</p>';
									$count ++;
								}
							}
						break;
						case 'CHIL':
							foreach ($person->getSpouseFamilies() as $family) {
								$children = $family->getChildren();
								foreach ($children as $child) {
									$event_date	= $child->getBirthDate();
									$age_diff	= KT_Date::Compare($event_date, $birth_date);
									if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
										$html .= '
											<p>
												<div class="first"><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></div>
												<div class="second"><span class="label">' . KT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
												<div class="third"><span class="label">' . KT_Gedcom_Tag::getLabel($tag2) . '<a href="' . $child->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $child->getFullName(). '</a>' . KT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $event_date->Display() . '</div>
											</p>';
										$count ++;
									}
								}
							}
						break;
					}
				break;
				case 'FAMC':
				break;
				default:
					$event = $person->getFactByType($tag_array[$i]);
					if ($event) {
						$event_date = $person->getFactByType($tag_array[$i])->getDate();
						$age_diff	= KT_Date::Compare($event_date, $birth_date);
						if ($event_date->MinJD() && $birth_date->MinJD() && ($age_diff < 0)) {
							$html .= '
								<p>
									<div class="first"><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></div>
									<div class="second"><span class="label">' . KT_Gedcom_Tag::getLabel('BIRT') . '</span>' . $birth_date->Display() . '</div>
									<div class="third"><span class="label">' . KT_Gedcom_Tag::getLabel($tag_array[$i]) . '</span>' . $event_date->Display() . '</div>
								</p>';
							$count ++;
						}
					}
				break;
			}
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function death_comparisons($tag_array) {
	$html		= '';
	$count		= 0;
	$tag_count	= count($tag_array);
	$start		= microtime(true);
	for ($i = 0; $i < $tag_count; $i ++) {
		$rows = KT_DB::prepare(
			"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` LIKE CONCAT('%1 ', ?, '%') AND `i_gedcom` NOT LIKE CONCAT('%1 ', ?, ' Y%')"
		)->execute(array(KT_GED_ID, $tag_array[$i], $tag_array[$i]))->fetchAll();
		foreach ($rows as $row) {
			$person		= KT_Person::getInstance($row->xref);
			$death_date = $person->getDeathDate();
			$event		= $person->getFactByType($tag_array[$i]);
			if ($event) {
				$event_date = $event->getDate();
				$age_diff	= KT_Date::Compare($event_date, $death_date);
				if ($event_date->MinJD() && $death_date->MinJD() && ($age_diff < 0)) {
					$html .= '
						<p>
							<div class="first"><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a></div>
							<div class="second"><span class="label">' . KT_Gedcom_Tag::getLabel($tag_array[$i]) . '</span>' . $event_date->Display() . '</div>
							<div class="third"><span class="label">' . KT_Gedcom_Tag::getLabel('DEAT') . '</span>' . $death_date->Display() . '</div>
						</p>';
					$count ++;
				}
			}
		}
	}
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function missing_tag($tag) {
	$html	= '<ul>';
	$count	= 0;
	$start	= microtime(true);
	$rows	= KT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` NOT REGEXP CONCAT('\n[0-9] ' , ?)"
	)->execute(array(KT_GED_ID, $tag))->fetchAll();
	foreach ($rows as $row) {
		$person = KT_Person::getInstance($row->xref);
		$html 	.= '
			<li>
				<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
			</li>';
		$count	++;
	}
	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function invalid_tag($tag) {
	$html	= '<ul>';
	$count	= 0;
	$start	= microtime(true);
	// Individuals
	$rows	= KT_DB::prepare(
		"SELECT i_id AS xref, i_gedcom AS gedrec FROM `##individuals` WHERE `i_file` = ? AND `i_gedcom` REGEXP CONCAT('[0-9] ', ?, ' [0-9]*\n') COLLATE utf8_bin"
	)->execute(array(KT_GED_ID, $tag))->fetchAll();
	foreach ($rows as $row) {
		$person = KT_Person::getInstance($row->xref);
		$html 	.= '
			<li>
				<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
			</li>';
		$count	++;
	}
	// Families (HUSB, WIFE)
 	$rows	= KT_DB::prepare(
		"SELECT f_id AS xref, f_gedcom AS gedrec FROM `##families` WHERE `f_file` = ? AND BINARY `f_gedcom` REGEXP CONCAT('[0-9] ', ?, ' [0-9]*\n') COLLATE utf8_bin"
	)->execute(array(KT_GED_ID, $tag))->fetchAll();
	foreach ($rows as $row) {
		$family = KT_Family::getInstance($row->xref);
		$html 	.= '
			<li>
				<a href="' . $family->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>
			</li>';
		$count	++;
	}
	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function duplicate_tag($tag) {
	$html	= '<ul>';
	$count	= 0;
	$start	= microtime(true);
	switch ($tag) {
		case 'BAPM' :
		case 'CHR' :
			$rows = KT_DB::prepare(
				"SELECT i_id AS xref FROM `##individuals` WHERE `i_file`= ? AND (
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'BAPM','%1 ', 'BAPM', '%')) OR
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'BAPM','%1 ', 'CHR', '%')) OR
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'CHR','%1 ', 'CHR', '%')) OR
					(`i_gedcom` LIKE BINARY CONCAT('%1 ', 'CHR','%1 ', 'BAPM', '%'))
				)"
			)->execute(array(KT_GED_ID))->fetchAll();
		break;
		default :
			$rows = KT_DB::prepare("SELECT i_id AS xref FROM `##individuals` WHERE `i_file`= ? AND `i_gedcom` LIKE BINARY CONCAT('%1 ', ?,'%1 ', ?, '%')"
			)->execute(array(KT_GED_ID, $tag, $tag))->fetchAll();
	}
	foreach ($rows as $row) {
		$person	= KT_Person::getInstance($row->xref);
		$html	.= '
			<li>
				<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
			</li>
		';
		$count	++;
	}
	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function duplicate_famtag($tag) {
	$html	= '<ul>';
	$count	= 0;
	$start	= microtime(true);
	$rows	= KT_DB::prepare("SELECT f_id AS xref FROM `##families` WHERE `f_file`= ? AND `f_gedcom` LIKE BINARY CONCAT('%1 ', ?,'%1 ', ?, '%')")->execute(array(KT_GED_ID, $tag, $tag))->fetchAll();

	foreach ($rows as $row) {
		$family	= KT_Family::getInstance($row->xref);
		$html	.= '
			<li>
				<a href="' . $family->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>
			</li>
		';
		$count	++;
	}
	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}


function duplicate_child() {
	$html	= '<ul>';
	$count	= 0;
	$start	= microtime(true);
	$rows	= KT_DB::prepare(
		"SELECT f_id AS xref FROM `##families` WHERE `f_file`= ? AND ROUND((LENGTH(`f_gedcom`) - LENGTH(REPLACE(`f_gedcom`, '1 CHIL @', '')))/LENGTH('1 CHIL @')) > 1"
	)->execute(array(KT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$names = array();
		$new_children = array();
		$family	= KT_Family::getInstance($row->xref);
		$children = $family->getChildren();
		foreach ($children as $child) {
			$names[]							= $child->getFullName();
			$new_children[$child->getXref()]	= $child->getFullName();
		}
		asort($new_children);
		if (count(array_unique($names)) < count($names)) {
			$single_names = array_diff($names, array_diff_assoc($names, array_unique($names)));
			$html .= '<li><a href="' . $family->getHtmlUrl() . '" target="_blank" rel="noopener noreferrer">' . $family->getFullName() . '</a>';
			foreach ($new_children as $xref => $name) {
			    if (!in_array($name, $single_names)) {
					$person	= KT_Person::getInstance($xref);
					$html	.= '<ul class="indent"><li>' . $person->getSexImage('small') . ' - ' . $person->getLifespanName() . '</li></ul>';
				}
			}
			$html	.= '</li>';
			$count	++;
		}
	}
	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function empty_tag() {
	global $emptyfacts;
	$html			= '<ul>';
	$count			= 0;
	$start			= microtime(true);
	$person_list	= array();
	$rows			= KT_DB::prepare( "SELECT i_id AS xref FROM `##individuals` WHERE `i_file` = ?" )->execute(array(KT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person		= KT_Person::getInstance($row->xref);
		$indifacts	= $person->getIndiFacts();
		$tag_list	= array();
		foreach ($indifacts as $key=>$value) {
			$fact	= $value->getDetail();
			$tag	= $value->getTag();
			if (!in_array($tag, $emptyfacts) && $fact == '') {
				$tag_list[] = $tag;
				$tag_count = array_count_values($tag_list)[$tag];
				if (!in_array($person->getXref(), $person_list)) {
					$count	++;
					$person_list[] = $person->getXref();
					$html .= '<li><a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>';
				}
				$html .= '<ul class="indent">';
					if ($tag_count == 1) {
						$html .= '<li><span>' . KT_I18N::translate('One or more empty %s tags ', $tag) . '</span></li>';
					}
				$html .= '</ul>';
				$html .= '</li>';

			}
		}
	}
	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function identical_name() {
	$html	= '<ul>';
	$count	= 0;
	$start	= microtime(true);
	$rows	= KT_DB::prepare(
		"SELECT n_id AS xref, COUNT(*) as count  FROM `##name` WHERE `n_file`= ? AND `n_type`= 'NAME' GROUP BY `n_id`, `n_sort` HAVING COUNT(*) > 1 "
 	)->execute(array(KT_GED_ID))->fetchAll();
	foreach ($rows as $row) {
		$person	= KT_Person::getInstance($row->xref);
		$html	.= '
			<li>
				<a href="' . $person->getHtmlUrl(). '" target="_blank" rel="noopener noreferrer">' . $person->getFullName() . '</a>
			</li>
		';
		$count	++;
	}

	$html .= '</ul>';
	$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}

function query_age($tag_array, $age) {
	$html		= '<ul>';
	$count		= 0;
	$tag_count	= count($tag_array);
	$start		= microtime(true);
	for ($i = 0; $i < $tag_count; $i ++) {
		switch ($tag_array[$i]) {
			case ('DEAT'):
				$sql = "
					SELECT SQL_CACHE
					 birth.d_gid AS xref,
					 YEAR(NOW()) - birth.d_year AS age,
					 birth.d_year AS birthyear
					FROM
					 `##dates` AS birth,
					 `##individuals` AS indi
					WHERE
					 indi.i_id = birth.d_gid AND
					 indi.i_gedcom NOT REGEXP '\\n1 (" . KT_EVENTS_DEAT . ")' AND
					 birth.d_file = ? AND
					 birth.d_fact = 'BIRT' AND
					 birth.d_file = indi.i_file AND
					 birth.d_julianday1 <> 0 AND
					 YEAR(NOW()) - birth.d_year > ?
					GROUP BY xref, birthyear
					ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array(KT_GED_ID, $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('MARR'):
				$sql = "
					SELECT SQL_CACHE
					 birth.d_gid AS xref,
					 married.d_year AS marryear,
					 married.d_year - birth.d_year AS age
					 FROM `##families` AS fam
					 INNER JOIN `##dates` AS birth ON birth.d_file = ?
					 INNER JOIN `##dates` AS married ON married.d_file = ?
					 WHERE
						fam.f_file = ? AND
						married.d_gid = fam.f_id AND
						(birth.d_gid = fam.f_wife OR birth.d_gid = fam.f_HUSB) AND
						birth.d_fact = 'BIRT' AND
						married.d_fact = 'MARR' AND
						birth.d_julianday1 <> 0 AND
						married.d_julianday2 > birth.d_julianday1 AND
						married.d_year - birth.d_year < ?
					GROUP BY xref, marryear, birth.d_year
					ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array(KT_GED_ID, KT_GED_ID, KT_GED_ID, $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('FAMS'):
				$sql = "
					SELECT SQL_CACHE
					 fam.f_id AS xref,
					 MIN(wifebirth.d_year-husbbirth.d_year) AS age
					 FROM `##families` AS fam
					 LEFT JOIN `##dates` AS wifebirth ON wifebirth.d_file = ?
					 LEFT JOIN `##dates` AS husbbirth ON husbbirth.d_file = ?
					 WHERE
						fam.f_file = ? AND
						husbbirth.d_gid = fam.f_husb AND
						husbbirth.d_fact = 'BIRT' AND
						wifebirth.d_gid = fam.f_wife AND
						wifebirth.d_fact = 'BIRT' AND
						husbbirth.d_julianday1 <> 0 AND
						wifebirth.d_year-husbbirth.d_year > ?
					 GROUP BY xref
					 ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array(KT_GED_ID, KT_GED_ID, KT_GED_ID, $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('CHIL_1'):
				$sql = "
					SELECT SQL_CACHE
					 parentfamily.l_to AS xref,
					 childfamily.l_to AS xref2,
					 MIN(childbirth.d_julianday2)-MIN(birth.d_julianday1) AS age,
					 MIN(birth.d_year) as dob
					 FROM `##link` AS parentfamily
					 JOIN `##link` AS childfamily ON childfamily.l_file = ?
					 JOIN `##dates` AS birth ON birth.d_file = ?
					 JOIN `##dates` AS childbirth ON childbirth.d_file = ?
					 WHERE
						birth.d_gid = parentfamily.l_to AND
						childfamily.l_to = childbirth.d_gid AND
						childfamily.l_type = 'CHIL' AND
						parentfamily.l_type = 'WIFE' AND
						childfamily.l_from = parentfamily.l_from AND
						parentfamily.l_file = ? AND
						birth.d_fact = 'BIRT' AND
						childbirth.d_fact = 'BIRT' AND
						birth.d_julianday1 <> 0 AND
						childbirth.d_julianday2-birth.d_julianday1 < ?
					GROUP BY xref, xref2
					ORDER BY age ASC
				";
				$rows		= KT_DB::prepare($sql)->execute(array(KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID, ($age * 365.25)))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			case ('CHIL_2'):
				$sql = "
					SELECT SQL_CACHE
					 parentfamily.l_to AS xref,
					 childfamily.l_to AS xref2,
					 MIN(childbirth.d_julianday2)-MIN(birth.d_julianday1) AS age,
					 MIN(birth.d_year) as dob
					 FROM `##link` AS parentfamily
					 JOIN `##link` AS childfamily ON childfamily.l_file = ?
					 JOIN `##dates` AS birth ON birth.d_file = ?
					 JOIN `##dates` AS childbirth ON childbirth.d_file = ?
					 WHERE
						birth.d_gid = parentfamily.l_to AND
						childfamily.l_to = childbirth.d_gid AND
						childfamily.l_type = 'CHIL' AND
						parentfamily.l_type = 'WIFE' AND
						childfamily.l_from = parentfamily.l_from AND
						parentfamily.l_file = ? AND
						birth.d_fact = 'BIRT' AND
						childbirth.d_fact = 'BIRT' AND
						birth.d_julianday1 <> 0 AND
						childbirth.d_julianday2-birth.d_julianday1 > ?
					GROUP BY xref, xref2
					ORDER BY age ASC
				";
				$rows		= KT_DB::prepare($sql)->execute(array(KT_GED_ID, KT_GED_ID, KT_GED_ID, KT_GED_ID, ($age * 365.25)))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
			default:
				$sql = "
					SELECT SQL_CACHE
					 tag.d_gid AS xref,
					 birth.d_year AS birtyear,
					 tag.d_year - birth.d_year AS age
					 FROM
						 `##dates` AS tag,
						 `##dates` AS birth
					 WHERE
						 birth.d_gid = tag.d_gid AND
						 tag.d_file = ? AND
						 birth.d_file = tag.d_file AND
						 birth.d_fact = 'BIRT' AND
						 tag.d_fact = ? AND
						 birth.d_julianday1 <> 0 AND
						 tag.d_julianday1 > birth.d_julianday2 AND
						 tag.d_year-birth.d_year > ?
					 GROUP BY xref, birtyear, tag.d_year
					 ORDER BY age DESC
				";
				$rows		= KT_DB::prepare($sql)->execute(array(KT_GED_ID, $tag_array[$i], $age))->fetchAll();
				$result_tag	= $tag_array[$i];
			break;
		}
		$link_url = $link_name = $result = false;

		foreach ($rows as $row) {
			switch ($result_tag) {
				case 'DEAT';
					$person = KT_Person::getInstance($row->xref);
					if ($person && !$person->getAllDeathDates()) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result 	= KT_I18N::translate('born in %1s, now aged %2s years', $row->birthyear, $row->age);
					}
					break;
				case 'MARR';
					$person = KT_Person::getInstance($row->xref);
					if ($person) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result 	= KT_I18N::translate('married in %1s at age %2s years', $row->marryear, $row->age);
					}
					break;
				case 'FAMS';
					$family = KT_Family::getInstance($row->xref);
					if ($family) {
						$link_url	= $family->getHtmlUrl();
						$link_name	= $family->getFullName();
						$result 	= KT_I18N::translate('Age difference =  %1s years', $row->age);
					}
					break;
				case 'CHIL_1';
					$person = KT_Person::getInstance($row->xref);
					$person2 = KT_Person::getInstance($row->xref2);
					if ($person && $person2) {
						$link_url	= $person->getHtmlUrl();
						$link_url2	= $person2->getHtmlUrl();
						$link_name	= $person->getFullName();
						$link_name2	= $person2->getFullName();
						$child		= '<a href="' . $link_url2. '" target="_blank" rel="noopener noreferrer">' . $link_name2 . '</a>';
						$result 	= KT_I18N::translate('gave birth before age %1s years to %2s in %3s', (int)($row->age / 365.25), $child, $row->dob);
					}
					break;
					case 'CHIL_2';
						$person = KT_Person::getInstance($row->xref);
						$person2 = KT_Person::getInstance($row->xref2);
						if ($person && $person2) {
							$link_url	= $person->getHtmlUrl();
							$link_url2	= $person2->getHtmlUrl();
							$link_name	= $person->getFullName();
							$link_name2	= $person2->getFullName();
							$child		= '<a href="' . $link_url2. '" target="_blank" rel="noopener noreferrer">' . $link_name2 . '</a>';
							$result 	= KT_I18N::translate('gave birth after age %1s years to %2s in %3s', (int)($row->age / 365.25), $child, $row->dob);
						}
						break;
				case 'BAPM';
					$person = KT_Person::getInstance($row->xref);
					if ($person) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result 	= KT_I18N::translate('born in %1s, baptised at age %2s years', $row->birtyear, $row->age);
					}
					break;
				case 'CHR';
					$person = KT_Person::getInstance($row->xref);
					if ($person) {
						$link_url	= $person->getHtmlUrl();
						$link_name	= $person->getFullName();
						$result 	= KT_I18N::translate('born in %1s, christened at age %2s years', $row->birtyear, $row->age);
					}
					break;
			}
				if ($link_url && $link_name && $result) {
					$html .= '
						<li>
							<a href="' . $link_url. '" target="_blank" rel="noopener noreferrer">' . $link_name. '</a>
							<span class="details"> ' . $result . '</span>
						</li>';
					$count ++;
				}
		}
		$html .= '</ul>';
		$time_elapsed_secs = number_format((microtime(true) - $start), 2);
	}
	return array('html' => $html, 'count' => $count, 'time' => $time_elapsed_secs);
}
