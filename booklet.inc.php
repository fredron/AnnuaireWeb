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

/* requiring fpdf file */
require('fpdf/fpdf.php');

/**
 * Booklet object inherits FPDF object, he has the following internal
 * variables
 *
 * @var $document the array containing the size of the document
 * @var $margins the array containing containing the size of the margins
 * @var $cell the array containing the size of a cell
 * @var $font the array containing the font settings
 * @var $credits the array containing the credits
 * @var $title the title of the document
 * @var $date the creation date of the document
 * @var $bigPagesList the array containing containing the data
 * @var $recordsCount the number of records
 */
class Booklet extends FPDF
{
    var $document;
    var $margins;
    var $cell;
    var $font;
    var $credits;
    var $title;
    var $date;
    var $bigPagesList;
    var $recordsCount;

    /**
     * create a Booklet object
     *
     * @param $orientation the orientation of the document (portrait or landscape)
     * @param $unit the unit used in the document
     * @return nothing
     */
    function __construct($orientation, $unit)
    {
        $this->FPDF($orientation, $unit);
    }

    /**
     * generate the document
     *
     * @return the generated document
     */
    function generateDocument()
    {
        $this->date = date("d\-m\-Y");

        $this->Open();
        $this->SetCompression(true);
        $this->SetTitle($this->title);
        $this->SetCreator($this->credits["creator"]);
        $this->SetAuthor($this->credits["author"]);
        $this->SetMargins($this->margins["left"], $this->margins["top"]);
        $this->SetAutoPageBreak(false);
        $this->SetFont($this->font["family"], $this->font["style"], $this->font["size"]);

        while (sizeof($this->bigPagesList) > 0) {
            $this->generateBigPage(array_shift($this->bigPagesList), $this->margins, $this->cell);
        }

        return $this->Output(null, "S");
    }

    /**
     * set page settings
     *
     * @param $documentWidth the width of the document
     * @param $documentHeight the height of the document
     * @param $sideMargin the size of the left and right margins
     * @param $centralMargin the size of the central margin
     * @param $cellWidth the width of a cell
     * @param $cellHeight the height of a cell
     * @param $interCellSpace the space between two cells
     * @param $fontFamily the font family
     * @param $fontSize the font size
     * @return nothing
     */
    function setPageSettings($documentWidth, $documentHeight, $sideMargin, $centralMargin, $cellWidth, $cellHeight, $interCellSpace, $fontFamily, $fontSize)
    {
        $this->document["width"] = $documentWidth;
        $this->document["height"] = $documentHeight;

        $this->margins["left"] = $sideMargin;
        $this->margins["top"] = $sideMargin;
        $this->margins["right"] = $sideMargin;
        $this->margins["bottom"] = $sideMargin;
        $this->margins["center"] = $centralMargin;

        $this->cell["width"] = $cellWidth;
        $this->cell["height"] = $cellHeight;
        $this->cell["inter"] = $interCellSpace;

        $this->font["family"] = $fontFamily;
        $this->font["style"] = null;
        $this->font["size"] = $fontSize;
    }

    /**
     * set credits of the document
     *
     * @param $creator the creator of the document
     * @param $author the author of the document
     * @param $title the title of the document
     * @return nothing
     */
    function setCredits($creator, $author, $title)
    {
        $this->credits["creator"] = $creator;
        $this->credits["author"] = $author;
        $this->title = $title;
    }

    /**
     * set parameters of the document
     *
     * @param $recordsCount the quantity of records
     * @param $bigPagesList the array containing data
     * @return nothing
     */
    function setParameters($recordsCount, $bigPagesList)
    {
        $this->recordsCount = $recordsCount;
        $this->bigPagesList = $bigPagesList;
    }

    /**
     * generate a big page
     *
     * @param $data the array containing data
     * @param $margins the array containing the size of the margins of the big page
     * @param $cell the array containing the size of a cell
     * @return nothing
     */
    function generateBigPage($data, $margins, $cell)
    {
        $this->AddPage();

        $documentHeight = $this->document["height"];
        $documentWidth = $this->document["width"];
        $documentHeightMiddle = $documentHeight/2;
        $documentWidthMiddle = $documentWidth/2;

        $this->Line($documentWidthMiddle, 0, $documentWidthMiddle, $documentHeight);
        $this->Line(0, $documentHeightMiddle, $documentWidth, $documentHeightMiddle);

        /* upper left page */
        $x = $margins["left"];
        $y = $margins["top"];
        if (array_key_exists("pageNumber", $data[0])) {
            $pageNumber = $data[0]["pageNumber"];
        } else {
            $pageNumber = null;
        }
        $this->generateSmallPage(array_shift($data), $x, $y, $cell, $pageNumber);

        /* upper right page */
        $x = $documentWidthMiddle + $margins["left"];
        $y = $margins["top"];
        if (array_key_exists("pageNumber", $data[0])) {
            $pageNumber = $data[0]["pageNumber"];
        } else {
            $pageNumber = null;
        }
        if ((array_key_exists("type", $data[0])) && ($data[0]["type"] == "title")) {
            $this->generateTitleSmallPage($x, $y, $this->title, $this->credits["author"], $this->recordsCount, $this->date);
            array_shift($data);
        } else {
            $this->generateSmallPage(array_shift($data), $x, $y, $cell, $pageNumber);
        }

        /* lower left page */
        $x = $margins["left"];
        $y = $documentHeightMiddle + $margins["top"];
        if ((is_array($data[0]) && array_key_exists("pageNumber", $data[0]))) {
            $pageNumber = $data[0]["pageNumber"];
        } else {
            $pageNumber = null;
        }
        $this->generateSmallPage(array_shift($data), $x, $y, $cell, $pageNumber);

        /* lower right page */
        $x = $documentWidthMiddle + $margins["left"];
        $y = $documentHeightMiddle + $margins["top"];
        if ((is_array($data[0]) && array_key_exists("pageNumber", $data[0]))) {
            $pageNumber = $data[0]["pageNumber"];
        } else {
            $pageNumber = null;
        }
        $this->generateSmallPage(array_shift($data), $x, $y, $cell, $pageNumber);
    }

