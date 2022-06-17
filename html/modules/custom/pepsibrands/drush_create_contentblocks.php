#!/usr/bin/env drush

<?php

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
    $args[] = $arg;
}

 if(count($args) == 1) {

    $brand = $args[0];
    if ($brand == 'gameday' || $brand == 'popcorners'){
      create_content_blocks($brand, 'lays', 'coupon');
    } else {
      create_content_blocks($brand);
    }
 }