<?php

namespace Drupal\photo_album\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\file\FileInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Photo entities.
 *
 * @ingroup photo_album
 */
interface PhotoInterface extends ContentEntityInterface, EntityOwnerInterface {

  /**
   * Gets the Photo image file.
   *
   * @return \Drupal\file\Entity\File
   */
  public function getImage();

  /**
   * Sets the Photo image file.
   *
   * @param FileInterface $image
   *   Photo image file.
   *
   * @return \Drupal\photo_album\Entity\PhotoInterface
   *   The called Photo entity.
   */
  public function setImage(FileInterface $image);

  /**
   * Gets the user's rate of the photo.
   * If user hasn't rated it, 0 will be returned
   *
   * @return int
   */
  public function getUserRate($uid);

  /**
   * Rates the photo.
   *
   * @param string $uid
   *   User ID who rates the photo.
   * @param int $rating
   *   Integer value from 1 to 5.
   *
   * @return \Drupal\photo_album\Entity\PhotoInterface
   *   The called Photo entity.
   */
  public function rate($uid, $rating);

  /**
   * Gets average rating for the photo.
   *
   * @return float
   *   Average rating of the photo.
   */
  public function getAverageRating();

  /**
   * Gets rates amount.
   *
   * @return int
   *   Rates amount for the photo.
   */
  public function getRatesAmount();

  /**
   * Gets total rating of the photo.
   *
   * @return int
   */
  public function getTotalRating();

  /**
   * Gets the Photo creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Photo.
   */
  public function getCreatedTime();

  /**
   * Sets the Photo creation timestamp.
   *
   * @param int $timestamp
   *   The Photo creation timestamp.
   *
   * @return \Drupal\photo_album\Entity\PhotoInterface
   *   The called Photo entity.
   */
  public function setCreatedTime($timestamp);

}
