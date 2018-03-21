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

/* ---------- initialization ---------- */

/* requiring configuration files */

try {
    require("config.inc.php");
} catch (Exception $exception) {
    $exceptionMessage = $exception->getMessage();
    printf(_("configuration file contains an error : %s"), $exceptionMessage);
    exit(1);
}

/* requiring functions include files */
require("util.inc.php");
require("sql.inc.php");

/* status */
$status = true;

/* getting templates content */
$mainTemplate =           file_get_contents($mainTemplateFilename);
$recordsToolbarTemplate = file_get_contents($recordsToolbarTemplateFilename);
$recordTemplate =         file_get_contents($recordTemplateFilename);
$recordFieldsetTemplate = file_get_contents($recordFieldsetTemplateFilename);
$recordFieldTemplate =    file_get_contents($recordFieldTemplateFilename);
$linkTemplate =           file_get_contents($linkTemplateFilename);
$optionItemTemplate =     file_get_contents($optionItemTemplateFilename);
$submitTemplate =         file_get_contents($submitTemplateFilename);
$requiredTemplate =       file_get_contents($requiredTemplateFilename);
$selectedTemplate =       file_get_contents($selectedTemplateFilename);

/**
 * un-quote input parameters quoted by magic_quotes_gpc if set to on
 * (useless with php6 as magic_quotes_gpc does not exist anymore)
 */
if (get_magic_quotes_gpc()) {
    $_GET = array_map("stripslashes", $_GET);
    $_POST = array_map("stripslashes", $_POST);
}

/* getting parameters */
$searchField = null;
$searchOperator = null;
$searchValue = null;
$action = null;
$id = null;
$exportFormat = null;

if ($_SERVER['REQUEST_METHOD'] == "GET") {
    $rawParametersList = $_GET;
} else {
    $rawParametersList = $_POST;
}

foreach ($rawParametersList as $key => $item) {
    $rawParametersList[$key] = htmlspecialchars($item, ENT_QUOTES, $charset, false);
}

$parametersList = array_intersect_key($rawParametersList, $userFieldsList);

if (array_key_exists("searchField", $rawParametersList)) {
    $searchField = $rawParametersList["searchField"];
}
if (array_key_exists("searchOperator", $rawParametersList)) {
    $searchOperator = $rawParametersList["searchOperator"];
}
if (array_key_exists("searchValue", $rawParametersList)) {
    $searchValue = $rawParametersList["searchValue"];
}
if (array_key_exists("action", $rawParametersList)) {
    $action = $rawParametersList["action"];
}
if (array_key_exists("id", $rawParametersList)) {
    $id = $rawParametersList["id"];
}
if (array_key_exists("exportFormat", $rawParametersList)) {
    $exportFormat = $rawParametersList["exportFormat"];
}

/* checking parameters */
if (!array_key_exists($searchField, $searchFieldsList)) {
    $searchField = $defaultSearchField;
}
if (!array_key_exists($searchOperator, $searchOperatorsList)) {
    $searchOperator = $defaultSearchOperator;
}
if ($searchValue == null) {
    $searchValue = $defaultSearchValue;
}
if ((!array_key_exists($action, $actionsList)) || (!$actionsList[$action]["enabled"])) {
    $action = $defaultAction;
}
if (!is_numeric($id) || $id <= 0) {
    $id = $defaultId;
}
if (!array_key_exists($exportFormat, $externalFormatsList)) {
    $exportFormat = $defaultExportFormat;
}

/* ---------- script ---------- */

/* setting database connection once and for all */
try {
    $dbResource = sqlConnect($dbServer, $dbLogin, $dbPassword, $dbCharset, $dbName);
} catch (Exception $exception) {
    $exceptionMessage = $exception->getMessage();
    $status = false;
}

/* ---------- database related operations ---------- */

