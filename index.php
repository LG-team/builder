<?php

require 'vendor/autoload.php';

use CSchmitz\HelloWorld\SayHello;

$sayhi = new SayHello;

echo $sayhi->world();
