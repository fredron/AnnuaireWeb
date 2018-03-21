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

/* requiring database specific functions include files */
require("$dbEngine.inc.php");

/**
 * generate the where conditions
 *
 * @param $dbResource the database resource
 * @param $whereConditionsList the array of the where conditions (logical operator, opening parenthesis, field name, comparison operator, field value, closing parenthesis)
 * @return the generated statement
 */
function generateWhereConditions($dbResource, $whereConditionsList)
{
    $dummyList = array();
    foreach ($whereConditionsList as $item) {
        $protectedFieldValue = $dbResource->quote($item["fieldValue"]);
        array_push($dummyList, $item["logicalOperator"] . " " . $item["openingParenthesis"] . $item["fieldName"] . " " . $item["comparisonOperator"] . " " . $protectedFieldValue . $item["closingParenthesis"]);
    }
    $statement = implode("\n", $dummyList) . "\n";

    return $statement;
}

/**
 * create the table $tablename according to the array $fieldsList
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table
 * @param $fieldsList the array of the fields (name, databaseType)
 * @return the generated statement
 */
function sqlCreateTable($dbResource, $tablename, $fieldsList)
{
    $statement = "CREATE TABLE $tablename (\n";

    $dummyList = array();
    foreach ($fieldsList as $item) {
        array_push($dummyList, " " . $item["name"] . " " . $item["databaseType"]);
    }
    $statement .= implode(",\n", $dummyList) . "\n";

    $statement .= ")\n";

    return $statement;
}

/**
 * rename the table $oldTablename to $newTablename
 *
 * @param $dbResource the database resource
 * @param $oldTablename the old name of the table
 * @param $newTablename the new name of the table
 * @return the generated statement
 */
function sqlRenameTable($dbResource, $oldTablename, $newTablename)
{
    $statement = "ALTER TABLE $oldTablename RENAME TO $newTablename\n";

    return $statement;
}

/**
 * drop the table $tablename
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table
 * @return the generated statement
 */
function sqlDropTable($dbResource, $tablename)
{
    $statement = "DROP TABLE IF EXISTS $tablename\n";

    return $statement;
}

/**
 * insert the records of the array $fieldsList in the table $tablename
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table
 * @param $fieldsList the array of the fields (name, value)
 * @return the generated statement
 */
function sqlInsert($dbResource, $tablename, $fieldsList)
{
    $statement = "INSERT INTO $tablename\n";

    $columnsList = array();
    $valuesList = array();
    foreach ($fieldsList as $item) {
        array_push($columnsList, $item["name"]);
        /* encode htmlspecialchars (see PDO::quote) */
        array_push($valuesList, $dbResource->quote($item["value"]));
    }
    $statement .= "(" . implode(", ", $columnsList) . ")\n";
    $statement .= "VALUES (" . implode(", ", $valuesList) . ")\n";

    return $statement;
}

/**
 * select the fields $fieldsList from the table $tablename satisfying the where
 * conditions $whereConditionsList, the order by conditions
 * $orderByConditionsList and the distinct flag
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table
 * @param $fieldsList the array of the fields (name)
 * @param $whereConditionsList the array  of the where conditions (logical operator, opening parenthesis, field name, comparison operator, field value, closing parenthesis)
 * @param $orderByConditionsList the array of the order by conditions (name)
 * @param $distinct set select statement to distinct if $distinct is not null
 * @return the generated statement
 */
function sqlSelect($dbResource, $tablename, $fieldsList, $whereConditionsList, $orderByConditionsList, $distinct)
{
    if ($distinct != null) {
        $distinct = "distinct";
    }
    $statement = "SELECT $distinct\n";

    $dummyList = array();
    foreach ($fieldsList as $item) {
        array_push($dummyList, " " . $item["name"]);
    }
    $statement .= implode(",\n", $dummyList) . "\n";

    $statement .= "FROM $tablename\n";
    $statement .= "WHERE 1\n";

    if (isset($whereConditionsList)) {
        $statement .= generateWhereConditions($dbResource, $whereConditionsList);
    }

    if (isset($orderByConditionsList)) {
        $statement .= "ORDER BY\n";
        $dummyList = array();
        foreach ($orderByConditionsList as $item) {
            array_push($dummyList, " " . $item["name"]);
        }
        $statement .= implode(",\n", $dummyList);
    }

    return $statement;
}

/**
 * select the fields $selectFieldsList from the table $selectTablename
 * satisfying the where conditions $whereConditionsList, the order by conditions
 * $orderByConditionsList and the distinct flag then insert them in the fields
 * $insertFieldsList of the table $insertTablename
 *
 * @param $dbResource the database resource
 * @param $insertTablename the name of the table to insert into
 * @param $insertFieldsList the array of the fields to insert (name)
 * @param $selectTablename the name of the table to select from
 * @param $selectFieldsList the array of the fields to select from (name)
 * @param $whereConditionsList the array of the where conditions (logical operator, opening parenthesis, field name, comparison operator, field value, closing parenthesis)
 * @param $orderByConditionsList the array of the order by conditions (name)
 * @param $distinct set select statement to distinct if $distinct is not null
 * @return the generated statement
 */
function sqlInsertSelect($dbResource, $insertTablename, $insertFieldsList, $selectTablename, $selectFieldsList, $whereConditionsList, $orderByConditionsList, $distinct)
{
    $statement = "INSERT INTO $insertTablename (\n";

    $dummyList = array();
    foreach ($insertFieldsList as $item) {
        array_push($dummyList, " " . $item["name"]);
    }
    $statement .= implode(",\n", $dummyList) . "\n";

    $statement .= ") ";
    $statement .= sqlSelect($dbResource, $selectTablename, $selectFieldsList, $whereConditionsList, $orderByConditionsList, $distinct);

    return $statement;
}

/**
 * update the records of the table $tablename satisfying the whereConditionsList
 * $whereConditionsList with the values of $fieldsList
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table to update
 * @param $fieldsList the array of the fields (name, value)
 * @param $whereConditionsList the array of the where conditions (logical operator, opening parenthesis, field name, comparison operator, field value, closing parenthesis)
 * @return the generated statement
 */
function sqlUpdate($dbResource, $tablename, $fieldsList, $whereConditionsList)
{
    $statement = "UPDATE $tablename\n";
    $statement .= "SET\n";

    $dummyList = array();
    foreach ($fieldsList as $item) {
        $protectedValue = $dbResource->quote($item["value"]);
        array_push($dummyList, " " . $item["name"] . " = $protectedValue");
    }
    $statement .= implode(",\n", $dummyList);

    $statement .= "\nWHERE 1\n";

    if (isset($whereConditionsList)) {
        $statement .= generateWhereConditions($dbResource, $whereConditionsList);
    }

    return $statement;
}

/**
 * delete the records satisfying the where conditions $whereConditionsList from
 * the table $tablename
 *
 * @param $dbResource the database resource
 * @param $tablename the name of the table for the delete
 * @param $whereConditionsList the array of the where conditions (logical operator, opening parenthesis, field name, comparison operator, field value, closing parenthesis)
 * @return the generated statement
 */
function sqlDelete($dbResource, $tablename, $whereConditionsList)
{
    $statement = "DELETE\n";
    $statement .= "FROM $tablename\n";
    $statement .= "WHERE 1\n";

    if (isset($whereConditionsList)) {
        $statement .= generateWhereConditions($dbResource, $whereConditionsList);
    }

    return $statement;
}
?>
