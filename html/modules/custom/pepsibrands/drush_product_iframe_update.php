
<?php 

/*
php vendor/bin/drush php-script modules/custom/pepsibrands/drush_product_iframe_update.php
*/

$args = [];

// while($arg = drush_shift() ){
foreach($extra as $arg) {
    $args[] = $arg;
}

function update_quaker_granola_products(){
    $_brands = [
                'quaker-chewy-granola' => 'chewy',
                'quaker-dipps-granola-bars' => 'dipps',
           ];
    $category_url = 'quaker-chewy-granola';

    echo " UPdating products iframes \n";

    foreach ($_brands as $category_url => $iframe_brand) {
        // code...
        $query = \Drupal::entityQuery('taxonomy_term');
        $query->condition('vid', 'product_categories');
        $query->condition('field_brand', 'quaker');
        $query->condition('field_product_link', $category_url, 'CONTAINS');
        $entity_ids = $query->execute();
        if (!empty($entity_ids)){
            $tids = array_values($entity_ids);
            $product_category_id = $tids[0];
            $query = \Drupal::entityQuery('node');
            $query->condition('type', 'product');
            $query->condition('field_brand', 'quaker');
            $query->condition('field_product_category.entity:taxonomy_term.tid', $product_category_id);
            $product_ids = $query->execute();
            if (!empty($product_ids)){
                $nids = array_values($product_ids);
                foreach ($nids as  $nid) {
                    $node = \Drupal\node\Entity\Node::load($nid);
                    $node->field_iframe_brand->value = $iframe_brand;
                    $node->save();
                }
            }


        }
    }

}

function update_fritolay_products(){
    echo "Updating fritolay products iframe brand \n";
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'product');
    $query->condition('status', 1);
    $query->condition('title', 'FRITO-LAY', 'CONTAINS');
    $query->condition('field_brand', 'Fritolayvarietypacks', '<>');
    $product_ids = $query->execute();
    if (!empty($product_ids)){
        $nids = array_values($product_ids);
        foreach ($nids as  $nid) {
            $node = \Drupal\node\Entity\Node::load($nid);
            $node->field_iframe_brand->value = 'Fritolayvarietypacks';
            $node->save();
        }
    }

}


function update_flaminhot_products(){
    echo "Updating flaminhot products iframe brand \n";
    $query = \Drupal::entityQuery('node');
    $query->condition('type', 'product');
    $query->condition('status', 1);
    // $query->condition('title', 'FRITO-LAY', 'CONTAINS');
    $query->condition('field_brand', 'Flaminhot');
    $product_ids = $query->execute();
    if (!empty($product_ids)){
        $nids = array_values($product_ids);
        foreach ($nids as  $nid) {
            $node = \Drupal\node\Entity\Node::load($nid);
            $title = $node->getTitle();
            $iframe_brand = '';
            if (strpos(strtolower($title), 'cheetos')!== false ){
                $iframe_brand = 'Cheetos';
            }
            if (strpos(strtolower($title), 'ruffles')!== false ){
                $iframe_brand = 'Ruffles';
            }

            if (strpos(strtolower($title), 'doritos')!== false ){
                $iframe_brand = 'Doritos';
            }
            if (!empty($iframe_brand)){
                $node->field_iframe_brand->value = $iframe_brand;
                $node->save();
            }
        }
    }

}

update_quaker_granola_products();
update_fritolay_products();
update_flaminhot_products();

