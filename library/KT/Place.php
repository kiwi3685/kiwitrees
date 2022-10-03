<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2022 kiwitrees.net
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

if (!defined('KT_KIWITREES')) {
	header('HTTP/1.0 403 Forbidden');
	exit;
}

class KT_Place {
	const GEDCOM_SEPARATOR = ', ';
	private $gedcom_place;  // e.g. array("Westminster", "London", "England")
	private $gedcom_id;     // We may have the same place in different trees

	public function __construct($gedcom_place, $gedcom_id) {
		if ($gedcom_place) {
			$this->gedcom_place=explode(self::GEDCOM_SEPARATOR, $gedcom_place);
		} else {
			// Empty => "Top Level"
			$this->gedcom_place=array();
			$this->place_id=0;
		}
		$this->gedcom_id=$gedcom_id;
	}

	public function getPlaceId() {
		$place_id=0;
		foreach (array_reverse($this->gedcom_place) as $place) {
			$place_id=
				KT_DB::prepare("SELECT p_id FROM `##places` WHERE p_parent_id=? AND p_place=? AND p_file=?")
				->execute(array($place_id, $place, $this->gedcom_id))
				->fetchOne();
		}
		return $place_id;
	}

	public function getParentPlace() {
		return new KT_Place(implode(self::GEDCOM_SEPARATOR, array_slice($this->gedcom_place, 1)), $this->gedcom_id);
	}

	public function getChildPlaces() {
		$children=array();
		if ($this->getPlaceId()) {
			$parent_text=self::GEDCOM_SEPARATOR . $this->getGedcomName();
		} else {
			$parent_text='';
		}

		$rows=
			KT_DB::prepare("SELECT p_place FROM `##places` WHERE p_parent_id=? AND p_file=? ORDER BY p_place COLLATE '".KT_I18N::$collation."'")
			->execute(array($this->getPlaceId(), $this->gedcom_id))
			->fetchOneColumn();
		foreach ($rows as $row) {
			$children[]=new KT_Place($row . $parent_text, $this->gedcom_id);
		}
		return $children;
	}

	public function getURL() {
		$url='placelist.php';
		foreach (array_reverse($this->gedcom_place) as $n=>$place) {
			$url.=$n ? '&amp;' : '?';
			$url.='parent%5B%5D='.rawurlencode((string) $place);
		}
		$url.='&amp;ged='.rawurlencode(get_gedcom_from_id($this->gedcom_id));
		return $url;
	}

	public function getGedcomName() {
		return implode(self::GEDCOM_SEPARATOR, $this->gedcom_place);
	}

	public function getPlaceName() {
		$place=reset($this->gedcom_place);
		return $place ? '<span dir="auto">'.htmlspecialchars((string) $place).'</span>' : KT_I18N::translate('unknown');
	}

	public function getFullName() {
		$tmp=array();
		foreach ($this->gedcom_place as $place) {
			$tmp[]='<span dir="auto">' . htmlspecialchars((string) $place) . '</span>';
		}
		return implode(KT_I18N::$list_separator, $tmp);
	}

	// For lists and charts, where the full name won't fit.
	public function getShortName() {
		global $SHOW_PEDIGREE_PLACES, $SHOW_PEDIGREE_PLACES_SUFFIX;

		if ($SHOW_PEDIGREE_PLACES >= count($this->gedcom_place)) {
			// A short place name - no need to abbreviate
			return $this->getFullName();
		} else {
			// Abbreviate the place name, for lists
			if ($SHOW_PEDIGREE_PLACES_SUFFIX) {
				// The *last* $SHOW_PEDIGREE_PLACES components
				$short_name=implode(self::GEDCOM_SEPARATOR, array_slice($this->gedcom_place, -$SHOW_PEDIGREE_PLACES));
			} else {
				// The *first* $SHOW_PEDIGREE_PLACES components
				$short_name=implode(self::GEDCOM_SEPARATOR, array_slice($this->gedcom_place, 0, $SHOW_PEDIGREE_PLACES));
			}
			// Add a tool-tip showing the full name
			return '<span title="'.htmlspecialchars((string) $this->getGedcomName()).'" dir="auto">'.htmlspecialchars((string) $short_name).'</span>';
		}
	}

	// For the "view all" option of placelist.php and find.php
	public function getReverseName() {
		$tmp=array();
		foreach (array_reverse($this->gedcom_place) as $place) {
			$tmp[]='<span dir="auto">' . htmlspecialchars((string) $place) . '</span>';
		}
		return implode(KT_I18N::$list_separator, $tmp);
	}

