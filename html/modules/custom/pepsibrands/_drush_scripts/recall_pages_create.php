#!/usr/bin/env drush

<?php

$brands = ['tostitos', 'Fritolayvarietypacks' , 'bare'];

$args = [];

while($arg = drush_shift() ){
    $args[] = $arg;
}

if(count($args) == 1) {

    $brand = $args[0];
    $brand = brand_strcase($brand);
    duplicate_recall_block($brand, "lays");

}

// foreach ($brands as $brand) {
//     $brand = brand_strcase($brand);

//     duplicate_recall_block($brand, "lays");
        
// }
