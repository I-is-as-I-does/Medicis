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
        $schBuild = $this->MetaMedicis->getMedicisMember('Schema')->schBuild($collcId);
        if (array_key_exists('err', $schBuild)) {
            return $schBuild;
        }
        $rslt = [];
        $rslt['sch'] = $schBuild['rslt'];
        $rslt['data'] = $this->dummyDataBuild($schBuild['sch']);
        $rslt['config'] = $this->pageConfigBuild($collcId, $schBuild['groupId'], $schBuild['priority']);
        if ($translToo === true) {
            $rslt['transl'] = $this->MetaMedicis->getMedicisMember('Transl')->collcTranslBuild($schBuild['sch']);
        }
        return $rslt;
    }

    public function pageConfigBuild($collcId, $groupId, $priority)
    {
        $pgid = 'studio/' . $collcId;
        $page = [];
        $page[$pgid]['status'] = 'required';
        $page[$pgid]['auth'] = 1;
        $page[$pgid]['template'] = 'collections';
        $page[$pgid]['menu']['section'] = $groupId;
        $page[$pgid]['menu']['priority'] = $priority;
        return $this->MetaMedicis->saveDistFile($page, $collcId, 'config');
    }

    public function iterateOnSchProps($props, $targ)
    {
        $data = [];
        foreach ($props as $k => $v) {
            if ($v['type'] == 'object') {
                $data[$k] = $this->iterateOnProperties($v['properties']);
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

    public function dummyDataBuild($SchPathOrId)
    {
        $sch = $this->MetaMedicis->getSchema($SchPathOrId);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $collcId = substr($sch['$id'], 0, -5);
        $dummyData = $this->iterateOnSchProps($sch['properties'], 'example');
        return $this->MetaMedicis->saveDistFile($dummyData, $collcId, 'data');
    }

}
