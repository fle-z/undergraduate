<?php
require_once "SocialRelation.php";

$s = new SocialRelation();
$data = $s -> getdata();
file_put_contents('../Data/SocialRelation.json', $data);
