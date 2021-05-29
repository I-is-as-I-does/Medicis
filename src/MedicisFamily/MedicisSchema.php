<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisSchema implements MedicisSchema_i
{

    private $MetaMedicis;
    private $MedicisModels;
    private $MedicisMap;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisMap = $MetaMedicis->getMedicisMap();
        $this->MedicisModels = $MetaMedicis->getMedicisMember('Models');
    }

    public function schBuild($collcId)
    {
        $collcInfos = $this->MedicisMap->getInfos($collcId, 'collc');
        if ($collcInfos === false) {
            return ['err' => 'Unknown collection Id ' . $collcId];
        }
        $src = Jack::File()->readJson($collcInfos['path']);
        if (empty($src)) {
            return ['err' => 'Read error: ' . $collcInfos['path']];
        }
        $props = $this->prcPropreties($src);
        if (array_key_exists('err', $props)) {
            return $props;
        }
        if (!array_key_exists('required', $src)) {
            $required = [];
        } else {
            $required = $src['required'];
        }
        $check = $this->checkValidity($props, $required);
        if ($check !== true) {
            return $check;
        }
        $collcTitle = $collcInfos['groupName'] . ' | ' . $src['name'];
        $schema = $this->schWrap($collcId, $collcTitle, $required, $props);
        $save = $this->MetaMedicis->saveDistFile($schema, $collcId, 'sch');
        if (array_key_exists('err', $save)) {
            return $save;
        }
        return ['rslt' => $save, 'sch' => $schema, 'groupId' => $collcInfos['groupId'], 'priority' => $src['priority']];
    }

    private function schWrap($collcId, $collcTitle, $required, $props)
    {
        return [
            '$schema' => "http://json-schema.org/draft-07/schema",
            '$id' => $collcId . '.json',
            'title' => $collcTitle,
            "type" => "object",
            "required" => $required,
            "properties" => $props,
            "additionalProperties" => false,
        ];
    }

    private function prcPropreties($src)
    {
        if (empty($src['props']) || !is_array($src['props'])) {
            return ['err' => 'Unvalid source: no properties data'];
        }

        $props = [];
        foreach ($src['props'] as $k => $info) {
            if (empty($info['param'])) {
                return ['err' => 'No specified param; must at least contain property id; param key: ' . $k];
            }
            $param = $info['param'];
            $id = $param[0];
            if (empty($info['method'])) {
                return ['err' => 'A MedicisModels method has not been specified for prop "' . $id . '"'];
            }
            $method = $info['method'];
            if (!method_exists($this->MedicisModels, $method)) {
                return ['err' => 'method "' . $method . '" does not exist in MedicisModels'];
            }
            $props[$id] = $this->MedicisModels->$method(...$param);
        }
        return $props;
    }

    private function checkValidity($props, $required)
    {
        $logErr = [];
        foreach ($props as $k => $prop) {
            if (!is_array($prop)) {
                $logErr[] = 'Unvalid schema property: ' . $k . '; ' . $prop;
            }
        }
        if(!empty($required)){
        foreach ($required as $propk) {
            if (!array_key_exists($propk, $props)) {
                $logErr[] = 'Missing required schema property: ' . $propk;
            }
        }
    }
        if (empty($logErr)) {
            return true;
        }
        return ['err' => implode(PHP_EOL, $logErr)];

    }

}
