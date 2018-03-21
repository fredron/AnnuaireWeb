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

/* application parameters */

/*$appName = "phpMyAddressbook";*/
$appName = "Annuaire";

$appVersion = "_BTN_2.4";
$language = "fr_FR";
$mime = "application/xhtml+xml";
$charset = "UTF-8";
$mailLanguage = "uni";
$timezoneIdentifier = "Europe/Paris";
$enableGettext = true;
$tagSeparator = ",";

$title = $appName;
$footer = "$appName v$appVersion";

$defaultAction = "login";
$defaultSearchAction = "search";
$defaultId = "0";

/* default search parameters */
$defaultSearchField = "name";
$defaultSearchOperator = "starts_with";
$defaultSearchValue = null;

/* shortcuts and tooltips display status */
$alphabetShortcutDisplay = true;
$alphabetShortcutTooltipDisplay = true;
$tagShortcutDisplay = true;
$tagShortcutTooltipDisplay = true;
$recordListTooltipDisplay = true;
$groupMailtoShortcutDisplay = true;
$groupMailtoShortcutTooltipDisplay = true;
$userCallShortcutDisplay = true;
$userCallShortcutTooltipDisplay = true;
$userMailtoShortcutDisplay = true;
$userMailtoShortcutTooltipDisplay = true;
$userBrowseShortcutDisplay = true;
$userBrowseShortcutTooltipDisplay = true;

/* database management parameters */
$reorderIdOnLogin = false;

/* export parameters */
$exportPath = "export";
$defaultExportFormat = "sql";
$labelAveryTemplate = "3422";

/* import parameters */
$importPath = "import";
$importArchivePath = "$importPath/archive";
$deleteBeforeImport = false;

/* technical parameters */
$appDir = dirname($_SERVER["SCRIPT_FILENAME"]);
$scriptName = basename($_SERVER["SCRIPT_FILENAME"]);

/* setting language and encoding parameters */
mb_language($mailLanguage);
mb_internal_encoding($charset);

/* setting gettext parameters */
if ($enableGettext) {
    if (!extension_loaded("gettext")) {
        throw new Exception("gettext extension is not available");
    }

    $domain = "messages";
    if (!setlocale(LC_ALL, "$language.$charset")) {
        throw new Exception(sprintf("setlocale call failed with parameter %s", "$language.$charset"));
    }
    if (!bindtextdomain($domain, "$appDir/locale")) {
        throw new Exception(sprintf("bindtextdomain call failed with parameter %s", "$appDir/locale"));
    }
    if (!bind_textdomain_codeset($domain, $charset)) {
        throw new Exception(sprintf("bind_textdomain_codeset call failed with parameter %s and parameter %s", $domain, $charset));
    }
    if (!textdomain($domain)) {
        throw new Exception(sprintf("textdomain call failed with parameter %s", $domain));
    }
}

/* setting timezone parameters */
if (!@date_default_timezone_set($timezoneIdentifier)) {
    throw new Exception(sprintf(_("the timezone identifier %s defined in configuration file is not valid"), $timezoneIdentifier));
}

/* commons labels */
$recordLabel =                _("contact");
$recordsLabel =               _("contacts");
$searchButtonLabel =          _("search");
$callButtonLabel =            _("call number %s");
$mailtoButtonLabel =          _("send an email to %s");
$browseButtonLabel =          _("browse website %s");
$cancelDeleteButtonLabel =    _("cancel deletion");
$alphabetShortcutJokerLabel = _("*");

$bookletExportTitleLabel =    _("addressbook");

/* install labels */
$installReportTitleLabel =        _("Installation report :");
$installDatabaseEngineLabel =     _("database engine is %s");
$installDatabaseServerLabel =     _("server is %s, user is %s");
$installDatabaseSchemaLabel =     _("table %s has been created on database %s");
$installDatabaseTriggersLabel =   _("triggers have been created (sqlite only)");
$installDatabaseErrorLabel =      _("installation failed due to the following error : %s");
$installDatabaseSuccessfulLabel = _("Installation completed, you can now use %s !");
$installDatabaseFailedLabel =     _("Installation failed, sorry !");

/* actions and related messages */
$actionsList = array();
$actionsList["login"] =     array("enabled" =>          true,
                                  "linkLabel" =>        null,
                                  "linkTooltipLabel" => null,
                                  "infoMessageLabel" => _("welcome"));

$actionsList["search"] =    array("enabled" =>          true,
                                  "linkLabel" =>        _("search for contacts whose"),
                                  "linkTooltipLabel" => _("search for contacts whose %s %s %s"),
                                  "infoMessageLabel" => _("there are %s contacts whose %s %s %s"));

