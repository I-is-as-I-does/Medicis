<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */
namespace SSITU\Medicis\MedicisFamily;
interface MedicisSchema_i {
public function __construct($MetaMedicis);
public function schBuild($collcId, $src = array ());
}