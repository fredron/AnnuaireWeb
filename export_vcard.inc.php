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
    array_push($selectFieldsList, $fieldsList["tags"]);
    array_push($selectFieldsList, $fieldsList["lastmodified"]);

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

    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        $fileContent .= vcardFormat($data);
    }
    $pdoResultSet->closeCursor();

    return $fileContent;
}

/**
 * format a string to vcard format
 *
 * @param $data the array containing the data to format (name, firstname, address, zipcode, city, country, homephone, cellphone, email, website, comments, tags, lastmodified)
 * @return the vcard-formatted string
 */
function vcardFormat($data)
{
    /* decode htmlspecialchars */
    foreach($data as &$item) {
        $item = htmlspecialchars_decode($item, ENT_QUOTES);
    }
    extract($data);

    $lastmodified = date("c", strtotime($lastmodified));

    $outputString = null;
    $outputString .= "BEGIN:VCARD\n";
    $outputString .= "VERSION:3.0\n";
    $outputString .= "UID:$firstname $name $zipcode\n";
    $outputString .= "FN:$firstname $name\n";
    $outputString .= "N:$name;$firstname\n";
    $outputString .= "ADR;TYPE=dom,postal,intl,parcel,home:;;$address;$city;;$zipcode;$country\n";
    $outputString .= "TEL;TYPE=voice,msg,home:$homephone\n";
    $outputString .= "TEL;TYPE=voice,msg,cell:$cellphone\n";
    $outputString .= "EMAIL;TYPE=internet:$email\n";
    $outputString .= "URL:$website\n";
    $outputString .= "NOTE:$comments\n";
    $outputString .= "CATEGORIES:$tags\n";
    $outputString .= "REV:$lastmodified\n";
    $outputString .= "END:VCARD\n";
    $outputString .= "\n";

    return $outputString;
}
?>
