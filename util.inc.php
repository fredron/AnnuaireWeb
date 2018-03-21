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

/* ---------- interface related functions ---------- */

/**
 * set a value according to its value and to the default value
 *
 * @param $value
 * @param $defaultValue the default value
 * @return the value
 */
function setValue($value, $defaultValue)
{
    if ($value == null) {
        $value = $defaultValue;
    }

    $value = trim($value);

    return $value;
}

/**
 * search for a record
 *
 * @param $dbResource the database resource
 * @param $dbTable the table name
 * @param $selectFieldsList the fields list to select
 * @param $whereFieldsList the fields list used as search criteria
 * @param $id the id of the record to search for
 * @return array containing the found record or throw an exception
 */
function getRecordFieldsList($dbResource, $dbTable, $selectFieldsList, $whereFieldsList, $id)
{
    $whereConditionsList = array();
    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $whereFieldsList["id"]["name"], "comparisonOperator" => "=", "fieldValue" => $id, "closingParenthesis" => null));

    $statement = sqlSelect($dbResource, $dbTable, $selectFieldsList, $whereConditionsList, null, null);
    try {
        $pdoResultSet = $dbResource->query($statement);
        $recordFieldsList = $pdoResultSet->fetch(PDO::FETCH_ASSOC);
        $pdoResultSet->closeCursor();
        return $recordFieldsList;
    } catch (Exception $exception) {
        throw $exception;
    }
}

/* ---------- url related functions ---------- */

/**
 * generate a url
 *
 * @param $searchField the field that will be used for the search
 * @param $searchOperator the operator that will be used for the search
 * @param $searchValue the value that will be used for the search
 * @param $action the action requested by the user
 * @param $id the id of the record to display
 * @param $exportFormat the format used to export data
 * @return the url
 */
function generateUrl($searchField, $searchOperator, $searchValue, $action, $id, $exportFormat)
{
    global $scriptName;
    $separator1 = "?";
    $separator2 = "&amp;";

    $url = $scriptName;
    $url .= $separator1 . "searchField=" . rawurlencode($searchField);
    $url .= $separator2 . "searchOperator=" . rawurlencode($searchOperator);
    $url .= $separator2 . "searchValue=" . rawurlencode($searchValue);
    $url .= $separator2 . "action=" . rawurlencode($action);
    $url .= $separator2 . "id=" . rawurlencode($id);
    $url .= $separator2 . "exportFormat=" . rawurlencode($exportFormat);

    return $url;
}

/* ---------- html related functions ---------- */

/**
 * return html tag in order to display the image
 *
 * @param $filename the filename of the image
 * @param $title [optional] the alternative text of the image
 * @param $link [optional] the link of the image
 * @return the html tag
 */
function generateImageTag($filename, $title = null, $link = null)
{
    if (!is_file($filename)) {
        return "no image";
    }

    $imageSize = getImageSize($filename);
    $linkTitle = null;
    $alt = null;

    if ($title != null) {
        if ($link != null) {
            $linkTitle = $title;
        } else {
            $alt = $title;
        }
    }

    $tag = "<img src=\"$filename\" " . $imageSize[3] . " alt=\"$alt\" />";

    if ($link != null) {
        $tag = "<a href=\"$link\" title=\"$linkTitle\">$tag</a>";
    }

    return $tag;
}

/**
 * generate an html item from an html template with 2 parameters
 *
 * @param $template the html template
 * @param $field1 dynamic field number 1
 * @param $field2 [optionnal] dynamic field number 2
 * @param $field3 [optionnal] dynamic field number 3
 * @param $field4 [optionnal] dynamic field number 4
 * @param $field5 [optionnal] dynamic field number 5
 * @param $field6 [optionnal] dynamic field number 6
 * @param $field7 [optionnal] dynamic field number 7
 * @param $field8 [optionnal] dynamic field number 8
 * @param $field9 [optionnal] dynamic field number 9
 * @return the html code
 */
function generateItem($template, $field1, $field2 = null, $field3 = null, $field4 = null, $field5 = null, $field6 = null, $field7 = null, $field8 = null, $field9 = null)
{
    $template = str_replace("%field1%", $field1, $template);
    $template = str_replace("%field2%", $field2, $template);
    $template = str_replace("%field3%", $field3, $template);
    $template = str_replace("%field4%", $field4, $template);
    $template = str_replace("%field5%", $field5, $template);
    $template = str_replace("%field6%", $field6, $template);
    $template = str_replace("%field7%", $field7, $template);
    $template = str_replace("%field8%", $field8, $template);
    $template = str_replace("%field9%", $field9, $template);

    return $template;
}

