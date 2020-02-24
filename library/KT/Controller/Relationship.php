<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2020 kiwitrees.net
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

require_once KT_ROOT.'library/algorithm/optimized_dijkstra.php';

class Descendant {

	private $xref; //xref

	public function getXref() {
		return $this->xref;
	}

	public function __construct($xref) {
		$this->xref = $xref;
	}
}

class IdWithDescendant {

	private $id; //xref
	private $descendant; //Descendant

	public function getId() {
		return $this->id;
	}

	public function getDescendant() {
		return $this->descendant;
	}

	public function getXref() {
		return $this->getDescendant()->getXref();
	}

	public function __construct($id, Descendant $descendant) {
		$this->id = $id;
		$this->descendant = $descendant;
	}

	/**
	 *
	 * @return IdWithPathElement
	 */
	public function next($id) {
		return new IdWithDescendant($id, new Descendant($this->getId()));
	}
}

class CommonAncestorAndPath {

	private $ca; //xref
	private $path; //string[]

	public function getCommonAncestor() {
		return $this->ca;
	}

	public function getPath() {
		return $this->path;
	}

	public function getSize() {
		return count($this->path);
	}

	public function __construct($ca, $path) {
		$this->ca = $ca;
		$this->path = $path;
	}

	/**
	 *
	 * @return IdWithPathElement
	 */
	public function next($id) {
		return new IdWithDescendant($id, new Descendant($this->getId()));
	}
}

class CorPlus {

	private $cor;
	private $actuallyBetterThan;
	private $equivalentRelationships;

	/**
	 *
	 * @return double
	 */
	public function getCor() {
		return $this->cor;
	}

	/**
	 *
	 * @return int only abs value is relevant
	 */
	public function getActuallyBetterThan() {
		return $this->actuallyBetterThan;
	}

	/**
	 *
	 * @return string[]|null
	 */
	public function getEquivalentRelationships() {
		return $this->equivalentRelationships;
	}

	public function __construct($cor, $actuallyBetterThan, $equivalentRelationships) {
		$this->cor = $cor;
		$this->actuallyBetterThan = $actuallyBetterThan;
		$this->equivalentRelationships = $equivalentRelationships;
	}
}

/**
 * Controller for the relationships calculations
 */
class KT_Controller_Relationship extends KT_Controller_Page {

	/**
	* Convert a path (list of XREFs) to an "old-style" string of relationships.
	*
	* Return an empty array, if privacy rules prevent us viewing any node.
	*
	* Adapted from webtrees\app\Http\Controllers\RelationshipsChartController.php
	* @param string[] $path Alternately Individual / Family
	*
	* @return string[]
	*/
	public function oldStyleRelationshipPath(array $path): array {
		$spouse_codes = [
			'M' => 'hus',
			'F' => 'wif',
			'U' => 'spo',
		];
		$parent_codes = [
			'M' => 'fat',
			'F' => 'mot',
			'U' => 'par',
		];
		$child_codes = [
			'M' => 'son',
			'F' => 'dau',
			'U' => 'chi',
		];
		$sibling_codes = [
			'M' => 'bro',
			'F' => 'sis',
			'U' => 'sib',
		];
		$relationships = [];
		global $KT_TREE;

		for ($i = 1, $count = count($path); $i < $count; $i += 2) {
			$family = KT_Family::getInstance($path[$i], $KT_TREE);
			$prev   = KT_Person::getInstance($path[$i - 1], $KT_TREE);
			$next   = KT_Person::getInstance($path[$i + 1], $KT_TREE);
			if (preg_match('/\n\d (HUSB|WIFE|CHIL) @' . $prev->getXref() . '@/', $family->getGedcomRecord(), $match)) {
				$rel1 = $match[1];
			} else {
				return [];
			}
			if (preg_match('/\n\d (HUSB|WIFE|CHIL) @' . $next->getXref() . '@/', $family->getGedcomRecord(), $match)) {
				$rel2 = $match[1];
			} else {
				return [];
			}
			if (($rel1 === 'HUSB' || $rel1 === 'WIFE') && ($rel2 === 'HUSB' || $rel2 === 'WIFE')) {
				$relationships[$i] = $spouse_codes[$next->getSex()];
			} elseif (($rel1 === 'HUSB' || $rel1 === 'WIFE') && $rel2 === 'CHIL') {
				$relationships[$i] = $child_codes[$next->getSex()];
			} elseif ($rel1 === 'CHIL' && ($rel2 === 'HUSB' || $rel2 === 'WIFE')) {
					$relationships[$i] = $parent_codes[$next->getSex()];
			} elseif ($rel1 === 'CHIL' && $rel2 === 'CHIL') {
			$relationships[$i] = $sibling_codes[$next->getSex()];
			}
		}

		return $relationships;
	}

