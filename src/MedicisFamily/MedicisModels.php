<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

class MedicisModels implements MedicisModels_i {
    private $MedicisMap;
    public $idpattern = '[\w\-]{21}';

    public function __construct($MetaMedicis, $idpattern = '')
    {
        $this->MedicisMap = $MetaMedicis->getMedicisMap();
        if(!empty($idpattern)){
            $this->setIdPattern($pattern);
        }
        
    }

    public function setIdPattern($pattern){     
        if(Jack::Help()->isValidPattern($pattern)){
            $this->idpattern = trim($pattern,'/');
            return true;
        }
        return false;
    }

    private function prcTitle($id)
    {
        if (stripos($id, '/') !== false) {
            $split = explode('/',$id);
            $id = array_shift($split);

            foreach($split as $part) {
                    $id .= ucfirst($part);
            }}
        return $id;
    }

    public function BaseArray($id)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->prcTitle($id);
        $prop['type'] = 'array';
        $prop["uniqueItems"] = true;
        $prop["additionalItems"] = true;
        return $prop;
    }

    public function EmailsArray($id)
    {
        $prop = $this->BaseArray($id);
        $prop["items"] = $this->Email($id . '/items');
        return $prop;
    }

    public function RefsArray($id, $refKey)
    {
        $prop = $this->BaseArray($id);
        $prop["items"] = $this->UniqueRef($id . '/items', $refKey);
        return $prop;
    }

    public function BoolsArray($id, $default = null)
    {
        $prop = $this->BaseArray($id);
        $prop["items"] = $this->Bool($id . '/items', $default);
        return $prop;
    }

    public function StringsArray($id, $itExample, $itMin = false, $itMax = false, $itPattern = false)
    {
        $prop = $this->BaseArray($id);
        $prop["items"] = $this->String($id . '/items', $itExample, $itMin, $itMax, $itPattern);
        return $prop;
    }

    public function NumbersArray($id, $itMin = false, $itMax = false)
    {
        $prop = $this->BaseArray($id);
        $prop["items"] = $this->Number($id . '/items', $itMin, $itMax);
        return $prop;
    }

    public function Label($id = 'label')
    {
        $example = 'Record Label';
        $prop = $this->String($id, $example);

        return $prop;
    }

    public function Bool($id, $default = null)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->prcTitle($id);
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

    public function String($id, $example, $minLen = false, $maxLen = false, $pattern = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->prcTitle($id);
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

    public function Path($id = "path")
    {
        $example = 'path/to/file.ext';
        $prop = $this->String($id, $example);
        $prop["format"] = "uri-reference";
        return $prop;
    }

    public function Number($id, $min = false, $max = false)
    {
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->prcTitle($id);;
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
        $example = 'A Short Version of Title';
        $maxLen = 50;
        return $this->String($id, $example, false, $maxLen);
    }

    public function Title($id = 'title')
    {
        $example = 'A Title That Can Be As Long as Needed';
        return $this->String($id, $example);
    }

    public function Year($id = "year")
    {
        $example = date('Y');
        $minLen = 4;
        $maxLen = 4;
        return $this->String($id, $example, $minLen, $maxLen);
    }

    public function UniqueRef($id, $refKey)
    {
        $refExists = $this->MedicisMap->collcExists($refKey);
        if ($refExists === false) {
            return ['err' => 'reference key not found: ' . $refKey];
        }
        $foreignFileName = $refKey . '-data';
        $escFilename = str_replace('-', '\-', $refKey);
        $prop = [];
        $prop['$id'] = "#/properties/" . $id;
        $prop['title'] = $this->prcTitle($id);;
        $prop["type"] = 'object';
        $prop["additionalProperties"] = false;

        $pointerId = 'relPointer';
        $pointerExample = $foreignFileName . '.json#/123e4567-e89b-12d3-a456-426614174000';
        $pointerPattern = '^' . $escFilename . '\.json#/' . $this->idpattern . '\$';
        $prop["properties"][$pointerId] = $this->String($id . '/'.$pointerId, $pointerExample, false, false, $pointerPattern);

        $labelId = 'relLabel';
        $labelExample = 'Related Item Label';
        $prop["properties"][$labelId] = $this->String($id . '/' . $labelId, $labelExample);

        return $prop;
    }
}
