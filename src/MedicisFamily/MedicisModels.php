<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

class MedicisModels implements MedicisModels_i
{
    private $MedicisMap;
    private $idlen = 21;
    private $idpattern;

    public function __construct($MetaMedicis)
    {
        $this->MedicisMap = $MetaMedicis->getMedicisMap();
        $this->idpattern = '[\w\-]{' . $this->idlen . '}';
    }

    protected function processTitle($id, $title)
    {
        if (empty($title)) {
            return ucfirst($id);
        }
        return $title;
    }

    public function BaseArray($id, $title = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->processTitle($id, $title);
        $prop['type'] = 'array';
        $prop["uniqueItems"] = true;
        $prop["additionalItems"] = true;
        return $prop;
    }

    public function EmailsArray($id, $title = false)
    {
        $prop = $this->BaseArray($id, $title);
        $prop["items"] = $this->Email($id . '/items', $prop['title'] . 'Items');
        return $prop;
    }

    public function RefsArray($id, $refKey, $title = false)
    {
        $prop = $this->BaseArray($id, $title);
        $prop["items"] = $this->UniqueRef($id . '/items', $refKey, $prop['title'] . 'Refs');
        return $prop;
    }

    public function BoolsArray($id, $default = null, $title = false)
    {
        $prop = $this->BaseArray($id, $title);
        $prop["items"] = $this->Bool($id . '/items', $default, $prop['title'] . 'Items');
        return $prop;
    }

    public function StringsArray($id, $itExample, $title = false, $itMin = false, $itMax = false, $itPattern = false)
    {
        $prop = $this->BaseArray($id, $title);
        $prop["items"] = $this->String($id . '/items', $itExample, $prop['title'] . 'Items', $itMin, $itMax, $itPattern);
        return $prop;
    }

    public function NumbersArray($id, $title = false, $itMin = false, $itMax = false)
    {
        $prop = $this->BaseArray($id, $title);
        $prop["items"] = $this->Number($id . '/items', $prop['title'] . 'Items', $itMin, $itMax);
        return $prop;
    }

    public function Label($id = 'label')
    {
        $title = ucfirst($id);
        $example = 'Record Label';
        $prop = $this->String($id, $example, $title);

        return $prop;
        //@label will be auto built by js with required fields
    }

    public function Bool($id, $default = null, $title = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->processTitle($id, $title);
        $prop['type'] = 'boolean';
        $prop["example"] = false;
        if ($default !== null) {
            $prop["example"] = $default;
            $prop["default"] = $default;
        }

        return $prop;
    }

    public function Email($id, $title = false)
    {
        $example = 'some.name@domain.xyz';
        $prop = $this->String($id, $example, $title);
        $prop["format"] = "email";
        return $prop;
    }

    public function String($id, $example, $title = false, $minLen = false, $maxLen = false, $pattern = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->processTitle($id, $title);
        $prop['type'] = 'string';

        $prop['example'] = $example;
        if (!empty($minLen)) {
            $prop["minLength"] = $minLen;
        }
        if (!empty($maxLen)) {
            $prop["maxLength"] = $maxLen;
        }
        if (!empty($pattern)) {
            $prop["pattern"] = $pattern;
        }
        return $prop;
    }

    public function Number($id, $title = false, $min = false, $max = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->processTitle($id, $title);
        $prop['type'] = 'number';

        if (!empty($min)) {
            $prop["minimum"] = $min;
        } else {
            $min = 0;
        }
        if (!empty($max)) {
            $prop["maximum"] = $max;
        } else {
            $max = 3000;
        }
        $prop["example"] = random_int($min, $max);
        return $prop;
    }

    public function ShortTitle($id = 'shortTitle')
    {
        $title = 'Short Title';
        $example = 'A Short Version of Title';
        $maxLen = 50;
        return $this->String($id, $example, $title, false, $maxLen);
    }

    public function Title($id = 'title')
    {
        $title = 'Title';
        $example = 'A Title That Can Be As Long as Needed';
        return $this->String($id, $example, $title);
    }

    public function Year($id = "year")
    {
        $title = 'Year';
        $example = date('Y');
        $minLen = 4;
        $maxLen = 4;
        return $this->String($id, $example, $title, $minLen, $maxLen);
    }

    public function UniqueRef($id, $refKey, $title = false)
    {
        $refExists = $this->MedicisMap->IdExists($refKey, 'collc');
        if ($refExists === false) {
            return ['err' => 'reference key not found: ' . $refKey];
        }
        $foreignFileName = $refKey . '-data';
        $escFilename = str_replace('-', '\-', $refKey);
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->processTitle($id, $title);
        $prop["type"] = 'object';
        $prop["additionalProperties"] = false;

        $pointerId = 'relPointer';
        $pointerExample = $foreignFileName . '.json#/f4_r5e5-HRz5f8-tZ45fR';
        $pointerPattern = '^' . $escFilename . '\.json#/' . $this->idpattern . '\$';
        $prop["properties"][$pointerId] = $this->String($id . '/properties/' . $pointerId, $pointerExample, ucfirst($pointerId), false, false, $pointerPattern);

        $labelId = 'relLabel';
        $labelExample = 'Related Item Label';
        $prop["properties"][$labelId] = $this->String($id . '/properties/' . $labelId, $labelExample, ucfirst($labelId));

        return $prop;
    }
}
