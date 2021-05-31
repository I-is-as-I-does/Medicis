<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

interface MedicisModels_i
{
    public function __construct($MetaMedicis, $idpattern = '');
    public function setIdPattern($pattern);
    public function BaseArray($id);
    public function EmailsArray($id);
    public function RefsArray($id, $refKey);
    public function BoolsArray($id, $default = null);
    public function StringsArray($id, $itExample, $itMin = false, $itMax = false, $itPattern = false);
    public function NumbersArray($id, $itMin = false, $itMax = false);
    public function Label($id = 'label');
    public function Bool($id, $default = null);
    public function Email($id);
    public function String($id, $example, $minLen = false, $maxLen = false, $pattern = false);
    public function Path($id = 'path');
    public function Number($id, $min = false, $max = false);
    public function ShortTitle($id = 'shortTitle');
    public function Title($id = 'title');
    public function Year($id = 'year');
    public function UniqueRef($id, $refKey);
}
