<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2023 kiwitrees.net
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

/**
* print the information for an individual chart box
*
* find and print a given individuals information for a pedigree chart
* @param string $pid the Gedcom Xref ID of the   to print
* @param int $style the style to print the box in, 1 for smaller boxes, 2 for larger boxes
* @param int $count on some charts it is important to keep a count of how many boxes were printed
*/
function print_pedigree_person($person, $style = 1, $count = 0, $personcount = "1") {
	global $HIDE_LIVE_PEOPLE, $SHOW_LIVING_NAMES, $GEDCOM;
	global $SHOW_HIGHLIGHT_IMAGES, $bwidth, $bheight, $PEDIGREE_FULL_DETAILS, $SHOW_PEDIGREE_PLACES;
	global $TEXT_DIRECTION, $DEFAULT_PEDIGREE_GENERATIONS, $OLD_PGENS, $talloffset, $PEDIGREE_LAYOUT;
	global $ABBREVIATE_CHART_LABELS;
	global $chart_style, $box_width, $generations, $show_spouse, $show_full;
	global $CHART_BOX_TAGS, $SHOW_LDS_AT_GLANCE, $PEDIGREE_SHOW_GENDER;
	global $SEARCH_SPIDER;

	if (empty($show_full)) {
		$show_full = 0;
	}
	if ($style == 3) {
		$show_full = 1;
	}
	if (empty($PEDIGREE_FULL_DETAILS)) {
		$PEDIGREE_FULL_DETAILS = 0;
	}

	if (!isset($OLD_PGENS)) {
		$OLD_PGENS = $DEFAULT_PEDIGREE_GENERATIONS;
	}
	if (!isset($talloffset)) {
		$talloffset = $PEDIGREE_LAYOUT;
	}
	// NOTE: Start div out-rand()
	if (!$person) {
		echo "<div id=\"out-", rand(), "\" class=\"person_boxNN\" style=\"width: ", $bwidth, "px; height: ", $bheight, "px; overflow: hidden;\">";
		echo '<br>';
		echo '</div>';
		return false;
	}
	$pid = $person->getXref();
	if ($count == 0) {
		$count = rand();
	}
	$lbwidth = $bwidth*.75;

	$lbwidth = $bwidth * .75;
	if ($lbwidth < 150) {
		$lbwidth = 150;
	}

	$tmp			= array('M'=>'', 'F'=>'F', 'U'=>'NN');
	$isF			= $tmp[$person->getSex()];
	$icons			= '';
	$classfacts		= '';
	$genderImage	= '';
	$BirthDeath		= '';
	$birthplace		= '';
	$outBoxAdd		= '';
	$showid			= '';
	$iconsStyleAdd	= 'float:right;';
	if ($TEXT_DIRECTION == 'rtl') {
		$iconsStyleAdd = 'float:left;';
	}

	$disp			= $person->canDisplayDetails();
	$uniqueID		= (int)(microtime(true) * 1000000);
	$boxID			= $pid . '.' . $personcount . '.' . $count . '.' . $uniqueID;
	$mouseAction4	= ' onclick="expandbox(\'' . $boxID . '\', ' . $style . '); return false;"';
	if ($person->canDisplayName()) {
		if (empty($SEARCH_SPIDER)) {
			$personlinks = getPersonLinks($person);
			if ($style == 1) {
				$outBoxAdd .= " class=\"person_box$isF person_box_template style1\" style=\"width: ".$bwidth."px; height: ".$bheight."px; z-index:-1;\"";
			} elseif ($style == 3) {
				$outBoxAdd .= " class=\"person_box$isF vertical_box_template style3\"";
			} else {
				$outBoxAdd .= " class=\"person_box$isF person_box_template style0\"";

			}
			// NOTE: Zoom
			if (!$show_full) {
				$outBoxAdd .= $mouseAction4;
			} else {
				if ($style <> 3) {
					$icons .= "<a href=\"#\"".$mouseAction4." id=\"iconz-$boxID\" class=\"icon-zoomin\" title=\"".KT_I18N::translate('Zoom in/out on this box.')."\"></a>";
				}
				$icons .= '<div class="itr"><a href="#" class="icon-pedigree"></a><div class="popup">'.$personlinks.'</div></div>';
			}
		} else {
			if ($style==1) {
				$outBoxAdd .= "class=\"person_box$isF\" style=\"width: ".$bwidth."px; height: ".$bheight."px; overflow: hidden;\"";
			} else {
				$outBoxAdd .= "class=\"person_box$isF\" style=\"overflow: hidden;\"";
			}
			// NOTE: Zoom
			if (!$SEARCH_SPIDER) {
				$outBoxAdd .= $mouseAction4;
			}
		}
	} else {
		if ($style == 1) {
			$outBoxAdd .= "class=\"person_box$isF person_box_template style1\" style=\"width: ".$bwidth."px; height: ".$bheight."px;\"";
		} elseif ($style == 3) {
			$outBoxAdd .= "class=\"person_box$isF vertical_box_template style3\"";
		} else {
			$outBoxAdd .= "class=\"person_box$isF person_box_template style0\"";
		}
	}
	//-- find the name
	$name		= $person->getFullName();
	$shortname	= $person->getShortName();

	if ($SHOW_HIGHLIGHT_IMAGES) {
		$thumbnail = $person->displayImage();
	} else {
		$thumbnail = '';
	}

	//-- find additional name, e.g. Hebrew
	$addname = $person->getAddName();

	// add optional CSS style for each fact
	$indirec	= $person->getGedcomRecord();
	$cssfacts	= array("BIRT", "CHR", "DEAT", "BURI", "CREM", "ADOP", "BAPM", "BARM", "BASM", "BLES", "CHRA", "CONF", "FCOM", "ORDN", "NATU", "EMIG", "IMMI", "CENS", "PROB", "WILL", "GRAD", "RETI", "CAST", "DSCR", "EDUC", "IDNO",
	"NATI", "NCHI", "NMR", "OCCU", "PROP", "RELI", "RESI", "SSN", "TITL", "BAPL", "CONL", "ENDL", "SLGC", "_MILI");
	foreach ($cssfacts as $indexval => $fact) {
		if (strpos($indirec, "1 $fact") !== false) $classfacts .= " $fact";
	}

	if ($PEDIGREE_SHOW_GENDER && $show_full) {
		$genderImage = " " . $person->getSexImage('small', "box-$boxID-gender");
	}

	// Here for alternate name2
	if ($addname) {
		$addname = "<br><span id=\"addnamedef-$boxID\" class=\"name1\"> ".$addname."</span>";
	}

	if ($SHOW_LDS_AT_GLANCE && $show_full) {
		$addname = ' <span class="details$style">'.get_lds_glance($indirec).'</span>' . $addname;
	}

	// Show BIRT or equivalent event
	$opt_tags=preg_split('/\W/', $CHART_BOX_TAGS, 0, PREG_SPLIT_NO_EMPTY);
	if ($show_full) {
		foreach (explode('|', KT_EVENTS_BIRT) as $birttag) {
			if (!in_array($birttag, $opt_tags)) {
				$event = $person->getFactByType($birttag);
				if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace()) && $event->canShow()) {
					$BirthDeath .= '<p>' . $event->print_simple_fact(true, true) . '</p>';
					break;
				}
			}
		}
	}
	// Show optional events (before death)
	foreach ($opt_tags as $key=>$tag) {
		if (!preg_match('/^('.KT_EVENTS_DEAT.')$/', $tag)) {
			$event = $person->getFactByType($tag);
			if (!is_null($event) && $event->canShow()) {
				$BirthDeath .= '<p>' . $event->print_simple_fact(true, true);
				unset ($opt_tags[$key]);
			}
		}
	}
	// Show DEAT or equivalent event
	if ($show_full) {
		foreach (explode('|', KT_EVENTS_DEAT) as $deattag) {
			$event = $person->getFactByType($deattag);
			if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace() || $event->getDetail()=='Y') && $event->canShow()) {
				$BirthDeath .= '<p>' . $event->print_simple_fact(true, true) . '</p>';
				if (in_array($deattag, $opt_tags)) {
					unset ($opt_tags[array_search($deattag, $opt_tags)]);
				}
				break;
			}
		}
	}
	// Show remaining optional events (after death)
	foreach ($opt_tags as $tag) {
		$event = $person->getFactByType($tag);
		if (!is_null($event) && $event->canShow()) {
			$BirthDeath .= '<p>' . $event->print_simple_fact(true, true) . '</p>';
		}
	}
	// Find the short birth place for compact chart
	$opt_tags = preg_split('/\W/', $CHART_BOX_TAGS, 0, PREG_SPLIT_NO_EMPTY);
	foreach (explode('|', KT_EVENTS_BIRT) as $birttag) {
		if (!in_array($birttag, $opt_tags)) {
			$event = $person->getFactByType($birttag);
			if (!is_null($event) && ($event->getDate()->isOK() || $event->getPlace()) && $event->canShow()) {
				$tmp = new KT_Place($event->getPlace(), KT_GED_ID);
				$birthplace .= $tmp->getShortName();
				break;
			}
		}
	}

	// Output to template
	if ($style == 3) {
	   require KT_THEME_DIR . 'templates/verticalbox_template.php';
	} else {
		if ($show_full) {
		   require KT_THEME_DIR . 'templates/personbox_template.php';
		} else {
		   require KT_THEME_DIR.'templates/compactbox_template.php';
		}
	}
}