if ($status) {
    switch ($action) {
     case "login":
        if ($reorderIdOnLogin) {
            try {
                reorderId($dbResource, $dbTable, $fieldsList, $sortRecordsListFieldsList);
            } catch (Exception $exception) {
                $exceptionMessage = $exception->getMessage();
                $status = false;
            }
        }
        break;
     case "search":
        /* nothing to do */
        break;
     case "view":
        /* nothing to do */
         break;
     case "new":
        /* nothing to do */
         break;
     case "add":
        /* inserting record */

        /* setting default values */
        foreach ($userFieldsList as $item) {
            $parametersList[$item["name"]] = setValue($parametersList[$item["name"]], $item["defaultValue"]);
        }

        $insertFieldsList = array();
        foreach ($userFieldsList as $item) {
            array_push($insertFieldsList, array("name" => $item["name"], "value" => $parametersList[$item["name"]]));
        }

        $statement = sqlInsert($dbResource, $dbTable, $insertFieldsList);
        try {
            $dbResource->exec($statement);
            $id = $dbResource->lastInsertId();
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
            $status = false;
        }
        break;
     case "duplicate":
        /* nothing to do */
         break;
     case "edit":
        /* updating record */

        /* setting default values */
        foreach ($userFieldsList as $item) {
            $parametersList[$item["name"]] = setValue($parametersList[$item["name"]], $item["defaultValue"]);
        }

        $updateFieldsList = array();
        foreach ($userFieldsList as $item) {
            array_push($updateFieldsList, array("name" => $item["name"], "value" => $parametersList[$item["name"]]));
        }

        $whereConditionsList = array();
        array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $techFieldsList["id"]["name"], "comparisonOperator" => "=", "fieldValue" => $id, "closingParenthesis" => null));

        $statement = sqlUpdate($dbResource, $dbTable, $updateFieldsList, $whereConditionsList);
        try {
            $dbResource->exec($statement);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
            $status = false;
        }
        break;
     case "export":
        /* requiring additional functions include files */
        require("export.inc.php");

        try {
            $exportFilename = export($dbResource, $exportFormat, $searchField, $searchOperator, $searchValue);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
            $status = false;
        }
        break;
     case "import":
        /* requiring additional functions include files */
        require("import.inc.php");

        try {
            $importFilename = import($dbResource);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
            $status = false;
        }
        break;
    }
}

/* setting record fields list */
$recordFieldsList = null;
if (($status) && ($id != 0)) {
    try {
        $recordFieldsList = getRecordFieldsList($dbResource, $dbTable, $userFieldsList, $techFieldsList, $id);
    } catch (Exception $exception) {
        $exceptionMessage = $exception->getMessage();
        $status = false;
    }
}

if ($status) {
    switch ($action) {
     case "delete":
        /* deleting record */

        $whereConditionsList = array();
        array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $techFieldsList["id"]["name"], "comparisonOperator" => "=", "fieldValue" => $id, "closingParenthesis" => null));

        $statement = sqlDelete($dbResource, $dbTable, $whereConditionsList);
        try {
            $dbResource->exec($statement);
        } catch (Exception $exception) {
            $exceptionMessage = $exception->getMessage();
            $status = false;
        }
        break;
    }
}

/* setting records list */
$records = null;
$recordsCounter = 0;

if ($actionsList["add"]["enabled"]) {
    $recordName = italize($actionsList["new"]["linkLabel"]);
    $recordUrl = generateUrl($searchField, $searchOperator, $searchValue, "new", null, null);

    if ($recordListTooltipDisplay) {
        $recordTooltip = $actionsList["new"]["linkTooltipLabel"];
    } else {
        $recordTooltip = null;
    }

    $records .= generateItem($linkTemplate, $recordName, $recordUrl, $recordTooltip);
}