    /**
     * generate the title small page
     *
     * @param $x the beginning point of the title small page on the x axis
     * @param $y the beginning point of the title small page on the y axis
     * @param $title the title of the document
     * @param $author the author of the document
     * @param $recordsCount the quantity of records
     * @param $date the creation date of the document
     * @return nothing
     */
    function generateTitleSmallPage($x, $y, $title, $author, $recordsCount, $date)
    {
        $this->SetXY($x + 20, $y + 180);
        $this->SetFont($this->font["family"], "i", 20);
        $this->Multicell(240, 40, $title, 1, 'C');

        $this->SetXY($x + 15, $y + 391);
        $this->SetFont($this->font["family"], null, $this->font["size"]);
        $this->Cell(75, 15, $recordsCount, 0, 0, 'C');
        $this->SetFont($this->font["family"], "i", $this->font["size"]);
        $this->Cell(117, 15, $author, 0, 0, 'C');
        $this->SetFont($this->font["family"], null, $this->font["size"]);
        $this->Cell(75, 15, $date, 0, 0, 'C');
    }

    /**
     * generate a small page
     *
     * @param $data the array containing data
     * @param $x the beginning point of the small page on the x axis
     * @param $y the beginning point of the small page on the y axis
     * @param $cell the array containing the size of a cell
     * @param $pageNumber the small page number
     * @return nothing
     */
    function generateSmallPage($data, $x, $y, $cell, $pageNumber)
    {
        $this->SetXY($x, $y);

        $documentHeightMiddle = $this->document["height"] / 2;
        $cellHeight = 3 * $cell["height"] + $cell["inter"];
        $recordsCountPage = floor($documentHeightMiddle / $cellHeight);

        for ($i = 0; $i < $recordsCountPage; $i++) {
            $x = $this->GetX();
            $y = $this->GetY();
            if (is_array($data) && (array_key_exists($i, $data))) {
                $content = $data[$i];
            } else {
                $content = null;
            }
            $this->generateCell($content, $x, $y, $cell);
            $this->SetXY($x, $y + $cellHeight);
        }
        $x = $this->GetX();
        $y = $this->GetY() - $cell["inter"];
        $this->SetXY($x, $y);

        $this->Multicell($cell["width"], $cell["height"], $pageNumber, 0, "C");
    }

    /**
     * generate a cell
     *
     * @param $data the array containing data
     * @param $x the beginning point of the cell on the x axis
     * @param $y the beginning point of the cell on the y axis
     * @param $cell the array containing the size of a cell
     * @return nothing
     */
    function generateCell($data, $x, $y, $cell)
    {
        $maxLength = 56;

        if ($data["name"] == null) {
            return;
        }

        $line1 = $data["name"] . " " . $data["firstname"];
        if ($data["cellphone"] != null) {
            $phone = " " . $data["cellphone"];
        } elseif ($data["homephone"] != null) {
            $phone = " " . $data["homephone"];
        } else {
            $phone = null;
        }
        $line1 = $this->centerPad($line1, $phone, $maxLength);

        $line2 = $data["address"] . " " . $data["zipcode"] . " " . $data["city"];
        $line2 = $this->centerPad($line2, null, $maxLength);

        $line3 = $this->centerPad($data["email"], $data["comments"], $maxLength);

        $content = "$line1\n$line2\n$line3\n";

        $this->Multicell($cell["width"], $cell["height"], $content, 1, "L");
    }

    /**
     * pad a string in the middle
     *
     * @param $begin the beginning of the string
     * @param $end the end of the string
     * @param $maxLength the default string length
     * @return the padded string
     */
    function centerPad($begin, $end, $maxLength)
    {
        $begin = trim($begin);
        $begin = str_replace("  ", " ", $begin);

        $end = trim($end);
        $end = str_replace("  ", " ", $end);

        $begin = str_pad($begin, ($maxLength - 1) - mb_strlen($end, "ISO-8859-1"));
        $paddedString = mb_substr($begin . " " . $end, 0, $maxLength, "ISO-8859-1");

        return $paddedString;
    }
}
?>