	public static function allPlaces($gedcom_id) {
		$places=array();
		$rows=
			KT_DB::prepare(
				"SELECT CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place)".
				" FROM      `##places` AS p1".
				" LEFT JOIN `##places` AS p2 ON (p1.p_parent_id=p2.p_id)".
				" LEFT JOIN `##places` AS p3 ON (p2.p_parent_id=p3.p_id)".
				" LEFT JOIN `##places` AS p4 ON (p3.p_parent_id=p4.p_id)".
				" LEFT JOIN `##places` AS p5 ON (p4.p_parent_id=p5.p_id)".
				" LEFT JOIN `##places` AS p6 ON (p5.p_parent_id=p6.p_id)".
				" LEFT JOIN `##places` AS p7 ON (p6.p_parent_id=p7.p_id)".
				" LEFT JOIN `##places` AS p8 ON (p7.p_parent_id=p8.p_id)".
				" LEFT JOIN `##places` AS p9 ON (p8.p_parent_id=p9.p_id)".
				" WHERE p1.p_file=?".
				" ORDER BY CONCAT_WS(', ', p9.p_place, p8.p_place, p7.p_place, p6.p_place, p5.p_place, p4.p_place, p3.p_place, p2.p_place, p1.p_place) COLLATE '".KT_I18N::$collation."'"
			)
			->execute(array($gedcom_id))
			->fetchOneColumn();
		foreach ($rows as $row) {
			$places[]=new KT_Place($row, $gedcom_id);
		}
		return $places;
	}

    /**
     * [findPlaces description]
     * @param  [string] $filter     [string from place name to search for. This search searches for matches containing this string.]
     * @param  [integer] $gedcom_id [description]
     * @return [Structured string]  [A complete place name]
     */
	public static function findPlaces($filter, $gedcom_id) {
		$places = array();
		$rows   =
			KT_DB::prepare(
				"SELECT CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place)".
				" FROM      `##places` AS p1".
				" LEFT JOIN `##places` AS p2 ON (p1.p_parent_id=p2.p_id)".
				" LEFT JOIN `##places` AS p3 ON (p2.p_parent_id=p3.p_id)".
				" LEFT JOIN `##places` AS p4 ON (p3.p_parent_id=p4.p_id)".
				" LEFT JOIN `##places` AS p5 ON (p4.p_parent_id=p5.p_id)".
				" LEFT JOIN `##places` AS p6 ON (p5.p_parent_id=p6.p_id)".
				" LEFT JOIN `##places` AS p7 ON (p6.p_parent_id=p7.p_id)".
				" LEFT JOIN `##places` AS p8 ON (p7.p_parent_id=p8.p_id)".
				" LEFT JOIN `##places` AS p9 ON (p8.p_parent_id=p9.p_id)".
				" WHERE CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) LIKE CONCAT('%', ?, '%') AND CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) REGEXP CONCAT('^[^,]*', ?) AND p1.p_file=?".
				" ORDER BY  CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) COLLATE '".KT_I18N::$collation."'"
			)
			->execute(array($filter, preg_quote((string) $filter), $gedcom_id))
			->fetchOneColumn();
		foreach ($rows as $row) {
			$places[] = new KT_Place($row, $gedcom_id);
		}
		return $places;
	}

    /**
     * [findPlacesInitial description]
     * @param  [string] $filter     [string from place name to search for. This search only searches for matches starting with this string.]
     * @param  [integer] $gedcom_id [description]
     * @return [Structured string]  [A complete place name]
     */
    public static function findPlacesInitial($filter, $gedcom_id) {
		$places = array();
		$rows   =
			KT_DB::prepare(
				"SELECT CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place)".
				" FROM      `##places` AS p1".
				" LEFT JOIN `##places` AS p2 ON (p1.p_parent_id=p2.p_id)".
				" LEFT JOIN `##places` AS p3 ON (p2.p_parent_id=p3.p_id)".
				" LEFT JOIN `##places` AS p4 ON (p3.p_parent_id=p4.p_id)".
				" LEFT JOIN `##places` AS p5 ON (p4.p_parent_id=p5.p_id)".
				" LEFT JOIN `##places` AS p6 ON (p5.p_parent_id=p6.p_id)".
				" LEFT JOIN `##places` AS p7 ON (p6.p_parent_id=p7.p_id)".
				" LEFT JOIN `##places` AS p8 ON (p7.p_parent_id=p8.p_id)".
				" LEFT JOIN `##places` AS p9 ON (p8.p_parent_id=p9.p_id)".
				" WHERE CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) LIKE CONCAT(?, '%') AND p1.p_file=?".
				" ORDER BY CONCAT_WS(', ', p1.p_place, p2.p_place, p3.p_place, p4.p_place, p5.p_place, p6.p_place, p7.p_place, p8.p_place, p9.p_place) COLLATE '" . KT_I18N::$collation . "'"
			)
			->execute(array($filter, $gedcom_id))
			->fetchOneColumn();
		foreach ($rows as $row) {
			$places[] = new KT_Place($row, $gedcom_id);
		}
		return $places;
	}


}