if (($status) && ($searchValue != null)) {
    $selectFieldsList = $selectRecordsListFieldsList;
    /* add the 'id' field in the select statement because the primary key is the unique identifier of a record */
    array_unshift($selectFieldsList, $techFieldsList["id"]);
    /* add the 'email' field (if it exists) in the select statement to allow emailing to a group of emails */
    if (array_key_exists("email", $userFieldsList)) {
        array_push($selectFieldsList, $userFieldsList["email"]);
    }

    $whereConditionsList = array();
    $searchMask = $searchOperatorsList[$searchOperator]["prefix"] . $searchValue . $searchOperatorsList[$searchOperator]["suffix"];
    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $searchField, "comparisonOperator" => $searchOperatorsList[$searchOperator]["operator"], "fieldValue" => $searchMask, "closingParenthesis" => null));

    $statement = sqlSelect($dbResource, $dbTable, $selectFieldsList, $whereConditionsList, $sortRecordsListFieldsList, null);
    try {
        $recordsEmailsList = array();

        $pdoResultSet = $dbResource->query($statement);
        $recordList = $pdoResultSet->fetchAll(PDO::FETCH_ASSOC);
        $pdoResultSet->closeCursor();
        $recordsCounter = count($recordList);

        foreach ($recordList as $recordItem) {
            /* get the 'email' field (if it exists) from the result set to allow emailing to a group of emails */
            if (array_key_exists("email", $userFieldsList)) {
                $recordEmail = array_pop($recordItem);
            }
            $recordId = array_shift($recordItem);
            $recordName = htmlspecialchars_decode(implode(" ", $recordItem), ENT_QUOTES);
            $recordUrl = generateUrl($searchField, $searchOperator, $searchValue, "view", $recordId, null);

            if ($recordListTooltipDisplay) {
                $recordTooltip = sprintf($actionsList["view"]["linkTooltipLabel"], $recordName);
            } else {
                $recordTooltip = null;
            }

            if (($id == 0) && ($action != "new")) {
                /* set $id to the first retrieved record so that it will be displayed */
                $id = $recordId;
            }

            if ($recordEmail != null) {
                array_push($recordsEmailsList, "$recordName <$recordEmail>");
            }

            if ($id == $recordId) {
                $recordName = emphasize($recordName);
            }

            $records .= generateItem($linkTemplate, $recordName, $recordUrl, $recordTooltip);
        }

        $groupMailtoShortcutTooltip = null;
        if ($groupMailtoShortcutDisplay) {
            $recordsEmailsList = array_unique($recordsEmailsList);
            if (count($recordsEmailsList) > 1) {
                $recordsEmails = implode(", ", $recordsEmailsList);
                if ($groupMailtoShortcutTooltipDisplay) {
                    $groupMailtoShortcutTooltip = htmlspecialchars(sprintf($mailtoButtonLabel, $recordsEmails), ENT_QUOTES, $charset, false);
                }
                $linkUrl = "mailto:" . rawurlencode($recordsEmails);
                $records .= generateItem($recordsToolbarTemplate, generateImageTag($mailtoImageFilename, $groupMailtoShortcutTooltip, $linkUrl));
            }
        }
    } catch (Exception $exception) {
        $exceptionMessage = $exception->getMessage();
        $status = false;
    }
}

/* setting record fields list (again) */
if (($status) && ($id != 0) && ($action != "delete")) {
    try {
        $recordFieldsList = getRecordFieldsList($dbResource, $dbTable, $userFieldsList, $techFieldsList, $id);
    } catch (Exception $exception) {
        $exceptionMessage = $exception->getMessage();
        $status = false;
    }
}

/* setting add, edit and delete tag */
$addSubmitTag = null;
if ($actionsList["add"]["enabled"]) {
    $addSubmitTag = generateItem($submitTemplate, $addImageFilename, $actionsList["add"]["linkTooltipLabel"]);
}

$editSubmitTag = null;
if ($actionsList["edit"]["enabled"]) {
    $editSubmitTag = generateItem($submitTemplate, $editImageFilename, $actionsList["edit"]["linkTooltipLabel"]);
}

$duplicateImageTag = null;
if (($actionsList["duplicate"]["enabled"]) && ($actionsList["add"]["enabled"])) {
    $duplicateImageTag = generateImageTag($duplicateImageFilename, $actionsList["duplicate"]["linkTooltipLabel"], generateUrl($searchField, $searchOperator, $searchValue, "duplicate", $id, null));
}

$deleteImageTag = null;
$cancelDeleteSubmitTag = null;
if ($actionsList["delete"]["enabled"]) {
    $deleteImageTag = generateImageTag($deleteImageFilename, $actionsList["delete"]["linkTooltipLabel"], generateUrl($searchField, $searchOperator, $searchValue, "delete", $id, null));
    $cancelDeleteSubmitTag = generateItem($submitTemplate, $editImageFilename, $cancelDeleteButtonLabel);
}

/* creating toolbar */
if (($id == 0) || ($action == "duplicate")){
    $nextEditAction = "add";
    $toolbar = $addSubmitTag;
} elseif ($action == "delete") {
    $nextEditAction = "add";
    $toolbar = $cancelDeleteSubmitTag;
} else {
    $nextEditAction = "edit";
    $toolbar = $editSubmitTag . $duplicateImageTag . $deleteImageTag;
}

