<?php

$data = '
400530
';

$result = preg_replace("/(\n)|(\s)|(\t)|(\')|(')|(，)/" ,',' ,$data);

echo $result;