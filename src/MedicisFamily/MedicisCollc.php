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
            $rslt['exmpl'] = $this->dummyDataBuild($collcId, $sch);
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

    public function iterateOnSchProps($sch, $targ, $defs =[])
    {

        if (array_key_exists($targ, $sch)) {
            return $sch[$targ];
        }

        if(empty($defs) && array_key_exists('definitions',$sch)){
            $defs = $sch["definitions"];
        }
        if (array_key_exists('$ref', $sch) && !empty($defs)) {
            $spltid = explode('/', $sch['$ref']);
            $refid = array_pop($spltid);
            if (!empty($defs[$refid])) {
                return $this->iterateOnSchProps($defs[$refid], $targ, $defs);
            }
        }

        if (!empty($sch['items'])) {
            return $this->iterateOnSchProps($sch['items'], $targ, $defs);
        }

        if (!empty($sch['properties'])) {
         
            $data = [];
            
            foreach ($sch['properties'] as $k => $v) {
                if($k == '$ref'){
                    $itr = $this->iterateOnSchProps(['$ref'=>$v], $targ, $defs);
                } else {
                $itr = $this->iterateOnSchProps($v, $targ, $defs);
            }
                if (!empty($itr)) {
                    $data[$k] = $itr;
                }
            }
            return $data;
        }
    }

    public function dummyDataBuild($collcId, $sch = [])
    {
        $sch = $this->MetaMedicis->quickCheckSchema($collcId, $sch);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $dummyData = $this->iterateOnSchProps($sch, 'example');
        return $this->MetaMedicis->saveDistFile($dummyData, $collcId, 'exmpl');
    }

}
