<?php

namespace Drupal\photo_album\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\photo_album\Entity\Photo;
use Symfony\Component\DependencyInjection\ContainerInterface;
use \Drupal\Core\Session\AccountProxy;

/**
 * Builds form for rating a photo.
 *
 * {@ingroup} photo_album
 */
class PhotoRateForm extends FormBase {

  /**
   * Drupal\Core\Session\AccountProxy definition.
   *
   * @var AccountProxy $currentUser
   */
  protected $currentUser;

  /**
   * PhotoRateForm constructor.
   */
  public function __construct(AccountProxy $current_user) {
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'photo_rate_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Photo $photo = NULL) {
    $form['photo_id'] = [
      '#type' => 'value',
      '#value' => $photo->id(),
    ];

    $range = range(1, 5);
    $form['rate'] = [
      '#type' => 'radios',
      '#title' => $this->t('Your rate:'),
      '#options' => array_combine($range, $range),
      '#required' => TRUE,
    ];

    if ($user_rate = $photo->getUserRate($this->currentUser->id())) {
      $form['rate']['#default_value'] = $user_rate;
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Rate'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $photo = Photo::load($form_state->getValue('photo_id'));
    $photo->rate($this->currentUser->id(), $form_state->getValue('rate'));
    drupal_set_message($this->t("You've successfully rated the photo."));
  }

}
