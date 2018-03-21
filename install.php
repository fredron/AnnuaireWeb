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

/* ---------- initialization ---------- */

/* requiring configuration files */
try {
    require("config.inc.php");
} catch (Exception $exception) {
    $exceptionMessage = $exception->getMessage();
    printf(_("configuration file contains an error : %s"), $exceptionMessage);
    exit(1);
}

/* requiring functions include files */
require("util.inc.php");
require("sql.inc.php");

/* status */
$status = true;

/* getting templates content */
$mainTemplate = file_get_contents($installTemplateFilename);
$linkTemplate = file_get_contents($linkTemplateFilename);

/* ---------- script ---------- */

/* setting database connection once and for all */
try {
    $dbResource = sqlConnect($dbServer, $dbLogin, $dbPassword, $dbCharset, $dbName);
} catch (Exception $exception) {
    $exceptionMessage = $exception->getMessage();
    $status = false;
}

/* ---------- database related operations ---------- */

$content = "$installReportTitleLabel\n";
$content .= "<ul>\n";

if ($status) {
    $statement = sqlCreateTable($dbResource, $dbTable, setPrimaryKey($fieldsList));

    $content .= generateListItem(sprintf($installDatabaseEngineLabel, emphasize($dbEngine)));

    if ($dbEngine == "mysql") {
        $content .= generateListItem(sprintf($installDatabaseServerLabel, emphasize($dbServer), emphasize($dbLogin)));
    }

    try {
        $dbResource->exec($statement);
        $content .= generateListItem(sprintf($installDatabaseSchemaLabel, emphasize($dbTable), emphasize($dbName)));
        sqlCreateTriggers($dbResource, $dbTable, $techFieldsList["lastmodified"]["name"]);
        $content .= generateListItem($installDatabaseTriggersLabel);
    } catch (Exception $exception) {
        $exceptionMessage = $exception->getMessage();
        $status = false;
    }
}

if (!$status) {
    $content .= generateListItem(sprintf($installDatabaseErrorLabel, emphasize($exceptionMessage)));
}

$content .= "</ul>";

/* ---------- setting display elements ---------- */

/* setting charset */
$dynamicContent["charset"] = $charset;

/* setting content language */
$dynamicContent["contentLanguage"] = str_replace("_", "-", $language);

/* setting website title */
$dynamicContent["title"] = $title;

/* setting css */
$dynamicContent["cssFilename"] = "$cssPath/$cssFilename";

/* setting subtitle */
if ($status) {
    $subtitle = sprintf($installDatabaseSuccessfulLabel, generateItem($linkTemplate, $appName, "."));
} else {
    $subtitle = $installDatabaseFailedLabel;
}
$dynamicContent["subtitle"] = $subtitle;

/* setting content */
$dynamicContent["content"] = $content;

/* setting footer */
$dynamicContent["footer"] = $footer;

/* ---------- display ---------- */

/* updating dynamic fields */
$htmlOutput = $mainTemplate;
foreach ($dynamicContent as $key => $item) {
    $htmlOutput = str_replace("%$key%", $item, $htmlOutput);
}

/* sending content type header */
header("Content-Type: $mime;charset=$charset");

/* displaying dynamic html */
print($htmlOutput);
?>
