<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

interface MedicisProfile_i
{
    public function __construct($MetaMedicis);
    public function profileBuild($profileId, $translToo = true);
}