/* setting record fields list values (it needs $nextEditAction and $toolbar to be set */
/* if - there is a record to display                                                  */
/*    - or if - nextEditAction is "add" action                                        */
/*            - and "add" action is enabled (just to generate the empty form)         */
$record = null;
if (($id != 0) || (($nextEditAction == "add") && ($actionsList["add"]["enabled"]))) {
    $recordFieldsets = null;
    foreach ($displayUserFieldsList as $legend => $fields) {
        $recordFields = null;
        foreach ($fields as $field) {
            $name = $field["name"];
            $htmlType = $field["htmlType"];
            $pattern = null; // $pattern will be set later if necessary
            $label = $field["label"];
            $value = $recordFieldsList[$field["name"]];
            $placeholder = $field["placeholder"];
            $required = null; // $required will be set later if necessary
            $link = null; // $link will be set later if necessary

            /* keeping firstname for later use, if userMailtoShortcutDisplay is enabled */
            if (($userMailtoShortcutDisplay) && ($name == $userFieldsList["firstname"]["name"]) && ($value != null)) {
                $userMailtoShortcutDisplayFirstname = $value;
            }

            /* keeping name for later use, if userMailtoShortcutDisplay is enabled */
            if (($userMailtoShortcutDisplay) && ($name == $userFieldsList["name"]["name"]) && ($value != null)) {
                $userMailtoShortcutDisplayName = $value;
            }

            /* set pattern attribute if set in config file */
            if ($field["pattern"] != null) {
                $pattern = "pattern=\"" . $field["pattern"] . "\"";
            }

            /* set required attribute if set in config file */
            if ($field["required"] == true) {
                $required = $requiredTemplate;
            }

            /* if homephone or cellphone is not null, there must be a link */
            if (($userCallShortcutDisplay) && (($name == $userFieldsList["homephone"]["name"]) || ($name == $userFieldsList["cellphone"]["name"])) && ($value != null)) {
                $userCallShortcutTooltip = null;
                if ($userCallShortcutTooltipDisplay) {
                    $userCallShortcutTooltip = sprintf($callButtonLabel, $value);
                }
                $link = generateImageTag($callImageFilename, $userCallShortcutTooltip, sprintf($clickToCallURL, rawurlencode($value)));
            }

            /* if email is not null, there must be a link */
            if (($userMailtoShortcutDisplay) && ($name == $userFieldsList["email"]["name"]) && ($value != null)) {
                $userMailtoShortcutDisplayFullName = htmlspecialchars_decode(trim("$userMailtoShortcutDisplayFirstname $userMailtoShortcutDisplayName"), ENT_QUOTES);
                $userMailtoShortcutDisplayFullName = trim("$userMailtoShortcutDisplayFullName <$value>");
                $userMailtoShortcutTooltip = null;
                if ($userMailtoShortcutTooltipDisplay) {
                    $userMailtoShortcutTooltip = sprintf($mailtoButtonLabel, $userMailtoShortcutDisplayFullName);
                }
                $link = generateImageTag($mailtoImageFilename, htmlspecialchars($userMailtoShortcutTooltip), "mailto:" . rawurlencode($userMailtoShortcutDisplayFullName));
            }

            /* if website is not null, there must be a link */
            if (($userBrowseShortcutDisplay) && ($name == $userFieldsList["website"]["name"]) && ($value != null)) {
                $userBrowseShortcutTooltip = null;
                if ($userBrowseShortcutTooltipDisplay) {
                    $userBrowseShortcutTooltip = sprintf($browseButtonLabel, $value);
                }
                $link = generateImageTag($browseImageFilename, $userBrowseShortcutTooltip, $value);
            }

            $recordFields .= generateItem($recordFieldTemplate, $name, $htmlType, $pattern, $label, $value, $placeholder, $required, $link);
        }
        $recordFieldsets .= generateItem($recordFieldsetTemplate, $legend, $recordFields);
    }
    $record = generateItem($recordTemplate, $scriptName, $recordFieldsets, $searchField, $searchOperator, $searchValue, $nextEditAction, $id, $toolbar);
}

/* ---------- setting display elements ---------- */

/* setting charset */
$dynamicContent["charset"] = $charset;

/* setting content language */
$dynamicContent["contentLanguage"] = str_replace("_", "-", $language);

/* setting website title */
$dynamicContent["title"] = $title;

