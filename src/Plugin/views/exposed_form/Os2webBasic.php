<?php

namespace Drupal\os2web_rest_api\Plugin\views\exposed_form;

use Drupal\views\Plugin\views\display\DisplayPluginBase;
use Drupal\views\Plugin\views\exposed_form\Basic;
use Drupal\views\ViewExecutable;

/**
 * Exposed form plugin that provides a basic exposed form.
 *
 * @ingroup views_exposed_form_plugins
 *
 * @ViewsExposedForm(
 *   id = "os2web_basic",
 *   title = @Translation("OS2Web Basic. GET, POST"),
 *   help = @Translation("OS2Web Basic exposed form. GET, POST")
 * )
 */
class Os2webBasic extends Basic {

  /**
   * {@inheritdoc}
   */
  public function init(ViewExecutable $view, DisplayPluginBase $display, array &$options = NULL) {
    parent::init($view, $display, $options);
    $exposed_input = array_merge(\Drupal::request()->request->all(), \Drupal::request()->query->all());
    $view->setExposedInput($exposed_input);
  }

}
