<?php

namespace Drupal\gtm_datalayer_views\Entity;

use Drupal\gtm_datalayer\Entity\DataLayer;

/**
 * Defines a GTM dataLayer configuration entity class.
 *
 * @ConfigEntityType(
 *   id = "gtm_datalayer_view",
 *   label = @Translation("GTM dataLayer View"),
 *   label_singular = @Translation("GTM dataLayer View"),
 *   label_plural = @Translation("GTM dataLayer Views"),
 *   label_count = @PluralTranslation(
 *     singular = "@count GTM dataLayer View",
 *     plural = "@count GTM dataLayer Views"
 *   ),
 *   admin_permission = "administer gtm datalayer",
 *   handlers = {
 *     "list_builder" = "Drupal\gtm_datalayer_views\DataLayerViewListBuilder",
 *     "form" = {
 *       "add" = "Drupal\gtm_datalayer_views\Form\DataLayerViewAddForm",
 *       "edit" = "Drupal\gtm_datalayer_views\Form\DataLayerViewEditForm",
 *       "delete" = "Drupal\Core\Entity\EntityDeleteView"
 *     },
 *     "route_provider" = {
 *       "default" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider"
 *     },
 *   },
 *   links = {
 *     "add-form" = "/admin/structure/datalayers/views/add",
 *     "edit-form" = "/admin/structure/datalayers/views/{gtm_datalayer_view}",
 *     "delete-form" = "/admin/structure/datalayers/views/{gtm_datalayer_view}/delete",
 *     "enable" = "/admin/structure/datalayers/views/{gtm_datalayer_view}/enable",
 *     "disable" = "/admin/structure/datalayers/views/{gtm_datalayer_view}/disable",
 *     "collection" = "/admin/structure/datalayers/views"
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "status" = "status",
 *     "uuid" = "uuid"
 *   },
 *   config_export = {
 *     "id",
 *     "label",
 *     "description",
 *     "view",
 *     "plugin",
 *     "weight",
 *     "access_conditions",
 *     "access_logic"
 *   }
 * )
 */
class DataLayerView extends DataLayer implements DataLayerViewInterface {

  /**
   * The View ID of the GTM dataLayer.
   *
   * @var string
   */
  protected $view;

  /**
   * {@inheritdoc}
   */
  public function getView() {
    return $this->view;
  }

  /**
   * {@inheritdoc}
   */
  public function setView($view) {
    $this->view = $view;

    return $this;
  }

}
