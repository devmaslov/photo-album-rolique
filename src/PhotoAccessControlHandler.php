<?php

namespace Drupal\photo_album;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Photo entity.
 *
 * @see \Drupal\photo_album\Entity\Photo.
 */
class PhotoAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\photo_album\Entity\PhotoInterface $entity */
    switch ($operation) {
      case 'view':
        return AccessResult::allowedIfHasPermission($account, 'view photo entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit photo entities');

      case 'delete':
        $current_user = \Drupal::currentUser();
        if ($current_user->hasPermission('delete own photo entities') && $entity->getOwnerId() == $current_user->id()) {
          return AccessResult::allowed();
        }

        return AccessResult::allowedIfHasPermission($account, 'delete photo entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add photo entities');
  }

}
