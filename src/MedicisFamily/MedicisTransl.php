<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\Jack\Jack;

class MedicisTransl implements MedicisTransl_i
{

    private $MetaMedicis;
    private $translMap;
    private $propsKey = 'prop';
    private $namesKey = 'name';

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->translMap = $MetaMedicis->getMedicisMap()->getTranslMap();
    }

    public function collcTranslBuild($collcId, $sch = [])
    {
        $sch = $this->MetaMedicis->quickCheckSchema($collcId, $sch = []);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $translJob = [$this->propsKey => array_keys($sch['properties']), $this->namesKey => [$collcId]];
        return $this->prcDoneAndTodo($translJob, $collcId);
    }

    public function groupTranslBuild($GroupId)
    {
        return $this->prcDoneAndTodo([$this->namesKey => [$GroupId]]);

    }

    private function prcDoneAndTodo($translJob, $saveId = false)
    {
        if (empty($this->translMap)) {
            return ["anomaly" => "No translation files found"];
        }
        $rslt = [];
        foreach ($this->translMap as $lang => $path) {
            $content = Jack::File()->readJson($path);
            $doneStock = [];
            $saveFile = false;
            foreach ($translJob as $trslKey => $itemIds) {
                if (!array_key_exists($trslKey, $content)) {
                    $content[$trslKey] = [];
                    $saveFile = true;
                }
                if (empty($content[$trslKey])) {
                    $rslt['todo'][$lang] = $itemIds;
                    foreach ($itemIds as $itemId) {
                        $content[$trslKey][$itemId] = '';
                    }
                    $saveFile = true;
                    continue;
                }

                foreach ($itemIds as $itemId) {
                    if (!array_key_exists($itemId, $content[$trslKey])) {
                        $content[$trslKey][$itemId] = '';
                        $saveFile = true;
                    }
                    if (strlen($content[$trslKey][$itemId]) > 0) {
                        $doneStock[$trslKey][$itemId] = $content[$trslKey][$itemId];
                    } else {
                        $rslt['todo'][$lang][] = $itemId;
                    }
                }
            }
            if (!empty($rslt['todo'][$lang])) {
                $rslt['todo'][$lang] = implode("; ", $rslt['todo'][$lang]);

                if ($saveFile) {
                    $save = Jack::File()->saveJson($content, $path, true);
                    if (array_key_exists('err', $save)) {
                        $rslt['err'][$lang]['file-update'] = $save['err'];
                    } else {
                        $rslt['success'][$lang]['file-update'] = $save['success'];
                    }
                }
            } elseif ($saveId !== false) {
                $finalSave = $this->MetaMedicis->saveDistFile($doneStock, $saveId, 'transl', false, $lang);
                if (array_key_exists('err', $finalSave)) {
                    $rslt['err'][$lang]['save-transl'] = $finalSave['err'];
                } else {
                    $rslt['success'][$lang]['save-transl'] = $finalSave['success'];
                }

            } else {
                $rslt['success'][$lang] = $doneStock;
            }

        }
        return $rslt;
    }

}
