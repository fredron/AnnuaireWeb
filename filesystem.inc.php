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

/* ---------- filesystem related functions ---------- */

/**
 * get the extension from a filename
 *
 * @param $filename the filename
 * @return the extension of the filename
 */
function getFileExtension($filename)
{
    $itemsList = explode(".", $filename);
    $extension = end($itemsList);
    return $extension;
}

/**
 * return file or directory list in an array
 *
 * @param $path path of directory to list
 * @param $type list files [f] or directories [d]
 * @param $recursive set the search recursive [r] or not [n]
 * @return array containing the file list or throw an exception
 */
function getFileList($path, $type = "f", $recursive = "n")
{
    if (!is_dir($path)) {
        throw new Exception(sprintf(_("directory %s does not exist"), $path));
    }

    if (mb_substr($path, -1) != "/") {
        $path .= "/";
    }

    $filesList = array();
    if (!$dir = @opendir($path)) {
        throw new Exception(sprintf(_("directory %s is not readable"), $path));
    }

    while ($file = readdir($dir)) {
        if ($file != "." && $file != "..") {
            if (($type == "f" && is_file($path . $file)) || ($type == "d" && is_dir($path . $file))) {
                array_push($filesList, $path . $file);
            }
            if ($recursive == "r" && is_dir($path . $file)) {
                $filesList = array_merge($filesList, getFileList($path . $file, $type, $recursive));
            }
        }
    }

    closedir($dir);
    if ($filesList != null) {
        sort($filesList);
    }

    return $filesList;
}
?>
