<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\Jack\Jack;

class MedicisModels implements MedicisModels_i
{
    private $MedicisMap;

    public $currenciesCodes = ["AFN", "ARS", "AWG", "AUD", "AZN", "BSD", "BBD", "BDT", "BYN", "BZD", "BMD", "BOP", "BAM", "BWP", "BGN", "BRL", "BND", "KHR", "CAD", "KYD", "CLP", "CNY", "COP", "CRC", "HRK", "CUP", "CZK", "DKK", "DOP", "XCD", "EGP", "SVC", "EEK", "EUR", "FKP", "FJD", "GHC", "GIP", "GTQ", "GGP", "GYD", "HNL", "HKD", "HUF", "ISK", "INR", "IDR", "IRR", "IMP", "ILS", "JMD", "JPY", "JEP", "KZT", "KPW", "KGS", "LAK", "LVL", "LBP", "LRD", "LTL", "MKD", "MYR", "MUR", "MXN", "MNT", "MZN", "NAD", "NPR", "ANG", "NZD", "NIO", "NGN", "NOK", "OMR", "PKR", "PAB", "PYG", "PEN", "PHP", "PLN", "QAR", "RON", "RUB", "SHP", "SAR", "RSD", "SCR", "SGD", "SBD", "SOS", "ZAR", "LKR", "SEK", "CHF", "SRD", "SYP", "TWD", "THB", "TTD", "TRY", "TVD", "UAH", "GBP", "UGX", "USD", "UYU", "UZS", "VEF", "VND", "ZWD",
    ];

    public $langCodes = ['ab', 'aa', 'af', 'ak', 'sq', 'am', 'ar', 'an', 'hy', 'as', 'av', 'ae', 'ay', 'az', 'bm', 'ba', 'eu', 'be', 'bn', 'bh', 'bi', 'bs', 'br', 'bg', 'my', 'ca', 'km', 'ch', 'ce', 'ny', 'zh', 'cu', 'cv', 'kw', 'co', 'cr', 'hr', 'cs', 'da', 'dv', 'nl', 'dz', 'en', 'eo', 'et', 'ee', 'fo', 'fj', 'fi', 'fr', 'ff', 'gd', 'gl', 'lg', 'ka', 'de', 'ki', 'el', 'kl', 'gn', 'gu', 'ht', 'ha', 'he', 'hz', 'hi', 'ho', 'hu', 'is', 'io', 'ig', 'id', 'ia', 'ie', 'iu', 'ik', 'ga', 'it', 'ja', 'jv', 'kn', 'kr', 'ks', 'kk', 'rw', 'kv', 'kg', 'ko', 'kj', 'ku', 'ky', 'lo', 'la', 'lv', 'lb', 'li', 'ln', 'lt', 'lu', 'mk', 'mg', 'ms', 'ml', 'mt', 'gv', 'mi', 'mr', 'mh', 'ro', 'mn', 'na', 'nv', 'nd', 'ng', 'ne', 'se', 'no', 'nb', 'nn', 'ii', 'oc', 'oj', 'or', 'om', 'os', 'pi', 'pa', 'ps', 'fa', 'pl', 'pt', 'qu', 'rm', 'rn', 'ru', 'sm', 'sg', 'sa', 'sc', 'sr', 'sn', 'sd', 'si', 'sk', 'sl', 'so', 'st', 'nr', 'es', 'su', 'sw', 'ss', 'sv', 'tl', 'ty', 'tg', 'ta', 'tt', 'te', 'th', 'bo', 'ti', 'to', 'ts', 'tn', 'tr', 'tk', 'tw', 'ug', 'uk', 'ur', 'uz', 've', 'vi', 'vo', 'wa', 'cy', 'fy', 'wo', 'xh', 'yi', 'yo', 'za', 'zu'];

    public function __construct($MetaMedicis)
    {
        $this->MedicisMap = $MetaMedicis->getMedicisMap();

    }

