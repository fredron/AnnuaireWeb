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
 * parse the import file to store data in a temporary array
 *
 * @param $filepath the filepath of the imported file
 * @param $fieldsList the array of valid fields (keys of the array are used for validation)
 * @param $charset the charset of the imported file
 * @return array of the parsed data or throw an exception
 */
function parseFile($filepath, $fieldsList, $charset)
{
    if (!$handle = @fopen($filepath, 'r')) {
        throw new Exception(sprintf(_("file %s is not readable"), $filepath));
    }

    if (($header = fgetcsv($handle)) !== false) {
        $headerCount = count($header);
        foreach ($header as $item) {
            if (!array_key_exists($item, $fieldsList)) {
                throw new Exception(_("header is not valid as defined in the configuration file"));
            }
        }
    }

    $parsedData = array();
    while (($data = fgetcsv($handle)) !== false) {
        $dataCount = count($data);
        if ($dataCount == $headerCount) {
            $recordData = array();
            foreach ($header as $item) {
                $convertedValue = htmlspecialchars(array_shift($data), ENT_QUOTES, $charset, false);
                array_push($recordData, array("name" => $item, "value" => $convertedValue));
            }
        } else {
            throw new Exception(sprintf(_("number of fields (%s) of a line differs from the number of field (%s) of the header"), $dataCount, $headerCount));
        }
        array_push($parsedData, $recordData);
    }

    return $parsedData;
}
?>
