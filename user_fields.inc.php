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
 * user fields list
 *
 * @param name is the field name in the database table
 * @param databaseType is the field type in the database table
 * @param htmlType is the field type in the html markup
 * @param pattern is the field pattern in the html markup
 * @param label is the label displayed next to each field (can be set with gettext)
 * @param defaultValue is the value stored in the database when the field is left empty (can be set with gettext)
 * @param placeholder is the help text displayed in the input field if it is empty (can be set with gettext)
 */
$userFieldsList = array();
$userFieldsList["name"] =      array("name" => "name",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("family name"),
                                     "defaultValue" => _("(unnamed)"),
                                     "placeholder" => _("name is mandatory"),
                                     "required" => true);

$userFieldsList["firstname"] = array("name" => "firstname",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("first name"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["address"] =   array("name" => "address",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("postal address"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["zipcode"] =   array("name" => "zipcode",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("postal code"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["city"] =      array("name" => "city",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("city"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["country"] =   array("name" => "country",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("country"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["homephone"] = array("name" => "homephone",
                                     "databaseType" => "text",
                                     "htmlType" => "tel",
                                     "pattern" => null,
                                     "label" => _("home phone"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["cellphone"] = array("name" => "cellphone",
                                     "databaseType" => "text",
                                     "htmlType" => "tel",
                                     "pattern" => null,
                                     "label" => _("mobile phone"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["email"] =     array("name" => "email",
                                     "databaseType" => "text",
                                     "htmlType" => "email",
                                     "pattern" => null,
                                     "label" => _("email"),
                                     "defaultValue" => null,
                                     "placeholder" => _("enter a valid email"),
                                     "required" => false);

$userFieldsList["website"] =   array("name" => "website",
                                     "databaseType" => "text",
                                     "htmlType" => "url",
                                     "pattern" => "https?://.+",
                                     "label" => _("website"),
                                     "defaultValue" => null,
                                     "placeholder" => _("enter a valid url"),
                                     "required" => false);

$userFieldsList["comments"] =  array("name" => "comments",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("comments"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$userFieldsList["tags"] =      array("name" => "tags",
                                     "databaseType" => "text",
                                     "htmlType" => "text",
                                     "pattern" => null,
                                     "label" => _("tags"),
                                     "defaultValue" => null,
                                     "placeholder" => null,
                                     "required" => false);

$displayUserFieldsList = array();
$displayUserFieldsList[$recordLabel] = $userFieldsList;
?>
