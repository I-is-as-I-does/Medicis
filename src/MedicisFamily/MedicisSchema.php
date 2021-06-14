<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\Jack\Jack;

class MedicisSchema implements MedicisSchema_i {

    private $MetaMedicis;
    private $MedicisModels;
    private $MedicisMap;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisModels = $MetaMedicis->getMedicisMember('Models');
        $this->MedicisMap =  $MetaMedicis->getMedicisMap();
    }

    public function schBuild($collcId, $src = [])
    {
        $src = $this->MetaMedicis->quickCheckSrc($collcId, $src);
        if (array_key_exists('err', $src)) {
            return $src;
        }
        if (!array_key_exists('props', $src)) {
            return ['err' => 'invalid source'];
        }
        $build = $this->prcProperties($src);
        if (array_key_exists('err', $build)) {
            return $build;
        }
        $sch = array_merge($this->schWrap($collcId), $this->coWrap($build['props'], $build['required']));
        if (!empty($build['defs'])) {
            $sch["definitions"] = $build['defs'];
        }
        return $sch;
    }

    private function subschWrap($id)
    {
        return ['$id' => "#/properties/" . $id,
            'title' => Jack::Help()->UpCamelCase($id),
        ];
    }

    private function coWrap($props, $required, $adtProp = false)
    {
        return ['type' => 'object',
            "required" => $required,
            "additionalProperties" => $adtProp,
            "properties" => $props];
    }

    private function schWrap($collcId)
    {
        //removed: '$id' => $collcId . '.json' (an aboslute uri is required, yet any change would make a mess...)
        return [
            '$schema' => "http://json-schema.org/draft-07/schema",
            '$id' => $this->MedicisMap->getSchAbslId($collcId);
            'title' => $collcId,
        ];
    }

    private function isValidSubsch($src, $subId)
    {
        return (is_array($src['subschemas']) && !empty($src['subschemas'][$subId]));
    }

    private function prcProperties($src, $subSrc = false)
    {       $job = $src;
        if($subSrc !== false){
            $job = $subSrc;
        }
        if (empty($job['props']) || !is_array($job['props'])) {
            return ['err' => 'invalid source'];
        }
        $build = [
            "props" => [],
            "required" => [],
            "defs" => [],
        ];

        foreach ($job['props'] as $k => $info) {

            if (empty($info['method'])) {
                return ['err' => 'A MedicisModels method has not been specified for prop n."' . $k + 1 . '"'];
            }
            $method = $info['method'];

            if (empty($info['argm'])) {
                $info['argm'] = [lcfirst($method)];
            }

            $argm = $info['argm'];
            $id = $argm[0];

            if (!method_exists($this->MedicisModels, $method)) {
                return ['err' => 'method "' . $method . '" does not exist in MedicisModels'];
            }
            $build["props"][$id] = $this->MedicisModels->$method(...$argm);
            if ($method === 'ObjectsArray') {
                $subPrc = $this->prcSubProperties($src, $build['defs'], $id, $argm);
                if (array_key_exists('err', $subPrc)) {
                    return $subPrc;
                }
                $build['defs'] = $subPrc;
            }
        }
        if (array_key_exists('required', $job)) {
            $build["required"] = $job['required'];
        }
        $check = $this->checkValidity($build["props"], $build["required"]);
        if ($check !== true) {
            return $check;
        }
        return $build;
    }

    private function prcSubProperties($src, $defs, $id, $argm)
    {
        if (empty($argm[1]) || !$this->isValidSubsch($src, $argm[1])) {
            return ['err' => 'Unvalid source: subschema not found for "' . $id . '"'];
        }
        $subprc = $this->prcProperties($src, $src['subschemas'][$argm[1]]);
        if (array_key_exists('err', $subprc)) {
            return $subprc;
        }
        $subprops = $subprc['props'];
        if (!empty($subprc['defs'])) {
            $defs = array_merge($defs, $subprc['defs']);
        }
        $subAdtProp = !empty($argm[2]);
        $defs[$argm[1]] = array_merge($this->subschWrap($argm[1]), $this->coWrap($subprc['props'], $subprc['required'], $subAdtProp));
        return $defs;
    }

    private function checkValidity($builtprops, $required)
    {

        $logErr = [];
        foreach ($builtprops as $k => $builtprop) {
            if (!is_array($builtprop)) {
                $logErr[] = 'Unvalid schema property: ' . $k . '; ' . $builtprop;
            }
        }
        if (!empty($required)) {
            foreach ($required as $propk) {
                if (!array_key_exists($propk, $builtprops)) {
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
