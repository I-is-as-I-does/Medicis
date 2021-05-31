<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

class MedicisSchema implements MedicisSchema_i
{

    private $MetaMedicis;
    private $MedicisModels;

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->MedicisModels = $MetaMedicis->getMedicisMember('Models');
    }

    public function schBuild($collcId, $src = [])
    {
        $src = $this->MetaMedicis->quickCheckSrc($collcId, $src);
        if (array_key_exists('err', $src)) {
            return $src;
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

        return $this->schWrap($collcId, $required, $props);
    }

    private function schWrap($collcId, $required, $props)
    {
        return [
            '$schema' => "http://json-schema.org/draft-07/schema",
            '$id' => $collcId . '.json',
            'title' => $collcId,
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

            if (empty($info['method'])) {
                return ['err' => 'A MedicisModels method has not been specified for prop n."' . $k + 1 . '"'];
            }
            $method = $info['method'];

            if (empty($info['param'])) {
                $info['param'] = [lcfirst($method)];
            }

            $param = $info['param'];
            $id = $param[0];

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
        if (!empty($required)) {
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
