<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis\MedicisFamily;

use SSITU\JackTrades\Jack;

class MedicisTransl implements MedicisTransl_i
{

    private $MetaMedicis;
    private $mainTranslPath;
    private $trslprfx = 'collc.'; //@doc: prefix of keys in transl files, to avoid conflicts when possibly merging different transl sources (for offline caching for ex)
    private $propsKey = 'prop.';
    private $namesKey = 'name.';

    public function __construct($MetaMedicis)
    {
        $this->MetaMedicis = $MetaMedicis;
        $this->mainTranslPath = $MetaMedicis->getMedicisMap()->getDir('src/transl');
    }

    public function collcTranslBuild($collcId, $sch = [])
    {
        $sch = $this->MetaMedicis->quickCheckSchema($collcId, $sch = []);
        if (array_key_exists('err', $sch)) {
            return $sch;
        }
        $propIds = array_keys($sch['properties']);
        $rslt = [];
        $rslt['properties'] = $this->prcDoneAndTodo($propIds, $this->propsKey, $collcId);
        $rslt['name'] = $this->prcDoneAndTodo([$collcId], $this->namesKey, $collcId);
        return $rslt;
    }

    public function groupTranslCheck($GroupId)
    {
        return $this->prcDoneAndTodo([$GroupId], $this->namesKey);
    }

    private function fillDoneAndTodo($content, $trkey, $path)
    {
        $rslt = [];
        if (empty($content) || empty($content[$trkey])) {
            $rslt['rslt'] = '[todo]'; //@doc: [] to avoid case of 'todo' translation
        } else {
            $rslt['rslt'] = $content[$trkey];
        }
        return $rslt;
    }

    private function prcDoneAndTodo($itemIds, $trslKey, $saveId = false)
    {

        $files = glob($this->mainTranslPath . 'collections-*.json');
        if (empty($files)) {
            return ["anomaly" => "No translation files found"];
        }
        $rslt = [];
        foreach ($files as $path) {
            $content = Jack::File()->readJson($path);
            $lang = $this->extractLang($path);
            $rslt[$lang] = [];
            $saveStock = [];
            $saveFile = false;
            foreach ($itemIds as $itemId) {
                $trkey = $this->trslprfx . $trslKey . $itemId;
                $subPrc = $this->fillDoneAndTodo($content, $trkey, $path);
                if ($subPrc['rslt'] === '[todo]') {
                    $rslt[$lang]['todo'][] = $itemId;
                    if (!array_key_exists($trkey, $content)) {
                        $content[$trkey] = '';
                        $saveFile = true;
                    }
                } elseif ($saveId !== false) {
                    $saveStock[$trkey] = $subPrc['rslt'];
                }
            }
            if (!empty($rslt[$lang]['todo'])) {
                $rslt[$lang]['todo'] = implode("; ", $rslt[$lang]['todo']);
                if ($saveFile) {
                    ksort($content);
                    $rslt[$lang]['file-update'] = Jack::File()->saveJson($content, $path, true);
                }
            } else {
                $rslt[$lang]['success'] = "all done";
                if ($saveId !== false) {
                    ksort($saveStock);
                    $rslt[$lang]['save-transl'] = $this->MetaMedicis->saveDistFile($saveStock, $saveId, 'transl');
                }
            }
        }
        return $rslt;
    }

    private function extractLang($path)
    {
        $parts = explode('-', basename($path, '.json'));
        return array_pop($parts);
    }

}