/**
 * generate a list item with html tags
 *
 * @param $inputString the list item to generate
 * @return the generated list item
 */
function generateListItem($inputString)
{
    $outputString = " <li>$inputString</li>\n";

    return $outputString;
}

/**
 * generate an option item in an html form
 *
 * @param $optionTemplate the option html template
 * @param $selectedTemplate the selected parameter html template
 * @param $value the value of the item to add
 * @param $label the label to display
 * @param $selectedValue the selected value is used to check is the value must be the default one
 * @return the html code
 */
function generateFormOptionItem($optionTemplate, $selectedTemplate, $value, $label, $selectedValue)
{
    $optionTemplate = str_replace("%value%", $value, $optionTemplate);
    if ($value == $selectedValue) {
        $optionTemplate = str_replace("%selected%", $selectedTemplate, $optionTemplate);
    } else {
        $optionTemplate = str_replace("%selected%", null, $optionTemplate);
    }
    $optionTemplate = str_replace("%label%", $label, $optionTemplate);

    return $optionTemplate;
}

/**
 * emphasize a string with html tags
 *
 * @param $inputString the string to emphasize
 * @return the emphasized string
 */
function emphasize($inputString)
{
    $outputString = "<em>$inputString</em>";

    return $outputString;
}

/**
 * italize a string with html tags
 *
 * @param $inputString the string to italize
 * @return the italized string
 */
function italize($inputString)
{
    $outputString = "<i>$inputString</i>";

    return $outputString;
}

/**
 * paragraphize a string with html tags
 *
 * @param $inputString the string to paragraphize
 * @return the paragraphized string
 */
function paragraphize($inputString)
{
    $outputString = "<p>$inputString</p>\n";

    return $outputString;
}

/**
 * format a string to make it pretty
 *
 * @param $inputString the string to format
 * @return the pretty-formatted string
 */
function prettyFormat($inputString)
{
    /* removing multiple spaces */
    $outputString = preg_replace("/  +/", " ", $inputString);
    /* trimming lines */
    $outputString = preg_replace("/\n /", "\n", $outputString);
    $outputString = preg_replace("/ \n/", "\n", $outputString);
    /* deleting empty lines */
    $outputString = preg_replace("/\n\n+/", "\n", $outputString);
    $outputString = preg_replace("/^\n/", null, $outputString);

    return $outputString;
}

/* ---------- database related functions ---------- */

/**
 * reorder id
 *
 * @param $dbResource the database resource
 * @param $tablename the table name
 * @param $fieldsList the array of the fields (name, databaseType, htmlType, pattern, label, defaultValue, placeholder, required)
 * @param $orderByConditionsList the array of the order by conditions (name)
 * @return nothing or throw an exception
 */
function reorderId($dbResource, $tablename, $fieldsList, $orderByConditionsList)
{
    $temporaryTablename = "temporary_$tablename";

    $dropTemporaryTableStatement = sqlDropTable($dbResource, $temporaryTablename);
    $createTemporaryTableStatement = sqlCreateTable($dbResource, $temporaryTablename, setPrimaryKey($fieldsList));
    /* remove the 'id' field in the insert-select statement so that the table content can be automatically reordered */
    unset($fieldsList["id"]);
    $insertSelectStatement = sqlInsertSelect($dbResource, $temporaryTablename, $fieldsList, $tablename, $fieldsList, null, $orderByConditionsList, null);
    $dropTableStatement = sqlDropTable($dbResource, $tablename);
    $renameTableStatement = sqlRenameTable($dbResource, $temporaryTablename, $tablename);

    try {
        $dbResource->exec($dropTemporaryTableStatement);
        $dbResource->exec($createTemporaryTableStatement);
        $dbResource->exec($insertSelectStatement);
        $dbResource->exec($dropTableStatement);
        $dbResource->exec($renameTableStatement);
        sqlCreateTriggers($dbResource, $tablename, $fieldsList["lastmodified"]["name"]);
    } catch (Exception $exception) {
        throw $exception;
    }
}
?>
