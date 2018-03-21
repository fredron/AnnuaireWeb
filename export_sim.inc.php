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
    array_push($selectFieldsList, $fieldsList["homephone"]);
    array_push($selectFieldsList, $fieldsList["cellphone"]);

    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => "(", "fieldName" => $fieldsList["homephone"]["name"], "comparisonOperator" => "!=", "fieldValue" => null, "closingParenthesis" => null));
    array_push($whereConditionsList, array("logicalOperator" => "OR", "openingParenthesis" => null, "fieldName" => $fieldsList["cellphone"]["name"], "comparisonOperator" => "!=", "fieldValue" => null, "closingParenthesis" => ")"));

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

    $counter = 1;
    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        $fileContent .= simFormat($data, $counter++);
    }
    $pdoResultSet->closeCursor();
    while ($counter < 251) {
        $fileContent .= simFormat(array("name" => null, "firstname" => null, "homephone" => null, "cellphone" => null), $counter++);
    }

    return $fileContent;
}

/**
 * format a string to sim format
 *
 * @param $data the array containing the data to format (name, firstname, homephone, cellphone)
 * @param $counter the record counter
 * @return the sim-formatted string
 */
function simFormat($data, $counter)
{
    /* decode htmlspecialchars */
    foreach($data as &$item) {
        $item = htmlspecialchars_decode($item, ENT_QUOTES);
    }
    $data = array_map("utf8_decode", $data);
    extract($data);

    $fullName = trim("$firstname $name");
    $fullName = gsm7bitFormat($fullName);
    $fullName = gsmUCS2Format($fullName);

    /* $cellphone and $homephone cannot both be null because of the where conditions of the sql statement */
    if ($cellphone != null) {
        $phone = $cellphone;
    } else {
        $phone = $homephone;
    }
    $phone = str_replace(" ", null, $phone);
    /* truncating phone number to 20 characters because of sim card specifications */
    $phone = mb_substr($phone, 0, 20, "ISO-8859-1");

    $outputString = "$counter\t$fullName\t$phone\n";

    return $outputString;
}

/**
 * replace special characters by gsm 7-bit default alphabet
 *
 * @param $inputString the string to format
 * @return the gsm 7-bit formatted string
 */
function gsm7bitFormat($inputString)
{
   /*
    * official gsm 7-bit default alphabet from <http://www.developershome.com/sms/gsmAlphabet.asp>
    *                      more information on <http://en.wikipedia.org/wiki/GSM_03.38>
    */
    $gsm7bitTranslationTable = array( 64 => chr(  0),           // @
                                     163 => chr(  1),           // £
                                      36 => chr(  2),           // $
                                     165 => chr(  3),           // ¥
                                     232 => chr(  4),           // è
                                     233 => chr(  5),           // é
                                     249 => chr(  6),           // ù
                                     236 => chr(  7),           // ì
                                     242 => chr(  8),           // ò
                                     199 => chr(  9),           // Ç
                                     216 => chr( 11),           // Ø
                                     248 => chr( 12),           // ø
                                     197 => chr( 14),           // Å
                                     229 => chr( 15),           // å
                                      // => chr( 16),           // Δ (used as default char)
                                      95 => chr( 17),           // _
                                      // => chr( 18),           // Φ
                                      // => chr( 19),           // Γ
                                      // => chr( 20),           // Λ
                                      // => chr( 21),           // Ω
                                      // => chr( 22),           // Π
                                      // => chr( 23),           // Ψ
                                      // => chr( 24),           // Σ
                                      // => chr( 25),           // Θ
                                      // => chr( 26),           // Ξ
                                      // => chr( 27),           // (escape)
                                      12 => chr(27) . chr( 10), // (form feed)
                                      94 => chr(27) . chr( 20), // ^
                                     123 => chr(27) . chr( 40), // {
                                     125 => chr(27) . chr( 41), // }
                                      92 => chr(27) . chr( 47), // \
                                      91 => chr(27) . chr( 60), // [
                                     126 => chr(27) . chr( 61), // ~
                                      93 => chr(27) . chr( 62), // ]
                                     124 => chr(27) . chr( 64), // |
                                      // => chr(27) . chr(101), // € (ISO 8859-15)
                                     198 => chr( 28),           // Æ
                                     230 => chr( 29),           // æ
                                     223 => chr( 30),           // ß
                                     201 => chr( 31),           // É
                                     164 => chr( 36),           // ¤
                                     161 => chr( 64),           // ¡
                                     196 => chr( 91),           // Ä
                                     214 => chr( 92),           // Ö
                                     209 => chr( 93),           // Ñ
                                     220 => chr( 94),           // Ü
                                     167 => chr( 95),           // §
                                     191 => chr( 96),           // ¿
                                     228 => chr(123),           // ä
                                     246 => chr(124),           // ö
                                     241 => chr(125),           // ñ
                                     252 => chr(126),           // ü
                                     224 => chr(127));          // à

    $fromChars = array_keys($gsm7bitTranslationTable);
    $fromChars = array_map("chr", $fromChars);

    $toChars = array_values($gsm7bitTranslationTable);

    $outputString = str_replace($fromChars, $toChars, $inputString);

    /* truncating contact name to 14 characters because of sim card specifications */
    $outputString = mb_substr($outputString, 0, 14, "ISO-8859-1");

    return $outputString;
}

/**
 * add a special header to enable UCS-2 charset
 *
 * @param $inputString the string to format
 * @return the UCS-2 formatted string
 */
function gsmUCS2Format($inputString)
{
    $outputString = $inputString;

    $data = str_split($inputString);
    sort($data);
    $max = ord(array_pop($data));

    if ($max > 127) {
        /* truncating contact name to 11 characters because of the special 3 bytes UCS-2 header */
        $outputString = mb_substr($inputString, 0, 11, "ISO-8859-1");

        $stringLength = mb_strlen($outputString, "ISO-8859-1");

        /* the special 3 bytes UCS-2 header seems to look like "chr(129) . chr($stringLength) . chr (01)" */
        $outputString = chr(129) . chr($stringLength) .chr(01) . $outputString;
    }

    return $outputString;
}
?>
