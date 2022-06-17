#!/usr/bin/env drush

<?php

$update_subbrand_field = true;
$nids = [2159,1310,1182,1651,1662,1667,1658,574,1479,594,2239,2238,2250,1482,1657,2272,2283,2286];

foreach ($nids as $key => $nid) {
    break;

    $recipe_node = \Drupal\node\Entity\Node::load($nid);
    $recipe_node = $recipe_node->getTranslation('en-us');

    $cloned_node = $recipe_node->createDuplicate();
    $cloned_node->field_spring_activation = 1;
    $cloned_node->save();
    $node = \Drupal\node\Entity\Node::load($cloned_node->id());
    $node = $node->getTranslation('en-us');

    $node->title->value = "Spookysnacklab - " . $node->title->value;
    $node->save();
    log_var($cloned_node->id(), " New recipe ID");
}

if ($update_subbrand_field){
    $query = \Drupal::entityQuery('node');
        $query->condition('type', 'recipe');
        $query->condition('field_spring_activation', 1);
        $entity_ids = $query->execute();

        foreach ($entity_ids as $nid) {
            $recipe_node = \Drupal\node\Entity\Node::load($nid);
            $recipe_node->field_sub_brand = "spookysnacklab";
            $recipe_node->save();
            echo "\n Saved recipe ID=  " . $recipe_node->id();
            // log_var($recipe_node->id(), "Recipe node updated ID : ");

        }
}