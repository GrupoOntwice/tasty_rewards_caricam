
<?php
// #!/usr/bin/env drush

$args = [];

// while($arg = drush_shift() ){
    // $args[] = $arg;
// }

 if(count($args) == 1) {

    $brand = $args[0];
   update_brand_coupon_content($brand);
 } else {
   $brands = ['lays', 'doritos', 'quaker', 'tostitos'];
   foreach ($brands as $brand) {
      echo "updating coupon for $brand \n";
      update_brand_coupon_content($brand);
   }
 }



