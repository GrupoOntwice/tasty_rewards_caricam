<?php

namespace Drupal\springactivation\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\Component\Serialization\Json;
use Drupal\user\Entity\User;
use Drupal\Core\Url;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Site\Settings;
use Drupal\Core\Datetime\DrupalDateTime;

use Drupal\pepsibrands\BrandsContentImport;
use Drupal\pepsibrands\BrandsContentFilters;


class SpringActivationController extends ControllerBase {

    private $nb_recipes_per_query = 6;
    /**
     * {@inheritdoc}
     */
    public function index(Request $request) {
    	$data = [];
    	//@TODO: Make sure only US pages is valid here
    	$langcode = get_current_langcode();

    	if ($langcode != 'en-us')
    		return new RedirectResponse(\Drupal\Core\Url::fromRoute("system.404")->toString()); 

        $recipes = BrandsContentFilters::instance()->fetch_spring_activation_recipes($this->nb_recipes_per_query, $offset = 0, $langcode);

        $data['recipes'] = $recipes;
        $data['banners'] = $this->fetch_springactivation_banner();
        $data['life_carousel'] = get_active_carousels($brand = null, 'article_spring');
    	
    	return array(
            '#theme' => 'springactivation_landing',
            '#data' => $data,
        );
    }

    public function moreRecipes(Request $request, $offset) {
        $data = [];
        $data['status'] = 'fail';
        $lang = $request->get('lang');
        $recipes = [];
        $nb_recipes = $this->nb_recipes_per_query;
        $recipes = BrandsContentFilters::instance()->fetch_spring_activation_recipes($nb_recipes, $offset);


        if (!empty($recipes)){
            $data['recipes'] = $recipes;
            $data['count'] = count($recipes);
            $skipped = $recipes[count($recipes) - 1]['skipped'];
            $data['new_offset'] = count($recipes) + $offset + $skipped;
        } else {
            $data['recipes'] = '';
            $data['count'] = 0;
        }
        $data['status'] = 'success';
        echo json_encode($data);
        die;
    }

    public function fetch_springactivation_banner(){
        $langcode = get_current_langcode($with_prefix = false);
        $slides = [];
        $query = \Drupal::entityQuery('node');
        $query->condition('type', 'home_page_carousel2');
        $query->condition('status', 1);
        $query->condition('field_carousel_type', "springactivation");
        $query->sort('field_carousel_position', 'ASC');
        $entity_ids = $query->execute();
        if (empty($entity_ids))
            return [];

        $lang_prefix = get_current_langcode();

        $nids = array_values($entity_ids);
        foreach ($nids as $nid) {
            $node = \Drupal\node\Entity\Node::load($nid); 
            if (empty($node))
                continue;
            $entity = [];
            $node_en = $node;
            if (!$node->hasTranslation($langcode)){
                continue;
            }
            $node = \Drupal::service('entity.repository')->getTranslationFromContext($node, $langcode);

            if (!empty($node->field_carousel_image->target_id)){
                $img = \Drupal\file\Entity\File::load($node->field_carousel_image->target_id);
            }

            if (!empty($node->field_mobile_image->target_id)){
                $mobile_img = \Drupal\file\Entity\File::load($node->field_mobile_image->target_id);
                $entity['mobile_image_url'] = !empty($mobile_img)? file_create_url( $mobile_img->getFileUri()) : '';
            }

            // Link : field_cta_text
            $link = "javascript:void(0);";
            if (!empty($node->field_cta_text->uri)){
                $link = $node->field_cta_text->uri;
            }
            $entity['title'] =  $node->field_subtitle->value;
            $entity['body'] =  $node->body->value;
            $entity['link'] = $link ;
            $entity['carousel_type'] =  $node->field_carousel_type->value;
            $entity['nid'] = $node->id();

            $entity['image_url'] = !empty($img)? file_create_url( $img->getFileUri()) : '';
            $slides[] = $entity;
        }
        return $slides;
    }

}