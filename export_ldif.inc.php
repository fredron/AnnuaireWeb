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
    array_push($selectFieldsList, $fieldsList["email"]);

    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $fieldsList["email"]["name"], "comparisonOperator" => "!=", "fieldValue" => null, "closingParenthesis" => null));

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
        $fileContent .= ldifFormat($data);
    }
    $pdoResultSet->closeCursor();

    $fileContent .= "\n";

    return $fileContent;
}

/**
 * format a string to ldif format
 *
 * @param $data the array containing the data to format (name, firstname, email)
 * @return the ldif-formatted string
 */
function ldifFormat($data)
{
    /* decode htmlspecialchars */
    foreach($data as &$item) {
        $item = htmlspecialchars_decode($item, ENT_QUOTES);
    }
    extract($data);

    $fullName = trim("$firstname $name");
    $outputString = "dn: cn=$fullName,mail=$email
objectclass: top
objectclass: person
objectclass: organizationalPerson
objectclass: inetOrgPerson
objectclass: mozillaAbPersonAlpha
givenName: $firstname
sn: $fullName
cn: $fullName
mail: $email
modifytimestamp: 0Z

";

    return $outputString;
}
?>
