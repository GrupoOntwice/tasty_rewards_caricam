<?php

namespace Drupal\taxonomy_tree;

use Drupal\Core\Entity\EntityTypeManager;

/**
 * Loads taxonomy terms in a tree
 */
class TaxonomyTermTree {

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * TaxonomyTermTree constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Loads the tree of a vocabulary.
   *
   * @param string $vocabulary
   *   Machine name
   *
   * @return array
   */
  public function load($vocabulary) {
    $terms = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary);
    $tree = [];
    $language = 'fr';
    foreach ($terms as $tree_object) {
      var_dump($tree_object);
      $term = \Drupal\taxonomy\Entity\Term::load($tree_object->tid);
      var_dump($term);
      
      if($term->hasTranslation('fr')){
        $tid = $term->id();
        $translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($term, $language);
        var_dump($translated_term);
      }

      /*
      $trans = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties([
        'vid' => $tree_object->vid,
        'langcode' => $language
      ]);
      */

      var_dump($trans);

      //if($tree_object->hasTranslation($language)){
        //$translated_term = \Drupal::service('entity.repository')->getTranslationFromContext($tree_object, $language);
        $this->buildTree($tree, $translated_term, $vocabulary);
      //}
    }

    return $tree;
  }

  /**
   * Populates a tree array given a taxonomy term tree object.
   *
   * @param $tree
   * @param $object
   * @param $vocabulary
   */
  protected function buildTree(&$tree, $object, $vocabulary) {
    if ($object->depth != 0) {
      return;
    }
    $tree[$object->tid] = $object;
    $tree[$object->tid]->children = [];
    $object_children = &$tree[$object->tid]->children;

    $children = $this->entityTypeManager->getStorage('taxonomy_term')->loadChildren($object->tid);
    if (!$children) {
      return;
    }

    $child_tree_objects = $this->entityTypeManager->getStorage('taxonomy_term')->loadTree($vocabulary, $object->tid);

    foreach ($children as $child) {
      foreach ($child_tree_objects as $child_tree_object) {
        if ($child_tree_object->tid == $child->id()) {
         $this->buildTree($object_children, $child_tree_object, $vocabulary);
        }
      }
    }
  }
}