/* setting css filename */
$dynamicContent["cssFilename"] = "$cssPath/$cssFilename";

/* setting search form action */
$dynamicContent["formAction"] = $scriptName;

/* setting search label */
$dynamicContent["searchLabel"] = $actionsList["search"]["linkLabel"];

/* setting search field items */
$searchFieldItems = null;
foreach ($searchFieldsList as $item) {
    $searchFieldItems .= generateFormOptionItem($optionItemTemplate, $selectedTemplate, $item["name"], $item["label"], $searchField);
}
$dynamicContent["searchFieldItems"] = $searchFieldItems;

/* setting search operator items */
$searchOperatorItems = null;
foreach ($searchOperatorsList as $key => $item) {
    $searchOperatorItems .= generateFormOptionItem($optionItemTemplate, $selectedTemplate, $key, $item["label"], $searchOperator);
}
$dynamicContent["searchOperatorItems"] = $searchOperatorItems;

/* setting search value */
$dynamicContent["searchValue"] = $searchValue;

/* setting next search action */
$dynamicContent["nextSearchAction"] = $defaultSearchAction;

/* setting search button label */
$dynamicContent["searchButtonLabel"] = $searchButtonLabel;

/* setting export label and export shortcut */
$data = null;
if ($actionsList["export"]["enabled"]) {
    $exportFormatsList = null;
    foreach ($externalFormatsList as $key => $item) {
        if ($externalFormatsList[$key]["exportEnabled"]) {
            $exportFormatsList .= generateItem($linkTemplate, $externalFormatsList[$key]["shortcutLabel"], generateUrl($searchField, $searchOperator, $searchValue, "export", null, $key));
        }
    }
    $data = paragraphize(sprintf($actionsList["export"]["linkLabel"], $exportFormatsList));
}
$dynamicContent["export"] = $data;

/* setting import label and import shortcut */
$data = null;
if ($actionsList["import"]["enabled"]) {
    $importFormatsList = null;
    foreach ($externalFormatsList as $key => $item) {
        if ($externalFormatsList[$key]["importEnabled"]) {
            $importFormatsList .= generateItem($linkTemplate, $externalFormatsList[$key]["shortcutLabel"], generateUrl($searchField, $searchOperator, "%", "import", null, $key));
        }
    }
    $data = paragraphize(sprintf($actionsList["import"]["linkLabel"], $importFormatsList));
}
$dynamicContent["import"] = $data;

/* setting alphabet shortcut */
$alphabetShortcut = null;
if ($alphabetShortcutDisplay) {
    $data = null;

    /* setting joker shortcut */
    $jokerLabel = $alphabetShortcutJokerLabel;
    if (($searchField == $alphabetShortcutSearchField["name"]) && ($searchValue == $alphabetShortcutJokerValue)) {
        $jokerLabel = emphasize($jokerLabel);
    }

    $jokerUrl = generateUrl($alphabetShortcutSearchField["name"], $alphabetShortcutSearchOperator["name"], $alphabetShortcutJokerValue, "search", null, null);

    if ($alphabetShortcutTooltipDisplay) {
        $jokerTooltip = sprintf($actionsList["search"]["linkTooltipLabel"], $alphabetShortcutSearchField["label"], $alphabetShortcutSearchOperator["label"], $alphabetShortcutJokerValue);
    } else {
        $jokerTooltip = null;
    }

    $jokerShortcut = generateItem($linkTemplate, $jokerLabel, $jokerUrl, $jokerTooltip);

    $data .= $jokerShortcut;

    /* setting other shortcut */
    for ($ascii = 97; $ascii <= 122; $ascii++)
    {
        $letter = chr($ascii);
        $letterLabel = $letter;
        if (($searchField == $alphabetShortcutSearchField["name"]) && ($searchValue == $letter)) {
            $letterLabel = emphasize($letterLabel);
        }

        $letterUrl = generateUrl($alphabetShortcutSearchField["name"], $alphabetShortcutSearchOperator["name"], $letter, "search", null, null);

        if ($alphabetShortcutTooltipDisplay) {
            $letterTooltip = sprintf($actionsList["search"]["linkTooltipLabel"], $alphabetShortcutSearchField["label"], $alphabetShortcutSearchOperator["label"], $letter);
        } else {
            $letterTooltip = null;
        }

        $data .= generateItem($linkTemplate, $letterLabel, $letterUrl, $letterTooltip);
    }

    /* adding another joker shortcut */
    $data .= $jokerShortcut;

    $alphabetShortcut = paragraphize($data);
}
$dynamicContent["alphabetShortcut"] = $alphabetShortcut;