/**
* Print HTML header meta links
*
* Adds meta tags to header common to all themes
*/
function header_links($META_DESCRIPTION, $META_ROBOTS, $META_GENERATOR, $LINK_CANONICAL) {
	global $KT_TREE, $view;
	$header_links = '';
	if (!empty($LINK_CANONICAL)) {
		$header_links .= '<link rel="canonical" href="'. $LINK_CANONICAL. '">';
	}
	if (!empty($META_DESCRIPTION)) {
		global $controller, $ctype;
		switch ($ctype) {
			case '':
				if ($view != 'simple') {
					if ($KT_TREE) {
						$header_links .= '<meta name="description" content="' . htmlspecialchars(strip_tags($controller->getPageTitle() . ' - ' . $KT_TREE->tree_title_html)) . '">';
					} else {
						$header_links .= '<meta name="description" content="' . htmlspecialchars(strip_tags($controller->getPageTitle())) . '">';
					}
				}
				break;
			case 'gedcom':
			default:
				$header_links .= '<meta name="description" content="'. htmlspecialchars((string) $META_DESCRIPTION). '">';
				break;
		}
	}
	if (!empty($META_ROBOTS)) {
		$header_links .= '<meta name="robots" content="'. $META_ROBOTS. '">';
	}
	if (!empty($META_GENERATOR)) {
		$header_links .= '<meta name="generator" content="'. $META_GENERATOR. '">';
	}
	$header_links .= '<meta name="viewport" content="width=device-width, initial-scale=1">';

	return $header_links;
}

// Generate a login link
function login_link() {
	global $SEARCH_SPIDER;

	if ($SEARCH_SPIDER) {
		return '';
	} else {
		return
			'<a href="' . KT_LOGIN_URL.'?url=' . rawurlencode(get_query_url()).'">'
				. (KT_Site::preference('USE_REGISTRATION_MODULE') ? KT_I18N::translate('Login or Register') : KT_I18N::translate('Login')) . '
			</a>';
	}
}

// Generate a logout link
function logout_link() {
	global $SEARCH_SPIDER;

	if ($SEARCH_SPIDER) {
		return '';
	} else {
		return '<a href="index.php?logout=1">' . KT_I18N::translate('Logout') . '</a>';
	}
}

//generate Who is online list
function whoisonline() {
	$NumAnonymous	= 0;
	$loggedusers	= array ();
	$content		= '';

	foreach (get_logged_in_users() as $user_id=>$user_name) {
		if (KT_USER_IS_ADMIN || get_user_setting($user_id, 'visibleonline')) {
			$loggedusers[$user_id]=$user_name;
		} else {
			$NumAnonymous++;
		}
	}

	$LoginUsers	= count($loggedusers);
	$content	.= '<div class="logged_in_count">';
	if ($NumAnonymous) {
		$content .= KT_I18N::plural('%d anonymous logged-in user', '%d anonymous logged-in users', $NumAnonymous, $NumAnonymous);
		if ($LoginUsers) {
			$content .=  '&nbsp;|&nbsp;';
		}
	}
	if ($LoginUsers) {
		$content .= KT_I18N::plural('%d logged-in user', '%d logged-in users', $LoginUsers, $LoginUsers);
	}
	$content .= '</div>';
	$content .= '<div class="logged_in_list">';
		if (KT_USER_ID) {
			$i=0;
			foreach ($loggedusers as $user_id=>$user_name) {
				$content .= '<div class="logged_in_name">';

					$individual = KT_Person::getInstance(KT_USER_GEDCOM_ID);
					if ($individual) {
						$content .= '<a href="individual.php?pid='. KT_USER_GEDCOM_ID . '&amp;ged='. KT_GEDURL . '">' . htmlspecialchars(getUserFullName($user_id)) . '</a>';
					} else {
						$content .= htmlspecialchars(getUserFullName($user_id));
					}
					$content .= ' - ' . htmlspecialchars((string) $user_name);

					if (KT_USER_ID != $user_id && get_user_setting($user_id, 'contactmethod')!="none") {
						$content .= '<a class="fa-envelope-o" href="message.php?to=' . $user_name . '&amp;url=' . addslashes(urlencode(get_query_url())) . '"  title="' . KT_I18N::translate('Send Message') . '"></a>';
					}

					$i++;

				$content .= '</div>';
			}
		}
	$content .= '</div>';

	return $content;
}


// Print a link to allow email/messaging contact with a user
// Optionally specify a method (used for webmaster/genealogy contacts)
function user_contact_link($user_id) {
	$method	= get_user_setting($user_id, 'contactmethod');

	switch ($method) {
	case 'none':
		return '';
	case 'mailto':
		return '<a href="mailto:' . KT_Filter::escapeHtml(getUserEmail($user_id)) . '">' . getUserFullName($user_id) . '</a>';
	default:
		return '<a href="#" onclick="window.open(\'message.php?to=' . KT_Filter::escapeHtml(get_user_name($user_id)) . '&amp;url=' . addslashes(urlencode(get_query_url())) . '\', \'_blank\')" rel="noopener noreferrer" title="' . KT_I18N::translate('Send Message') . '">' . getUserFullName($user_id) . '</a>';
	}
}

