<?php

namespace Drupal\os2web_rest_api\Plugin\views\filter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\taxonomy\VocabularyStorageInterface;
use Drupal\views\Plugin\views\filter\ManyToOne;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Filter by term name.
 *
 * @ingroup views_filter_handlers
 *
 * @ViewsFilter("os2web_rest_taxonomy_index_term_name")
 */
class TaxonomyIndexTermName extends ManyToOne {

  /**
   * The vocabulary storage.
   *
   * @var \Drupal\taxonomy\VocabularyStorageInterface
   */
  protected $vocabularyStorage;

  /**
   * The term storage.
   *
   * @var \Drupal\taxonomy\TermStorageInterface
   */
  protected $termStorage;

  /**
   * Constructs a TaxonomyIndexTid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\taxonomy\VocabularyStorageInterface $vocabulary_storage
   *   The vocabulary storage.
   * @param \Drupal\taxonomy\TermStorageInterface $term_storage
   *   The term storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, VocabularyStorageInterface $vocabulary_storage, TermStorageInterface $term_storage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->vocabularyStorage = $vocabulary_storage;
    $this->termStorage = $term_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('taxonomy_vocabulary'),
      $container->get('entity_type.manager')->getStorage('taxonomy_term')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasExtraOptions() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['vid'] = ['default' => ''];
    $options['limit'] = ['default' => TRUE];
    $options['argument'] = ['default' => ''];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposeForm(&$form, FormStateInterface $form_state) {
    parent::buildExposeForm($form, $form_state);

    // Hide unused value on exposed filter.
    $form['value']['#access'] = FALSE;
    $form['admin_label']['#access'] = FALSE;
    $hidden_exposed_elements = [
      'required',
      'label',
      'description',
      'use_operator',
      'operator_id',
      'multiple',
      'remember',
      'reduce',
      'remember_roles',
      'operator_limit_selection',
      'operator_list',
    ];
    foreach ($hidden_exposed_elements as $element) {
      if (empty($form['expose'][$element])) {
        continue;
      }
      $form['expose'][$element]['#access'] = FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildExtraOptionsForm(&$form, FormStateInterface $form_state) {
    $vocabularies = $this->vocabularyStorage->loadMultiple();
    $options = [];
    foreach ($vocabularies as $voc) {
      $options[$voc->id()] = $voc->label();
    }

    if ($this->options['limit']) {
      // We only do this when the form is displayed.
      if (empty($this->options['vid'])) {
        $first_vocabulary = reset($vocabularies);
        $this->options['vid'] = $first_vocabulary->id();
      }

      if (empty($this->definition['vocabulary'])) {
        $form['vid'] = [
          '#type' => 'radios',
          '#title' => $this->t('Vocabulary'),
          '#options' => $options,
          '#description' => $this->t('Select which vocabulary to show terms for in the regular options.'),
          '#default_value' => $this->options['vid'],
        ];
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function valueForm(&$form, FormStateInterface $form_state) {
    $vocabulary = $this->vocabularyStorage->load($this->options['vid']);
    if (empty($vocabulary) && $this->options['limit']) {
      $form['markup'] = [
        '#markup' => '<div class="js-form-item form-item">' . $this->t('An invalid vocabulary is selected. Please change it in the options.') . '</div>',
      ];
      return;
    }
    $input_argument = $this->options['expose']['identifier'];
    $user_input = $form_state->getUserInput();
    $tids = [];
    if (!empty($user_input[$input_argument])) {
      $term_names = explode(',', $user_input[$input_argument]);
      /** @var \Drupal\taxonomy\Entity\Term $term */
      foreach ($term_names as $term_name) {
        $terms = $this->termStorage->loadByProperties([
          'name' => strip_tags(trim($term_name)),
          'vid' => $vocabulary->id(),
        ]);
        // In case taxonomy term not found we pass "-1" as tid.
        if (empty($terms)) {
          $tids[] = -1;
          continue;
        }
        $term = reset($terms);
        $tids[] = $term->id();
      }
    }
    $form['value'] = [
      '#type' => 'textfield',
      '#default_value' => $tids,
    ];
    $form_state->setUserInput(['tids_by_names' => $tids]);
  }

  /**
   * {@inheritdoc}
   */
  protected function valueSubmit($form, FormStateInterface $form_state) {
    // prevent array_filter from messing up our arrays in parent submit.
  }

  /**
   * {@inheritdoc}
   */
  public function calculateDependencies() {
    $dependencies = parent::calculateDependencies();

    $vocabulary = $this->vocabularyStorage->load($this->options['vid']);
    $dependencies[$vocabulary->getConfigDependencyKey()][] = $vocabulary->getConfigDependencyName();

    return $dependencies;
  }

}
