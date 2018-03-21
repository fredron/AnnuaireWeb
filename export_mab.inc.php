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
    array_push($selectFieldsList, $fieldsList["website"]);
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
    $dataValues = null;
    $dataLinks = null;
    $fieldCounter = 84;
    $recordCounter = 0;

    while ($data = $pdoResultSet->fetch(PDO::FETCH_ASSOC)) {
        /* decode htmlspecialchars */
        foreach($data as &$item) {
            $item = htmlspecialchars_decode($item, ENT_QUOTES);
        }
        $recordCounter++;
        $result = mabFormat($recordCounter, $fieldCounter, $data);
        $fieldCounter = $result["counter"];
        $dataValues .= $result["values"];
        $dataLinks .= $result["links"];
    }
    $pdoResultSet->closeCursor();

    $fileContent = generateMabHeader();

    $fileContent .= "< (81=)\n";
    $fileContent .= "  (82=)\n";
    $fileContent .= "  (83=)\n";
    $fileContent .= $dataValues;
    $fileContent .= ">\n\n";

    $fileContent .= "{1:^80 {(k^BF:c)(s=9)}\n";
    $fileContent .= "  [1:^82(^BE=$recordCounter)]\n";
    $fileContent .= $dataLinks;
    $fileContent .= "}";

    return $fileContent;
}

/**
 * generate a mab header
 *
 * @return the mab header
 */
function generateMabHeader()
{
    $header = <<<END
// <!-- <mdb:mork:z v="1.4"/> -->
< <(a=c)> // (f=iso-8859-1)
  (B8=Custom3)(B9=Custom4)(BA=Notes)(BB=LastModifiedDate)(BC=RecordKey)
  (BD=AddrCharSet)(BE=LastRecordKey)(BF=ns:addrbk:db:table:kind:pab)
  (C0=ListName)(C1=ListNickName)(C2=ListDescription)
  (C3=ListTotalAddresses)(C4=LowercaseListName)
  (C5=ns:addrbk:db:table:kind:deleted)
  (80=ns:addrbk:db:row:scope:card:all)
  (81=ns:addrbk:db:row:scope:list:all)
  (82=ns:addrbk:db:row:scope:data:all)(83=FirstName)(84=LastName)
  (85=PhoneticFirstName)(86=PhoneticLastName)(87=DisplayName)
  (88=NickName)(89=PrimaryEmail)(8A=LowercasePrimaryEmail)
  (8B=SecondEmail)(8C=DefaultEmail)(8D=CardType)(8E=PreferMailFormat)
  (8F=PopularityIndex)(90=WorkPhone)(91=HomePhone)(92=FaxNumber)
  (93=PagerNumber)(94=CellularNumber)(95=WorkPhoneType)(96=HomePhoneType)
  (97=FaxNumberType)(98=PagerNumberType)(99=CellularNumberType)
  (9A=HomeAddress)(9B=HomeAddress2)(9C=HomeCity)(9D=HomeState)
  (9E=HomeZipCode)(9F=HomeCountry)(A0=WorkAddress)(A1=WorkAddress2)
  (A2=WorkCity)(A3=WorkState)(A4=WorkZipCode)(A5=WorkCountry)
  (A6=JobTitle)(A7=Department)(A8=Company)(A9=_AimScreenName)
  (AA=AnniversaryYear)(AB=AnniversaryMonth)(AC=AnniversaryDay)
  (AD=SpouseName)(AE=FamilyName)(AF=DefaultAddress)(B0=Category)
  (B1=WebPage1)(B2=WebPage2)(B3=BirthYear)(B4=BirthMonth)(B5=BirthDay)
  (B6=Custom1)(B7=Custom2)>


END;

    return $header;
}

/**
 * format a string to mab format
 *
 * @param $recordCounter the record counter
 * @param $fieldCounter the field counter
 * @param $data the array containing the data to format (name, firstname, address, zipcode, city, country, homephone, cellphone, email, website, comments)
 * @return the mab-formatted string
 */
function mabFormat($recordCounter, $fieldCounter, $data)
{
    $data = array_map("mabSpecialCharFormat", $data);
    extract($data);
    $fullName = trim("$firstname $name");

    $dataValues = "  ";
    $dataLinks = "  [$recordCounter"; /* mab id */

    $dataValues .= "($fieldCounter=$firstname)";
    $dataLinks .= "(^83^" . $fieldCounter . ")"; /* ^83 = FirstName */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$name)";
    $dataLinks .= "(^84^" . $fieldCounter . ")"; /* ^84 = LastName */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$fullName)";
    $dataLinks .= "(^87^" . $fieldCounter . ")"; /* ^87 = DisplayName */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$email)";
    $dataLinks .= "(^89^" . $fieldCounter . ")"; /* ^89 = PrimaryEmail */
    $dataLinks .= "(^8A^" . $fieldCounter . ")"; /* ^8A = LowercasePrimaryEmail */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$homephone)";
    $dataLinks .= "(^91^" . $fieldCounter . ")"; /* ^91 = HomePhone */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$cellphone)";
    $dataLinks .= "(^94^" . $fieldCounter . ")"; /* ^94 = CellularNumber */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$address)";
    $dataLinks .= "(^9A^" . $fieldCounter . ")"; /* ^9A = HomeAddress */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$zipcode)";
    $dataLinks .= "(^9C^" . $fieldCounter . ")"; /* ^9C = HomeCity */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$city)";
    $dataLinks .= "(^9E^" . $fieldCounter . ")"; /* ^9E = HomeZipCode */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$country)";
    $dataLinks .= "(^9F^" . $fieldCounter . ")"; /* ^9F = HomeCountry */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$website)";
    $dataLinks .= "(^B1^" . $fieldCounter . ")"; /* ^B1 = WebPage1 */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$comments)";
    $dataLinks .= "(^BA^" . $fieldCounter . ")"; /* ^BA = Notes */
    $fieldCounter++;

    $dataValues .= "($fieldCounter=$recordCounter)";
    $dataLinks .= "(^BC=" . $recordCounter . ")"; /* ^BC = RecordKey */
    $fieldCounter++;

    $dataValues .= "\n";
    $dataLinks .= "]\n";

    return array("counter" => $fieldCounter, "values" => $dataValues, "links" => $dataLinks);
}

/**
 * replace special characters by mab-formatted special characters
 *
 * @param $inputString the string to format
 * @return the mab-formatted string
 */
function mabSpecialCharFormat($inputString)
{
    $fromSpecialChar = array(')', '$', 'é', 'è', 'ç', 'à', 'â', 'ê', 'û', 'î', 'ô', 'ù', 'ë', 'ü');
    $toSpecialChar = array('\)', '\$', '$C3$A9', '$C3$A8', '$C3$A7', '$C3$A0', '$C3$A2', '$C3$AA', '$C3$BB', '$C3$AE', '$C3$B4', '$C3$B9', '$C3$AB', '$C3$BC');

    $outputString = str_replace($fromSpecialChar, $toSpecialChar, $inputString);

    return $outputString;
}
?>