	public function getCorFromPaths($paths) {
		$cor = 0.0;
		foreach ($paths as $path) {
			$pathSegments = count($path)-1;

			//if ca is INDI, we have to add a single path for this ca.
			//
			//if ca is FAM, we actually have to add two paths (one per family spouse) of length $pathSegments+2
			//i.e. the formula is
			//$cor += 2*pow(2,-($pathSegments+2)/2);
			//which is the same as
			//$cor += pow(2,-$pathSegments/2);
			//so we don't actually have to distinguish the two cases here!

			//in each case,
			//divide by 2 to collapse all 'INDI - FAM - INDI' segments to 'INDI - INDI' segments

			$cor += pow(2,-$pathSegments/2);
		}
		return $cor;
	}

	public function getCorFromCaAndPaths($caAndPaths) {
		$bestPathLength = null;
		$bestPath = null;

		$cor = 0.0;
		foreach ($caAndPaths as $caAndPath) {
			$path = $caAndPath->getPath();
			$pathSegments = count($path)-1;

			//if ca is INDI, we have to add a single path for this ca.
			//
			//if ca is FAM, we actually have to add two paths (one per family spouse) of length $pathSegments+2
			//i.e. the formula is
			//$cor += 2*pow(2,-($pathSegments+2)/2);
			//which is the same as
			//$cor += pow(2,-$pathSegments/2);
			//so we don't actually have to distinguish the two cases here!

			//in each case,
			//divide by 2 to collapse all 'INDI - FAM - INDI' segments to 'INDI - INDI' segments

			$singleCor = pow(2,-$pathSegments/2);
			$cor += $singleCor;

			if (!$bestPathLength) {
				$bestPathLength = $pathSegments/2;
				$bestPath = $path;
			}
		}

		$actuallyBetterThan = 1;
		$equivalentPathLength = -(log($cor,2));
		$eplFraction = $equivalentPathLength - floor($equivalentPathLength);
		if ($eplFraction < 0.001) {
			$actuallyBetterThan = 0;
		} else if ($eplFraction < 0.5) {
			$actuallyBetterThan = -1;
		}
		$equivalentPathLength = (int)round($equivalentPathLength);
		if ($equivalentPathLength == $bestPathLength) {
			return new CorPlus($cor, $actuallyBetterThan, null);
		}

		//create an equivalent relationship that's similar 'in character'
		//to the relationship established via the closest ca

		$relationships = $this->oldStyleRelationshipPath($bestPath);
		$up = array();
		$right = array();
		$dn = array();
		foreach ($relationships as $rel) {
			if (("mot" === $rel) || ("fat" === $rel)) {
				$up[] = $rel;
			} else if (("bro" === $rel) || ("sis" === $rel)) {
				$right[] = $rel;
			} else {
				$dn[] = $rel;
			}
		}

		$toRemove = $bestPathLength - $equivalentPathLength;
		$upShortened = sizeof($up);
		$dnShortened = sizeof($dn);
		$diff = abs(sizeof($up) - sizeof($dn));
		if ($toRemove <= $diff) {
			//remove entirely from longer
			if (sizeof($up) > sizeof($dn)) {
				$upShortened -= $toRemove;
			} else {
				$dnShortened -= $toRemove;
			}
		} else {
			if ($diff === 0) {
				$toRemoveFromBoth = floor($toRemove/2);
				$upShortened -= $toRemoveFromBoth;
				$dnShortened -= $toRemoveFromBoth;
				if ($toRemove % 2 !== 0) {
					//we have to pick one
					$upShortened -= 1;
				}
			} else {
				//remove as much as possible from longer
				$toRemoveFromBoth = ceil(($toRemove - $diff)/2);
				$toRemoveFromLonger = $toRemove - $toRemoveFromBoth*2;

				$upShortened -= $toRemoveFromBoth;
				$dnShortened -= $toRemoveFromBoth;
				if (sizeof($up) > sizeof($dn)) {
					$upShortened -= $toRemoveFromLonger;
				} else {
					$dnShortened -= $toRemoveFromLonger;
				}
			}
		}

		//shorten 'least significant' parts of path
		$shortenedRelationships = array();
		for ($i=0; $i<$upShortened; $i++) {
			$shortenedRelationships[] = $up[$i+sizeof($up)-$upShortened];
		}
		//0 or 1 times
		foreach ($right as $rel) {
			$shortenedRelationships[] = $rel;
		}
		for ($i=0; $i<$dnShortened; $i++) {
			$shortenedRelationships[] = $dn[$i+sizeof($dn)-$dnShortened];
		}

		return new CorPlus($cor, $actuallyBetterThan, $shortenedRelationships);
	}

