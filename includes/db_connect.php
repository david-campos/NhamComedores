<?php
include_once dirname(__FILE__).'/db_config/psl-config.php';
$mysqli = new mysqli(HOST, USER, PASSWORD, DATABASE);
// utf8 please
$mysqli->set_charset("utf8");