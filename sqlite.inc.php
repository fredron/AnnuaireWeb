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
 * open a connection to the database
 *
 * @param $dbServer the database server name
 * @param $dbLogin the database login
 * @param $dbPassword the database password
 * @param $dbCharset the database charset
 * @param $dbName the database name
 * @return the database resource or throw an exception
 */
function sqlConnect($dbServer, $dbLogin, $dbPassword, $dbCharset, $dbName)
{
    try {
        $dbResource = new PDO("sqlite:$dbName");
        $dbResource->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $exception) {
        throw $exception;
    }

    return $dbResource;
}

/**
 * set a fields list item as a primary key
 *
 * @param $fieldsList the array of the fields (id, databaseType)
 * @return the modified fields list
 */
function setPrimaryKey($fieldsList)
{
    $fieldsList["id"]["databaseType"] .= " primary key autoincrement";

    return $fieldsList;
}

/**
 * create insert and update triggers
 *
 * @param $dbResource the database resource
 * @param $dbTable the table name
 * @param $fieldName the field name
 * @return nothing or throw an exception
 */
function sqlCreateTriggers($dbResource, $dbTable, $fieldName)
{
    $insertTriggerStatement = "
CREATE TRIGGER insert_record AFTER INSERT ON $dbTable
BEGIN
 UPDATE $dbTable SET $fieldName = DATETIME('NOW', 'LOCALTIME')  WHERE rowid = new.rowid;
END;";

    $updateTriggerStatement = "
CREATE TRIGGER update_record AFTER UPDATE ON $dbTable
BEGIN
 UPDATE $dbTable SET $fieldName = DATETIME('NOW', 'LOCALTIME')  WHERE rowid = new.rowid;
END;";

    try {
        $dbResource->exec($insertTriggerStatement);
        $dbResource->exec($updateTriggerStatement);
    } catch (Exception $exception) {
        throw $exception;
    }
}
?>
