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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class census_assistant_KT_Module extends KT_Module {
	// Extend KT_Module
	public function getTitle() {
		return /* I18N: Name of a module */ KT_I18N::translate('Census assistant');
	}

	// Extend KT_Module
	public function getDescription() {
		return /* I18N: Description of the “Census assistant” module */ KT_I18N::translate('An alternative way to enter census transcripts and link them to individuals.');
	}

	// Extend KT_Module
	public function modAction($mod_action) {
		switch($mod_action) {
			case 'census_find':
				$this->censusFind();
				break;
			default:
				echo $mod_action;
				header('HTTP/1.0 404 Not Found');
		}
	}

	/**
	 * Find an individual.
	 */
	function censusFind() {
		global $KT_TREE;

		$controller = new KT_Controller_Simple();
		$filter     = KT_Filter::get('filter');
		$action     = KT_Filter::get('action');
		$census     = KT_Filter::get('census');
		$census     = new $census;

		$controller
			->restrictAccess($census instanceof KT_Census_CensusInterface)
			->setPageTitle(KT_I18N::translate('Find an individual'))
			->pageHeader();
		?>

		<div id="census-search">
			<h2><?php echo KT_I18N::translate('Find an individual'); ?></h2>
			<hr>

			<?php if ($action == 'filter') {
				$filter       = trim($filter);
				$filter_array = explode(' ', preg_replace('/ {2,}/', ' ', $filter));

				// Output Individual for census assistant search ====================== ?>
				<div>
					<?php $myindilist = search_indis_names($filter_array, array(KT_GED_ID), 'AND');
					if ($myindilist) { ?>
						<ul>
							<?php usort($myindilist, array('KT_GedcomRecord', 'Compare'));
							foreach ($myindilist as $indi) { ?>
								<li>
									<a href="#" onclick="window.opener.appendCensusRow('<?php echo KT_Filter::escapeJs(census_assistant_KT_Module::censusTableRow($census, $indi, null)); ?>'); window.close();" >
										<b><?php echo $indi->getFullName(); ?></b>
									</a>
									<?php echo $indi->format_first_major_fact(KT_EVENTS_BIRT, 1);
									echo $indi->format_first_major_fact(KT_EVENTS_DEAT, 1); ?>
									<hr>
								</li>
							<?php } ?>
						</ul>
					<?php } else { ?>
						<p> <?php echo KT_I18N::translate('No results found.'); ?> </p>
					<?php } ?>
					<button onclick="window.close();">
						<i class="fa fa-close"></i>
						<?php echo KT_I18N::translate('close'); ?>
					</button>
				</div>
			<?php } ?>
		</div>
		<?php
	}

	/**
	 * Convert custom markup into HTML
	 *
	 * @param Note $note
	 *
	 * @return string
	 */
	public static function formatCensusNote(KT_Note $note) {
		global $KT_TREE;

		if (preg_match('/(.*)((?:\n.*)*)\n\.start_formatted_area\.\n(.+)\n(.+(?:\n.+)*)\n.end_formatted_area\.((?:\n.*)*)/', $note->getNote(), $match)) {
			// This looks like a census-assistant shared note
			$title     = KT_Filter::escapeHtml($match[1]);
			$preamble  = KT_Filter::escapeHtml($match[2]);
			$header    = KT_Filter::escapeHtml($match[3]);
			$data      = KT_Filter::escapeHtml($match[4]);
			$postamble = KT_Filter::escapeHtml($match[5]);

			// Get the column headers for the census to which this note refers
			// requires the fact place & date to match the specific census
			// censusPlace() (Soundex match) and censusDate() functions
			$fmt_headers   = array();

			$linkedRecords = array_merge($note->fetchLinkedIndividuals(), $note->fetchLinkedFamilies());

			$firstRecord   = array_shift($linkedRecords);
			if ($firstRecord) {
				$countryCode	= '';
				$date			= '';

				foreach ($firstRecord->getFacts('CENS') as $fact) {
					if (trim($fact->getAttribute('NOTE'), '@') === $note->getXref()) {
						$date        = $fact->getAttribute('DATE');
						// get country code from census place
						$kt_place	 = new KT_Place($fact->getPlace(), KT_GED_ID);
						$place       = explode(',', strip_tags($kt_place->getFullName()));
						$countryCode = KT_Soundex::soundex_dm(array_pop($place));
						break;
					}
				}

				foreach (KT_Census_Census::allCensusPlaces() as $censusPlace) {
					if (KT_Soundex::compare($countryCode, KT_Soundex::soundex_dm($censusPlace->censusPlace()))) {
						foreach ($censusPlace->allCensusDates() as $census) {
							if ($census->censusDate() == $date) {
								foreach ($census->columns() as $column) {
									$abbrev = $column->abbreviation();
									if ($abbrev) {
										$description          = $column->title() ? $column->title() : KT_I18N::translate('Description unavailable');
										$fmt_headers[$abbrev] = '<span title="' . $description . '">' . $abbrev . '</span>';
									}
								}
								break 2;
							}
						}
					}
				}
			}
			// Substitute header labels and format as HTML
			$thead = '<tr><th>' . strtr(str_replace('|', '</th><th>', $header), $fmt_headers) . '</th></tr>';
			$thead = str_replace('.b.', '', $thead);

			// Format data as HTML
			$tbody = '';
			foreach (explode("\n", $data) as $row) {
				$tbody .= '<tr>';
				foreach (explode('|', $row) as $column) {
					// allow some markup-type code
					$class = '';
					/* 1 - highlight */
					strstr($column, '.h.') ? $class .= ' high' : $class .= '';
					$column = str_replace('.h.', '', $column);
					/* 2 - strikout */
					strstr($column, '.s.') ? $class .= ' strike' : $class .= '';
					$column = str_replace('.s.', '', $column);
					/* 3 - strikout */
					strstr($column, '.u.') ? $class .= ' under' : $class .= '';
					$column = str_replace('.u.', '', $column);
					/* 4 - bold */
					strstr($column, '.b.') ? $class .= ' bold' : $class .= '';
					$column = str_replace('.b.', '', $column);

					$tbody .= '<td class="' . $class . '">' . $column . '</td>';
				}
				$tbody .= '</tr>';
			}

			return
				'<div class="census_text">
					<p>' . $preamble . '</p>
					<table class="ca">
						<thead>' . $thead . '</thead>
						<tbody>' . $tbody . '</tbody>
					</table>
					<p>' . $postamble . '</p>
				</div>';
		} else {
			// Not a census-assistant shared note - apply default formatting
			return KT_Filter::formatText($note->getNote());
		}
	}

	/*++++++++++++++++++++++++++++++*/
	/**
	 * Generate an HTML row of data for the census header
	 *
	 * Add prefix cell (store XREF and drag/drop)
	 * Add suffix cell (delete button)
	 *
	 * @param CensusInterface $census
	 *
	 * @return string
	 */
	public static function censusTableHeader($census) {
		$html = '';
		foreach ($census->columns() as $column) {
			$column->title() ? $title = ' title="' . $column->title() . '"' : $title = ' title="' . KT_I18N::translate('Description unavailable') . '"';
			$column->style() ? $style = ' style="' . $column->style() . '"' : $style = '';
			$html .= '<th' . $title . $style . '">' . $column->abbreviation() . '</th>';
		}

		return '<tr><th style="display:none;"></th>' . $html . '<th class="delete"></th></tr>';
	}

	/**
	 * Generate an HTML row of data for the census
	 *
	 * Add prefix cell (store XREF and drag/drop)
	 * Add suffix cell (delete button)
	 *
	 * @param CensusInterface $census
	 *
	 * @return string
	 */
	public static function censusTableEmptyRow(KT_Census_CensusInterface $census) {
		return '<tr><td style="display:none;"></td>' . str_repeat('<td><input type="text"></td>', count($census->columns())) . '<td class="delete"><a class="icon-delete" href="#" title="' . KT_I18N::translate('Remove') . '"></a></td></tr>';
	}

	/**
	 * Generate an HTML row of data for the census
	 *
	 * Add prefix cell (store XREF and drag/drop)
	 * Add suffix cell (delete button)
	 *
	 * @param CensusInterface $census
	 * @param Individual      $individual
	 * @param Individual|null $head
	 *
	 * @return string
	 */
	public static function censusTableRow($census, KT_Person $individual, KT_Person $head = null) {
		$html = '';
		foreach ($census->columns() as $column) {
			$html .= '<td><input type="text" value="' . $column->generate($individual, $head) . '"></td>';
		}
		return '<tr><td style="display:none;">' . $individual->getXref() . '</td>' . $html . '<td class="delete"><a class="icon-delete" href="#" title="' . KT_I18N::translate('Remove') . '"></a></td></tr>';
	}

	/**
	 * Create a family on the census navigator.
	 *
	 * @param CensusInterface $census
	 * @param Family          $family
	 * @param Individual      $head
	 *
	 * @return string
	 */
	public static function censusNavigatorFamily(KT_Census_CensusInterface $census, KT_Family $family, KT_Person $head) {
		$headImg2 = '<i class="icon-button_head" title="' . KT_I18N::translate('Click to choose person as Head of family.') . '"></i>';

		foreach ($family->getSpouses() as $spouse) {
			$menu = new KT_Menu(getCloseRelationshipName($head, $spouse));
			foreach ($spouse->getChildFamilies() as $grandparents) {
				foreach ($grandparents->getSpouses() as $grandparent) {
					$submenu = new KT_Menu(
						getCloseRelationshipName($head, $grandparent) . ' - ' . $grandparent->getFullName(),
						'#',
						'',
						array('onclick' => 'return appendCensusRow("' . KT_Filter::escapeJs(self::censusTableRow($census, $grandparent, $head)) . '");')
					);
					$submenu->addClass('submenuitem', '');
					$menu->addSubmenu($submenu);
					$menu->addClass('', 'submenu');
				}
			}

			?>
			<tr>
				<td>
					<?php echo $menu->getMenu(); ?>
				</td>
				<td class="nowrap">
					<a href="#" onclick="return appendCensusRow('<?php echo KT_Filter::escapeJs(self::censusTableRow($census, $spouse, $head)); ?>');">
						<?php echo $spouse->getFullName(); ?>
					</a>
				</td>
				<td>
					<?php if ($head !== $spouse): ?>
						<a href="edit_interface.php?action=addnewnote_assisted&amp;noteid=newnote&amp;xref=<?php echo $spouse->getXref(); ?>&amp;gedcom=<?php echo KT_GEDURL; ?>&amp;census=<?php echo get_class($census); ?>">
							<?php echo $headImg2; ?>
						</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}

		foreach ($family->getChildren() as $child) {
			$menu = new KT_Menu(getCloseRelationshipName($head, $child));
			foreach ($child->getSpouseFamilies() as $spouse_family) {
				foreach ($spouse_family->getSpouses() as $spouse_family_spouse) {
					if ($spouse_family_spouse != $child) {
						$submenu = new KT_Menu(
							getCloseRelationshipName($head, $spouse_family_spouse) . ' - ' . $spouse_family_spouse->getFullName(),
							'#',
							'',
							array('onclick' => 'return appendCensusRow("' . KT_Filter::escapeJs(self::censusTableRow($census, $spouse_family_spouse, $head)) . '");')
						);
						$submenu->addClass('submenuitem', '');
						$menu->addSubmenu($submenu);
						$menu->addClass('', 'submenu');
					}
				}
				foreach ($spouse_family->getChildren() as $spouse_family_child) {
					$submenu = new KT_Menu(
						getCloseRelationshipName($head, $spouse_family_child) . ' - ' . $spouse_family_child->getFullName(),
						'#',
						'',
						array('onclick' => 'return appendCensusRow("' . KT_Filter::escapeJs(self::censusTableRow($census, $spouse_family_child, $head)) . '");')
					);
					$submenu->addClass('submenuitem', '');
					$menu->addSubmenu($submenu);
					$menu->addClass('', 'submenu');
				}
			}

			?>
			<tr>
				<td>
					<?php echo $menu->getMenu(); ?>
				</td>
				<td>
					<a href="#" onclick="return appendCensusRow('<?php echo KT_Filter::escapeJs(self::censusTableRow($census, $child, $head)); ?>');">
						<?php echo $child->getFullName(); ?>
					</a>
				</td>
				<td>
					<?php if ($head !== $child): ?>
						<a href="edit_interface.php?action=addnewnote_assisted&amp;noteid=newnote&amp;xref=<?php echo $child->getXref(); ?>&amp;gedcom=<?php echo KT_GEDURL; ?>&amp;census=<?php echo get_class($census); ?>">
							<?php echo $headImg2; ?>
						</a>
					<?php endif; ?>
				</td>
			</tr>
			<?php
		}
		echo '<tr><td colspan="3">&nbsp;</td></tr>';
	}
}