	public function calculateRelationships_123456(KT_Person $individual1, KT_Person $individual2, $mode, $recursion, $beforeJD = null) {
		global $KT_TREE;
		return $this->x_calculateRelationships_123456($KT_TREE, $individual1->getXref(), $individual2->getXref(), $mode, $recursion, $beforeJD);
	}

	public function x_calculateRelationships_123456($KT_TREE, $xref1, $xref2, $mode, $recursion, $beforeJD = null) {
		global $KT_TREE;
		if ($mode === 1) {
			//single slca
			$caAndPaths = $this->x_calculateRelationships_slca($KT_TREE, $xref1, $xref2, $mode);
			$paths = array();
			foreach ($caAndPaths as $caAndPath) {
				$paths[] = $caAndPath->getPath();
			}
			return $paths;
		}

		if ($mode === 2) {
			//all slcas
			$caAndPaths = $this->x_calculateRelationships_slca($KT_TREE, $xref1, $xref2, $mode);
			$paths = array();
			foreach ($caAndPaths as $caAndPath) {
				$paths[] = $caAndPath->getPath();
			}
			return $paths;
		}

		if ($mode === 3) {
			//lcas: all paths for CoR (uncorrected coefficient of relationship)
			$caAndPaths = $this->x_calculateRelationships_slca($KT_TREE, $xref1, $xref2, $mode);
			$paths = array();
			foreach ($caAndPaths as $caAndPath) {
				$paths[] = $caAndPath->getPath();
			}
			return $paths;
		}

		if ($mode === 4) {
			//adjusted original algorithm + dated links
			$ret = $this->x_calculateRelationships_withWeights($KT_TREE, $xref1, $xref2, 0, $beforeJD);
			if (empty($ret) && $beforeJD) {
				//beforeJD and nothing found - check if we can at least provide results via common ancestors
				//(dated links may not have found anything due to insufficient dates)
				return $this->x_calculateRelationships_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			}
			return $ret;
		}

		if ($mode === 5) {
			//original algorithm, optimized + dated links
			$ret = $this->x_calculateRelationships_optimized($KT_TREE, $xref1, $xref2, 0, $beforeJD);
			if (empty($ret) && $beforeJD) {
				//beforeJD and nothing found - check if we can at least provide results via common ancestors
				//(dated links may not have found anything due to insufficient dates)
				return $this->x_calculateRelationships_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			}
			return $ret;
		}

		if ($mode === 6) {
			//original algorithm, optimized + dated links
			$ret = $this->x_calculateRelationships_optimized($KT_TREE, $xref1, $xref2, $recursion, $beforeJD);
			if (empty($ret) && $beforeJD) {
				//beforeJD and nothing found - check if we can at least provide results via common ancestors
				//(dated links may not have found anything due to insufficient dates)
				return $this->x_calculateRelationships_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			}
			return $ret;
		}

		//1 with fallback to 5
		if ($mode === 7) {
			//1
			$ret = $this->x_calculateRelationships_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			if (!empty($ret)) {
				return $ret;
			}
			//5 (directly: if $beforeJD, we don't have to fallback again to 1)
			return $this->x_calculateRelationships_optimized($KT_TREE, $xref1, $xref2, 0, $beforeJD);
			//return $this->x_calculateRelationships_123456($tree, $xref1, $xref2, 5, $recursion, $beforeJD);
		}

		return "unexpected mode!";
	}

	public function calculateCaAndPaths_123456(KT_Person $individual1, KT_Person $individual2, $mode, $recursion, $beforeJD = null) {
		global $KT_TREE;
		return $this->x_calculateCaAndPaths_123456($KT_TREE, $individual1->getXref(), $individual2->getXref(), $mode, $recursion, $beforeJD);
	}

