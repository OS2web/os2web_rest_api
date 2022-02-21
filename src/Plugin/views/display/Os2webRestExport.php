<?php

namespace Drupal\os2web_rest_api\Plugin\views\display;

use Drupal\rest\Plugin\views\display\RestExport;
use Drupal\views\Views;
use Symfony\Component\Routing\RouteCollection;

/**
 * The plugin that handles Data response callbacks for REST resources.
 *
 * @ingroup views_display_plugins
 *
 * @ViewsDisplay(
 *   id = "os2web_rest_export",
 *   title = @Translation("OS2Web REST export"),
 *   help = @Translation("Create a OS2Web REST export resource."),
 *   uses_route = TRUE,
 *   admin = @Translation("OS2Web REST export"),
 *   returns_response = TRUE
 * )
 */
class Os2webRestExport extends RestExport {

  /**
   * {@inheritdoc}
   */
  public function collectRoutes(RouteCollection $collection) {
    parent::collectRoutes($collection);
    $view_id = $this->view->storage->id();
    $display_id = $this->display['id'];

    if ($route = $collection->get("view.$view_id.$display_id")) {
      // OS2Web REST exports should only respond to GET and POST methods.
      $route->setMethods(['GET', 'POST']);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function displaysExposed() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function usesExposed() {
    $this->has_exposed = TRUE;
    return $this->has_exposed;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    // Restore exposed form and blocks.
    $options['exposed_form'] = [
      'contains' => [
        'type' => ['default' => 'basic'],
        'options' => ['default' => []],
      ],
      'merge_defaults' => [$this, 'mergePlugin'],
    ];
    $options['exposed_block'] = ['default' => FALSE];
    return $options;
  }


  /**
   * {@inheritdoc}
   */
  public function optionsSummary(&$categories, &$options) {
    parent::optionsSummary($categories, $options);

    $categories['exposed'] = [
      'title' => $this->t('Exposed form'),
      'column' => 'third',
      'build' => [
        '#weight' => 1,
      ],
    ];
    if ($this->usesExposedFormInBlock()) {
      $options['exposed_block'] = [
        'category' => 'exposed',
        'title' => $this->t('Exposed form in block'),
        'value' => $this->getOption('exposed_block') ? $this->t('Yes') : $this->t('No'),
        'desc' => $this->t('Allow the exposed form to appear in a block instead of the view.'),
      ];
    }

    /** @var \Drupal\views\Plugin\views\exposed_form\ExposedFormPluginInterface $exposed_form_plugin */
    $exposed_form_plugin = $this->getPlugin('exposed_form');
    if (!$exposed_form_plugin) {
      // Default to the no cache control plugin.
      $exposed_form_plugin = Views::pluginManager('exposed_form')->createInstance('basic');
    }

    $exposed_form_str = $exposed_form_plugin->summaryTitle();

    $options['exposed_form'] = [
      'category' => 'exposed',
      'title' => $this->t('Exposed form style'),
      'value' => $exposed_form_plugin->pluginTitle(),
      'setting' => $exposed_form_str,
      'desc' => $this->t('Select the kind of exposed filter to use.'),
    ];

    if ($exposed_form_plugin->usesOptions()) {
      $options['exposed_form']['links']['exposed_form_options'] = $this->t('Exposed form settings for this exposed form style.');
    }

  }

}
