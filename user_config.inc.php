<?php
/**
 * This file is part of phpMyAddressbook.
 *
 * phpMyAddressbook is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * phpMyAddressbook is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with phpMyAddressbook.  If not, see <http://www.gnu.org/licenses/>.
 */

/* mandatory database parameters */
$dbEngine = "mysql";
$dbName = "annuaire";
$dbTable = "contact";

/* mysql database parameters */
$dbServer = "localhost";
$dbLogin = "root";
$dbPassword = "rubicube";
$dbCharset = "UTF8";

/* user parameters */
$mailingSenderEmail = null;
$mailingSenderName = null;
$clickToCallURL = "http://asterisk.server/path/page.php?caller=usernumber&amp;callie=%s";

?>
