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

/**
 * export data
 *
 * @param $dbResource the database resource
 * @param $exportFormat the format used to export data
 * @param $searchField the field that will be used for the search
 * @param $searchOperator the operator that will be used for the search
 * @param $searchValue the value that will be used for the search
 * @return the filepath of the exported file or throw an exception
 */
function export($dbResource, $exportFormat, $searchField, $searchOperator, $searchValue)
{
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
    require("export_$exportFormat.inc.php");

    /* configuration */
    $exportMode = $externalFormatsList[$exportFormat]["exportMode"];
    $date = date("Ymd");
    $filenameTemplate = $externalFormatsList[$exportFormat]["exportFilenameTemplate"];

    $from = array("%dbTable%", "%date%");
    $to = array($dbTable, $date);
    $filename = str_replace($from, $to, $filenameTemplate);

    $filepath = "$exportPath/$filename";

    /* ---------- script ---------- */
    if (!$externalFormatsList[$exportFormat]["exportEnabled"]) {
        throw new Exception(sprintf(_("export format %s is disabled in the configuration file"), $exportFormat));
    }

    /* setting where conditions */
    $whereConditionsList = array();
    $searchMask = $searchOperatorsList[$searchOperator]["prefix"] . $searchValue . $searchOperatorsList[$searchOperator]["suffix"];
    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $searchField, "comparisonOperator" => $searchOperatorsList[$searchOperator]["operator"], "fieldValue" => $searchMask, "closingParenthesis" => null));

    $statement = generateSql($dbResource, $dbTable, $fieldsList, $whereConditionsList, $sortRecordsListFieldsList);
    try {
        $pdoResultSet = $dbResource->query($statement);
        $fileContent = generateExport($dbResource, $pdoResultSet);
    } catch (Exception $exception) {
        throw $exception;
    }

    switch ($exportMode) {
     case "raw":
        header("Content-Type: text/plain; charset=$charset");
        print($fileContent);
        exit(0);
     case "client":
        header("Content-Type: application/octet-stream");
        header("Content-Disposition: attachment; filename=$filename");
        print($fileContent);
        exit(0);
     case "server":
        if (!$handle = @fopen($filepath, 'w+')) {
            throw new Exception(sprintf(_("file %s is not writable"), $filepath));
        }
        if (fwrite($handle, $fileContent) === false) {
            throw new Exception(sprintf(_("writing into the file %s failed"), $filepath));
        }
        fclose($handle);
        return $filepath;
     case "mailing":
        return $fileContent;
     default:
        throw new Exception(sprintf(_("no exportMode defined for export format %s in the configuration file"), $exportFormat));
    }
}
?>
