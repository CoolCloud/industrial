<?php

spl_autoload_register(function ($classname) {
    $fname = "src/" . str_replace(array('\\','_'), '/', $classname) . ".php";
    if (file_exists($fname)) {
        include ($fname);
        if (class_exists($classname)) {
            return true;
        }
    }

    return false;
});