// print links for genealogy and technical contacts
//
// this function will print appropriate links based on the preferred contact methods for the genealogy
// contact user and the technical support contact user
function contact_links($ged_id = KT_GED_ID) {
	$contact_user_id	= get_gedcom_setting($ged_id, 'CONTACT_USER_ID');
	$webmaster_user_id	= get_gedcom_setting($ged_id, 'WEBMASTER_USER_ID');
	$supportLink		= user_contact_link($webmaster_user_id);
	if ($webmaster_user_id == $contact_user_id) {
		$contactLink = $supportLink;
	} else {
		$contactLink = user_contact_link($contact_user_id);
	}

	if (!$contact_user_id && !$webmaster_user_id) {
		return '';
	}

	if (!$supportLink && !$contactLink) {
		return '';
	}

	if ($supportLink == $contactLink) {
		return '<div class="contact_links">'.KT_I18N::translate('If you have any questions or comments please contact %s', $supportLink) .'</div>';
	} else {
		if ($webmaster_user_id || $contact_user_id) {
			$returnText = '<div class="contact_links">';
				if ($supportLink && $webmaster_user_id) {
					$returnText .= KT_I18N::translate('For technical support and information please contact %s', $supportLink);
					if ($contactLink) {
						$returnText .= '<br>';
					}
				}
				if ($contactLink && $contact_user_id) {
					$returnText .= KT_I18N::translate('For help with genealogy questions please contact %s', $contactLink);
				}
			$returnText .= '</div>';
			return $returnText;
		} else {
			return '';
		}
	}
}

/**
 * print a note record
 *
 * @param string $text
 * @param int $nlevel the level of the note record
 * @param string $nrec the note record to print
 * @param bool $textOnly Don't print the "Note: " introduction
 *
 * @return string
 */
function print_note_record($text, $nlevel, $nrec, $textOnly = false) {
	global $KT_TREE, $EXPAND_NOTES;
	$element_id	= '';
	$first_line	= '';
	$expand1	= '';
	$expand2	= '';
	$text_cont	= get_cont($nlevel, $nrec);

	// Check if shared note (we have already checked that it exists)
	preg_match('/^0 @(' . KT_REGEX_XREF . ')@ NOTE/', $nrec, $match);
	if ($match) {
		$element_id = $match[1] . '-' . (int)(microtime(true)*1000000);
		$note		= KT_Note::getInstance($match[1], $KT_TREE);
		$label		= 'SHARED_NOTE';
		// If Census assistant installed, allow it to format the note
		if (array_key_exists('census_assistant', KT_Module::getActiveModules())) {
			$html	= census_assistant_KT_Module::formatCensusNote($note);
		} else {
			$html	= KT_Filter::formatText($note->getNote());
		}
	} else {
		$element_id = 'N-' . (int)(microtime(true)*1000000);
		$note		= null;
		$label		= 'NOTE';
		$html		= KT_Filter::formatText($text . $text_cont);

	}

	if ($textOnly) {
		return strip_tags($html);
	}


	if (strpos($text . $text_cont, "\n") === false) {
		// A one-line note? strip the block-level tags, so it displays inline
		return KT_Gedcom_Tag::getLabelValue($label, strip_tags($html, '<a><strong><em>'));
	} else {
		// A multi-line note, with an expand/collapse option
		if ($note) {
			if (KT_SCRIPT_NAME === 'note.php') {
				$first_line = $note->getFullName();
			} else {
				$first_line = '<a href="' . $note->getHtmlUrl() . '">' . $note->getFullName() . '</a>';
			}

			// special case required to display title for census shared notes when expanded by default
			if (preg_match('/<span id="title">.*<\/span>/', $html, $match)) {
				if (KT_SCRIPT_NAME === 'note.php') {
					$first_line = $match[0];
				} else {
					$first_line = '<a href="' . $note->getHtmlUrl() . '">' . $match[0] . '</a>';
				}
				$html = preg_replace('/<span id="title">.*<\/span>/', '', $html);
			}

		} else {
			if (strlen($text) > 100) {
				$first_line = mb_substr($text, 0, 100) . KT_I18N::translate('…');
			} else {
				$first_line	= KT_Filter::formatText($text);
				$html		= KT_Filter::formatText($text_cont);
			}
		}

		if ($EXPAND_NOTES) {
			$plusminus='icon-minus';
		} else {
			$plusminus='icon-plus';
		}
		$expand1 = '
			<a href="#" onclick="return expand_layer(\'' . $element_id . '\');">
				<i id="' . $element_id . '_img" class="' . $plusminus . '"></i>
			</a>
		';

		if (!$EXPAND_NOTES && KT_SCRIPT_NAME !== 'note.php') {
			$expand2 = '" style="display:none"';
		}

		return
			'<div class="fact_NOTE">
				<span class="label">
					' . KT_Gedcom_Tag::getLabel($label) . ':&nbsp;
				</span>
				<span class="field">' . $first_line . $expand1 . '</span>
				<div class="note_details" id="' . $element_id . '"' . $expand2 . '>' . $html . '</div>
			</div>
		';

	}
}

/**
* Print all of the notes in this fact record
* @param string $factrec the factrecord to print the notes from
* @param int $level The level of the factrecord
* @param bool $textOnly Don't print the "Note: " introduction
*/
function print_fact_notes($factrec, $level, $textOnly = false, $return = false) {
	global $KT_TREE;

	$data          = '';
	$previous_spos = 0;
	$nlevel        = $level + 1;
	$ct            = preg_match_all("/$level NOTE (.*)/", $factrec, $match, PREG_SET_ORDER);
	for ($j = 0; $j < $ct; $j++) {
		$spos1 = strpos($factrec, $match[$j][0], $previous_spos);
		$spos2 = strpos($factrec . "\n$level", "\n$level", $spos1 + 1);
		if (!$spos2) {
			$spos2 = strlen($factrec);
		}
		$previous_spos = $spos2;
		$nrec          = substr($factrec, $spos1, $spos2 - $spos1);
		if (!isset($match[$j][1])) {
			$match[$j][1] = '';
		}
		if (!preg_match('/@(.*)@/', $match[$j][1], $nmatch)) {
			$data .= print_note_record($match[$j][1], $nlevel, $nrec, $textOnly);
		} else {
			$note = KT_Note::getInstance($nmatch[1], $KT_TREE);
			if ($note) {
				if ($note->canDisplayDetails()) {
					$noterec = $note->getGedcomRecord();
					$nt      = preg_match("/0 @$nmatch[1]@ NOTE (.*)/", $noterec, $n1match);
					$data	 .= print_note_record(($nt > 0) ? $n1match[1] : "", 1, $noterec, $textOnly);
					if (!$textOnly) {
						if (strpos($noterec, '1 SOUR') !== false) {
							$data .= print_fact_sources($noterec, 1);
						}
					}
				}
			} else {
				$data = '<div class="fact_NOTE">
					<span class="label">' . KT_I18N::translate('Note') . '</span>:&nbsp;
					<span class="field error">' . $nmatch[1] . '</span>
				</div>';
			}
		}

		if (!$textOnly) {
			if (strpos($factrec, "$nlevel SOUR") !== false) {
				$data .= '
					<div class="indent">' .
						print_fact_sources($nrec, $nlevel, true) . '
					</div>
				';
			}
		}
	}

	if ($return) {
		return $data;
	} else {
		echo $data;
	}

}

//-- function to print a privacy error with contact method
function print_privacy_error() {
	$user_id	= get_gedcom_setting(KT_GED_ID, 'CONTACT_USER_ID');
	$method		= get_user_setting($user_id, 'contactmethod');
	$fullname	= getUserFullName($user_id);

	echo '<div class="error">' . KT_I18N::translate('This information is private and cannot be shown.') . '</div>';
	switch ($method) {
	case 'none':
		break;
	case 'mailto':
		$email = getUserEmail($user_id);
		echo '<div class="error">' . KT_I18N::translate('For more information contact') . ' ' . '<a href="mailto:' . htmlspecialchars((string) $email) . '">' . htmlspecialchars((string) $fullname) . '</a></div>';
		break;
	default:
		echo '<div class="error">' . KT_I18N::translate('For more information contact') . ' ' . '<a class="fa-envelope-o" href="message.php?to=' . $user_id . '&amp;url=' . addslashes(urlencode(get_query_url())) . '"  title="' . KT_I18N::translate('Send Message') . '">' . $fullname . '</a></div>';
		break;
	}
}