	public function x_calculateCaAndPaths_123456($KT_TREE, $xref1, $xref2, $mode, $recursion, $beforeJD = null) {
		if ($mode === 1) {
			//single slca
			return $this->x_calculateRelationships_slca($KT_TREE, $xref1, $xref2, $mode);
		}

		if ($mode === 2) {
			//all slcas
			return $this->x_calculateRelationships_slca($KT_TREE, $xref1, $xref2, $mode);
		}

		if ($mode === 3) {
			//lcas: all paths for CoR (uncorrected coefficient of relationship)
			return $this->x_calculateRelationships_slca($KT_TREE, $xref1, $xref2, $mode);
		}

		if ($mode === 4) {
			//adjusted original algorithm + dated links
			$paths = $this->x_calculateRelationships_withWeights($KT_TREE, $xref1, $xref2, 0, $beforeJD);
			if (empty($paths) && $beforeJD) {
				//beforeJD and nothing found - check if we can at least provide results via common ancestors
				//(dated links may not have found anything due to insufficient dates)
				return $this->x_calculateCaAndPaths_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			}
			$caAndPaths = array();
			foreach ($paths as $path) {
				$caAndPaths[] = new CommonAncestorAndPath(null, $path);
			}
			return $caAndPaths;
		}

		if ($mode === 5) {
			//original algorithm, optimized + dated links
			$paths = $this->x_calculateRelationships_optimized($KT_TREE, $xref1, $xref2, 0, $beforeJD);
			if (empty($paths) && $beforeJD) {
				//beforeJD and nothing found - check if we can at least provide results via common ancestors
				//(dated links may not have found anything due to insufficient dates)
				return $this->x_calculateCaAndPaths_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			}
			$caAndPaths = array();
			foreach ($paths as $path) {
				$caAndPaths[] = new CommonAncestorAndPath(null, $path);
			}
			return $caAndPaths;
		}

		if ($mode === 6) {
			//original algorithm, optimized + dated links
			$paths = $this->x_calculateRelationships_optimized($KT_TREE, $xref1, $xref2, $recursion, $beforeJD);
			if (empty($paths) && $beforeJD) {
				//beforeJD and nothing found - check if we can at least provide results via common ancestors
				//(dated links may not have found anything due to insufficient dates)
				return $this->x_calculateCaAndPaths_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			}
			$caAndPaths = array();
			foreach ($paths as $path) {
				$caAndPaths[] = new CommonAncestorAndPath(null, $path);
			}
			return $caAndPaths;
		}

		//mode 7: 1 with fallback to 5
		if ($mode === 7) {
			//1
			$ret = $this->x_calculateCaAndPaths_123456($KT_TREE, $xref1, $xref2, 1, $recursion);
			if (!empty($ret)) {
				return $ret;
			}
			//5 (directly: if $beforeJD, we don't have to fallback again to 1)
			$paths = $this->x_calculateRelationships_optimized($KT_TREE, $xref1, $xref2, 0, $beforeJD);
			$caAndPaths = array();
			foreach ($paths as $path) {
				$caAndPaths[] = new CommonAncestorAndPath(null, $path);
			}
			return $caAndPaths;
			//return $this->x_calculateCaAndPaths_123456($tree, $xref1, $xref2, 5, $recursion, $beforeJD);
		}

		return "unexpected mode!";
	}

	public static function compareCommonAncestorAndPath(CommonAncestorAndPath $a, CommonAncestorAndPath $b) {
		if ($a == $b) {
			return 0;
    }
    return ($a->getSize() < $b->getSize()) ? -1 : 1;
	}

	public function calculateRelationships_slca(KT_Person $individual1, KT_Person $individual2, $mode) {
		global $KT_TREE;
		return $this->x_calculateRelationships_slca($KT_TREE, $individual1->getXref(), $individual2->getXref(), $mode);
	}

