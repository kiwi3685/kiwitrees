<?php
/**
 * Kiwitrees: Web based Family History software
 * Copyright (C) 2012 to 2018 kiwitrees.net
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

/**
 * Marital status.
 */
class KT_Census_CensusColumnConditionCan extends KT_Census_CensusColumnConditionEnglish {
	/* Text to display for married individuals */
	protected $husband = 'M';
	protected $wife    = 'M';

	/* Text to display for unmarried individuals */
	protected $bachelor = '-';
	protected $spinster = '-';

	/* Text to display for children */
	protected $boy  = '-';
	protected $girl = '-';

	/* Text to display for divorced individuals */
	protected $divorce  = '-';
	protected $divorcee = '-';

	/* Text to display for widowed individuals */
	protected $widower = 'W';
	protected $widow   = 'W';
}