// Print a link for a popup help window
function help_link($help_topic, $module='') {
	return '<span class="icon-help" onclick="helpDialog(\''.$help_topic.'\',\''.$module.'\'); return false;">&nbsp;</span>';
}

// Print help as on-page text
function help_text($help_topic) {
	return '<iframe class="help_text_frame" src = "help_text.php?help=' . $help_topic . '"></iframe>';
}

// When a user has searched for text, highlight any matches in
// the displayed string.
function highlight_search_hits($string) {
	global $controller;
	if ($controller instanceof KT_Controller_Search && $controller->query) {
		// TODO: when a search contains multiple words, we search independently.
		// e.g. searching for "FOO BAR" will find records containing both FOO and BAR.
		// However, we only highlight the original search string, not the search terms.
		// The controller needs to provide its "query_terms" array.
		$regex = array();
		foreach (array($controller->query) as $search_term) {
			$regex[] = preg_quote((string) $search_term, '/');
		}
		// Match these strings, provided they do not occur inside HTML tags
		$regex = '('.implode('|', $regex).')(?![^<]*>)';
		return preg_replace('/'.$regex.'/i', '<span class="search_hit">$1</span>', $string);
	} else {
		return $string;
	}
}

// Print the associations from the associated individuals in $event to the individuals in $record
function print_asso_rela_record(KT_Event $event, KT_GedcomRecord $record) {
	global $SEARCH_SPIDER;

	// To whom is this record an assocate?
	if ($record instanceof KT_Person) {
		// On an individual page, we just show links to the person
		$associates = array($record);
	} elseif ($record instanceof KT_Family) {
		// On a family page, we show links to both spouses
		$associates = $record->getSpouses();
	} else {
		// On other pages, it does not make sense to show associates
		return;
	}

	preg_match_all('/^1 ASSO @(' . KT_REGEX_XREF . ')@((\n[2-9].*)*)/', $event->getGedcomRecord(), $amatches1, PREG_SET_ORDER);
	preg_match_all('/\n2 _?ASSO @(' . KT_REGEX_XREF . ')@((\n[3-9].*)*)/', $event->getGedcomRecord(), $amatches2, PREG_SET_ORDER);
	// For each ASSO record
	foreach (array_merge($amatches1, $amatches2) as $amatch) {
		$person = KT_Person::getInstance($amatch[1]);
		if (!$person) {
			// If the target of the ASSO does not exist, create a dummy person, so
			// the user can see that something is present.
			$person = new KT_Person('');
		}
		if (preg_match('/\n[23] RELA (.+)/', $amatch[2], $rmatch)) {
			$rela = $rmatch[1];
		} else {
			$rela = '';
		}
		if (preg_match('/\n[23] NOTE (.+)/', $amatch[2], $nmatch)) {
			$label_3 = KT_I18N::translate('Note');
			$note = $nmatch[1];
			if (strpos($note, '@') !== false && strrpos($note, '@') !== false) {
				$label_3 = KT_I18N::translate('Shared note');
				$nid = substr($note, 1, -1);
				$snote	= KT_Note::getInstance($nid);
				if ($snote) {
					$noterec = $snote->getGedcomRecord();
					$nt = preg_match("/^0 @[^@]+@ NOTE (.*)/", $noterec, $n1match);
					$line1 = $n1match[1];
					$text  = get_cont(1, $noterec);
					// If Census assistant installed,
					if (array_key_exists('census_assistant', KT_Module::getActiveModules())) {
						$note = census_assistant_KT_Module::formatCensusNote($note);
					} else {
						$note = KT_Filter::formatText($note->getNote(), $KT_TREE);
					}
				} else {
					$note = '<span class="error">' . htmlspecialchars((string) $nid) . '</span>';
				}
			}
		} else {
			$note = '';
		}
		$html = array();
		foreach ($associates as $associate) {
			if ($associate) {
				if ($rela) {
					$label		= '<span class="rela_type">' . KT_Gedcom_Code_Rela::getValue($rela, $person) . ':&nbsp;</span>';
					$label_2	= '<span class="rela_name">' . get_relationship_name(get_relationship($associate, $person, true, 4)) . '</span>';
				} else {
					// Generate an automatic RELA
					$label		= '';
					$label_2	= '<span class="rela_name">' . get_relationship_name(get_relationship($associate, $person, true, 4)) . '</span>';
				}
				if (!$label && !$label_2) {
					$label		= KT_I18N::translate('Relationships');
					$label_2	= '';
				}
				// For family records (e.g. MARR), identify the spouse with a sex icon
				if ($record instanceof KT_Family) {
					$label_2 = $associate->getSexImage() . $label_2;
				}

				if ($SEARCH_SPIDER) {
					$html[] = $label_2; // Search engines cannot use the relationship chart.
				} else {
					$html[] = '<a href="relationship.php?pid1=' . $associate->getXref() . '&amp;pid2=' . $person->getXref() . '&amp;ged=' . KT_GEDURL . '">' . $label_2 . '</a>';
				}
			}
		}
		$html = array_unique($html);
		?>
		<div class="fact_ASSO">
			<?php echo $label . implode(KT_I18N::$list_separator, $html); ?>
			 -
			<a href="<?php echo $person->getHtmlUrl(); ?>">
				<?php echo $person->getFullName(); ?>
			</a>
			<!-- find notes for each fact -->
			<?php if ($note) { ?>
				<div class="indent">
					<span class="label"><?php echo $label_3; ?>:</span>
					<span><?php echo $note; ?></span>
				</div>
			<?php } ?>
		</div>
	<?php }
}

/**
* Format age of parents in HTML
*
* @param string $pid child ID
*/
function format_parents_age($pid, $birth_date=null) {
	global $SHOW_PARENTS_AGE;

	$html='';
	if ($SHOW_PARENTS_AGE) {
		$person=KT_Person::getInstance($pid);
		$families=$person->getChildFamilies();
		// Where an indi has multiple birth records, we need to know the
		// date of it.  For person boxes, etc., use the default birth date.
		if (is_null($birth_date)) {
			$birth_date=$person->getBirthDate();
		}
		// Multiple sets of parents (e.g. adoption) cause complications, so ignore.
		if ($birth_date->isOK() && count($families)==1) {
			$family=current($families);
			foreach ($family->getSpouses() as $parent) {
				if ($parent->getBirthDate()->isOK()) {
					$sex=$parent->getSexImage();
					$age=KT_Date::getAge($parent->getBirthDate(), $birth_date, 2);
					$deatdate=$parent->getDeathDate();
					switch ($parent->getSex()) {
					case 'F':
						// Highlight mothers who die in childbirth or shortly afterwards
						if ($deatdate->isOK() && $deatdate->MinJD()<$birth_date->MinJD()+90) {
							$html.=' <span title="'.KT_Gedcom_Tag::getLabel('_DEAT_PARE', $parent).'" class="parentdeath">'.$sex.$age.'</span>';
						} else {
							$html.=' <span title="'.KT_I18N::translate('Mother\'s age').'">'.$sex.$age.'</span>';
						}
						break;
					case 'M':
						// Highlight fathers who die before the birth
						if ($deatdate->isOK() && $deatdate->MinJD()<$birth_date->MinJD()) {
							$html.=' <span title="'.KT_Gedcom_Tag::getLabel('_DEAT_PARE', $parent).'" class="parentdeath">'.$sex.$age.'</span>';
						} else {
							$html.=' <span title="'.KT_I18N::translate('Father\'s age').'">'.$sex.$age.'</span>';
						}
						break;
					default:
						$html.=' <span title="'.KT_I18N::translate('Parent\'s age').'">'.$sex.$age.'</span>';
						break;
					}
				}
			}
			if ($html) {
				$html='<span class="age">'.$html.'</span>';
			}
		}
	}
	return $html;
}

