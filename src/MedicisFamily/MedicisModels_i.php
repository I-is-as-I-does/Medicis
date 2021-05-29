<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

interface MedicisModels_i
{
    public function __construct($MetaMedicis);
    public function BaseArray($id, $title = false);
    public function EmailsArray($id, $title = false);
    public function RefsArray($id, $refKey, $title = false);
    public function BoolsArray($id, $default = null, $title = false);
    public function StringsArray($id, $itExample, $title = false, $itMin = false, $itMax = false, $itPattern = false);
    public function NumbersArray($id, $title = false, $itMin = false, $itMax = false);
    public function Label($id = 'label');
    public function Bool($id, $default = null, $title = false);
    public function Email($id, $title = false);
    public function String($id, $example, $title = false, $minLen = false, $maxLen = false, $pattern = false);
    public function Number($id, $title = false, $min = false, $max = false);
    public function ShortTitle($id = 'shortTitle');
    public function Title($id = 'title');
    public function Year($id = 'year');
    public function UniqueRef($id, $refKey, $title = false);
}