	/**
	 * 'naive' algorithm (no precomputations), performance seems to be sufficient for ~max. 15 generations
	 *
	 * @param String  $xref1
	 * @param String  $xref2
	 * @param integer $mode
	 *
	 * @return CommonAncestorAndPath[]
	 */
	public function x_calculateRelationships_slca($tree, $xref1, $xref2, $mode) {

		$rows = KT_DB::prepare(
			//we don't need 'FAMS', 'CHIL' (we're only ascending!)
			"SELECT l_from, l_to FROM `##link` WHERE l_file = :tree_id AND l_type IN ('FAMC', 'HUSB', 'WIFE')" // AND l_from = :from
		)->execute(array(
			'tree_id' => $tree->getTreeId()

			//fetching one seems to be not much faster than fetching all ...
			/*,'from' => $xref1()*/
		))->fetchAll();

		$graph = array();
		foreach ($rows as $row) {
			if (!array_key_exists($row->l_from, $graph)) {
				$graph[$row->l_from] = array();
			}
			$graph[$row->l_from][] = $row->l_to;
		}

		$queue1 = array(); //key = (generated); value = IdWithDescendant;
		$queue1[] = new IdWithDescendant($xref1, new Descendant(null));
		$ancestors1 = array(); //key = id; value = array of (key = Descendant xref, value = Descendant);

		while (!is_null($current = array_shift($queue1))) {
			//echo "in queue:".$current;
			if (($mode != 3) && array_key_exists($current->getId(), $ancestors1)) {
				//implex
				//echo "already there!";
			} else {
				//add to ancestors
				if (!array_key_exists($current->getId(), $ancestors1)) {
					$ancestors1[$current->getId()] = array();
				}
				//add (effectively no-op if combination already exists)
				$ancestors1[$current->getId()][$current->getXref()] = $current->getDescendant();

				//add ancestors to queue
				if (array_key_exists($current->getId(), $graph)) {
					foreach ($graph[$current->getId()] as $next) {
						$queue1[] = $current->next($next);
					}
				}
			}
		}

		$queue2 = array(); //key = (generated); value = Descendant;
		$queue2[] = new IdWithDescendant($xref2, new Descendant(null));
		$ancestors2 = array(); //key = id; value = array of (key = Descendant xref, value = Descendant);

		//cas = common ancestors
		//lcas = lowest common ancestors
		//slcas = smallest lowest common ancestors

		//if (mode == 3), later filtered to lcas
		//if (mode != 3), only collects some lcas, later filtered to slcas
		$cas = array(); //key = id; value = array of (key = Descendant xref, value = Descendant);

		while (!is_null($current = array_shift($queue2))) {
			//echo "in queue:".$current;
			if (($mode != 3) && array_key_exists($current->getId(), $ancestors2)) {
				//implex
				//echo "already there 2: "; self::debug_echo($current->getId());
			} else {
				//is it a common ancestor?
				if (array_key_exists($current->getId(), $ancestors1)) {
					if (($mode != 3) && array_key_exists($current->getId(), $cas)) {
						//implex
						//echo "ca already there: "; self::debug_echo($current->getId());
					} else {
						//echo "ca: "; self::debug_echo($current->getId());

						//add to cas
						if (!array_key_exists($current->getId(), $cas)) {
							$cas[$current->getId()] = array();
						}
						//add (effectively no-op if combination already exists)
						$cas[$current->getId()][$current->getXref()] = $current->getDescendant();
					}

					if ($mode != 3) {
						//we can stop here, cas further up are no slcas
					} else {

						//add to ancestors
						if (!array_key_exists($current->getId(), $ancestors2)) {
							$ancestors2[$current->getId()] = array();
						}
						//add (effectively no-op if combination already exists)
						$ancestors2[$current->getId()][$current->getXref()] = $current->getDescendant();

						//add ancestors to queue
						if (array_key_exists($current->getId(), $graph)) {
							foreach ($graph[$current->getId()] as $next) {
								$queue2[] = $current->next($next);
							}
						}
					}
				} else {
					//echo "add: "; self::debug_echo($current->getId());

					//add to ancestors
					if (!array_key_exists($current->getId(), $ancestors2)) {
						$ancestors2[$current->getId()] = array();
					}
					//add (effectively no-op if combination already exists)
					$ancestors2[$current->getId()][$current->getXref()] = $current->getDescendant();

					//add ancestors to queue
					if (array_key_exists($current->getId(), $graph)) {
						foreach ($graph[$current->getId()] as $next) {
							$queue2[] = $current->next($next);
						}
					}
				}
			}
		}

		//finished processing queues
		/*
		echo "finished <br/>";
		foreach ($cas as $caKey => $caValue) {
			echo "ca: "; self::debug_echo($caKey);
			foreach ($caValue as $caValue2) {
				echo "ca desc: "; echo $caValue2->getXref();
				echo "<br/>";
			}
		}
		*/

		//filter to lcas or slcas, and get the paths
		$paths = array();
		foreach ($cas as $caKey => $caValue) {
			foreach ($caValue as $caValue2) {
				$caPaths = self::getPaths($caKey, $caValue2->getXref(), $mode, $cas, $ancestors1, $ancestors2);
				foreach ($caPaths as $caPath) {
					$paths[] = new CommonAncestorAndPath($caKey, $caPath);
				}
			}
		}

		//sort by length (couold additional sort, in case of same length, by sth like distance to ca)
		usort($paths, array($this,'compareCommonAncestorAndPath'));

		if ($mode != 1) {
			return $paths;
		}

		if (empty($paths)) {
			return $paths;
		}

		//return one of the shortest paths
		return array(array_shift($paths));
	}

	private static function getPaths($ca, $caDescendant, $mode, $cas, $ancestors1, $ancestors2) {
		$path = array();

		//initialize ascent
		$path[] = $ca;

		return self::getPathsAscending($path, $caDescendant, $mode, $cas, $ancestors1, $ancestors2);
	}

