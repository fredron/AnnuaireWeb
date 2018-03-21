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
    unset($selectFieldsList["id"]);
    unset($selectFieldsList["lastmodified"]);

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
    $fileContent = null;
    $csvContent = null;

    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        $header = array_keys($data);

        $csvContent .= csvFormat($data);
    }
    $pdoResultSet->closeCursor();

    /* do not generate any content if there is no result */
    if (isset($header)) {
        $csvHeader = csvFormat($header);
        $fileContent = $csvHeader . $csvContent;
    }

    return $fileContent;
}

/**
 * format a string to csv format
 *
 * @param $data the array containing the data to format
 * @return the csv-formatted string
 */
function csvFormat($data)
{
    $data = array_map("escape", $data);
    /* decode htmlspecialchars */
    foreach($data as &$item) {
        $item = htmlspecialchars_decode($item, ENT_QUOTES);
    }

    /* $outputString = "\"" . implode("\",\"", $data) . "\"\n"; */

	/**
	 *  Ajout du Byte-Order-Mask en début de fichier pour qu'Excell interprète correctement le fichier en UTF-8 (prise en comptes des caractères * *  accentués.
	 *  Suppression des double quote, inutile lors de l'import dans Excell
	 */
	$outputString = implode(",", $data) . "\n";
	
    return "ï»¿" + $outputString;
}

/**
 * escape the double quote character in order to be csv compliant
 *
 * @param $string the string to escape
 * @return the escaped string
 */
function escape($string)
{
    return str_replace("\"", "\"\"", $string);
}
?>
