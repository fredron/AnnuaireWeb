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
    /* requiring functions include files */
    require("label/PDF_Label.php");

    /* configuration */
    global $labelAveryTemplate;

    /* generating pdf file */
    $recordList = array();
    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        /* decode htmlspecialchars */
        foreach($data as &$item) {
            $item = htmlspecialchars_decode($item, ENT_QUOTES);
        }
        array_push($recordList, array_map("utf8_decode", $data));
    }
    $pdoResultSet->closeCursor();

    $pdf = new PDF_Label($labelAveryTemplate);
    $pdf->AddPage();

    foreach ($recordList as $record) {
        extract($record);

        $label = <<<END
$firstname $name
$address
$zipcode $city
$country
END;

        $label = prettyFormat($label);
        $pdf->Add_Label($label);
    }

    return $pdf->Output(null, "S");
}
?>
