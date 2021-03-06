<?php
/* This file is part of Medicis | SSITU | (c) 2021 I-is-as-I-does | MIT License */

namespace SSITU\Medicis;

use SSITU\Euclid\EuclidCompanion;

class MedicisCli
{
    private $Companion;
    private $MedicisMap;
    private $MetaMedicis;

    private $currentMenu;

    private $callableMap;
    private $backToMain = [',' => 'Main Menu' . PHP_EOL];
    private $mainMenu = [1 => 'Collc', 2 => "Group"];
    private $groupOpts = ["all", 'transl'];
    private $collcOpts = ['all', 'transl'];
    private $divider = ' -> ';
    private $groupCallMap;
    private $collcCallMap;

    public function __construct($collectionDirPath, $runBuild = false)
    {
        $this->Companion = EuclidCompanion::inst();
        $this->MedicisMap = new MedicisMap($collectionDirPath);
        $log = $this->MedicisMap->getLog();
        if (!empty($log['err'])) {
            $this->Companion::output($log);
            exit;
        }
        if (!empty($log)) {
            echo PHP_EOL;
            $this->Companion::msg('MedicisMap recorded some events: ', 'yellow');
            $this->Companion::output($log, 'auto');
            echo PHP_EOL;
        }

        $this->MetaMedicis = new MetaMedicis($this->MedicisMap);

        $this->groupCallMap = $this->buildCallableMap(2);
        $this->collcCallMap = $this->buildCallableMap(1);

        $this->callableMap = $this->mainMenu;

        if ($runBuild) {
            return $this->run();
        }
    }

    private function buildCallableMap($idK)
    {
        $mapName = $this->mainMenu[$idK];
        $method = 'getAll' . $mapName . 'Ids';
        $optprop = strtolower($mapName) . 'Opts';
        $opts = $this->$optprop;
        $callableMap = $this->backToMain;
        $items = $this->MedicisMap->$method();

        $sk = 0;
        foreach ($items as $item) {
            $sk++;
            foreach ($opts as $k => $opt) {
                $callableMap[$sk . ($k + 1)] = $item . $this->divider . $opt;
            }
        }
        return $callableMap;

    }

    public function run()
    {
        $this->Companion->set_callableMap($this->callableMap);
        $this->Companion::echoDfltNav();
        $requestk = $this->Companion->printCallableAndListen();

        $this->handleCmd($requestk);
    }

    protected function handleCmd($requestk)
    {
        $request = $this->callableMap[$requestk];

        if (strlen('' . $requestk . '') === 1) {
            if ($requestk == ',') {
                $classProp = 'mainMenu';
            } else {
                $classProp = strtolower($request) . 'CallMap';
            }
            $this->callableMap = $this->$classProp;
            $this->currentMenu = $requestk;
            return $this->run();
        }

        $split = explode($this->divider, $request);
        $Id = $split[0];
        $opt = $split[1];
        if ($opt === 'transl') {
            $member = ucfirst($opt);
            if ($this->currentMenu == 1) {
                $method = 'collcTranslBuild';
            } else {
                $method = 'groupTranslBuild';
            }
        } else {
            $member = $this->mainMenu[$this->currentMenu];
            $method = strtolower($member) . 'Build';
        }
        $build = $this->MetaMedicis->getMedicisMember($member)->$method($Id);
        $requestk = $this->Companion->printRslt($build);
        $this->handleCmd($requestk);
    }
}