$actionsList["view"] =      array("enabled" =>          true,
                                  "linkLabel" =>        null,
                                  "linkTooltipLabel" => _("display contact %s"),
                                  "infoMessageLabel" => _("here is the contact you requested"));

$actionsList["new"] =       array("enabled" =>          true,
                                  "linkLabel" =>        _("new contact"),
                                  "linkTooltipLabel" => _("add a new contact"),
                                  "infoMessageLabel" => _("you can add a new contact"));

$actionsList["add"] =       array("enabled" =>          true,
                                  "linkLabel" =>        null,
                                  "linkTooltipLabel" => _("add"),
                                  "infoMessageLabel" => _("contact has been added"));

$actionsList["duplicate"] = array("enabled" =>          true,
                                  "linkLabel" =>        null,
                                  "linkTooltipLabel" => _("duplicate"),
                                  "infoMessageLabel" => _("you can duplicate this contact"));

$actionsList["edit"] =      array("enabled" =>          true,
                                  "linkLabel" =>        null,
                                  "linkTooltipLabel" => _("edit"),
                                  "infoMessageLabel" => _("contact has been updated"));

$actionsList["delete"] =    array("enabled" =>          true,
                                  "linkLabel" =>        null,
                                  "linkTooltipLabel" => _("delete"),
                                  "infoMessageLabel" => _("contact has been deleted"));

$actionsList["export"] =    array("enabled" =>          true,
                                  "linkLabel" =>        _("export listed contacts in format %s"),
                                  "linkTooltipLabel" => null,
                                  "infoMessageLabel" => _("contacts whose %s %s %s have been exported to %s"));

$actionsList["import"] =    array("enabled" =>          true,
                                  "linkLabel" =>        _("import some contacts from a %s file"),
                                  "linkTooltipLabel" => null,
                                  "infoMessageLabel" => _("contacts have been imported from %s"));

$errorLabel = _("an error occurred : %s");

/* requiring user fields configuration file */
require("user_fields.inc.php");

/* technical fields list */
$techFieldsList = array();
$techFieldsList["id"] =           array("name" => "id",
                                        "databaseType" => "integer",
                                        "htmlType" => null,
                                        "pattern" => null,
                                        "label" => null,
                                        "defaultValue" => null,
                                        "placeholder" => null,
                                        "required" => null);

$techFieldsList["lastmodified"] = array("name" => "lastmodified",
                                        "databaseType" => "timestamp",
                                        "htmlType" => null,
                                        "pattern" => null,
                                        "label" => null,
                                        "defaultValue" => null,
                                        "placeholder" => null,
                                        "required" => null);

/* all fields (technical fields list + user fields list) */
$fieldsList = array();
$fieldsList = array_merge($techFieldsList, $userFieldsList);

/* search fields list */
$searchFieldsList = array();
$searchFieldsList["name"] =      $userFieldsList["name"];
$searchFieldsList["firstname"] = $userFieldsList["firstname"];
$searchFieldsList["zipcode"] =   $userFieldsList["zipcode"];
$searchFieldsList["city"] =      $userFieldsList["city"];
$searchFieldsList["country"] =   $userFieldsList["country"];
$searchFieldsList["tags"] =      $userFieldsList["tags"];

/* search operators */
$searchOperatorsList = array();
$searchOperatorsList["is"] =               array("name" => "is",
                                                 "label" => _("is"),
                                                 "operator" => "=",
                                                 "prefix" => null,
                                                 "suffix" => null);

$searchOperatorsList["contains"] =         array("name" => "contains",
                                                 "label" => _("contains"),
                                                 "operator" => "LIKE",
                                                 "prefix" => "%",
                                                 "suffix" => "%");

$searchOperatorsList["does_not_contain"] = array("name" => "does_not_contain",
                                                 "label" => _("does not contain"),
                                                 "operator" => "NOT LIKE",
                                                 "prefix" => "%",
                                                 "suffix" => "%");

$searchOperatorsList["starts_with"] =      array("name" => "starts_with",
                                                 "label" => _("starts with"),
                                                 "operator" => "LIKE",
                                                 "prefix" => null,
                                                 "suffix" => "%");

/* display fields list */
$selectRecordsListFieldsList = array();
array_push($selectRecordsListFieldsList, $userFieldsList["firstname"]);
array_push($selectRecordsListFieldsList, $userFieldsList["name"]);

