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
    $selectFieldsList = array();
    array_push($selectFieldsList, $fieldsList["name"]);
    array_push($selectFieldsList, $fieldsList["firstname"]);
    array_push($selectFieldsList, $fieldsList["address"]);
    array_push($selectFieldsList, $fieldsList["zipcode"]);
    array_push($selectFieldsList, $fieldsList["city"]);
    array_push($selectFieldsList, $fieldsList["country"]);
    array_push($selectFieldsList, $fieldsList["homephone"]);
    array_push($selectFieldsList, $fieldsList["cellphone"]);
    array_push($selectFieldsList, $fieldsList["email"]);
    array_push($selectFieldsList, $fieldsList["website"]);
    array_push($selectFieldsList, $fieldsList["comments"]);

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
    $fileContent = <<<END
"email","fileAs","firstName","fullName","homeCity","homeCountry","homePhone","homePostalCode","homeStreet","homeURL","lastName","mobilePhone","notes"

END;

    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        $fileContent .= csvFormat($data);
    }
    $pdoResultSet->closeCursor();

    return $fileContent;
}

/**
 * format a string to csv format
 *
 * @param $data the array containing the data to format (name, firstname, address, zipcode, city, country, homephone, cellphone, email, website, comments)
 * @return the csv-formatted string
 */
function csvFormat($data)
{
    $data = array_map("escape", $data);
    /* decode htmlspecialchars */
    foreach($data as &$item) {
        $item = htmlspecialchars_decode($item, ENT_QUOTES);
    }
    extract($data);

    $fullName = "$firstname $name";
    $outputString = <<<END
"$email","2","$firstname","$fullName","$city","$country","$homephone","$zipcode","$address","$website","$name","$cellphone","$comments"

END;

    return $outputString;
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
