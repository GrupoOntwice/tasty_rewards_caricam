<?php

namespace Drupal\gtm_datalayer_views\Plugin;

use Drupal\gtm_datalayer\Plugin\DataLayerProcessorBase;
use Drupal\views\ViewExecutable;

/**
 * Provides a view base class for a GTM dataLayer Processor.
 */
class DataLayerProcessorViewBase extends DataLayerProcessorBase implements DataLayerProcessorViewBaseInterface {

  /**
   * The view object.
   *
   * @var \Drupal\views\ViewExecutable
   */
  protected $view;

  /**
   * The view's machine name.
   *
   * @var string
   */
  protected $viewId;

  /**
   * {@inheritdoc}
   */
  public function configure(ViewExecutable $view, string $view_id) {
    $this->setView($view);
    $this->setViewId($view_id);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function render() {
    if ($this->currentRequest->attributes->has('exception')) {
      $this->statusCode = $this->currentRequest->attributes->get('exception')->getStatusCode();
      $this->addTag(['status_code'], $this->statusCode);
    }

    if (!$this->isRequestException() && $this->getView() !== NULL) {
      $this->addTag(['views', $this->getViewId()], $this->getViewId());
    }

    return $this->getTags();
  }

  /**
   * Gets the view object.
   *
   * @return \Drupal\views\ViewExecutable|null
   *   The view object.
   */
  protected function getView() {
    return $this->view;
  }

  /**
   * Sets the view object.
   *
   * @param \Drupal\views\ViewExecutable $view
   *   The view object.
   *
   * @return $this
   */
  protected function setView(ViewExecutable $view) {
    $this->view = $view;

    return $this;
  }

  /**
   * Gets the name of the form itself.
   *
   * @return string
   *   The view's name.
   */
  protected function getViewId() {
    return $this->viewId;
  }

  /**
   * Sets the name of the view itself.
   *
   * @param string $view_id
   *   The name of the view itself.
   *
   * @return $this
   */
  protected function setViewId($view_id) {
    $this->viewId = $view_id;

    return $this;
  }

}
