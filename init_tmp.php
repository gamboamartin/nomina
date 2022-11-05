<?php
require "vendor/autoload.php";

$path_tmp = (new \config\generales())->path_base.'/archivos/tmp';
$del = (new \gamboamartin\plugins\files())->rmdir_recursive(dir: $path_tmp, mismo: true);
print_r($del);