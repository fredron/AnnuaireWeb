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
 * generate the sql statement
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table
 * @param $fieldsList the array of the fields (name, databaseType, htmlType, pattern, label, defaultValue, placeholder, required)
 * @param $whereConditionsList the array of the where conditions (logical operator, opening parenthesis, field name, comparison operator, field value, closing parenthesis)
 * @param $sortFieldsList the array of the fields used for sorting (name)
 * @return the sql statement
 */
function generateSql($dbResource, $tablename, $fieldsList, $whereConditionsList, $sortFieldsList)
{
    $selectFieldsList = $fieldsList;

    $statement = sqlSelect($dbResource, $tablename, $selectFieldsList, $whereConditionsList, $sortFieldsList, null);

    return $statement;
}

/**
 * generate the export file
 *
 * @param $dbResource the database resource
 * @param $pdoResultSet the pdo statement result set to fetch
 * @return the exported file as a string
 */
function generateExport($dbResource, $pdoResultSet)
{
    global $appName;
    global $dbServer;
    global $dbName;
    global $dbTable;
    global $fieldsList;
    global $dbServer;

    $fileContent = <<<END
#
# $appName dump
#
# server   - $dbServer
# database - $dbName

END;

    $fileContent .= "# address  - " . (($_SERVER["HTTPS"] == "on" ) ? "https" : "http") . "://" . $_SERVER["SERVER_NAME"] . (($d = dirname($_SERVER["SCRIPT_NAME"])) != "/" ? $d : null) . "/\n";

    $fileContent .= <<<END

#
# table structure for table $dbTable
#

END;

    $fileContent .= sqlDropTable($dbResource, $dbTable) . ";\n";
    $fileContent .= sqlCreateTable($dbResource, $dbTable, setPrimaryKey($fieldsList)) . ";\n";

    $fileContent .= <<<END

#
# dumping data for table $dbTable
#

END;

    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        $insertFieldsList = array();
        foreach ($data as $key => $item) {
            /* decode htmlspecialchars */
            array_push($insertFieldsList, array("name" => $key, "value" => htmlspecialchars_decode($item, ENT_QUOTES)));
        }
        $fileContent .= sqlInsert($dbResource, $dbTable, $insertFieldsList) . ";\n";
    }
    $pdoResultSet->closeCursor();

    return $fileContent;
}
?>
