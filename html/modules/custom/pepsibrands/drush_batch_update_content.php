#!/usr/bin/env drush

<?php

$args = [];

while($arg = drush_shift() ){
    $args[] = $arg;
}

 if(count($args) == 1) {

    $brand = $args[0];
    batch_update_content($brand);
 }