	private static function getPathsAscending($path, $caDescendant, $mode, $cas, $ancestors1, $ancestors2) {
		//ascend (moving backwards from ca)

		//get first element of current path
		$next = reset($path);
		//echo "asc: "; self::debug_echo($next);

		$paths = array();
		foreach ($ancestors1[$next] as $value) {
			$next = $value->getXref();

			if ($next) {
				if (self::abortAscent($next, $mode, $cas)) {
					return array();
				}

				//copy path
				$newPath = $path;

				//add next element (at the front)
				array_unshift($newPath, $next);

				$extPaths = self::getPathsAscending($newPath, $caDescendant, $mode, $cas, $ancestors1, $ancestors2);
				$paths = array_merge($paths, $extPaths);
			} else {
				//we're finished ascending here

				if (!$caDescendant) {
					//ca is the target = we're done
					$paths[] = $path;

				} else {
					if (self::abortDescent($caDescendant, $path, $mode, $cas)) {
						return array();
					}

					//initialize descent
					$path[] = $caDescendant;

					$extPaths = self::getPathsDescending($path, $mode, $cas, $ancestors2);
					$paths = array_merge($paths, $extPaths);
				}
			}
		}

		return $paths;
	}

	private static function abortAscent($next, $mode, $cas) {
		if (($mode != 3) && array_key_exists($next, $cas)) {
			//this lca ascends through another lca = it's no slca!
			//echo "skip lca on ascent: "; self::debug_echo($next);

			//abort
			return true;
		}

		return false;
	}

	private static function abortDescent($next, $path, $mode, $cas) {
		if (($mode != 3) && array_key_exists($next, $cas)) {
			//this lca descends through another lca = it's no slca!
			//echo "skip lca on descent: "; self::debug_echo($next);

			//abort
			return true;
		}

		if (($mode == 3) && in_array($next, $path)) {
			//this ca ascends and descends through the same node = it's no lca!

			//abort
			return true;
		} //else no need to check - cannot occur

		return false;
	}

	private static function getPathsDescending($path, $mode, $cas, $ancestors2) {
		//descend (moving forwards from ca)

		//get last element of current path
		$next = end($path);
		//echo "desc: "; self::debug_echo($next);

		$paths = array();

		foreach ($ancestors2[$next] as $value) {
			$next = $value->getXref();

			if ($next) {
				if (self::abortDescent($next, $path, $mode, $cas)) {
					return array();
				}

				//copy path
				$newPath = $path;

				//add next element
				$newPath[] = $next;

				$extPaths = self::getPathsDescending($newPath, $mode, $cas, $ancestors2);
				$paths = array_merge($paths, $extPaths);
			} else {
				//we're finished
				$paths[] = $path;
			}
		}

		return $paths;
	}

	private static function debug_echo($xref) {
		global $KT_TREE;

		//not correct in all cases: INDIs may use different prefixes!
		//$caIsIndi = (substr($slcaKey, 0, 1) === "I");

		$caIsIndi = KT_GedcomRecord::getInstance($xref, $KT_TREE);
//		$caIsIndi = "Fisharebest\Webtrees\Individual" === get_class($record);

		if ($caIsIndi) {
			$indi = $record; //KT_Person::getInstance($xref, $WT_TREE);
			echo $indi->getFullName();
		} else {
			$fam = $record; //Family::getInstance($xref, $WT_TREE);
			foreach ($fam->getSpouses() as $indi) {
				$names[] = $indi->getFullName();
			}
			$famName = implode(' & ', $names);
			echo $famName;
		}
		echo "<br />";
	}

	public function calculateRelationships_withWeights(KT_Person $individual1, KT_Person $individual2, $all, $beforeJD = null) {
		global $KT_TREE;
		return $this->x_calculateRelationships_withWeights($KT_TREE, $individual1->getXref(), $individual2->getXref(), $all, $beforeJD);
	}

