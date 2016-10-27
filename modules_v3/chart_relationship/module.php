<?php
// View for the relationship tree.
//
// Kiwitrees: Web based Family History software
// Copyright (C) 2016 kiwitrees.net
//
// Derived from webtrees
// Copyright (C) 2012 webtrees development team
//
// Derived from PhpGedView
// Copyright (C) 2002 to 2010 PGV Development Team
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

class chart_relationship_WT_Module extends WT_Module implements WT_Module_Chart, WT_Module_Config {

	// Extend class WT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ WT_I18N::translate('Relationship');
	}

	// Extend class WT_Module
	public function getDescription() {
		return /* I18N: Description of “Relationship chart” module */ WT_I18N::translate('An individual\'s relationship chart');
	}

	// Extend WT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
		case 'show':
			$this->show();
			break;
		case 'admin_config':
			$this->config();
			break;
		default:
			header('HTTP/1.0 404 Not Found');
		}
	}

	// Extend class WT_Module
	public function defaultAccessLevel() {
		return WT_PRIV_PUBLIC;
	}

	// Implement WT_Module_Config
	public function getConfigLink() {
		return 'module.php?mod='.$this->getName().'&amp;mod_action=admin_config';
	}

	// Implement WT_Module_Chart
	public function getChartMenus() {
		global $controller;
		$indi_xref			= $controller->getSignificantIndividual()->getXref();
		$PEDIGREE_ROOT_ID 	= get_gedcom_setting(WT_GED_ID, 'PEDIGREE_ROOT_ID');
		$menus				= array();
		if ($indi_xref) {
			// Pages focused on a specific person - from the person, to me
			$pid1 = WT_USER_GEDCOM_ID ? WT_USER_GEDCOM_ID : WT_USER_ROOT_ID;
			if (!$pid1 && $PEDIGREE_ROOT_ID) {
				$pid1 = $PEDIGREE_ROOT_ID;
			};
			$pid2 = $indi_xref;
			if ($pid1 == $pid2) {
				$pid2 = $PEDIGREE_ROOT_ID ? $PEDIGREE_ROOT_ID : '';
			}
			$menu = new WT_Menu(
				WT_USER_GEDCOM_ID ? WT_I18N::translate('Relationship to me') : $this->getTitle(),
				'relationship.php?pid1=' . $pid1 .'&amp;pid2=' . $pid2 .'&amp;ged=' . WT_GEDURL,
				'menu-chart-relationship'
			);
			$menus[] = $menu;
		} else {
			// Regular pages - from me, to somebody
			$pid1 = WT_USER_GEDCOM_ID ? WT_USER_GEDCOM_ID : WT_USER_ROOT_ID;
			$pid2 = $PEDIGREE_ROOT_ID ? $PEDIGREE_ROOT_ID : '';
			$menu = new WT_Menu(
				WT_USER_GEDCOM_ID ? WT_I18N::translate('Relationship to me') : $this->getTitle(),
				'relationship.php?pid1=' . $pid1 .'&amp;pid2=' . $pid2 .'&amp;ged=' . WT_GEDURL,
				'menu-chart-relationship'
			);
			$menus[] = $menu;
		}
		return $menus;
	}

	private function config() {
		require WT_ROOT.'includes/functions/functions_edit.php';
		$controller = new WT_Controller_Page();
		$controller
			->requireAdminLogin()
			->setPageTitle($this->getTitle())
			->pageHeader()
			->addInlineJavascript('
				jQuery(function() {
					jQuery("div.config_options:odd").addClass("odd");
					jQuery("div.config_options:even").addClass("even");
				});
			');

		// Possible options for the recursion option
		$recursionOptions = array(
			0	=> WT_I18N::translate('none'),
			1	=> WT_I18N::number(1),
			2	=> WT_I18N::number(2),
			3	=> WT_I18N::number(3),
			99	=> WT_I18N::translate('unlimited'),
		);

		// defaults
		$chart1		 = 1;
		$chart2		 = 0;
		$chart3		 = 1;
		$chart4		 = 1;
		$chart5		 = 0;
		$chart6		 = 1;
		$chart7		 = 0;
		$rec_options = 99;
		$rel1		 = '1';
		$rel2		 = '1';
		$rel3		 = '1';
		$rel1_ca	 = '1';
		$rel2_ca	 = '1';
		$rel3_ca	 = '1';

		if (WT_Filter::postBool('reset')) {
			set_gedcom_setting(WT_GED_ID, 'CHART_1',							1);
			set_gedcom_setting(WT_GED_ID, 'CHART_2',							0);
			set_gedcom_setting(WT_GED_ID, 'CHART_3',							1);
			set_gedcom_setting(WT_GED_ID, 'CHART_4',							1);
			set_gedcom_setting(WT_GED_ID, 'CHART_5',							0);
			set_gedcom_setting(WT_GED_ID, 'CHART_6',							1);
			set_gedcom_setting(WT_GED_ID, 'CHART_7',							0);
			set_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION', 			99);
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI',			'1');
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS',					'1');
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE',					'1');
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA',	'1');
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS_SHOW_CA',			'1');
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE_SHOW_CA',			'1');

			AddToLog($this->getTitle().' set to default values', 'config');
		}

		if (WT_Filter::postBool('save')) {
			set_gedcom_setting(WT_GED_ID, 'CHART_1',							WT_Filter::postBool('NEW_CHART_1', $chart1));
			set_gedcom_setting(WT_GED_ID, 'CHART_2',							WT_Filter::postBool('NEW_CHART_2', $chart2));
			set_gedcom_setting(WT_GED_ID, 'CHART_3',							WT_Filter::postBool('NEW_CHART_3', $chart3));
			set_gedcom_setting(WT_GED_ID, 'CHART_4',							WT_Filter::postBool('NEW_CHART_4', $chart4));
			set_gedcom_setting(WT_GED_ID, 'CHART_5',							WT_Filter::postBool('NEW_CHART_5', $chart5));
			set_gedcom_setting(WT_GED_ID, 'CHART_6',							WT_Filter::postBool('NEW_CHART_6', $chart6));
			set_gedcom_setting(WT_GED_ID, 'CHART_7',							WT_Filter::postBool('NEW_CHART_7', $chart7));
			set_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION', 			WT_Filter::post('NEW_RELATIONSHIP_RECURSION', WT_REGEX_INTEGER, $rec_options));
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI',			WT_Filter::post('NEW_TAB_REL_TO_DEFAULT_INDI', WT_REGEX_INTEGER, $rel1));
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS',					WT_Filter::post('NEW_TAB_REL_OF_PARENTS', WT_REGEX_INTEGER, $rel2));
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE',					WT_Filter::post('NEW_TAB_REL_TO_SPOUSE', WT_REGEX_INTEGER, $rel3));
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA',	WT_Filter::post('NEW_TAB_REL_TO_DEFAULT_INDI_SHOW_CA', WT_REGEX_INTEGER, $rel1_ca));
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS_SHOW_CA',			WT_Filter::post('NEW_TAB_REL_OF_PARENTS_SHOW_CA', WT_REGEX_INTEGER, $rel2_ca));
			set_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE_SHOW_CA',			WT_Filter::post('NEW_TAB_REL_TO_SPOUSE_SHOW_CA', WT_REGEX_INTEGER, $rel3_ca));

			AddToLog($this->getTitle().' set to new values', 'config');
		}

		$chart1		 = get_gedcom_setting(WT_GED_ID, 'CHART_1');
		$chart2		 = get_gedcom_setting(WT_GED_ID, 'CHART_2');
		$chart3		 = get_gedcom_setting(WT_GED_ID, 'CHART_3');
		$chart4		 = get_gedcom_setting(WT_GED_ID, 'CHART_4');
		$chart5		 = get_gedcom_setting(WT_GED_ID, 'CHART_5');
		$chart6		 = get_gedcom_setting(WT_GED_ID, 'CHART_6');
		$chart7		 = get_gedcom_setting(WT_GED_ID, 'CHART_7');
		$rec_options = get_gedcom_setting(WT_GED_ID, 'RELATIONSHIP_RECURSION');
		$rel1		 = get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI');
		$rel2		 = get_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS');
		$rel3		 = get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE');
		$rel1_ca	 = get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_DEFAULT_INDI_SHOW_CA');
		$rel2_ca	 = get_gedcom_setting(WT_GED_ID, 'TAB_REL_OF_PARENTS_SHOW_CA');
		$rel3_ca	 = get_gedcom_setting(WT_GED_ID, 'TAB_REL_TO_SPOUSE_SHOW_CA');

		?>

		<div id="relations_config">
			<a class="current faq_link" href="http://kiwitrees.net/faqs/general-topics/displaying-relationships/" target="_blank" title="<?php echo WT_I18N::translate('View FAQ for this page.'); ?>"><?php echo WT_I18N::translate('View FAQ for this page.'); ?><i class="fa fa-comments-o"></i></a>
			<h2><?php echo /* I18N: Configuration page title */ WT_I18N::translate('Relationship calculation options'); ?></h2>
			<form method="post" action="#" name="tree">
				<div class="config_options">
					<label><?php echo WT_I18N::translate('Family tree'); ?></label>
					<?php echo select_edit_control('ged', WT_Tree::getNameList(), null, WT_GEDCOM, ' onchange="tree.submit();"'); ?>
				</div>
			</form>
			<form method="post" name="rela_form" action="<?php echo $this->getConfigLink(); ?>">
				<input type="hidden" name="save" value="1">
				<div id="config-chart">
					<h3><?php echo /* I18N: Configuration option */ WT_I18N::translate('Chart settings'); ?></h3>
					<h4 class="accepted"><?php echo /* I18N: Configuration option */ WT_I18N::translate('Options to show in the chart'); ?></h4>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('Find a closest relationship via common ancestors'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_1', $chart1); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Configuration option */ WT_I18N::translate('Determines the shortest path between two individuals via a LCA (lowest common ancestor), i.e. a common ancestor who only appears on the path once.') ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_2', $chart2); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Configuration option */ WT_I18N::translate('Each SLCA (smallest lowest common ancestor) essentially represents a part of the tree which both individuals share (as part of their ancestors). More technically, the SLCA set of two individuals is a subset of the LCA set (excluding all LCAs that are themselves ancestors of other LCAs).') ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('Find all relationships via lowest common ancestors'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_3', $chart3); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Configuration option */ WT_I18N::translate('All paths between the two individuals that contribute to the CoR (Coefficient of Relationship), as defined here: <a href = "http://www.genetic-genealogy.co.uk/Toc115570135.html" target="_blank">Coefficient of Relationship</a>'); ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('Find the closest overall connections (preferably via common ancestors)'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_4', $chart4); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Configuration option */ WT_I18N::translate('Prefers partial paths via common ancestors, even if there is no direct common ancestor.') ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_7', $chart7); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Configuration option */ WT_I18N::translate('For close relationships similar to the previous option, but faster. Internally just a combination of two other methods.') ?>
							</div>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('Find the closest overall connections'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_5', $chart5); ?>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Find other/all overall connections'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_CHART_6', $chart6); ?>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo WT_I18N::translate('How much recursion to use when searching for relationships'); ?></label>
						<div class="input_group">
							<?php echo radio_buttons('NEW_RELATIONSHIP_RECURSION', $recursionOptions, $rec_options, 'class="radio_inline"'); ?>
							<div class="helpcontent">
								<?php echo /* I18N: Configuration option for relationship chart */ WT_I18N::translate('Searching for all possible relationships can take a lot of time in complex trees, This option can help limit the extent of relationships included in the relationship chart.'); ?>
							</div>
						 </div>
					</div>
				</div>
				<div id="config-tab">
					<h3><?php echo /* I18N: Configuration option */ WT_I18N::translate('Families tab settings'); ?></h3>
					<!-- RELATIONS TO DEFAULT INDIVIDUAL -->
					<h4 class="accepted"><?php echo /* I18N: Configuration option */ WT_I18N::translate('How to determine relationships to the default individual'); ?></h4>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Do not show any relationship'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="0" <?php echo ($rel1 === '0') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<div class="helpcontent">
							<?php echo /* I18N: Configuration option */ WT_I18N::translate('The following options refer to the same algorithms used in the relationships chart. Choose any one of these.') ?>
						</div>
						<label class="indent"><?php echo WT_I18N::translate('Find a closest relationship via common ancestors'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="1" <?php echo ($rel1 === '1') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="2" <?php echo ($rel1 === '2') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find all relationships via lowest common ancestors'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="3" <?php echo ($rel1 === '3') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find the closest overall connections (preferably via common ancestors)'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="4" <?php echo ($rel1 === '4') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find a closest relationship via common ancestors, or fallback to the closest overall connection'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="7" <?php echo ($rel1 === '7') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find the closest overall connections') ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="5" <?php echo ($rel1 === '5') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find other/all overall connections') ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_DEFAULT_INDI" value="6" <?php echo ($rel1 === '6') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Show common ancestors'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_TAB_REL_TO_DEFAULT_INDI_SHOW_CA', $rel1_ca); ?>
						</div>
					</div>
					<!-- RELATIONS BETWEEN PARENTS -->
					<h4 class="accepted"><?php echo /* I18N: Configuration option */ WT_I18N::translate('How to determine relationships between parents'); ?></h4>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Do not show any relationship'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="0" <?php echo ($rel2 === '0') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<div class="helpcontent">
							<?php echo /* I18N: Configuration option */ WT_I18N::translate('The following options refer to the same algorithms used in the relationships chart. Choose any one of these.') ?>
						</div>
						<label class="indent"><?php echo WT_I18N::translate('Find a closest relationship via common ancestors'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="1" <?php echo ($rel2 === '1') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="2" <?php echo ($rel2 === '2') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find all relationships via lowest common ancestors'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_OF_PARENTS" value="3" <?php echo ($rel2 === '3') ? 'checked' : ''; ?>>
						</div>
					<div class="helpcontent">
						<?php echo /* I18N: Configuration option */ WT_I18N::translate('Searching for overall connections is not included here because there is always a trivial HUSB - WIFE connection.') ?>
					</div>
					</div>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Show common ancestors'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_TAB_REL_OF_PARENTS_SHOW_CA', $rel2_ca); ?>
						</div>
					</div>
					<!-- RELATIONS TO SPOUSES -->
					<h4 class="accepted"><?php echo /* I18N: Configuration option */ WT_I18N::translate('How to determine relationships to spouses'); ?></h4>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Do not show any relationship'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="0" <?php echo ($rel3 === '0') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<div class="helpcontent">
							<?php echo /* I18N: Configuration option */ WT_I18N::translate('The following options refer to the same algorithms used in the relationships chart. Choose any one of these.') ?>
						</div>
						<label class="indent"><?php echo WT_I18N::translate('Find a closest relationship via common ancestors'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="1" <?php echo ($rel3 === '1') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find all smallest lowest common ancestors, show a closest connection for each'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="2" <?php echo ($rel3 === '2') ? 'checked' : ''; ?>>
						</div>
					</div>
					<div class="config_options">
						<label class="indent"><?php echo WT_I18N::translate('Find all relationships via lowest common ancestors'); ?></label>
						<div class="input_group">
							<input type="radio" name="NEW_TAB_REL_TO_SPOUSE" value="3" <?php echo ($rel3 === '3') ? 'checked' : ''; ?>>
						</div>
					<div class="helpcontent">
						<?php echo /* I18N: Configuration option */ WT_I18N::translate('Searching for overall connections is not included here because there is always a trivial HUSB - WIFE connection.') ?>
					</div>
					</div>
					<div class="config_options">
						<label><?php echo /* I18N: Configuration option */ WT_I18N::translate('Show common ancestors'); ?></label>
						<div class="input_group">
							<?php echo edit_field_yes_no('NEW_TAB_REL_TO_SPOUSE_SHOW_CA', $rel3_ca); ?>
						</div>
					</div>
				</div>
				<button class="btn btn-primary save" type="submit">
					<i class="fa fa-floppy-o"></i>
					<?php echo WT_I18N::translate('save'); ?>
				</button>
			</form>
				<form method="post" name="rela_form" action="<?php echo $this->getConfigLink(); ?>">
				<input type="hidden" name="reset" value="1">
				<button class="btn btn-primary reset" type="submit">
					<i class="fa fa-refresh"></i>
					<?php echo WT_I18N::translate('reset'); ?>
				</button>
			</form>
		</div>
	<?php }

}
