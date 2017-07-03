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

if (!defined('WT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class tab_census_WT_Module extends WT_Module implements WT_Module_Tab {
	// Extend WT_Module
	public function getTitle() {
		return /* I18N: Name of a module/tab on the individual page. */ WT_I18N::translate('Census summary');
	}

	// Extend WT_Module
	public function getDescription() {
		return /* I18N: Description of the "Facts and events" module */ WT_I18N::translate('A tab summarising census events.');
	}

	// Implement WT_Module_Tab
	public function defaultTabOrder() {
		return 100;
	}

	// Implement WT_Module_Tab
	public function isGrayedOut() {
		return $this->getCensFacts() == null;
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return false;
	}

	// Implement WT_Module_Tab
	public function getTabContent() {
		global $controller;
		$person		= $controller->getSignificantIndividual();
		$xref		= $controller->record->getXref();
		$facts		= $this->getCensFacts();
		?>
		<style>
			#tab_census_content div.descriptionbox {border: 1px solid #555; border-radius: 5px; margin: 2px 0;}
			#tab_census_content table {border-collapse: collapse; width: 100%;}
			#tab_census_content th {background: #ddd; border: 1px solid; font-weight: 700; padding: 8px;}
			#tab_census_content td {border: 1px solid; padding: 3px 8px;}
			#tab_census_content td.small {font-size: 90%;}
			#tab_census_content td.nowrap {white-space: nowrap;}
		</style>
		<div id="tab_census_content">
			<!-- Show header Links -->
			<?php if (WT_USER_CAN_EDIT) { ?>
				<div class="descriptionbox rela">
					<span>
						<a href="edit_interface.php?action=add&pid=<?php echo $xref; ?>&fact=CENS&accesstime=<?php echo WT_TIMESTAMP; ?>&ged=<?php echo WT_GEDCOM; ?>" target="_blank">
							<i style="margin: 0 3px 0 10px;" class="icon-image_add">&nbsp;</i>
							<?php echo WT_I18N::translate('Add census'); ?>
						</a>
					</span>
				</div>
			<?php } ?>
			<?php if ($person && $person->canDisplayDetails()) { ?>
				<table>
					<thead>
						<tr>
							<th><?php echo WT_I18N::translate('Date'); ?></th>
							<th><?php echo WT_I18N::translate('Place'); ?></th>
							<th><?php echo WT_I18N::translate('Address'); ?></th>
							<th><?php echo WT_I18N::translate('Notes'); ?></th>
							<th><?php echo WT_I18N::translate('Sources'); ?></th>
							<th><?php echo WT_I18N::translate('Media'); ?></th>
							<?php if (WT_USER_CAN_EDIT) { ?>
								<th></th>
							<?php } ?>
						</tr>
					</thead>
					<tbody>
						<?php foreach ($facts as $fact) {
							if ($fact->getTag() === 'CENS') {
								$styleadd = "";
								if ($fact->getIsNew()) $styleadd = "change_new";
								if ($fact->getIsOld()) $styleadd = "change_old";
								?>
								<tr>
									<td class="nowrap"><?php echo $fact->getDate()->JD() != 0 ?  format_fact_date($fact, $person, false, false, false) : ""; ?></td>
									<td class="nowrap"><?php echo format_fact_place($fact, true); ?></td>
									<td class="small nowrap"><?php echo print_address_structure($fact->getGedcomRecord(), 2, 'inline'); ?></td>
									<td class="small"><?php echo print_fact_notes($fact->getGedcomRecord(), 2); ?></td>
									<td class="small"><?php echo print_fact_sources($fact->getGedcomRecord(), 2, true, true); ?></td>
									<td><?php echo print_media_links($fact->getGedcomRecord(), 2, $xref); ?></td>
									<?php if (WT_USER_CAN_EDIT && $styleadd!='change_old' && $fact->getLineNumber()>0 && $fact->canEdit()) { ?>
										<td>
											<div class="editfacts">
												<div class="editlink">
													<a class="icon-edit" onclick="return edit_record('<?php echo $xref; ?>', <?php echo $fact->getLineNumber(); ?>);" href="#" title="<?php echo WT_I18N::translate('Edit'); ?>">
														<span class="link_text"><?php echo WT_I18N::translate('Edit'); ?></span>
													</a>
												</div>
												<div class="copylink">
													<a class="icon-copy" href="#" onclick="jQuery.post('action.php',{action:'copy-fact', type:'<?php echo $fact->getParentObject()->getType(); ?>', factgedcom:'<?php echo rawurlencode($fact->getGedcomRecord()); ?>'},function(){location.reload();})" title="<?php echo  WT_I18N::translate('Copy'); ?>">
														<span class="link_text"><?php echo WT_I18N::translate('Copy'); ?></span>
													</a>
												</div>
												<div class="deletelink">
													<a class="icon-delete" onclick="return delete_fact('<?php echo $xref; ?>', <?php echo $fact->getLineNumber(); ?>, '', '<?php echo WT_I18N::translate('Are you sure you want to delete this fact?'); ?>');" href="#" title="<?php echo WT_I18N::translate('Delete'); ?>">
														<span class="link_text"><?php echo WT_I18N::translate('Delete'); ?></span>
													</a>
												</div>
											</div>
										</td>
									<?php } ?>
								</tr>
							<?php } ?>
						<?php } ?>
					</tbody>
				</table>
			<?php } ?>
		</div>
		<?php
	}

	// Implement WT_Module_Tab
	public function hasTabContent() {
		return WT_USER_CAN_EDIT || $this->getCensFacts();
	}

	// Implement WT_Module_Tab
	public function canLoadAjax() {
		return true;
	}

	// Implement WT_Module_Tab
	public function getPreLoadContent() {
		return '';
	}

	private function getCensFacts() {
		global $controller;
		$person			= $controller->getSignificantIndividual();
		$fullname		= $controller->record->getFullName();
		$xref			= $controller->record->getXref();
		$indifacts		= $person->getIndiFacts();
		$censusFacts	= array();

		foreach ($indifacts as $fact) {
			if ($fact->getTag() === 'CENS') {
				$censusFacts[] = $fact;
			}
		}
		sort_facts($censusFacts);

		return $censusFacts;
	}

}
