<?php

namespace Drupal\os2web_rest_api\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Core\Render\Markup;
use Drupal\taxonomy\Entity\Term;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class SettingsForm.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler) {
    parent::__construct($config_factory);
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      'os2web_rest_api.settings',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'os2web_reset_api_settings_form';
  }

  /**
   * Title callback.
   */
  public static function getTitle() {
    return t('OS2Web Rest API Settings');
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['documentation_header'] = [
      '#prefix' => '<h3>',
      '#markup' => $this->t('Documentation'),
      '#suffix' => '</h3>',
    ];

    $form['documentation']= [
      '#prefix' => '<p>',
      '#markup' => $this->t('See original documentation for this module and <a href="https://github.com/os2web/os2web_rest_api#how-does-it-work" target="_blank">how does it work</a>.'),
      '#suffix' => '</p>',
    ];

    $path_array = [
      'OS2WEB Rest list node' => '/admin/structure/views/view/os2web_rest_list_node',
      'OS2WEB Rest list term' => '/admin/structure/views/view/os2web_rest_list_term',
    ];
    $predefined_url = [
      '#theme' => 'item_list',
      '#items' => [],
    ];

    foreach ($path_array as $label => $path) {
      $predefined_url['#items'][$label] = Link::fromTextAndUrl($label, Url::fromUri('internal:' . $path, ['absolute' => TRUE]))->toString();
    }
    $form['urls']= [
      '#prefix' => '<p>',
      '#markup' => $this->t('Predefined views to export list of entities:@list', [
        '@list' => Markup::create(\Drupal::service('renderer')->renderPlain($predefined_url)),
      ]),
      '#suffix' => '</p>',
    ];
    $example_path_array = [];
    $nid = \Drupal::entityQuery('node')->accessCheck(false)->range(0, 1)->execute();
    if (!empty($nid)) {
      $example_path_array['Node'] = str_replace(':nid', reset($nid), '/node/:nid?_format=json');
    }

    $tid = \Drupal::database()->select('taxonomy_index', 'tn')->fields('tn', ['tid'])->isNotNull('nid')->range(0, 1)->execute()->fetchCol();
    if (!empty($tid)) {
      $tid = reset($tid);
      $example_path_array['Nodes list'] = str_replace(':tid', $tid, '/rest/os2web/list/node/:tid');
      $example_path_array['Taxonomy terms list'] = '/rest/os2web/list/term';
      $term = Term::load($tid);
      $vid = $term->bundle();
      $example_path_array['Taxonomy terms list by vocabulary'] = str_replace(':vid', $vid, '/rest/os2web/list/term/:vid');
    }
    else {
      $example_path_array['No examples on nodes and taxonomy terms lists. Create node with taxonomy term reference first'] = '/node/add';
    }
    $example_urls = [
      '#theme' => 'item_list',
      '#items' => [],
    ];

    foreach ($example_path_array as $label => $path) {
      $url = Url::fromUri('internal:' . $path, ['absolute' => TRUE]);
      $example_urls['#items'][$label] = Markup::create($label . ': ' . Link::fromTextAndUrl($url->toString(), $url)->toString());
    }
    $form['example_urls']= [
      '#prefix' => '<p>',
      '#markup' => $this->t('Examples on API-get requests:@list', [
        '@list' => Markup::create(\Drupal::service('renderer')->renderPlain($example_urls)),
      ]),
      '#suffix' => '</p>',
    ];


    $form['auth_header'] = [
      '#prefix' => '<h3>',
      '#markup' => $this->t('Authorization'),
      '#suffix' => '</h3>',
    ];

    if ($this->moduleHandler->moduleExists('basic_auth')) {
      $form['basic_auth'] = [
        '#prefix' => '<p>',
        '#markup' => $this->t('You can configure basic authorization for listing by editing access section in views @node_list_api_url or @term_list_api_url.', [
          '@node_list_api_url' => $predefined_url['#items']['OS2WEB Rest list node'],
          '@term_list_api_url' => $predefined_url['#items']['OS2WEB Rest list term'],
        ]),
        '#suffix' => '</p>',
      ];
    }
    else {
      $form['basic_auth'] = [
        '#prefix' => '<p>',
        '#markup' => $this->t('To configure basic authorization download and activate <a href="https://www.drupal.org/project/basic_auth" target="_blank">basic_auth</a> module.'),
        '#suffix' => '</p>',
      ];
    }


    $form['extensions_header'] = [
      '#prefix' => '<h3>',
      '#markup' => $this->t('Useful extensions'),
      '#suffix' => '</h3>',
    ];

    if ($this->moduleHandler->moduleExists('restui')) {
      $form['restui'] = [
        '#prefix' => '<p>',
        '#markup' => $this->t('Get <a href="/admin/config/services/rest">overview on RESTfull API configuration</a>'),
        '#suffix' => '</p>',
      ];
    }
    else {
      $form['restui'] = [
        '#prefix' => '<p>',
        '#markup' => $this->t('To get overview on RESTfull API configuration you need to download and activate <a href="https://www.drupal.org/project/restui" target="_blank">restui</a> module.'),
        '#suffix' => '</p>',
      ];
    }
    // There is nothing to save.
    // Skipping parent::buildForm($form, $form_state) call.
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // There is nothing to save here at the current moment.
    parent::submitForm($form, $form_state);
  }

}
