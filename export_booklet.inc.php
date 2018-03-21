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
    /* requiring functions include files */
    require("booklet.inc.php");

    /* configuration */
    global $appName;
    global $recordsLabel;
    global $bookletExportTitleLabel;
    $recordsPerSmallPage = 11;

    /* distributing records in small pages then in big pages */
    $recordList = array();
    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        /* decode htmlspecialchars */
        foreach($data as &$item) {
            $item = htmlspecialchars_decode($item, ENT_QUOTES);
        }
        array_push($recordList, array_map("utf8_decode", $data));
    }
    $pdoResultSet->closeCursor();

    $recordsCount = count($recordList) . " " . $recordsLabel;

    $smallPageNumber = 0;
    $smallPagesList = array();

    /* creating title page */
    $smallPagesList[$smallPageNumber]["pageNumber"] = $smallPageNumber;
    $smallPagesList[$smallPageNumber]["type"] = "title";

    /* distributing records in small pages */
    while (sizeof($recordList) > 0) {
        $smallPageNumber++;
        $smallPagesList[$smallPageNumber]["pageNumber"] = $smallPageNumber;
        for ($recordNumber = 0; $recordNumber < $recordsPerSmallPage; $recordNumber++) {
            if ((sizeof($recordList) > 0)) {
                array_push($smallPagesList[$smallPageNumber], array_shift($recordList));
            }
        }
    }

    /* adding small blank pages at the end of the document if the modulo(4) differs from 0 */
    while ((count($smallPagesList)%4) != 0) {
        $smallPageNumber++;
        $smallPagesList[$smallPageNumber]["pageNumber"] = $smallPageNumber;
    }

    /* adding 4 small blank pages in the middle of the document if the modulo(8) differs from 0 */
    if (count($smallPagesList)%8 != 0) {
        $smallPagesCount = count($smallPagesList) - 1;
        for ($i = $smallPagesCount; $i > $smallPagesCount / 2; $i--) {
            $j = $i + 4;
            $smallPagesList[$j] = $smallPagesList[$i];
            $smallPagesList[$i] = array();
        }
    }

    /* sorting the array of small pages according to the page number */
    ksort($smallPagesList);

    $bigPageNumber = 0;
    $bigPagesList = array();

    /* distributing small pages in big pages */
    while (sizeof($smallPagesList) > 0) {
        $bigPagesList[$bigPageNumber][] = array_pop($smallPagesList);
        $bigPagesList[$bigPageNumber][] = array_shift($smallPagesList);
        $smallPageTemporary1 = array_shift($smallPagesList);
        $smallPageTemporary2 = array_pop($smallPagesList);
        $bigPagesList[$bigPageNumber][] = array_pop($smallPagesList);
        $bigPagesList[$bigPageNumber][] = array_shift($smallPagesList);
        $bigPageNumber++;
        $bigPagesList[$bigPageNumber][] = $smallPageTemporary1;
        $bigPagesList[$bigPageNumber][] = $smallPageTemporary2;
        $bigPagesList[$bigPageNumber][] = array_shift($smallPagesList);
        $bigPagesList[$bigPageNumber][] = array_pop($smallPagesList);
        $bigPageNumber++;
    }

    /* generating pdf file */
    $documentWidth = 595;
    $documentHeight = 842;
    $defaultMargin = 10;
    $centralMargin = $defaultMargin * 2;
    $cellWidth = 277;
    $cellHeight = 10;
    $cellMargin = 6;
    $fontFamily = "Courier";
    $fontSize = 8;
    $creator = "fpdf";
    $author = $appName;
    $title = $bookletExportTitleLabel;

    $pdf = new Booklet("P", "pt");
    $pdf->setPageSettings($documentWidth, $documentHeight, $defaultMargin, $centralMargin, $cellWidth, $cellHeight, $cellMargin, $fontFamily, $fontSize);
    $pdf->setCredits($creator, $author, $title);
    $pdf->setParameters($recordsCount, $bigPagesList);
    return $pdf->generateDocument();
}
?>