// print fact DATE TIME
//
// $event - event containing the date/age
// $record - the person (or couple) whose ages should be printed
// $anchor option to print a link to calendar
// $time option to print TIME value
function format_fact_date(KT_Event $event, KT_GedcomRecord $record, $anchor = false, $time = false, $show_age = true) {
	global $pid, $SEARCH_SPIDER;
	global $GEDCOM;
	$ged_id = get_id_from_gedcom($GEDCOM);

	$factrec	= $event->getGedcomRecord();
	$html		= '';
	// Recorded age
	$fact_age = get_gedcom_value('AGE', 2, $factrec);
	if ($fact_age == '')
		$fact_age = get_gedcom_value('DATE:AGE', 2, $factrec);
	$husb_age = get_gedcom_value('HUSB:AGE', 2, $factrec);
	$wife_age = get_gedcom_value('WIFE:AGE', 2, $factrec);

	// Calculated age
	if (preg_match('/2 DATE (.+)/', $factrec, $match)) {
		$date = new KT_Date($match[1]);
		$html .= ' ' . $date->Display($anchor && !$SEARCH_SPIDER);
		// time
		if ($time) {
			$timerec = get_sub_record(2, '2 TIME', $factrec);
			if ($timerec == '') {
				$timerec = get_sub_record(2, '2 DATE', $factrec);
			}
			if (preg_match('/[2-3] TIME (.*)/', $timerec, $tmatch)) {
				$html .= '<span class="date"> - ' . $tmatch[1].'</span>';
			}
		}
		$fact = $event->getTag();
		if ($record instanceof KT_Person) {
			// Can't use getBirthDate(), as this also gives BAPM/CHR events, which
			// wouldn't give the correct "days after birth" result for people with
			// no BIRT.
			$birth_event = $record->getFactByType('BIRT');
			if ($birth_event) {
				$birth_date = $birth_event->getDate();
			} else {
				$birth_date = new KT_Date('');
			}
			// age of parents at child birth
			$parents_age = false;
			if (($birth_date->isOK() && $fact === 'BIRT') || (!$birth_date->isOK() && in_array($fact, array('CHR','BAPM'))) && $show_age) {
				$html .= format_parents_age($record->getXref(), $date);
				$parents_age = true;
			}
			// age at event
			elseif (!$parents_age && ($fact !== 'BIRT' && $fact !== 'CHAN' && $fact !== '_TODO')) {
				// Can't use getDeathDate(), as this also gives BURI/CREM events, which
				// wouldn't give the correct "days after death" result for people with
				// no DEAT.
				$death_event = $record->getFactByType('DEAT');
				if ($death_event) {
					$death_date = $death_event->getDate();
				} else {
					$death_date = new KT_Date('');
				}
				$ageText = '';
				if ((KT_Date::Compare($date, $death_date) <= 0 || !$record->isDead()) || $fact == 'DEAT') {
					// Before death, print age
					$age = KT_Date::GetAgeGedcom($birth_date, $date);
					// Only show calculated age if it differs from recorded age
					if ($age != '') {
						if (
							$fact_age != '' && $fact_age!=$age ||
							$fact_age == '' && $husb_age=='' && $wife_age=='' ||
							$husb_age != '' && $record->getSex()=='M' && $husb_age!=$age ||
							$wife_age != '' && $record->getSex()=='F' && $wife_age!=$age
						) {
							if ($age!="0d") {
								$ageText = '('.KT_I18N::translate('Age').' '.get_age_at_event($age, false).')';
							}
						}
					}
				}
				if ($fact != 'DEAT' && KT_Date::Compare($date, $death_date)>=0) {
					// After death, print time since death
					$age=get_age_at_event(KT_Date::GetAgeGedcom($death_date, $date), true);
					if ($age!='') {
						if (KT_Date::GetAgeGedcom($death_date, $date)=="0d") {
							$ageText = '('.KT_I18N::translate('on the date of death').')';
						} else {
							$ageText = '('.$age.' '.KT_I18N::translate('after death').')';
							// Family events which occur after death are probably errors
							if ($event->getParentObject() instanceof KT_Family) {
								$ageText.='<i class="icon-warning"></i>';
							}
						}
					}
				}
				if ($ageText && $show_age) $html .= ' <span class="age">'.$ageText.'</span>';
			}
		} elseif ($record instanceof KT_Family) {
			$indirec=find_person_record($pid, $ged_id);
			$indi=new KT_Person($indirec);
			$birth_date=$indi->getBirthDate();
			$death_date=$indi->getDeathDate();
			$ageText = '';
			if (KT_Date::Compare($date, $death_date)<=0) {
				$age=KT_Date::GetAgeGedcom($birth_date, $date);
				// Only show calculated age if it differs from recorded age
				if ($age!='' && $age>0) {
					if (
						$fact_age!='' && $fact_age!=$age ||
						$fact_age=='' && $husb_age=='' && $wife_age=='' ||
						$husb_age!='' && $indi->getSex()=='M' && $husb_age!= $age ||
						$wife_age!='' && $indi->getSex()=='F' && $wife_age!=$age
					) {
						$ageText = '('.KT_I18N::translate('Age').' '.get_age_at_event($age, false).')';
					}
				}
			}
			if ($ageText && $show_age) $html .= ' <span class="age">'.$ageText.'</span>';
		}
	} else {
		// 1 DEAT Y with no DATE => print YES
		// 1 BIRT 2 SOUR @S1@ => print YES
		// 1 DEAT N is not allowed
		// It is not proper GEDCOM form to use a N(o) value with an event tag to infer that it did not happen.
		$factdetail = explode(' ', trim($factrec));
		if (isset($factdetail) && (count($factdetail) == 3 && strtoupper($factdetail[2]) == 'Y') || (count($factdetail) == 4 && $factdetail[2] == 'SOUR')) {
			$html.=KT_I18N::translate('yes');
		}
	}
	// print gedcom ages
	foreach (array(KT_Gedcom_Tag::getLabel('AGE')=>$fact_age, KT_Gedcom_Tag::getLabel('HUSB')=>$husb_age, KT_Gedcom_Tag::getLabel('WIFE')=>$wife_age) as $label=>$age) {
		if ($age!='' && $show_age) {
			$html.=' <span class="label">'.$label.':</span> <span class="age">'.get_age_at_event($age, false).'</span>';
		}
	}
	return $html;
}
/**
* print fact PLACe TEMPle STATus
*
* @param Event $event gedcom fact record
* @param boolean $anchor option to print a link to placelist
* @param boolean $sub option to print place subrecords
* @param boolean $lds option to print LDS TEMPle and STATus
*/
function format_fact_place(KT_Event $event, $anchor = false, $sub = false, $lds = false) {
	global $SHOW_PEDIGREE_PLACES, $SHOW_PEDIGREE_PLACES_SUFFIX, $SEARCH_SPIDER;

	$factrec	= $event->getGedcomRecord();
	$name_parts = explode(', ', (string) $event->getPlace());
	$ct			= count($name_parts);
	$kt_place	= new KT_Place($event->getPlace(), KT_GED_ID);

	if ($anchor) {
		// Show the full place name, for facts/events tab
		if ($SEARCH_SPIDER) {
			$html = '&nbsp;' . $kt_place->getFullName();
		} else {
			$html = '&nbsp;<a href="' . $kt_place->getURL() . '">' . $kt_place->getFullName() . '</a>';
		}
	} else {
		// Abbreviate the place name, for chart boxes
		return '&nbsp;' . $kt_place->getShortName();
	}

	$ctn = 0;
	if ($sub) {
		$placerec = get_sub_record(2, '2 PLAC', $factrec);
		if (!empty($placerec)) {
			if (preg_match_all('/\n3 (?:_HEB|ROMN) (.+)/', $placerec, $matches)) {
				foreach ($matches[1] as $match) {
					$kt_place	= new KT_Place($match, KT_GED_ID);
					$html		.= '&nbsp;' . $kt_place->getFullName();
				}
			}
			$map_lati	= "";
			$cts		= preg_match('/\d LATI (.*)/', $placerec, $match);
			if ($cts > 0) {
				$map_lati	= $match[1];
				$html		.= '<br><span>' . KT_Gedcom_Tag::getLabel('LATI') . ': </span>' . $map_lati;
			}
			$map_long	= "";
			$cts		= preg_match('/\d LONG (.*)/', $placerec, $match);
			if ($cts > 0) {
				$map_long	= $match[1];
				$html		.= ' <span>' . KT_Gedcom_Tag::getLabel('LONG') . ': </span>' . $map_long;
			}
			if ($map_lati && $map_long && empty($SEARCH_SPIDER)) {
				$map_lati = trim(strtr($map_lati, "NSEW,�", " - -. ")); // S5,6789 ==> -5.6789
				$map_long = trim(strtr($map_long, "NSEW,�", " - -. ")); // E3.456� ==> 3.456
				if ($name_parts) {
					$place = $name_parts[0];
				} else {
					$place = '';
				}
				$html .= ' <a target="_blank" rel="noopener noreferrer" rel="nofollow" href="https://maps.google.com/maps?q=' . $map_lati . ',' . $map_long . '" class="icon-googlemaps" title="' . KT_I18N::translate('Google Maps™') . '"></a>';
				$html .= ' <a target="_blank" rel="noopener noreferrer" rel="nofollow" href="https://www.bing.com/maps/?lvl=15&cp=' . $map_lati . '~' . $map_long . '" class="icon-bing" title="' . KT_I18N::translate('Bing Maps™') . '"></a>';
				$html .= ' <a target="_blank" rel="noopener noreferrer" rel="nofollow" href="https://www.openstreetmap.org/#map=15/' . $map_lati . '/' . $map_long . '" class="icon-osm" title="' . KT_I18N::translate('OpenStreetMap™') . '"></a>';
			}
			if (preg_match('/\d NOTE (.*)/', $placerec, $match)) {
				ob_start();
				print_fact_notes($placerec, 3);
				$html .= '<br>' . ob_get_contents();
				ob_end_clean();
			}
		}
	}
	if ($lds) {
		if (preg_match('/2 TEMP (.*)/', $factrec, $match)) {
			$tcode = trim($match[1]);
			$html .= '<br>' . KT_I18N::translate('LDS Temple') . ': ' . KT_Gedcom_Code_Temp::templeName($match[1]);
		}
		if (preg_match('/2 STAT (.*)/', $factrec, $match)) {
			$html .= '<br>' . KT_I18N::translate('Status') . ': ' . KT_Gedcom_Code_Stat::statusName($match[1]);
			if (preg_match('/3 DATE (.*)/', $factrec, $match)) {
				$date = new KT_Date($match[1]);
				$html .= ', ' . KT_Gedcom_Tag::getLabel('STAT:DATE') . ': ' . $date->Display(false);
			}
		}
	}
	return $html;
}