	/**
	 * Calculate the shortest paths - or all paths - between two individuals.
	 * blood relationships preferred!
	 * extension: only links established before given julian day
	 *
	 * @param String $xref1
	 * @param String $xref2
	 * @param bool   $all
	 *
	 * @return string[][]
	 */
	public function x_calculateRelationships_withWeights($tree, $xref1, $xref2, $all, $beforeJD = null) {
		$graph = array();

		if ($beforeJD === null) {
			$rows = KT_DB::prepare(
				"SELECT l_from, l_to, l_type FROM `##link` WHERE l_file = :tree_id AND l_type IN ('FAMS', 'FAMC')"
			)->execute(array(
				'tree_id' => $tree->getTreeId(),
			))->fetchAll();

			foreach ($rows as $row) {
				if ($row->l_type === 'FAMS') {
					//edge between 'descent' nodes
					$graph["D_".$row->l_from]["D_".$row->l_to] = 1;

					//revert as ascent node (HUSB/WIFE)
					$graph["A_".$row->l_to]["A_".$row->l_from] = 1;
				} else {
					//edge between 'ascent' nodes
					$graph["A_".$row->l_from]["A_".$row->l_to] = 1;

					//revert as descent node (CHIL)
					$graph["D_".$row->l_to]["D_".$row->l_from] = 1;
				}

				//edges connecting 'ascent' and 'descent' nodes
				//(maybe added more than once per node)
				$graph["A_".$row->l_from]["D_".$row->l_from] = 0; //turn around (related)
				$graph["A_".$row->l_to]["D_".$row->l_to] = 0; //turn around (related)
				$graph["D_".$row->l_from]["A_".$row->l_from] = 1000; //turn around (non-related)
				$graph["D_".$row->l_to]["A_".$row->l_to] = 1000; //turn around (non-related)
			}
		} else {
			//make sure the tables exist.
			Sync::initializeSchema();

			$rows = KT_DB::prepare(
				"SELECT l_from, l_to FROM `##link`
				INNER JOIN ##rel_families ON ##link.l_to = ##rel_families.f_id AND ##link.l_file = ##rel_families.f_file AND ##rel_families.f_from < :beforeJD
				WHERE l_file = :tree_id AND l_type = 'FAMS'"
			)->execute(array(
				'tree_id' => $tree->getTreeId(),
				'beforeJD' => $beforeJD
			))->fetchAll();

			foreach ($rows as $row) {
				//edge between 'descent' nodes
				$graph["D_".$row->l_from]["D_".$row->l_to] = 1;

				//revert as ascent node (HUSB/WIFE)
				$graph["A_".$row->l_to]["A_".$row->l_from] = 1;

				//edges connecting 'ascent' and 'descent' nodes
				//(maybe added more than once per node)
				$graph["A_".$row->l_from]["D_".$row->l_from] = 0; //turn around (related)
				$graph["A_".$row->l_to]["D_".$row->l_to] = 0; //turn around (related)
				$graph["D_".$row->l_from]["A_".$row->l_from] = 1000; //turn around (non-related)
				$graph["D_".$row->l_to]["A_".$row->l_to] = 1000; //turn around (non-related)
			}

			$rows = KT_DB::prepare(
				"SELECT l_from, l_to FROM `##link`
				INNER JOIN ##rel_individuals ON ##link.l_from = ##rel_individuals.i_id AND ##link.l_file = ##rel_individuals.i_file AND ##rel_individuals.i_from < :beforeJD
				WHERE l_file = :tree_id AND l_type = 'FAMC'"
			)->execute(array(
				'tree_id' => $tree->getTreeId(),
				'beforeJD' => $beforeJD
			))->fetchAll();

			foreach ($rows as $row) {
				//edge between 'ascent' nodes
				$graph["A_".$row->l_from]["A_".$row->l_to] = 1;

				//revert as descent node (CHIL)
				$graph["D_".$row->l_to]["D_".$row->l_from] = 1;

				//edges connecting 'ascent' and 'descent' nodes
				//(maybe added more than once per node)
				$graph["A_".$row->l_from]["D_".$row->l_from] = 0; //turn around (related)
				$graph["A_".$row->l_to]["D_".$row->l_to] = 0; //turn around (related)
				$graph["D_".$row->l_from]["A_".$row->l_from] = 1000; //turn around (non-related)
				$graph["D_".$row->l_to]["A_".$row->l_to] = 1000; //turn around (non-related)
			}
		}

		$dijkstra = new OptimizedDijkstra($graph);

		$paths = $dijkstra->shortestPaths("A_".$xref1, "D_".$xref2);
		$paths = KT_Controller_Relationship::adjustPaths($paths);

		if ($all) {
			// Only process each exclusion list once;
			$excluded = array();

			$queue = array();
			foreach ($paths as $path) {
				// Insert the paths into the queue, with an exclusion list.
				$queue[] = array('path' => $path, 'exclude' => array());
				// While there are un-extended paths
				while (list(, $next) = each($queue)) {
					// For each family on the path
					for ($n = count($next['path']) - 2; $n >= 1; $n -= 2) {
						$exclude   = $next['exclude'];
						$exclude[] = $next['path'][$n];
						sort($exclude);
						$tmp = implode('-', $exclude);
						if (in_array($tmp, $excluded)) {
							continue;
						} else {
							$excluded[] = $tmp;
						}
						// Add any new path to the queue
						$new_paths = $dijkstra->shortestPaths($xref1, $xref2, $costFunction, $exclude);
						$new_paths = ExtendedRelationshipController::adjustPaths($new_paths);
						foreach ($new_paths as $new_path) {
							$queue[] = array('path' => $new_path, 'exclude' => $exclude);
						}
					}
				}
			}
			// Extract the paths from the queue, removing duplicates.
			$paths = array();
			foreach ($queue as $next) {
				$paths[implode('-', $next['path'])] = $next['path'];
			}
		}

		//1. would be nice to have additional tiebreakers for multiple paths with same length and same weight,
		//in order to obtain consistent ordering;
		//2. seems reasonable to use symmetric ordering (order should be the same when swapping source and target individual),
		//otherwise potentially confusing;
		//due to 2., we cannot use "shortest name" reliably, nor sth like positions of weights within the path
		//we could use 'established on date', but that's too inefficient to determine
		//
		//remaining options (resulting order is semantically rather irrelevant):
		//compare via participating individuals, use smallest alphanumeric id(s) as tiebreaker

		return $paths;
	}

