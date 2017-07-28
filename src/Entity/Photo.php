<?php

namespace Drupal\photo_album\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\file\FileInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Photo entity.
 *
 * @ingroup photo_album
 *
 * @ContentEntityType(
 *   id = "photo",
 *   label = @Translation("Photo"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "views_data" = "Drupal\views\EntityViewsData",
 *
 *     "form" = {
 *       "default" = "Drupal\photo_album\Form\PhotoForm",
 *       "add" = "Drupal\photo_album\Form\PhotoForm",
 *       "delete" = "Drupal\Core\Entity\ContentEntityDeleteForm",
 *     },
 *     "access" = "Drupal\photo_album\PhotoAccessControlHandler",
 *   },
 *   base_table = "photo_album",
 *   admin_permission = "administer photo entities",
 *   fieldable = FALSE,
 *   translatable = FALSE,
 *   entity_keys = {
 *     "id" = "pid",
 *     "uuid" = "uuid",
 *     "uid" = "uid",
 *   },
 *   links = {
 *     "canonical" = "/photo/{photo}",
 *     "add-form" = "/photo/add",
 *     "delete-form" = "/photo/{photo}/delete",
 *   },
 * )
 */
class Photo extends ContentEntityBase implements PhotoInterface {

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'uid' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getImage() {
    return $this->get('image')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function setImage(FileInterface $image) {
    $this->set('image', $image->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getUserRate($uid) {
    $rate = \Drupal::database()->select('photo_album_rate', 'r')
      ->fields('r', ['rate'])
      ->condition('r.uid', $uid)
      ->condition('r.pid', $this->id())
      ->execute()
      ->fetchField();

    return (int) $rate;
  }

  /**
   * @inheritDoc
   */
  public function getAverageRating() {
    return $this->get('average_rating')->value;
  }

  /**
   * @inheritDoc
   */
  public function getRatesAmount() {
    return $this->get('rates_amount')->value;
  }

  /**
   * @inheritDoc
   */
  public function getTotalRating() {
    return $this->get('total_rating')->value;
  }

  /**
   * @inheritDoc
   */
  public function rate($uid, $rating) {
    $users_rate = $this->getUserRate($uid);

    \Drupal::database()->merge('photo_album_rate')
      ->keys([
        'pid' => $this->id(),
        'uid' => $uid,
      ])
      ->fields(['rate' => $rating])
      ->execute();

    // Update rating info directly by db query to avoid collisions when several users rate one photo at the same time.
    $rating_diff = $rating - $users_rate;
    $query = \Drupal::database()->update('photo_album')
      ->condition('pid', $this->id())
      ->expression('total_rating', 'total_rating + :rating', [':rating' => $rating_diff]);

    if (!$users_rate) {
      $query->expression('rates_amount', 'rates_amount + 1');
    }

    $query->expression('average_rating', 'total_rating / rates_amount');
    $query->execute();

    // Clearing cache as we just updated entity table directly by db query.
    $storage = $this->entityTypeManager()->getStorage('photo');
    $storage->resetCache([$this->id()]);
    $this->postSave($storage);

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The author of the photo.'))
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setDisplayOptions('view', [
        'type' => 'hidden',
      ])
      ->setDisplayOptions('form', [
        'type' => 'hidden',
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['image'] = BaseFieldDefinition::create('image')
      ->setLabel(t('Photo'))
      ->setRequired(TRUE)
      ->setSettings([
        'file_directory' => 'photos',
        'alt_field_required' => FALSE,
        'file_extensions' => 'png jpg jpeg',
      ])
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'image',
        'weight' => 0,
        'settings' => [
          'image_link' => 'content',
          'image_style' => 'large',
        ]
      ])
      ->setDisplayOptions('form', [
        'label' => 'hidden',
        'type' => 'image_image',
        'weight' => 0,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['total_rating'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Total rating'))
      ->setDescription(t('Total rating for the photo.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDefaultValue(0);

    $fields['rates_amount'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Rates amount'))
      ->setDescription(t('Amount of users who rated the photo.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDefaultValue(0);

    $fields['average_rating'] = BaseFieldDefinition::create('float')
      ->setLabel(t('Average rating'))
      ->setDescription(t('Average photo rating.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE)
      ->setDefaultValue(0);

    return $fields;
  }

}
