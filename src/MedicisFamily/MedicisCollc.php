<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

class MedicisCollc implements MedicisCollc_i
{

    private $MetaMedicis;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
    }

    public function collcBuild($collcId, $translToo = true)
    {
        $src = $this->MetaMedicis->quickCheckSrc($collcId);
        if (array_key_exists('err', $src)) {
            return $src;
        }
        $sch = $this->MetaMedicis->getMedicisMember('Schema')->schBuild($collcId, $src);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $rslt = [];
        $rslt['sch'] = $this->MetaMedicis->saveDistFile($sch, $collcId, 'sch');
        if (!array_key_exists('err', $rslt['sch'])) {
            $rslt['data'] = $this->dummyDataBuild($collcId, $sch);
            $rslt['config'] = $this->collcConfigBuild($collcId, $src);
            if ($translToo === true) {
                $rslt['transl'] = $this->MetaMedicis->getMedicisMember('Transl')->collcTranslBuild($collcId, $sch);
            }
        }
        return $rslt;
    }

    public function collcConfigBuild($collcId, $src = [])
    {

        $src = $this->MetaMedicis->quickCheckSrc($collcId, $src);
        if (array_key_exists('err', $src)) {
            return $src;
        }
        if (!empty($src['config'])) {
            return $this->MetaMedicis->saveDistFile($src['config'], $collcId, 'config');
        }
        return ['skipped' => 'No config found in "' . $collcId . '" source file'];
    }

    public function iterateOnSchProps($props, $targ)
    {
        $data = [];
        foreach ($props as $k => $v) {
            if ($v['type'] == 'object') {
                $data[$k] = $this->iterateOnSchProps($v['properties'], $targ);
            } else {
                if ($v['type'] == 'array') {
                    $v = $v['items'];
                }
                if (array_key_exists($targ, $v)) {
                    $data[$k] = $v[$targ];
                }
            }
        }
        return $data;
    }

    public function dummyDataBuild($collcId, $sch = [])
    {
        $sch = $this->MetaMedicis->quickCheckSchema($collcId, $sch);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $dummyData = $this->iterateOnSchProps($sch['properties'], 'example');
        return $this->MetaMedicis->saveDistFile($dummyData, $collcId, 'data');
    }

}