/**
* Check for facts that may exist only once for a certain record type.
* If the fact already exists in the second array, delete it from the first one.
*/
function CheckFactUnique($uniquefacts, $recfacts, $type) {
	foreach ($recfacts as $indexval => $factarray) {
		$fact=false;
		if (is_object($factarray)) {
			/* @var $factarray Event */
			$fact = $factarray->getTag();
		}
		else {
			if (($type == "SOUR") || ($type == "REPO")) $factrec = $factarray[0];
			if (($type == "FAM") || ($type == "INDI")) $factrec = $factarray[1];

		$ft = preg_match("/1 (\w+)(.*)/", $factrec, $match);
		if ($ft>0) {
			$fact = trim($match[1]);
			}
		}
		if ($fact!==false) {
			$key = array_search($fact, $uniquefacts);
			if ($key !== false) unset($uniquefacts[$key]);
		}
	}
	return $uniquefacts;
}

/**
* Print a new fact box on details pages
* @param string $id the id of the person, family, source etc the fact will be added to
* @param array $usedfacts an array of facts already used in this record
* @param string $type the type of record INDI, FAM, SOUR etc
*/
function print_add_new_fact($id, $usedfacts, $type) {
	global $KT_SESSION;

	// -- Add from clipboard
	if ($KT_SESSION->clipboard) {
		$newRow = true;
		foreach (array_reverse($KT_SESSION->clipboard, true) as $key => $fact) {
			if ($fact["type"] == $type || $fact["type"] == 'all') {
				if ($newRow) {
					$newRow = false;
					echo '<tr><td class="descriptionbox">';
					echo KT_I18N::translate('Add from clipboard'), '</td>';
					echo '<td class="optionbox wrap"><form method="get" name="newFromClipboard" action="" onsubmit="return false;">';
					echo '<select id="newClipboardFact" name="newClipboardFact">';
				}
				$fact_type=KT_Gedcom_Tag::getLabel($fact['fact']);
				echo '<option value="clipboard_', $key, '">', $fact_type;
				// TODO use the event class to store/parse the clipboard events
				if (preg_match('/^2 DATE (.+)/m', $fact['factrec'], $match)) {
					$tmp=new KT_Date($match[1]);
					echo '; ', $tmp->minDate()->Format('%Y');
				}
				if (preg_match('/^2 PLAC ([^,\n]+)/m', $fact['factrec'], $match)) {
					echo '; ', $match[1];
				}
				echo '</option>';
			}
		}
		if (!$newRow) {
			echo '</select>';
			echo '&nbsp;&nbsp;<input type="button" value="', KT_I18N::translate('Add'), "\" onclick=\"addClipboardRecord('$id', 'newClipboardFact');\"> ";
			echo '</form></td></tr>', "\n";
		}
	}

	// -- Add from pick list
	switch ($type) {
	case "INDI":
		$addfacts   	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts 	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "FAM":
		$addfacts   	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD'),     -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE'),  -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts 	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_QUICK'),   -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "SOUR":
		$addfacts   	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts 	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "NOTE":
		$addfacts   	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts		= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "REPO":
		$addfacts   	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts 	= preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	default:
		return;
	}
	$addfacts				= array_merge(CheckFactUnique($uniquefacts, $usedfacts, $type), $addfacts);
	$quickfacts				= array_intersect($quickfacts, $addfacts);
	$translated_addfacts	= array();
	foreach ($addfacts as $addfact) {
		$translated_addfacts[$addfact] = KT_Gedcom_Tag::getLabel($addfact);
	}
	uasort($translated_addfacts, 'factsort');

    ?>
    <tr>
        <td class="descriptionbox">
            <?php
            echo KT_I18N::translate('Fact or event');
            help_link('add_facts');
            ?>
        </td>
        <td class="optionbox wrap">
	        <form method="get" name="newfactform" action="" onsubmit="return false;">
	            <select id="newfact" name="newfact">
	                <option value="" disabled selected><?php echo KT_I18N::translate('Select'); ?></option>
            	    <?php foreach ($translated_addfacts as $fact => $fact_name) { ?>
            		    <option value="<?php echo $fact; ?>"><?php echo $fact_name; ?></option>
            	    <?php }
                	if ($type == 'INDI' || $type == 'FAM') { ?>
                		<option value="EVEN"><?php echo KT_I18N::translate('Custom event'); ?></option>
                        <option value="FACT"><?php echo KT_I18N::translate('Custom fact'); ?></option>
                	<?php } ?>
	            </select>
            	<input type="button" value="<?php echo KT_I18N::translate('Add'); ?>" onclick="add_record('<?php echo $id; ?>', 'newfact');">
            	<span class="quickfacts">
                	<?php foreach ($quickfacts as $fact) { ?>
                        <a href="#" onclick="add_new_record('<?php echo $id; ?>','<?php echo $fact; ?>');return false;">
                            <?php echo KT_Gedcom_Tag::getLabel($fact); ?>
                        </a>
                	<?php } ?>
            	</span>
            </form>
	    </td>
    </tr>
    <?php
}

