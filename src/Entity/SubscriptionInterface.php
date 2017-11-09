<?php

namespace Drupal\commerce_recurring\Entity;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_payment\Entity\PaymentMethodInterface;
use Drupal\commerce_price\Price;
use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\user\UserInterface;

/**
 * Defines the interface for subscriptions.
 */
interface SubscriptionInterface extends ContentEntityInterface, PurchasableEntityInterface {

  /**
   * Gets the subscription type.
   *
   * @return \Drupal\commerce_recurring\Plugin\Commerce\SubscriptionType\SubscriptionTypeInterface
   *   The subscription type.
   */
  public function getType();

  /**
   * Gets the store.
   *
   * @return \Drupal\commerce_store\Entity\StoreInterface
   *   The store.
   */
  public function getStore();

  /**
   * Gets the store ID.
   *
   * @return int
   *   The store ID.
   */
  public function getStoreId();

  /**
   * Gets the billing schedule.
   *
   * @return \Drupal\commerce_recurring\Entity\BillingScheduleInterface
   *   The billing schedule.
   */
  public function getBillingSchedule();

  /**
   * Sets the billing schedule.
   *
   * @param \Drupal\commerce_recurring\Entity\BillingScheduleInterface $billing_schedule
   *   The billing schedule.
   *
   * @return $this
   */
  public function setBillingSchedule(BillingScheduleInterface $billing_schedule);

  /**
   * Gets the customer.
   *
   * @return \Drupal\user\UserInterface
   *   The customer.
   */
  public function getCustomer();

  /**
   * Sets the customer.
   *
   * @param \Drupal\user\UserInterface $account
   *   The customer.
   *
   * @return $this
   */
  public function setCustomer(UserInterface $account);

  /**
   * Gets the customer ID.
   *
   * @return int
   *   The customer ID.
   */
  public function getCustomerId();

  /**
   * Sets the customer ID.
   *
   * @param int $uid
   *   The customer ID.
   *
   * @return $this
   */
  public function setCustomerId($uid);

  /**
   * Gets the payment method.
   *
   * @return \Drupal\commerce_payment\Entity\PaymentMethodInterface|null
   *   The payment method, or NULL.
   */
  public function getPaymentMethod();

  /**
   * Sets the payment method.
   *
   * @param \Drupal\commerce_payment\Entity\PaymentMethodInterface $payment_method
   *   The payment method.
   *
   * @return $this
   */
  public function setPaymentMethod(PaymentMethodInterface $payment_method);

  /**
   * Gets the payment method ID.
   *
   * @return int|null
   *   The payment method ID, or NULL.
   */
  public function getPaymentMethodId();

  /**
   * Gets whether the subscription has a purchased entity.
   *
   * @return bool
   *   TRUE if the subscription has a purchased entity, FALSE otherwise.
   */
  public function hasPurchasedEntity();

  /**
   * Gets the purchased entity.
   *
   * @return \Drupal\commerce\PurchasableEntityInterface|null
   *   The purchased entity, or NULL.
   */
  public function getPurchasedEntity();

  /**
   * Sets the purchased entity.
   *
   * @param \Drupal\commerce\PurchasableEntityInterface $purchased_entity
   *   The purchased entity.
   *
   * @return $this
   */
  public function setPurchasedEntity(PurchasableEntityInterface $purchased_entity);

  /**
   * Gets the purchased entity ID.
   *
   * @return int|null
   *   The purchased entity ID, or NULL.
   */
  public function getPurchasedEntityId();

  /**
   * Gets the subscription amount.
   *
   * @return \Drupal\commerce_price\Price
   *   The subscription amount.
   */
  public function getAmount();

  /**
   * Sets the subscription amount.
   *
   * @param \Drupal\commerce_price\Price $amount
   *   The subscription amount.
   *
   * @return $this
   */
  public function setAmount(Price $amount);

  /**
   * Gets the subscription state.
   *
   * @return \Drupal\state_machine\Plugin\Field\FieldType\StateItemInterface
   *   The subscription state.
   */
  public function getState();

  /**
   * Gets the created timestamp.
   *
   * @return int
   *   The created timestamp.
   */
  public function getCreatedTime();

  /**
   * Sets the created timestamp.
   *
   * @param int $timestamp
   *   The created timestamp.
   *
   * @return $this
   */
  public function setCreatedTime($timestamp);

  /**
   * Gets the start timestamp.
   *
   * @return int
   *   The start timestamp.
   */
  public function getStartTime();

  /**
   * Sets the start timestamp.
   *
   * @param int $timestamp
   *   The start timestamp.
   *
   * @return $this
   */
  public function setStartTime($timestamp);

  /**
   * Gets the end timestamp.
   *
   * @return int
   *   The end timestamp.
   */
  public function getEndTime();

  /**
   * Sets the end timestamp.
   *
   * @param int $timestamp
   *   The end timestamp.
   *
   * @return $this
   */
  public function setEndTime($timestamp);

}