	public function calculateRelationships_optimized(KT_Person $individual1, KT_Person $individual2, $recursion, $beforeJD = null) {
		global $KT_TREE;
		return $this->x_calculateRelationships_optimized($KT_TREE, $individual1->getXref(), $individual2->getXref(), $recursion, $beforeJD);
	}

	//adjustment from original: OptimizedDijkstra
	//extension: only links established before given julian day
	public function x_calculateRelationships_optimized($tree, $xref1, $xref2, $recursion, $beforeJD = null) {
		$graph = array();

		if ($beforeJD === null) {
			$rows = KT_DB::prepare(
				"SELECT l_from, l_to FROM `##link` WHERE l_file = :tree_id AND l_type IN ('FAMS', 'FAMC')"
			)->execute(array(
				'tree_id' => $tree->getTreeId(),
			))->fetchAll();

			foreach ($rows as $row) {
				$graph[$row->l_from][$row->l_to] = 1;
				$graph[$row->l_to][$row->l_from] = 1;
			}
		} else {
			//make sure the tables exist.
			Sync::initializeSchema();

			$rows = KT_DB::prepare(
				"SELECT l_from, l_to FROM `##link`
				INNER JOIN ##rel_families ON ##link.l_to = ##rel_families.f_id AND ##link.l_file = ##rel_families.f_file AND ##rel_families.f_from < :beforeJD
				WHERE l_file = :tree_id AND l_type = 'FAMS'"
			)->execute(array(
				'tree_id' => $tree->getTreeId(),
				'beforeJD' => $beforeJD
			))->fetchAll();

			foreach ($rows as $row) {
				$graph[$row->l_from][$row->l_to] = 1;
				$graph[$row->l_to][$row->l_from] = 1;
			}

			$rows = KT_DB::prepare(
				"SELECT l_from, l_to FROM `##link`
				INNER JOIN ##rel_individuals ON ##link.l_from = ##rel_individuals.i_id AND ##link.l_file = ##rel_individuals.i_file AND ##rel_individuals.i_from < :beforeJD
				WHERE l_file = :tree_id AND l_type = 'FAMC'"
			)->execute(array(
				'tree_id' => $tree->getTreeId(),
				'beforeJD' => $beforeJD
			))->fetchAll();

			foreach ($rows as $row) {
				$graph[$row->l_from][$row->l_to] = 1;
				$graph[$row->l_to][$row->l_from] = 1;
			}
		}

		$dijkstra = new OptimizedDijkstra($graph);
		$paths    = $dijkstra->shortestPaths($xref1, $xref2);

		// Only process each exclusion list once;
		$excluded = array();

		$queue = array();
		foreach ($paths as $path) {
			// Insert the paths into the queue, with an exclusion list.
			$queue[] = array('path' => $path, 'exclude' => array());
			// While there are un-extended paths
			//while (list(, $next) = each($queue)) {
			foreach ($queue as $next) {
				// For each family on the path
				for ($n = count($next['path']) - 2; $n >= 1; $n -= 2) {
					$exclude = $next['exclude'];
					if (count($exclude) >= $recursion) {
						continue;
					}
					$exclude[] = $next['path'][$n];
					sort($exclude);
					$tmp = implode('-', $exclude);
					if (in_array($tmp, $excluded)) {
						continue;
					} else {
						$excluded[] = $tmp;
					}
					// Add any new path to the queue
					foreach ($dijkstra->shortestPaths($xref1, $xref2, $exclude) as $new_path) {
						$queue[] = array('path' => $new_path, 'exclude' => $exclude);
					}
				}
			}
		}
		// Extract the paths from the queue, removing duplicates.
		$paths = array();
		foreach ($queue as $next) {
			$paths[implode('-', $next['path'])] = $next['path'];
		}

		return $paths;
	}

	public static function adjustPaths($paths) {
		//clean up paths
		$finalPaths = array();
		foreach ($paths as $path) {
			$finalPath = array();
			$previous = null;
			foreach ($path as $pathElement) {
				$pathElement = substr($pathElement, 2);
				if ($previous !== $pathElement) {
					$finalPath[] = $pathElement;
					$previous = $pathElement;
				}
			}
			$finalPaths[] = $finalPath;
		}

		return $finalPaths;
	}
}