/**
* Print a new fact box on details pages - VERSION 2
* @param string $id the id of the person, family, source etc the fact will be added to
* @param array $usedfacts an array of facts already used in this record
* @param string $type the type of record INDI, FAM, SOUR etc
*/
function print_add_new_fact2($id, $usedfacts, $type) {
	global $KT_SESSION;

	// -- Add from clipboard
	if ($KT_SESSION->clipboard) {
		$newRow = true;
		foreach (array_reverse($KT_SESSION->clipboard, true) as $key=>$fact) {
			if ($fact["type"]==$type || $fact["type"]=='all') {
				if ($newRow) {
					$newRow = false;
					echo '<tr><td class="descriptionbox">';
					echo KT_I18N::translate('Add from clipboard'), '</td>';
					echo '<td class="optionbox wrap"><form method="get" name="newFromClipboard" action="" onsubmit="return false;">';
					echo '<select id="newClipboardFact" name="newClipboardFact">';
				}
				$fact_type=KT_Gedcom_Tag::getLabel($fact['fact']);
				echo '<option value="clipboard_', $key, '">', $fact_type;
				// TODO use the event class to store/parse the clipboard events
				if (preg_match('/^2 DATE (.+)/m', $fact['factrec'], $match)) {
					$tmp=new KT_Date($match[1]);
					echo '; ', $tmp->minDate()->Format('%Y');
				}
				if (preg_match('/^2 PLAC ([^,\n]+)/m', $fact['factrec'], $match)) {
					echo '; ', $match[1];
				}
				echo '</option>';
			}
		}
		if (!$newRow) {
			echo '</select>';
			echo '&nbsp;&nbsp;<input type="button" value="', KT_I18N::translate('Add'), "\" onclick=\"addClipboardRecord('$id', 'newClipboardFact');\"> ";
			echo '</form></td></tr>', "\n";
		}
	}

	// -- Add from pick list
	switch ($type) {
	case "INDI":
		$addfacts   =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts=preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'INDI_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "FAM":
		$addfacts   =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_ADD'),     -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts=preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_UNIQUE'),  -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'FAM_FACTS_QUICK'),   -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "SOUR":
		$addfacts   =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts=preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'SOUR_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "NOTE":
		$addfacts   =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts=preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'NOTE_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	case "REPO":
		$addfacts   =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_ADD'),    -1, PREG_SPLIT_NO_EMPTY);
		$uniquefacts=preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_UNIQUE'), -1, PREG_SPLIT_NO_EMPTY);
		$quickfacts =preg_split("/[, ;:]+/", get_gedcom_setting(KT_GED_ID, 'REPO_FACTS_QUICK'),  -1, PREG_SPLIT_NO_EMPTY);
		break;
	default:
		return;
	}
	$addfacts=array_merge(CheckFactUnique($uniquefacts, $usedfacts, $type), $addfacts);
	$quickfacts=array_intersect($quickfacts, $addfacts);
	$translated_addfacts=array();
	foreach ($addfacts as $addfact) {
		$translated_addfacts[$addfact] = KT_Gedcom_Tag::getLabel($addfact);
	}
	uasort($translated_addfacts, 'factsort');

    ?>
    <tr>
        <td class="descriptionbox">
            <?php echo
            KT_I18N::translate('Fact or event');
            help_link('add_facts');
            ?>
        </td>
        <td class="optionbox wrap">
	        <form method="get" name="newfactform" action="" onsubmit="return false;">
	            <select id="newfact2" name="newfact2">
	                <option value="" disabled selected><?php echo KT_I18N::translate('Select'); ?></option>
                	    <?php foreach ($translated_addfacts as $fact => $fact_name) { ?>
                		<option value="<?php echo $fact; ?>"><?php echo $fact_name; ?></option>
                	<?php }
                	if ($type == 'INDI' || $type == 'FAM') { ?>
                        <option value="EVEN"><?php echo KT_I18N::translate('Custom event'); ?></option>
                        <option value="FACT"><?php echo KT_I18N::translate('Custom fact'); ?></option>
                	<?php } ?>
	            </select>
                <input type="button" value="<?php echo KT_I18N::translate('Add'); ?>" onclick="add_record('<?php echo $id; ?>', 'newfact2');">
                <span class="quickfacts">
                	<?php foreach ($quickfacts as $fact) { ?>
                        <a href="#" onclick="add_new_record('<?php echo $id; ?>','<?php echo $fact; ?>');return false;">
                            <?php echo KT_Gedcom_Tag::getLabel($fact); ?>
                        </a>
                	<?php } ?>
            	</span>
            </form>
	    </td>
    </tr>
    <?php
}

