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
        $url = 'http://127.0.0.1:5001';
        $url = 'https://ipfs.infura.io:5001';
        $folderName = 'test3';

        $fileNames = $this->scanDirectories($compAL);
        echo "<pre>";print_r($fileNames);


        foreach ($fileNames as $fileName) {
            $arg = [
                'file' => "@".$fileName
            ];

            $resp = $curl->post($url.'/api/v0/add', $arg);

            echo $resp."<BR><BR>";
        }

        

exit;

        $resp = $curl->post($url.'/api/v0/files/mkdir?arg=/$folderName/images&parents=true');

        echo "<pre>";print_r($resp);




        $resp = $curl->post($url.'/api/v0/files/cp?arg=/ipfs/QmUoXSbfqmvLaHD1Bpta6YgBWYBL656QpNZXGyDWAnSNpz&arg=/$folderName/index.html');
        echo "<pre>";print_r($resp);
        $resp = $curl->post($url.'/api/v0/files/cp?arg=/ipfs/QmdJY8hopM99ZscVrNYaeDEScMTU1Ea4Q8RzvXSRjJ6ANt&arg=/$folderName/images/nico1.jpeg');
        echo "<pre>";print_r($resp);
        $resp = $curl->post($url.'/api/v0/files/cp?arg=/ipfs/QmPPKy8kdfEWqSqdRFdPcFF5svqZ9NBi8UC1tZDmq1B94D&arg=/$folderName/images/nico2.jpeg');
        echo "<pre>";print_r($resp);

        $resp = $curl->post($url.'/api/v0/files/stat?arg=/$folderName');
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

    public function actionUpload() {

        $curl = new Curl();

        

        $url = 'https://ipfs.infura.io:5001';
        //$url = 'http://127.0.0.1:5001';

        $arg = [
                'file' => "@".$_FILES['file']['tmp_name']
        ];

        $resp = $curl->post($url.'/api/v0/add?wrap-with-directory=true', $arg);
        //echo "<pre>";print_r($_FILES);
        //$response['source'] = 'https://static.wikia.nocookie.net/wikimemia/images/a/af/Me_Gusta.png';
        //echo "<pre>";print_r(json_decode($resp, true));
        $hashes = explode("\n",$resp);
        $ipfsResponse  = json_decode($hashes[0], true);
        //$response['source'] = "https://cloudflare-ipfs.com/ipfs/$resp['Hash']";
        //echo "<pre>";print_r($ipfsResponse);
        $response['source'] = "https://ipfs.io/ipfs/".$ipfsResponse['Hash'];
        echo json_encode($response);
    }

    public function actionUploadMetaAndSecret() {
        $data = json_decode(file_get_contents('php://input'), true);    
        echo "<pre>";print_r($data);


        $metadata['name'] = $data['name'];
        $metadata['image'] = $data['image'];
        $metadata['external_link'] = $data['external_link'];
        $metadata['seller_fee_basis_points'] = $data['seller_fee_basis_points'];
        $metadata['fee_recipient'] = $data['fee_recipient'];

        $arg = [
                'path' => "@test.json",
                'content' => json_encode($metadata)
        ];

        $url = 'https://ipfs.infura.io:5001';
        $curl = new Curl();
        $resp = $curl->post($url.'/api/v0/add?wrap-with-directory=true', $arg);



        echo $resp;

        /*
        if (isset($data['images']) && !empty($data['images'])) {
            $ipfsImages = [];
            foreach ($data['images']) {
                
            }
        }
        */
        

    }

    

}