    public function baseArray($id, $arrMinMax = [null, null], $unique = true)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = Jack::Help()->UpCamelCase($id);
        $prop['type'] = 'array';
        $prop["uniqueItems"] = $unique;
        if (is_int($arrMinMax[1])) {
            $prop["maxItems"] = $arrMinMax[1];
        } else {
            $prop["additionalItems"] = true;
        }
        if (is_int($arrMinMax[0])) {
            $prop["minItems"] = $arrMinMax[0];
        }
        return $prop;
    }

    private function fillArrayNumExample($prop, $arrMinMax, $MinMax)
    {
        if (is_null($arrMinMax[0]) || $arrMinMax[0] < 2) {
            return $this->fillArrayExample($prop, $arrMinMax);
        }
        if (!is_int($MinMax[0])) {
            $MinMax[0] = 0;
        }
        if (!is_int($MinMax[1])) {
            $MinMax[1] = 142;
        }
        $prop["example"] = [$MinMax[0]];
        if ($arrMinMax[0] > 2) {
            $existant = $prop["items"]['example'];
            $floor = 2;
            if (!in_array($existant, $MinMax)) {
                $prop["example"][] = $existant;
                $floor++;
            }
            if ($arrMinMax[0] > $floor) {
                for ($c = $floor; $c < $arrMinMax[0]; $c++) {
                    $prop["example"][] = random_int($MinMax[0], $MinMax[1]);
                }
            }
        }
        $prop["example"][] = $MinMax[1];
        return $prop;
    }

    private function getArrayExample($prop)
    {
        if (!empty($prop["example"])) {
            return $prop["example"];
        }
        if (!empty($prop["items"])) {
            return $this->getArrayExample($prop["items"]);
        }
        if (!empty($prop["properties"])) {
            $pile = [];
            foreach ($prop["properties"] as $objprop) {
                $pile[] = $this->getArrayExample($objprop);
            }
            return $pile;
        }
        return '';
    }

    private function fillArrayExample($prop, $arrMinMax)
    {
        $exmpl = $this->getArrayExample($prop);
        $prop["example"][] = $exmpl;
        if ($arrMinMax[0] > 1) {

            for ($c = 1; $c < $arrMinMax[0]; $c++) {
                $prop["example"][] = $exmpl;
            }
        }
        return $prop;
    }

    public function SubObject($id, $subSchemaId, $adtProp = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = Jack::Help()->UpCamelCase($id);
        $prop['type'] = 'object';
        $prop['additionalProperties'] = $adtProp;
        $prop['$ref'] = "#/definitions/" . $subSchemaId;
        return $prop;
    }

    public function ObjectsArray($id, $subSchemaId, $arrMinMax = [null, null])
    {
        $prop = $this->baseArray($id, $arrMinMax);
        $prop["items"] = ['$ref' => "#/definitions/" . $subSchemaId];
        return $prop;
    }

    public function EmailsArray($id, $arrMinMax = [null, null])
    {
        $prop = $this->baseArray($id, $arrMinMax);
        $prop["items"] = $this->Email($id . '/items');

        return $this->fillArrayExample($prop, $arrMinMax);
    }

    public function RefsArray($id, $refKey, $arrMinMax = [null, null])
    {
        $prop = $this->baseArray($id, $arrMinMax);
        $prop["items"] = $this->UniqueRef($id . '/items', $refKey);
        if (array_key_exists('err',$prop["items"])) {
            return $prop["items"];
        }

        return $this->fillArrayExample($prop, $arrMinMax);
    }

    public function BoolsArray($id, $default = null, $arrMinMax = [null, null])
    {
        $prop = $this->baseArray($id, $arrMinMax, false);
        $prop["items"] = $this->Bool($id . '/items', $default);
        return $this->fillArrayExample($prop, $arrMinMax);
    }

    public function StringsArray($id, $itExample, $lenMinMax = [null, null], $itPattern = false, $arrMinMax = [null, null], $unique = true)
    {
        $prop = $this->baseArray($id, $arrMinMax, $unique);
        $prop["items"] = $this->String($id . '/items', $itExample, $lenMinMax, $itPattern);
        return $this->fillArrayExample($prop, $arrMinMax);
    }

    public function NumbersArray($id, $MinMax = [null, null], $arrMinMax = [null, null], $unique = false)
    {
        $prop = $this->baseArray($id, $arrMinMax, $unique);
        $prop["items"] = $this->Number($id . '/items', $MinMax);
        return $this->fillArrayNumExample($prop, $arrMinMax, $MinMax);
    }

    public function Label($id = 'label')
    {
        $example = 'Record Label';
        $prop = $this->String($id, $example);

        return $prop;
    }

    public function NanoId($id = 'id', $pattern = '[\w\-]{21}')
    {
        $example = 'V1StGXR8_Z5jdHi6B-myT';
        $prop = $this->String($id, $example, [21, 21], $pattern);

        return $prop;
    }

    public function Bool($id, $default = null)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = Jack::Help()->UpCamelCase($id);
        $prop['type'] = 'boolean';
        $prop["example"] = false;
        if ($default !== null) {
            $prop["example"] = $default;
            $prop["default"] = $default;
        }

        return $prop;
    }

    public function Email($id)
    {
        $example = 'some.name@domain.xyz';
        $prop = $this->String($id, $example);
        $prop["format"] = "email";
        return $prop;
    }

    public function String($id, $example, $lenMinMax = [null, null], $pattern = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = Jack::Help()->UpCamelCase($id);
        $prop['type'] = 'string';

        $prop['example'] = $example;
        if (is_int($lenMinMax[0])) {
            $prop["minLength"] = $lenMinMax[0];
        }
        if (is_int($lenMinMax[1])) {
            $prop["maxLength"] = $lenMinMax[1];
        }
        if (!empty($pattern)) {
            $prop["pattern"] = $pattern;
        }
        return $prop;
    }
    public function IsoDate($id)
    {
        $example = explode('T', date("c"))[0];
        $prop = $this->String($id, $example);
        $prop["format"] = "date";
        return $prop;
    }

    public function IsoTime($id)
    {
        $example = explode('T', date("c"))[1];
        $prop = $this->String($id, $example);
        $prop["format"] = "time";
        return $prop;
    }

    public function IsoDateTime($id)
    {
        $example = date("c");
        $prop = $this->String($id, $example);
        $prop["format"] = "date-time";
        return $prop;
    }

    public function NumberWithEnum($id, $enumList)
    {
        $min = min($enumList);
        $max = max($enumList);
        $prop = $this->Number($id, [$min, $max]);
        $prop["enum"] = $enumList;
        return $prop;
    }

    public function StringWithEnum($id, $enumList)
    {
        $example = $enumList[0];
        $prop = $this->String($id, $example);
        $prop["enum"] = $enumList;
        return $prop;
    }

    public function Timezone($id = 'timezone')
    {
        $enumList = Jack::Time()->timezonesList();
        return $this->StringWithEnum($id, $enumList);
    }

    public function CurrencyCode($id = 'currencyCode')
    {
        $enumList = $this->currenciesCodes;
        return $this->StringWithEnum($id, $enumList);
    }

    public function LangCode($id = 'langCode')
    {
        $enumList = $this->langCodes;
        return $this->StringWithEnum($id, $enumList);
    }

    public function Path($id = "path")
    {
        $example = 'some-path.ext';
        $prop = $this->String($id, $example);
        $prop["format"] = "uri-reference";
        return $prop;
    }

    public function Number($id, $MinMax = [null, null])
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = Jack::Help()->UpCamelCase($id);
        $prop['type'] = 'number';

        if (is_int($MinMax[0])) {
            $prop["minimum"] = $MinMax[0];
        } else {
            $MinMax[0] = 0;
        }
        if (is_int($MinMax[1])) {
            $prop["maximum"] = $MinMax[1];
        } else {
            $MinMax[1] = 142;
        }
        $prop["example"] = random_int($MinMax[0], $MinMax[1]);
        return $prop;
    }

    public function ShortTitle($id = 'shortTitle')
    {
        $example = 'A Short Version of Title';
        return $this->String($id, $example, [0, 50]);
    }

    public function Title($id = 'title')
    {
        $example = 'A Title That Can Be As Long as Needed';
        return $this->String($id, $example);
    }

    public function Year($id = "year")
    {
        $example = date('Y');
        return $this->String($id, $example, [4, 4]);
    }

    public function UniqueRef($id, $refKey, $idpattern = '[\w\-]{21}')
    {
        $refExists = $this->MedicisMap->collcExists($refKey);
        if ($refExists === false) {
            return ['err' => 'reference key not found: ' . $refKey];
        }
        $foreignFileName = $refKey . '-exmpl';
        $escFilename = str_replace('-', '\-', $refKey);
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = Jack::Help()->UpCamelCase($id);
        $prop["type"] = 'object';
        $prop["additionalProperties"] = false;

        $pointerId = 'relPointer';
        $pointerExample = $foreignFileName . '.json#/123e4567-e89b-12d3-a456-426614174000';
        $pointerPattern = '^' . $escFilename . '\.json#/' . $idpattern . '\$';
        $prop["properties"][$pointerId] = $this->String($id . '/' . $pointerId, $pointerExample, [null, null], $pointerPattern);

        $labelId = 'relLabel';
        $labelExample = 'Related Item Label';
        $prop["properties"][$labelId] = $this->String($id . '/' . $labelId, $labelExample);

        return $prop;
    }

    public function StrongPassword($id, $pattern = '^(?=\\S*?[A-Z])(?=\\S*?[a-z])(?=\\S*?[0-9])(?=\\S*?[*&!@%^#$]).{8,}$')
    {
        $example = 'o9#&5w&2$@EL87n3512MqXcg9Ln%^#v0';
        $prop = $this->String($id, $example, [null, null], $pattern);
        return $prop;
    }
}
