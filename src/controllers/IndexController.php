<?php

namespace src\controllers;

use inc\Controller;
use src\lib\Router;
use src\models\secure\Validator;
use Curl\Curl;

/**
 * Core Index file as Landing Page
 */
class IndexController extends Controller {


    public function __construct() {
        parent::__construct();
        $this->layout = 'main';
    }
    /**
     * Action to test the Index
     * @return Mixed
     */
    
    public function actionIndex() {

        return $this->renderUI('index/index');
    }

    public function actionGetData() {
        $data = ['test' => 'hello'];

        echo json_encode($data);
    }

    public function actionTestInfura() {
        $curl = new Curl();



        $compAL = dirname(__FILE__, 3) . "/upload" ;

/*
        $arg = [
            'arg' => 'QmfYEdMmtzTtAYci3mmku94bAzYFqUFGduTqGZiqgxPdTV'
        ];

        $curl->setOpt('user', '1sZ6OvNsQIlPb6KUCHls43KP9si:6bf5d8620ad220288eddc36fa456b97a');
        $curl->setOpt('Content-Type', 'application/json');
        $resp = $curl->get('https://1sZ6OvNsQIlPb6KUCHls43KP9si:6bf5d8620ad220288eddc36fa456b97a@filecoin.infura.io', $arg);
        echo "d";
        echo $resp;
*/


// QmfYEdMmtzTtAYci3mmku94bAzYFqUFGduTqGZiqgxPdTV


        $fileNames = $this->scanDirectories($compAL);
        echo "<pre>";print_r($fileNames);


        foreach ($fileNames as $fileName) {
            $arg = [
                'file' => "@".$fileName
            ];

            $resp = $curl->post('http://127.0.0.1:5001/api/v0/add', $arg);

            echo $resp."<BR><BR>";
        }





        $resp = $curl->post('http://127.0.0.1:5001/api/v0/files/mkdir?arg=/test1/images&parents=true');

        echo "<pre>";print_r($resp);


        $resp = $curl->post('http://127.0.0.1:5001/api/v0/files/cp?arg=/ipfs/QmUoXSbfqmvLaHD1Bpta6YgBWYBL656QpNZXGyDWAnSNpz&arg=/test1/index.html');
        echo "<pre>";print_r($resp);
        $resp = $curl->post('http://127.0.0.1:5001/api/v0/files/cp?arg=/ipfs/QmdJY8hopM99ZscVrNYaeDEScMTU1Ea4Q8RzvXSRjJ6ANt&arg=/test1/images/nico1.jpeg');
        echo "<pre>";print_r($resp);
        $resp = $curl->post('http://127.0.0.1:5001/api/v0/files/cp?arg=/ipfs/QmPPKy8kdfEWqSqdRFdPcFF5svqZ9NBi8UC1tZDmq1B94D&arg=/test1/images/nico2.jpeg');
        echo "<pre>";print_r($resp);

        $resp = $curl->post('http://127.0.0.1:5001/api/v0/files/stat?arg=/test1');
        echo "<pre>";print_r($resp);

        exit;

        $arg = [
            'file' => "@".$compAL."/images/nico1.jpeg"
        ];
        $resp = $curl->post('http://127.0.0.1:5001/api/v0/add?recursive=true&wrap-with-directory=true', $arg );

        echo $resp;

        exit;

        $arg = [
            'file' => "@".$compAL
        ];echo "<pre>";print_r($arg);

        $headers = [
            "Content-Disposition"=>"form-data; name='file'; filename=".$compAL."", 
        "Content-Type"=>"application/x-directory"
    ];

        $curl->setHeader("Content-Type", "application/x-directory");
        $curl->setHeader("Content-Disposition", "form-data; name='file'; filename=".$compAL."");
        $resp = $curl->post('http://127.0.0.1:5001/api/v0/add?recursive=true&wrap-with-directory=true');

        echo $resp;
        
    }

    public function scanDirectories($rootDir, $allData=array()) {
        // set filenames invisible if you want
        $invisibleFileNames = array(".", "..", ".htaccess", ".htpasswd",".DS_Store");
        // run through content of root directory
        $dirContent = scandir($rootDir);
        foreach($dirContent as $key => $content) {
            // filter all files not accessible
            $path = $rootDir.'/'.$content;
            if(!in_array($content, $invisibleFileNames)) {
                // if content is file & readable, add to array
                if(is_file($path) && is_readable($path)) {
                    // save file name with path
                    $allData[] = $path;
                // if content is a directory and readable, add path and name
                }elseif(is_dir($path) && is_readable($path)) {
                    // recursive callback to open new directory
                    $allData = $this->scanDirectories($path, $allData);
                }
            }
        }
        return $allData;
    }

    public function actionLayout() {
        $layout = [
            "layout" => "gallery",
            "images" => [
                [
                    'id' => 'QmdJY8hopM99ZscVrNYaeDEScMTU1Ea4Q8RzvXSRjJ6ANt',
                    'url' => "https://ipfs.io/ipfs/QmdJY8hopM99ZscVrNYaeDEScMTU1Ea4Q8RzvXSRjJ6ANt?filename=nico1.jpeg"
                ],
                [
                    'id' => "QmPPKy8kdfEWqSqdRFdPcFF5svqZ9NBi8UC1tZDmq1B94D",
                    'url' => "https://ipfs.io/ipfs/QmPPKy8kdfEWqSqdRFdPcFF5svqZ9NBi8UC1tZDmq1B94D?filename=nico2.jpeg"
                ]
            ]
        ];

        echo json_encode($layout);
    }

}
