<?php

namespace Drupal\photo_album\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Photo edit forms.
 *
 * @ingroup photo_album
 */
class PhotoForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status = parent::save($form, $form_state);

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('The photo has been added.'));
        break;

      default:
        drupal_set_message($this->t('The photo has been saved.'));
    }
    $form_state->setRedirect('entity.photo.canonical', ['photo' => $this->entity->id()]);
  }

}
