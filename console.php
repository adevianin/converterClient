<?php

require_once 'Converter.php';
require_once 'ConsoleMng.php';

$consoleMng = new ConsoleMng(new Converter('54.201.177.157'));
$consoleMng->start();
