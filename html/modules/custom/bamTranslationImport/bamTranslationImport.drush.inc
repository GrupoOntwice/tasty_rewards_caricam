<?php

use Drupal\node\Entity\Node;

/**
 * @file Contains the code to generate the custom drush commands.
 */
/**
 * Implements hook_drush_command().
 */
function bamTranslationImport_drush_command() {
  $items = array();
  $items['bam_translation_import'] = [
      'description' => 'create or delete the translations with the imported nodes',
      'arguments' => [
          'process' => 'create or delete',
      ],
      'drupal dependencies' => ['bamTranslationImport'],
      'aliases' => ['bam_ti'],
  ];
  return $items;
}
/**
 * Call back function drush_custom_drush_command_say_hello()
 * The call back function name in the  following format
 *   drush_{module_name}_{item_id_for_command}()
 */
function drush_bamTranslationImport_bam_translation_import($process = '') {

    $bamconf = \Drupal::config('bamTranslationImport.settings')->get('bamTranslationImport');
    $translation_fields = $bamconf['translation_fields'];
    $translation_language = $bamconf['translation_language'];
    $node_array = $bamconf['nid'];
    $count = count($node_array);

    if($count){

        $create_count = 0;
        $reset_count = 0;
        $delete_imported_fr_count = 0;
        $delete_imported_en_count = 0;
        $delete_count = 0;

        if( $process == 'create' ){

            foreach( $node_array as $node_line ){

                $node = node_split($node_line);


                if( $node['original']['node']->hasTranslation($translation_language) ){

                    $node['original']['node']->removeTranslation($translation_language);
                    $reset_count++;
                }

                add_translation($node,$translation_fields);
                $create_count++;

                delete_imported_translation_node($node);
                $delete_imported_fr_count++;
            }

            drush_print('Create success. STATUS: '.$create_count.' created, '.$reset_count.' reset, '.$delete_imported_fr_count.' imported_fr_deleted');

        }elseif( $process == 'delete_translation' ){

            foreach( $node_array as $node_line ){

                $node = node_split($node_line);

                if( $node['original']['node']->hasTranslation($translation_language) ){

                    $node['original']['node']->removeTranslation($translation_language);
                    $node['original']['node']->save();
                    $delete_count++;
                }
            }

            drush_print('Delete success. STATUS: '.$delete_count.' translation deleted');

        }elseif( $process == 'delete_fr' ){

            foreach( $node_array as $node_line ){

                $node = node_split($node_line);

                delete_imported_translation_node($node);
                $delete_imported_fr_count++;
            }

            drush_print('Delete success. STATUS: '.$delete_imported_fr_count.' French nodes deleted');

        }elseif( $process == 'delete_en' ){

            foreach( $node_array as $node_line ){

                $node = node_split($node_line);

                delete_imported_original_node($node);
                $delete_imported_en_count++;
            }

            drush_print('Delete success. STATUS: '.$delete_imported_en_count.' English nodes deleted');

        }else{
            drush_print('Please input parameter "create" or "delete_translation" or "delete_en" or "delete_fr" .');
        }
    }else{
        drush_print('There is no nid settings on config file.');
    }

}

function node_split($node_line){

    $node_info = explode("-", $node_line);

    $original_node_id = $node_info[0];
    $original_node = Node::load($original_node_id);

    $translation_node_id = $node_info[1];
    $translation_node = Node::load($translation_node_id);

    $node = array(
        'original' => array(
            'id'=>$original_node_id,
            'node'=>$original_node
        ),
        'translation' => array(
            'id'=>$translation_node_id,
            'node'=>$translation_node
        )
    );

    return $node;
}


function add_translation($node,$translation_fields){

    $entitytype_manager = \Drupal::service('entity_type.manager');
    $storageNode = $entitytype_manager->getStorage('node');

    // get original node
    $node_original = $storageNode->load($node['original']['id']);
    $entity_array = $node_original->toArray();

    // get translation node
    $node_translation = $storageNode->load($node['translation']['id']);
    $entity_translation_array = $node_translation->toArray();

    // update the translation content
    $translation_fields_array = explode(',', $translation_fields);
    foreach($translation_fields_array as $translation_fields_item){
        $entity_array[$translation_fields_item] = $entity_translation_array[$translation_fields_item];
    }

    // add translation
    $node_original->addTranslation('fr', $entity_array)->save();

}


function delete_imported_translation_node($node){
    $entitytype_manager = \Drupal::service('entity_type.manager');
    $storageNode = $entitytype_manager->getStorage('node');
    $node_translation = $storageNode->load($node['translation']['id']);
    $node_translation->delete();
}

function delete_imported_original_node($node){
    $entitytype_manager = \Drupal::service('entity_type.manager');
    $storageNode = $entitytype_manager->getStorage('node');
    $node_original = $storageNode->load($node['original']['id']);
    $node_original->delete();
}