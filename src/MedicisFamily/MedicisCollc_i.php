<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

interface MedicisCollc_i
{
    public function __construct($MetaMedicis);
    public function collcBuild($collcId, $translToo = true);
    public function pageConfigBuild($collcId, $groupId, $priority);
    public function iterateOnSchProps($props, $targ);
    public function dummyDataBuild($SchPathOrId);
}
