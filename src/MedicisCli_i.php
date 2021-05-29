<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

interface MedicisCli_i
{
    public function __construct($collectionDirPath, $runBuild = false);
    public function build();
}
