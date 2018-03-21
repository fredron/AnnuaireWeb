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
 * insert data from a temporary array into the database
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table to insert into
 * @param $recordList the records to insert (name, value)
 * @return nothing or throw an exception
 */
function insertData($dbResource, $tablename, $recordList)
{
    $statementsList = array();

    foreach ($recordList as $item) {
        $statement = sqlInsert($dbResource, $tablename, $item);
        try {
            $dbResource->exec($statement);
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}

/**
 * import data
 *
 * @param $dbResource the database resource
 * @return the filepath of the imported file or throw an exception
 */
function import($dbResource)
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
    require("filesystem.inc.php");

    /* ---------- script ---------- */
    try {
        $filesList = getFileList($importPath, "f", "n");
    } catch (Exception $exception) {
        throw $exception;
    }

    if (sizeof($filesList) == 1) {
        $filepath = array_shift($filesList);
        $filename = basename($filepath);
        $importFormat = getFileExtension($filename);
    } else {
        throw new Exception(sprintf(_("directory %s contains no file or more than one file"), $importPath));
    }

    if (!$externalFormatsList[$importFormat]["importEnabled"]) {
        throw new Exception(sprintf(_("import format %s is disabled in the configuration file"), $importFormat));
    }

    /* requiring functions include files */
    require("import_$importFormat.inc.php");

    try {
        $parsedData = parseFile($filepath, $userFieldsList, $charset);
        if ($deleteBeforeImport) {
            $statement = sqlDelete($dbResource, $dbTable, null);
            $dbResource->exec($statement);
        }
        insertData($dbResource, $dbTable, $parsedData);
    } catch (Exception $exception) {
        throw $exception;
    }

    if (!@rename($filepath, "$importArchivePath/$filename")) {
        throw new Exception(sprintf(_("moving the import file %s to archive directory %s failed"), $filepath, $importArchivePath));
    }

    return $filepath;
}
?>
