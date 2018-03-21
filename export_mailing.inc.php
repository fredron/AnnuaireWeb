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
    $selectFieldsList = $fieldsList;
    unset($selectFieldsList["id"]);
    unset($selectFieldsList["lastmodified"]);
    unset($selectFieldsList["tags"]);

    array_push($whereConditionsList, array("logicalOperator" => "AND", "openingParenthesis" => null, "fieldName" => $fieldsList["email"]["name"], "comparisonOperator" => "!=", "fieldValue" => null, "closingParenthesis" => null));

    $statement = sqlSelect($dbResource, $tablename, $selectFieldsList, $whereConditionsList, $sortFieldsList, null);

    return $statement;
}

/**
 * generate the export file
 *
 * @param $dbResource the database resource
 * @param $pdoResultSet the pdo statement result set to fetch
 * @return the number of sent mail or throw an exception
 */
function generateExport($dbResource, $pdoResultSet)
{
    global $appName;
    global $charset;
    global $mailingSenderName;
    global $mailingSenderEmail;
    global $mailingSubject;
    global $mailingHeader;
    global $mailingFooter;
    global $mailingLogFile;
    global $mailingLogHeader;
    global $mailingLogEvent;
    global $mailingLogEventSuccessful;
    global $mailingLogEventFailed;
    global $mailingLogReport;
    global $mailingLogFooter;
    global $fieldsList;

    /* checking mailingSenderEmail parameter */
    if ($mailingSenderEmail == null) {
        throw new Exception(_("mailingSenderEmail parameter is null and must be defined in user configuration file"));
    }

    /* opening mailing log file and writing header */
    if (!$handle = @fopen($mailingLogFile, 'w+')) {
        throw new Exception(sprintf(_("file %s is not writable"), $mailingLogFile));
    }
    if (fwrite($handle, "$mailingLogHeader\n") === false) {
        throw new Exception(sprintf(_("writing into the file %s failed"), $mailingLogFile));
    }

    /* setting common parts */
    if ($mailingSenderName != null) {
        $emailSenderName = mb_encode_mimeheader($mailingSenderName, $charset, "B");
        $emailSender = "$emailSenderName <$mailingSenderEmail>";
    } else {
        $emailSender = $mailingSenderEmail;
    }

    $emailSubject = $mailingSubject;
    $emailHeader = $mailingHeader;
    $emailFooter = "$mailingFooter\n\n$mailingSenderName";

    /* begin mailing */
    $okCounter = 0;
    $koCounter = 0;

    $data = array();
    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        /* decode htmlspecialchars */
        foreach($data as $key => $item) {
            $data[$key] = htmlspecialchars_decode($item, ENT_QUOTES);
        }

        $recipientName = $data["name"];
        $recipientFirstname = $data["firstname"];
        $recipientEmail = $data["email"];

        $recipientFullName = trim("$recipientFirstname $recipientName");

        if ($recipientFullName != null) {
            $rawEmailRecipient = "$recipientFullName <$recipientEmail>";
            $recipientFullName = mb_encode_mimeheader($recipientFullName, $charset, "B");
            $emailRecipient = "$recipientFullName <$recipientEmail>";
        } else {
            $rawEmailRecipient = $recipientEmail;
            $emailRecipient = $recipientEmail;
        }

        $emailData = null;
        foreach ($data as $key => $item) {
            $emailData .= $fieldsList[$key]["label"] . " : $item\n";
        }

        $emailBody = wordwrap("$emailHeader\n\n$emailData\n$emailFooter", 70);
        $emailDate = date("r");
        $emailHeaders = <<<END
X-Mailer: $appName
From: $emailSender
Date: $emailDate
END;

        /* sending mail to $emailRecipient */
        $emailSendingReport = sprintf($mailingLogEvent, $rawEmailRecipient);

        if (mb_send_mail($emailRecipient, $emailSubject, $emailBody, $emailHeaders)) {
            $emailSendingReport .= $mailingLogEventSuccessful . "\n";
            $okCounter++;
        } else {
            $emailSendingReport .= $mailingLogEventFailed . "\n";
            $koCounter++;
        }

        if (fwrite($handle, $emailSendingReport) === false) {
            throw new Exception(sprintf(_("writing into the file %s failed"), $mailingLogFile));
        }
    }
    $pdoResultSet->closeCursor();

    /* $okCounter mails sent without error, $koCounter mails not sent */

    /* writing report to log file */
    if (fwrite($handle, sprintf($mailingLogReport, $okCounter, $koCounter) . "\n") === false) {
        throw new Exception(sprintf(_("writing into the file %s failed"), $mailingLogFile));
    }

    /* writing footer and closing mailing log file */
    if (fwrite($handle, "$mailingLogFooter\n") === false) {
        throw new Exception(sprintf(_("writing into the file %s failed"), $mailingLogFile));
    }
    fclose($handle);

    /* returning number of sent mails or throw an exception if there was one error (or many) */
    if ($koCounter == 0) {
        return "$okCounter mails";
    } else {
        throw new Exception(_("one or more mails have not been sent"));
    }
}
?>
