<?php
require dirname(__FILE__).'/../core/init.php';

class SocialRelation{
    public $id = 0;
    public $name = '';
    public $position = '';
    public $source = 0;
    public $weight = 0;
    public $target = 0;

    public function __construct(){
    }

    public function getData(){
        $dbN = new db();
        $nodes = $dbN -> getAll("nodes");
        $dbE = new db();
        $edges = $dbE -> getAll("edges");
        $data['nodes'] = $nodes;
        $data['edges'] = $edges;
        $json_data =  json_encode($data);
        return $json_data;
    }
}
