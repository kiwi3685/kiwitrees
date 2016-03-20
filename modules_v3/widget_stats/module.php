<?php
// Classes and libraries for module system
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2010 John Finlay
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

if (!defined('WT_WEBTREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class widget_stats_WT_Module extends WT_Module implements WT_Module_Widget {
	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Statistics');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Statistics” module */ WT_I18N::translate('The size of the family tree, earliest and latest events, common names, etc.');
	}

	// Implement class WT_Module_Block
	public function getWidget($widget_id, $template=true, $cfg=null) {

		$show_last_update		= get_block_setting($widget_id, 'show_last_update',     true);
		$stat_indi				= get_block_setting($widget_id, 'stat_indi',            true);
		$stat_fam				= get_block_setting($widget_id, 'stat_fam',             true);
		$stat_sour				= get_block_setting($widget_id, 'stat_sour',            true);
		$stat_media				= get_block_setting($widget_id, 'stat_media',           true);
		$stat_repo				= get_block_setting($widget_id, 'stat_repo',            true);
		$stat_surname			= get_block_setting($widget_id, 'stat_surname',         true);
		$stat_events			= get_block_setting($widget_id, 'stat_events',          true);
		$stat_users				= get_block_setting($widget_id, 'stat_users',           true);
		$stat_first_birth		= get_block_setting($widget_id, 'stat_first_birth',     true);
		$stat_last_birth		= get_block_setting($widget_id, 'stat_last_birth',      true);
		$stat_first_death		= get_block_setting($widget_id, 'stat_first_death',     true);
		$stat_last_death		= get_block_setting($widget_id, 'stat_last_death',      true);
		$stat_long_life			= get_block_setting($widget_id, 'stat_long_life',       true);
		$stat_avg_life			= get_block_setting($widget_id, 'stat_avg_life',        true);
		$stat_most_chil			= get_block_setting($widget_id, 'stat_most_chil',       true);
		$stat_avg_chil			= get_block_setting($widget_id, 'stat_avg_chil',        true);
		$stat_link				= get_block_setting($widget_id, 'stat_link',            true);
		if ($cfg) {
			foreach (array('stat_indi', 'stat_fam', 'stat_sour', 'stat_media', 'stat_surname', 'stat_events', 'stat_users', 'stat_first_birth', 'stat_last_birth', 'stat_first_death', 'stat_last_death', 'stat_long_life', 'stat_avg_life', 'stat_most_chil', 'stat_avg_chil', 'stat_link') as $name) {
				if (array_key_exists($name, $cfg)) {
					$$name=$cfg[$name];
				}
			}
		}

		$id=$this->getName().$widget_id;
		$class=$this->getName();

		if (WT_USER_GEDCOM_ADMIN) {
			$title='<i class="icon-admin" title="'.WT_I18N::translate('Configure').'" onclick="modalDialog(\'block_edit.php?block_id='.$widget_id.'\', \''.$this->getTitle().'\');"></i>';
		} else {
			$title='';
		}
		$title.=$this->getTitle();
		$stats=new WT_Stats(WT_GEDCOM);

		$content = '<h3>'.WT_TREE_TITLE.'</h3>';
		if ($show_last_update) {
			$content .= '<div>'./* I18N: %s is a date */ WT_I18N::translate('This family tree was last updated on %s.', strip_tags($stats->gedcomUpdated())).'</div>';
		}
		$content .= '<ul>';
			if ($stat_indi) {
				$content.='
					<li>
						<span class="inset">'.WT_I18N::translate('Individuals').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data"><a href="'."indilist.php?surname_sublist=no&amp;ged=".WT_GEDURL.'">'.$stats->totalIndividuals().'</a></span>
					</li>
					<li>
						<span class="inset">'.WT_I18N::translate('Males').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">'.$stats->totalSexMales() . ' (' . $stats->totalSexMalesPercentage().')</span>
					</li>
					<li>
						<span class="inset">'.WT_I18N::translate('Females').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">'.$stats->totalSexFemales() . ' (' . $stats->totalSexFemalesPercentage().')</span>
					</li>
				';
			}
			if ($stat_surname) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Total surnames').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data"><a href="indilist.php?show_all=yes&amp;surname_sublist=yes&amp;ged='.WT_GEDURL.'">'.$stats->totalSurnames().'</a></span>
					</li>
				';
			}
			if ($stat_fam) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Families').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data"><a href="famlist.php?ged='.WT_GEDURL.'">'.$stats->totalFamilies().'</a></span>
					</li>
				';
			}
			if ($stat_sour) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Sources').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data"><a href="sourcelist.php?ged='.WT_GEDURL.'">'.$stats->totalSources().'</a></span>
					</li>
				';
			}
			if ($stat_media) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Media objects').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data"><a href="medialist.php?ged='.WT_GEDURL.'">'.$stats->totalMedia().'</a></span>
					</li>
				';
			}
			if ($stat_repo) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Repositories').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data"><a href="repolist.php?ged='.WT_GEDURL.'">'.$stats->totalRepositories().'</a></span>
					</li>
				';
			}
			if ($stat_events) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Total events').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">'.$stats->totalEvents().'</span>
					</li>
				';
			}
			if ($stat_users) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Total users').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">';
							if (WT_USER_GEDCOM_ADMIN) {
								$content .= '<a href="admin_users.php">'.$stats->totalUsers().'</a>';
							} else {
								$content .= $stats->totalUsers();
							}
						$content .= '</span>
					</li>
				';
			}
			if ($stat_first_birth) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Earliest birth year').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">'.$stats->firstBirthYear().'</span>
						<p class="stats_record">'.$stats->firstBirth().'</p>
					</li>
				';
			}
			if ($stat_last_birth) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Latest birth year').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">'.$stats->lastBirthYear().'</span>
						<p class="stats_record">'.$stats->lastBirth().'</p>
					</li>
				';
			}
			if ($stat_first_death) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Earliest death year').'</span>
						<span class="filler">&nbsp;</span>
						<span class="stats_data">'.$stats->firstDeathYear().'</span>
						<p class="stats_record">'.$stats->firstDeath().'</p>
					</li>
				';
			}
			if ($stat_last_death) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Latest death year').'</span>
						<span class="filler">&nbsp;</span><span class="stats_data">'.$stats->lastDeathYear().' </span>
						<p class="stats_record">'.$stats->lastDeath().'</p>
					</li>
				';
			}
			if ($stat_long_life) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Person who lived the longest').'</span>
						<span class="filler">&nbsp;</span><span class="stats_data">'.$stats->LongestLifeAge().'</span>
						<p class="stats_record">'.$stats->LongestLife().'</p>
					</li>
				';
			}
			if ($stat_avg_life) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Average age at death').'</span>
						<span class="filler">&nbsp;</span><span class="stats_data">'.$stats->averageLifespan().'</span>
						<p class="stats_record">'.WT_I18N::translate('Males').':&nbsp;'.$stats->averageLifespanMale().'&nbsp;&nbsp;&nbsp;'.WT_I18N::translate('Females').':&nbsp;'.$stats->averageLifespanFemale().'</p>
					</li>
				';
			}
			if ($stat_most_chil) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Family with the most children').'</span>
						<span class="filler">&nbsp;</span><span class="stats_data">'.$stats->largestFamilySize().'</span>
						<p class="stats_record">'.$stats->largestFamily().'</p>
					</li>
				';
			}
			if ($stat_avg_chil) {
				$content .= '
					<li>
						<span class="inset">'.WT_I18N::translate('Average number of children per family').'</span>
						<span class="filler">&nbsp;</span><span class="stats_data">'.$stats->averageChildren().'</span>
					</li>
				';
			}
		$content .= '</ul>';
		if ($stat_link) {
			$content .= '
				<h3>
					<a href="statistics.php?ged='.WT_GEDURL.'">'.WT_I18N::translate('View statistics as graphs').'</a>
				</h3>
			';
		}

		if ($template) {
			require WT_THEME_DIR.'templates/widget_template.php';
		} else {
			return $content;
		}
	}

	// Implement class WT_Module_Widget
	public function loadAjax() {
		return false;
	}

	// Implement WT_Module_Widget
	public function defaultWidgetOrder() {
		return 60;
	}

	// Implement WT_Module_Menu
	public function defaultAccessLevel() {
		return false;
	}

	// Implement class WT_Module_Block
	public function configureBlock($widget_id) {
		if (WT_Filter::postBool('save') && WT_Filter::checkCsrf()) {
			set_block_setting($widget_id, 'show_last_update',     WT_Filter::postBool('show_last_update'));
			set_block_setting($widget_id, 'stat_indi',            WT_Filter::postBool('stat_indi'));
			set_block_setting($widget_id, 'stat_fam',             WT_Filter::postBool('stat_fam'));
			set_block_setting($widget_id, 'stat_sour',            WT_Filter::postBool('stat_sour'));
			set_block_setting($widget_id, 'stat_other',           WT_Filter::postBool('stat_other'));
			set_block_setting($widget_id, 'stat_media',           WT_Filter::postBool('stat_media'));
			set_block_setting($widget_id, 'stat_repo',            WT_Filter::postBool('stat_repo'));
			set_block_setting($widget_id, 'stat_surname',         WT_Filter::postBool('stat_surname'));
			set_block_setting($widget_id, 'stat_events',          WT_Filter::postBool('stat_events'));
			set_block_setting($widget_id, 'stat_users',           WT_Filter::postBool('stat_users'));
			set_block_setting($widget_id, 'stat_first_birth',     WT_Filter::postBool('stat_first_birth'));
			set_block_setting($widget_id, 'stat_last_birth',      WT_Filter::postBool('stat_last_birth'));
			set_block_setting($widget_id, 'stat_first_death',     WT_Filter::postBool('stat_first_death'));
			set_block_setting($widget_id, 'stat_last_death',      WT_Filter::postBool('stat_last_death'));
			set_block_setting($widget_id, 'stat_long_life',       WT_Filter::postBool('stat_long_life'));
			set_block_setting($widget_id, 'stat_avg_life',        WT_Filter::postBool('stat_avg_life'));
			set_block_setting($widget_id, 'stat_most_chil',       WT_Filter::postBool('stat_most_chil'));
			set_block_setting($widget_id, 'stat_avg_chil',        WT_Filter::postBool('stat_avg_chil'));
			set_block_setting($widget_id, 'stat_link',            WT_Filter::postBool('stat_link'));
			exit;
		}

		require_once WT_ROOT.'includes/functions/functions_edit.php';

		$show_last_update=get_block_setting($widget_id, 'show_last_update', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo /* I18N: label for yes/no option */ WT_I18N::translate('Show date of last update?');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('show_last_update', $show_last_update);
		echo '</td></tr>';

		$stat_indi           =get_block_setting($widget_id, 'stat_indi',            true);
		$stat_fam            =get_block_setting($widget_id, 'stat_fam',             true);
		$stat_sour           =get_block_setting($widget_id, 'stat_sour',            true);
		$stat_other          =get_block_setting($widget_id, 'stat_other',           true);
		$stat_media          =get_block_setting($widget_id, 'stat_media',           true);
		$stat_repo           =get_block_setting($widget_id, 'stat_repo',            true);
		$stat_surname        =get_block_setting($widget_id, 'stat_surname',         true);
		$stat_events         =get_block_setting($widget_id, 'stat_events',          true);
		$stat_users          =get_block_setting($widget_id, 'stat_users',           true);
		$stat_first_birth    =get_block_setting($widget_id, 'stat_first_birth',     true);
		$stat_last_birth     =get_block_setting($widget_id, 'stat_last_birth',      true);
		$stat_first_death    =get_block_setting($widget_id, 'stat_first_death',     true);
		$stat_last_death     =get_block_setting($widget_id, 'stat_last_death',      true);
		$stat_long_life      =get_block_setting($widget_id, 'stat_long_life',       true);
		$stat_avg_life       =get_block_setting($widget_id, 'stat_avg_life',        true);
		$stat_most_chil      =get_block_setting($widget_id, 'stat_most_chil',       true);
		$stat_avg_chil       =get_block_setting($widget_id, 'stat_avg_chil',        true);
		$stat_link           =get_block_setting($widget_id, 'stat_link',            true);
?>
	<tr>
	<td class="descriptionbox wrap width33"><?php echo WT_I18N::translate('Select the stats to show in this block'); ?></td>
	<td class="optionbox">
	<table>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_indi"
			<?php if ($stat_indi) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Individuals'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_first_birth"
			<?php if ($stat_first_birth) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Earliest birth year'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_surname"
			<?php if ($stat_surname) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Total surnames'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_last_birth"
			<?php if ($stat_last_birth) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Latest birth year'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_fam"
			<?php if ($stat_fam) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Families'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_first_death"
			<?php if ($stat_first_death) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Earliest death year'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_sour"
			<?php if ($stat_sour) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Sources'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_last_death"
			<?php if ($stat_last_death) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Latest death year'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_media"
			<?php if ($stat_media) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Media objects'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_long_life"
			<?php if ($stat_long_life) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Person who lived the longest'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_repo"
			<?php if ($stat_repo) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Repositories'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_avg_life"
			<?php if ($stat_avg_life) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Average age at death'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_other"
			<?php if ($stat_other) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Other records'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_most_chil"
			<?php if ($stat_most_chil) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Family with the most children'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_events"
			<?php if ($stat_events) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Total events'); ?></td>
			<td><input type="checkbox" value="yes" name="stat_avg_chil"
			<?php if ($stat_avg_chil) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Average number of children per family'); ?></td>
		</tr>
		<tr>
			<td><input type="checkbox" value="yes" name="stat_users"
			<?php if ($stat_users) echo ' checked="checked"'; ?>>
			<?php echo WT_I18N::translate('Total users'); ?></td>
			<td>&nbsp;</td>
		</tr>
	</table>
	</td>
	</tr>
	<?php
		$stat_link=get_block_setting($widget_id, 'stat_link', true);
		echo '<tr><td class="descriptionbox wrap width33">';
		echo WT_I18N::translate('Show link to Statistics charts?');
		echo '</td><td class="optionbox">';
		echo edit_field_yes_no('stat_link', $stat_link);
		echo '</td></tr>';
	}
}