/* setting tags shortcut */
$tagsShortcut = null;
if (($status) && ($tagShortcutDisplay)) {
    $selectFieldsList = array();
    array_push($selectFieldsList, $tagsShortcutSearchField);

    $whereConditionsList = array();
    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $tagsShortcutSearchField["name"], "comparisonOperator" => "!=", "fieldValue" => null, "closingParenthesis" => null));

    $sortFieldsList = array();
    array_push($sortFieldsList, $tagsShortcutSearchField);

    $statement = sqlSelect($dbResource, $dbTable, $selectFieldsList, $whereConditionsList, $sortFieldsList, "distinct");
    try {
        $pdoResultSet = $dbResource->query($statement);
        $allTagsList = array();

        while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
            $someTags = array_shift($data);
            $someTagsList = mb_split($tagSeparator, $someTags);
            $allTagsList = array_merge($allTagsList, $someTagsList);
        }
        $pdoResultSet->closeCursor();
        $allTagsList = array_map("trim", $allTagsList);
        $allTagsList = array_unique($allTagsList);
        asort($allTagsList);

        $data = null;
        foreach ($allTagsList as $tag) {
            $tagLabel = $tag;
            if (($searchField == $tagsShortcutSearchField["name"]) && ($searchValue == $tag)) {
                $tagLabel = emphasize($tagLabel);
            }

            $tagUrl = generateUrl($tagsShortcutSearchField["name"], $tagsShortcutSearchOperator["name"], $tag, "search", null, null);

            if ($tagShortcutTooltipDisplay) {
                $tagTooltip = sprintf($actionsList["search"]["linkTooltipLabel"], $tagsShortcutSearchField["label"], $tagsShortcutSearchOperator["label"], $tag);
            } else {
                $tagTooltip = null;
            }

            $data .= generateItem($linkTemplate, $tagLabel, $tagUrl, $tagTooltip);
        }

        $tagsShortcut = paragraphize($data);
    } catch (Exception $exception) {
        $exceptionMessage = $exception->getMessage();
        $status = false;
    }
}
$dynamicContent["tagsShortcut"] = $tagsShortcut;

/* setting icon and info message */
if ($status) {
    $imageTag = generateImageTag($infoImageFilename);
    switch ($action) {
     case "search":
        $part1 = emphasize($recordsCounter);
        $part2 = emphasize($searchFieldsList[$searchField]["label"]);
        $part3 = $searchOperatorsList[$searchOperator]["label"];
        $part4 = emphasize($searchValue);
        break;
     case "export":
        $part1 = emphasize($searchFieldsList[$searchField]["label"]);
        $part2 = $searchOperatorsList[$searchOperator]["label"];
        $part3 = emphasize($searchValue);
        $part4 = emphasize($exportFilename);
        break;
     case "import":
        $part1 = emphasize($importFilename);
        $part2 = null;
        $part3 = null;
        $part4 = null;
        break;
     default:
        $part1 = null;
        $part2 = null;
        $part3 = null;
        $part4 = null;
        break;
    }
    $infoMessage = $imageTag . sprintf($actionsList[$action]["infoMessageLabel"], $part1, $part2, $part3, $part4);
} else {
    $imageTag = generateImageTag($alertImageFilename);
    $part1 = emphasize($exceptionMessage);
    $part2 = null;
    $part3 = null;
    $part4 = null;
    $infoMessage = $imageTag . sprintf($errorLabel, $part1, $part2, $part3, $part4);
}

$dynamicContent["infoMessage"] = $infoMessage;

/* setting records list */
$dynamicContent["recordList"] = $records;

/* setting record */
$dynamicContent["record"] = $record;

/* setting footer */
$dynamicContent["footer"] = $footer;

/* ---------- display ---------- */

/* updating dynamic fields */
$htmlOutput = $mainTemplate;
foreach ($dynamicContent as $key => $item) {
    $htmlOutput = str_replace("%$key%", $item, $htmlOutput);
}

/* sending content type header */
header("Content-Type: $mime;charset=$charset");

/* displaying dynamic html */
print($htmlOutput);
?>