/**
* javascript declaration for calendar popup
*
* @param none
*/
function init_calendar_popup() {
	global $WEEK_START, $controller;

	$controller->addInlineJavascript('
		cal_setMonthNames(
			"' . KT_I18N::translate_c('NOMINATIVE', 'January') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'February') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'March') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'April') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'May') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'June') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'July') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'August') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'September') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'October') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'November') . '",
			"' . KT_I18N::translate_c('NOMINATIVE', 'December') . '"
		)
		cal_setDayHeaders(
			"' . KT_I18N::translate('Sun') . '",
			"' . KT_I18N::translate('Mon') . '",
			"' . KT_I18N::translate('Tue') . '",
			"' . KT_I18N::translate('Wed') . '",
			"' . KT_I18N::translate('Thu') . '",
			"' . KT_I18N::translate('Fri') . '",
			"' . KT_I18N::translate('Sat') . '"
		)
		cal_setWeekStart(' . $WEEK_START . ');
	');
}

function print_findindi_link($element_id, $indiname='', $ged=KT_GEDCOM) {
	return '<a href="#" onclick="findIndi(document.getElementById(\'' . $element_id . '\'), document.getElementById(\''.$indiname.'\'), \'' . KT_Filter::escapeHtml($ged) . '\'); return false;" class="icon-button_indi" title="'.KT_I18N::translate('Find an individual').'"></a>';
  }

function print_findplace_link($element_id) {
	return '<a href="#" onclick="findPlace(document.getElementById(\'' . $element_id . '\'), \''.KT_GEDURL.'\'); return false;" class="icon-button_place" title="'.KT_I18N::translate('Find a place').'"></a>';
}

function print_findfamily_link($element_id) {
	return '<a href="#" onclick="findFamily(document.getElementById(\'' . $element_id . '\'), \''.KT_GEDURL.'\'); return false;" class="icon-button_family" title="'.KT_I18N::translate('Find a family').'"></a>';
}

function print_specialchar_link($element_id) {
	return '<span onclick="findSpecialChar(document.getElementById(\'' . $element_id . '\')); if (window.updatewholename) { updatewholename(); } return false;" class="icon-button_keyboard" title="'.KT_I18N::translate('Find a special character').'"></span>';
}

function print_autopaste_link($element_id, $choices) {
	echo '<small>';
	foreach ($choices as $indexval => $choice) {
		echo '<span onclick="document.getElementById(\'', $element_id, '\').value=';
		echo '\'', $choice, '\';';
		echo " return false;\">", $choice, '</span> ';
	}
	echo '</small>';
}

function print_findsource_link($element_id, $sourcename='') {
	return '<a href="#" onclick="findSource(document.getElementById(\'' . $element_id . '\'), document.getElementById(\''.$sourcename.'\'), \''.KT_GEDURL.'\'); return false;" class="icon-button_source" title="'.KT_I18N::translate('Find a source').'"></a>';
}

function print_findnote_link($element_id, $notename='') {
	return '<a href="#" onclick="findnote(document.getElementById(\'' . $element_id . '\'), document.getElementById(\''.$notename.'\'), \''.KT_GEDURL.'\'); return false;" class="icon-button_findnote" title="'.KT_I18N::translate('Find a note').'"></a>';
}

function print_findrepository_link($element_id) {
	return '<a href="#" onclick="findRepository(document.getElementById(\'' . $element_id . '\'), \''.KT_GEDURL.'\'); return false;" class="icon-button_repository" title="'.KT_I18N::translate('Find a repository').'"></a>';
}

function print_findmedia_link($element_id, $choose='') {
	return '<a href="#" onclick="findMedia(document.getElementById(\'' . $element_id . '\'), \''.$choose.'\', \''.KT_GEDURL.'\'); return false;" class="icon-button_media" title="'.KT_I18N::translate('Find a media object').'"></a>';
}

function print_findfact_link($element_id) {
	return '<a href="#" onclick="findFact(document.getElementById(\'' . $element_id . '\'), \''.KT_GEDURL.'\'); return false;" class="icon-button_find_facts" title="'.KT_I18N::translate('Find a fact or event').'"></a>';
}

function print_findfact_edit_link($element_id) {
	return '<a href="#" onclick="findFact(document.getElementById(\'' . $element_id . '\'), \''.KT_GEDURL.'\'); return false;" title="'.KT_I18N::translate('Find a fact or event').'">
				<i class="fa fa-pencil"></i>' . KT_I18N::translate('Edit'). '
			</a>';
}


/**
* get a quick-glance view of current LDS ordinances
* @param string $indirec
* @return string
*/
function get_lds_glance($indirec) {
	global $GEDCOM;
	$ged_id=get_id_from_gedcom($GEDCOM);
	$text = "";

	$ord = get_sub_record(1, "1 BAPL", $indirec);
	if ($ord) $text .= "B";
	else $text .= "_";
	$ord = get_sub_record(1, "1 ENDL", $indirec);
	if ($ord) $text .= "E";
	else $text .= "_";
	$found = false;
	$ct = preg_match_all("/1 FAMS @(.*)@/", $indirec, $match, PREG_SET_ORDER);
	for ($i=0; $i<$ct; $i++) {
		$famrec = find_family_record($match[$i][1], $ged_id);
		if ($famrec) {
			$ord = get_sub_record(1, "1 SLGS", $famrec);
			if ($ord) {
				$found = true;
				break;
			}
		}
	}
	if ($found) $text .= "S";
	else $text .= "_";
	$ord = get_sub_record(1, "1 SLGC", $indirec);
	if ($ord) $text .= "P";
	else $text .= "_";
	return $text;
}

function getPersonLinks ($person){
	global $PEDIGREE_FULL_DETAILS, $OLD_PGENS, $GEDCOM;
	global $box_width, $chart_style, $generations, $show_spouse, $talloffset;

	$pid = $person->getXref();
	$tmp = array('M'=>'', 'F'=>'F', 'U'=>'NN');
	$isF = $tmp[$person->getSex()];

	$personlinks = '<ul class="person_box' . $isF . '">
		<li>
			<a href="pedigree.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;PEDIGREE_GENERATIONS=' . $OLD_PGENS . '&amp;talloffset=' . $talloffset . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Pedigree') . '</b>
			</a>
		</li>';
		if (array_key_exists('googlemap', KT_Module::getActiveModules())) {
			$personlinks .= '
				<li>
					<a href="module.php?mod=googlemap&amp;mod_action=pedigree_map&amp;rootid=' . $pid . '&amp;ged=' . KT_GEDURL . '">
						<b>' . KT_I18N::translate('Pedigree map') . '</b>
					</a>
				</li>
			';
		}
		if (KT_USER_GEDCOM_ID && KT_USER_GEDCOM_ID != $pid) {
			$personlinks .= '
				<li>
					<a href="relationship.php?show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;pid1=' . KT_USER_GEDCOM_ID . '&amp;pid2=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;pretty=2&amp;followspouse=1&amp;ged=' . KT_GEDURL . '">
						<b>' . KT_I18N::translate('Relationship to me') . '</b>
					</a>
				</li>
			';
		}
		$personlinks .= '<li>
			<a href="descendancy.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;generations=' . $generations . '&amp;box_width=' . $box_width . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Descendants') . '</b>
			</a>
		</li>
		<li>
			<a href="ancestry.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;chart_style=' . $chart_style . '&amp;PEDIGREE_GENERATIONS=' . $OLD_PGENS . '&amp;box_width=' . $box_width . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Ancestors') . '</b>
			</a>
		</li>
		<li>
			<a href="compact.php?rootid=' . $pid . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Compact tree') . '</b>
			</a>
			</li>
		<li>
			<a href="module.php?mod=chart_fanchart&mod_action=show&rootid=' . $pid . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '">
				<b>' . KT_I18N::translate('Fanchart') . '</b>
			</a>
		</li>
		<li>
			<a href="hourglass.php?rootid=' . $pid . '&amp;show_full=' . $PEDIGREE_FULL_DETAILS . '&amp;chart_style=' . $chart_style . '&amp;PEDIGREE_GENERATIONS=' . $OLD_PGENS . '&amp;box_width=' . $box_width . '&amp;ged=' . rawurlencode((string) $GEDCOM) . '&amp;show_spouse=' . $show_spouse . '">
				<b>' . KT_I18N::translate('Hourglass chart') . '</b>
			</a>
		</li>';
		if (array_key_exists('tree', KT_Module::getActiveModules())) {
			$personlinks .= '
				<li>
					<a href="module.php?mod=tree&amp;mod_action=treeview&amp;ged=' . KT_GEDURL . '&amp;rootid=' . $pid . '">
						<b>' . KT_I18N::translate('Interactive tree') . '</b>
					</a>
				</li>
			';
		}
		foreach ($person->getSpouseFamilies() as $family) {
			$spouse		= $family->getSpouse($person);
			$children	= $family->getChildren();
			$num		= count($children);
			$personlinks .= '<li>';
				if ((!empty($spouse))||($num>0)) {
					$personlinks .= '
						<a href="' . $family->getHtmlUrl() . '">
							<b>' . KT_I18N::translate('Family with spouse') . '</b>
						</a>
						<br>
					';
					if (!empty($spouse)) {
						$personlinks .= '
							<a href="' . $spouse->getHtmlUrl() . '">' .
								$spouse->getFullName() . '
							</a>
						';
					}
				}
			$personlinks .= '</li>
			<li>
				<ul>';
					foreach ($children as $child) {
						$personlinks .= '
							<li>
								<a href="' . $child->getHtmlUrl() . '">' .
									$child->getFullName() . '
								</a>
							</li>
						';
					}
				$personlinks .= '</ul>
			</li>';
		}
	$personlinks .= '</ul>';

	return $personlinks;

}
