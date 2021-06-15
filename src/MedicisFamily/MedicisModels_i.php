<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;
interface MedicisModels_i {
public function __construct($MetaMedicis);
public function baseArray($id, $arrMinMax = array (0 => NULL,
  1 => NULL,), $unique = true);
public function SubObject($id, $subSchemaId, $adtProp = false);
public function ObjectsArray($id, $subSchemaId, $arrMinMax = array (0 => NULL,
  1 => NULL,));
public function EmailsArray($id, $arrMinMax = array (0 => NULL,
  1 => NULL,));
public function RefsArray($id, $refKey, $arrMinMax = array (0 => NULL,
  1 => NULL,));
public function BoolsArray($id, $default = NULL, $arrMinMax = array (0 => NULL,
  1 => NULL,));
public function StringsArray($id, $itExample, $lenMinMax = array (0 => NULL,
  1 => NULL,), $itPattern = false, $arrMinMax = array (0 => NULL,
  1 => NULL,), $unique = true);
public function NumbersArray($id, $MinMax = array (0 => NULL,
  1 => NULL,), $arrMinMax = array (0 => NULL,
  1 => NULL,), $unique = false);
public function Label($id = 'label');
public function NanoId($id = 'id', $pattern = '[\\w\\-]{21}');
public function Bool($id, $default = NULL);
public function Email($id);
public function String($id, $example, $lenMinMax = array (0 => NULL,
  1 => NULL,), $pattern = false);
public function IsoDate($id);
public function IsoTime($id);
public function IsoDateTime($id);
public function NumberWithEnum($id, $enumList);
public function StringWithEnum($id, $enumList);
public function Timezone($id = 'timezone');
public function CurrencyCode($id = 'currencyCode');
public function LangCode($id = 'langCode');
public function Path($id = 'path');
public function Number($id, $MinMax = array (0 => NULL,
  1 => NULL,));
public function ShortTitle($id = 'shortTitle');
public function Title($id = 'title');
public function Year($id = 'year');
public function UniqueRef($id, $refKey, $idpattern = '[\\w\\-]{21}');
public function StrongPassword($id, $pattern = '^(?=\\S*?[A-Z])(?=\\S*?[a-z])(?=\\S*?[0-9])(?=\\S*?[*&!@%^#$]).{8,}$');

}