/* sort fields list */
$sortRecordsListFieldsList = array();
array_push($sortRecordsListFieldsList, $userFieldsList["name"]);
array_push($sortRecordsListFieldsList, $userFieldsList["firstname"]);

/* alphabet shortcut */
$alphabetShortcutSearchField = $userFieldsList["name"];
$alphabetShortcutSearchOperator = $searchOperatorsList["starts_with"];
$alphabetShortcutJokerValue = "%";

/* tag shortcut */
$tagsShortcutSearchField = $userFieldsList["tags"];
$tagsShortcutSearchOperator = $searchOperatorsList["contains"];

/* external formats list */
$externalFormatsList = array();

$externalFormatsList["booklet"] = array("shortcutLabel" => _("booklet"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.pdf");

$externalFormatsList["label"] =   array("shortcutLabel" => _("label"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.pdf");

$externalFormatsList["sql"] =     array("shortcutLabel" => _("sql"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.sql");

$externalFormatsList["ldif"] =    array("shortcutLabel" => _("ldif"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.ldif");

$externalFormatsList["vcard"] =   array("shortcutLabel" => _("vcard"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.vcf");

$externalFormatsList["csv"] =     array("shortcutLabel" => _("csv"),
                                        "importEnabled" => true,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.csv");

$externalFormatsList["zimbra"] =  array("shortcutLabel" => _("zimbra"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.csv");

$externalFormatsList["email"] =   array("shortcutLabel" => _("email"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.txt");

$externalFormatsList["sim"] =     array("shortcutLabel" => _("sim"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "client",
                                        "exportFilenameTemplate" => "%dbTable%_%date%.sim");

$externalFormatsList["mab"] =     array("shortcutLabel" => _("mab"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "server",
                                        "exportFilenameTemplate" => "%dbTable%.mab");

$externalFormatsList["text"] =    array("shortcutLabel" => _("text"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "raw",
                                        "exportFilenameTemplate" => null);

$externalFormatsList["mailing"] = array("shortcutLabel" => _("mailing"),
                                        "importEnabled" => false,
                                        "exportEnabled" => true,
                                        "exportMode" => "mailing",
                                        "exportFilenameTemplate" => null);

/* mailing parameters */
$mailingSubject = _("updating my addressbook");
$mailingHeader =  _("Hello,\n\nIf the following information below are not accurate, please send me an email so that I can update my addressbook.");
$mailingFooter =  _("Thank you.");

$mailingLogFile = "$exportPath/mailing.log";
$mailingLogHeader =          _("start of mailing");
$mailingLogEvent =           _("trying to send email to %s");
$mailingLogEventSuccessful = _(", sending successful");
$mailingLogEventFailed =     _(", sending failed");
$mailingLogReport =          _("%s mails sent without error, %s mails probably not sent ");
$mailingLogFooter =          _("end of mailing");

/* theme */
$theme = "default";
$themePath = "themes/$theme";

/* css */
$cssPath = "$themePath/css";
$cssFilename = "screen.css";

/* templates */
$templatePath = "$themePath/templates";
$mainTemplateFilename =           "$templatePath/main.html";
$recordsToolbarTemplateFilename = "$templatePath/records_toolbar.html";
$recordTemplateFilename =         "$templatePath/record.html";
$recordFieldsetTemplateFilename = "$templatePath/record_fieldset.html";
$recordFieldTemplateFilename =    "$templatePath/record_field.html";
$linkTemplateFilename =           "$templatePath/link.html";
$optionItemTemplateFilename =     "$templatePath/option_item.html";
$submitTemplateFilename =         "$templatePath/submit.html";
$requiredTemplateFilename =       "$templatePath/required.html";
$selectedTemplateFilename =       "$templatePath/selected.html";
$installTemplateFilename =        "$templatePath/install.html";

/* images */
$imagePath = "$themePath/images";
$addImageFilename =       "$imagePath/accessories-text-editor.png";
$editImageFilename =      "$imagePath/accessories-text-editor.png";
$duplicateImageFilename = "$imagePath/edit-copy.png";
$deleteImageFilename =    "$imagePath/user-trash.png";
$callImageFilename =      "$imagePath/audio-input-microphone.png";
$mailtoImageFilename =    "$imagePath/mail-message-new.png";
$browseImageFilename =    "$imagePath/internet-web-browser.png";
$infoImageFilename =      "$imagePath/dialog-information.png";
$alertImageFilename =     "$imagePath/dialog-warning.png";

/* requiring user configuration file */
require("user_config.inc.php");
?>
