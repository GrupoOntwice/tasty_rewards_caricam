<?php 

namespace Drupal\pepsibrands\Theme;
 
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\Routing\Route;
use Drupal\Core\Theme\ThemeNegotiatorInterface;
use Drupal\pepsibrands\BrandsThemeResolver;
 
/**
 * Our Gianduja Theme Negotiator
 */
class BrandsThemeNegotiator implements ThemeNegotiatorInterface {
 
  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    $route = $route_match->getRouteObject();
    if (!$route instanceof Route) {
      return FALSE;
    }
    // $option = $route->getOption('_custom_theme');
    // if (!$option) {
    //   return FALSE;
    // }
    // return $option == 'brandstheme';
    return TRUE;
 
  }
 
  /**
   * {@inheritdoc}
   */
  public function determineActiveTheme(RouteMatchInterface $route_match) {
    if ( BrandsThemeResolver::instance()->is_brands_page($route_match) ){
      return 'brandstheme';
    }

    return 'tastytheme';
  }
}