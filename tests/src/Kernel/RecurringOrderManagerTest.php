<?php

namespace Drupal\Tests\commerce_recurring\Kernel;

use Drupal\commerce_payment\Exception\HardDeclineException;
use Drupal\commerce_price\Price;
use Drupal\commerce_recurring\Entity\Subscription;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * @coversDefaultClass \Drupal\commerce_recurring\RecurringOrderManager
 * @group commerce_recurring
 */
class RecurringOrderManagerTest extends RecurringKernelTestBase {

  /**
   * A test subscription.
   *
   * @var \Drupal\commerce_recurring\Entity\SubscriptionInterface
   */
  protected $subscription;

  /**
   * The recurring order manager.
   *
   * @var \Drupal\commerce_recurring\RecurringOrderManagerInterface
   */
  protected $recurringOrderManager;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $subscription = Subscription::create([
      'type' => 'product_variation',
      'store_id' => $this->store->id(),
      'billing_schedule' => $this->billingSchedule,
      'uid' => $this->user,
      'payment_method' => $this->paymentMethod,
      'purchased_entity' => $this->variation,
      'title' => $this->variation->getOrderItemTitle(),
      'quantity' => '2',
      'unit_price' => new Price('2', 'USD'),
      'state' => 'pending',
      'starts' => strtotime('2017-02-24 17:30:00'),
    ]);
    $subscription->save();
    $this->subscription = $this->reloadEntity($subscription);

    $this->recurringOrderManager = $this->container->get('commerce_recurring.order_manager');
  }

  /**
   * @covers ::ensureOrder
   * @covers ::collectSubscriptions
   */
  public function testEnsureOrder() {
    $order = $this->recurringOrderManager->ensureOrder($this->subscription);
    /** @var \Drupal\commerce_recurring\Plugin\Field\FieldType\BillingPeriodItem $billing_period_item */
    $billing_period_item = $order->get('billing_period')->first();
    $billing_period = $billing_period_item->toBillingPeriod();

    $this->assertTrue($this->subscription->hasOrder($order));
    $this->assertEmpty($this->subscription->getRenewedTime());
    $this->assertEquals('recurring', $order->bundle());
    $this->assertEquals($this->subscription->getStoreId(), $order->getStoreId());
    $this->assertEquals($this->user->id(), $order->getCustomerId());
    $this->assertEquals($this->billingSchedule->id(), $order->get('billing_schedule')->target_id);
    $this->assertEquals('draft', $order->getState()->value);

    $this->assertTrue($order->hasItems());
    $order_items = $order->getItems();
    $order_item = reset($order_items);
    /** @var \Drupal\commerce_recurring\BillingPeriod $order_item_billing_period */
    $order_item_billing_period = $order_item->get('billing_period')->first()->toBillingPeriod();

    $this->assertEquals('recurring_product_variation', $order_item->bundle());
    $this->assertEquals($this->subscription->getTitle(), $order_item->getTitle());
    $this->assertEquals($this->subscription->getQuantity(), $order_item->getQuantity());
    $this->assertEquals($this->variation, $order_item->getPurchasedEntity());
    // The subscription was created mid-cycle, the unit price should be
    // half the usual due to proration.
    $this->assertEquals($this->subscription->getUnitPrice()->divide('2'), $order_item->getUnitPrice());
    $this->assertEquals(new DrupalDateTime('2017-02-24 17:30:00'), $order_item_billing_period->getStartDate());
    $this->assertEquals($billing_period->getEndDate(), $order_item_billing_period->getEndDate());
    $this->assertEquals(3600, $billing_period->getDuration());
  }

  /**
   * @covers ::renewOrder
   */
  public function testCloseOrderWithoutPaymentMethod() {
    $this->subscription->set('payment_method', NULL);
    $this->subscription->save();

    $this->setExpectedException(HardDeclineException::class, 'Payment method not found.');
    $order = $this->recurringOrderManager->ensureOrder($this->subscription);
    $this->recurringOrderManager->closeOrder($order);
  }

  /**
   * @covers ::closeOrder
   */
  public function testCloseOrder() {
    $order = $this->recurringOrderManager->ensureOrder($this->subscription);
    $this->recurringOrderManager->closeOrder($order);

    $this->assertEquals('completed', $order->getState()->value);
    /** @var \Drupal\commerce_payment\PaymentStorageInterface $payment_storage */
    $payment_storage = $this->container->get('entity_type.manager')->getStorage('commerce_payment');
    $payments = $payment_storage->loadMultipleByOrder($order);
    $this->assertCount(1, $payments);
    /** @var \Drupal\commerce_payment\Entity\PaymentInterface $payment */
    $payment = reset($payments);
    $this->assertEquals($this->paymentGateway->id(), $payment->getPaymentGatewayId());
    $this->assertEquals($this->paymentMethod->id(), $payment->getPaymentMethodId());
    $this->assertEquals($order->id(), $payment->getOrderId());
    $this->assertEquals($order->getTotalPrice(), $payment->getAmount());
    $this->assertEquals('completed', $payment->getState()->value);
  }

  /**
   * @covers ::renewOrder
   */
  public function testRenewOrder() {
    $order = $this->recurringOrderManager->ensureOrder($this->subscription);
    $next_order = $this->recurringOrderManager->renewOrder($order);
    /** @var \Drupal\commerce_recurring\Plugin\Field\FieldType\BillingPeriodItem $billing_period_item */
    $billing_period_item = $order->get('billing_period')->first();
    $billing_period = $billing_period_item->toBillingPeriod();
    /** @var \Drupal\commerce_recurring\Plugin\Field\FieldType\BillingPeriodItem $next_billing_period_item */
    $next_billing_period_item = $next_order->get('billing_period')->first();
    $next_billing_period = $next_billing_period_item->toBillingPeriod();

    $this->subscription = $this->reloadEntity($this->subscription);
    $this->assertTrue($this->subscription->hasOrder($order));
    $this->assertTrue($this->subscription->hasOrder($next_order));
    $this->assertNotEmpty($this->subscription->getRenewedTime());

    $this->assertEquals($billing_period->getEndDate(), $next_billing_period->getStartDate());
    $this->assertEquals($order->bundle(), $next_order->bundle());
    $this->assertEquals($order->getStoreId(), $next_order->getStoreId());
    $this->assertEquals($order->getCustomerId(), $next_order->getCustomerId());
    $this->assertEquals($order->get('billing_schedule')->target_id, $next_order->get('billing_schedule')->target_id);
    $this->assertEquals('draft', $next_order->getState()->value);

    $this->assertTrue($next_order->hasItems());
    $order_items = $next_order->getItems();
    $order_item = reset($order_items);
    $this->assertEquals('recurring_product_variation', $order_item->bundle());
    $this->assertEquals($this->subscription->getTitle(), $order_item->getTitle());
    $this->assertEquals($this->subscription->getQuantity(), $order_item->getQuantity());
    $this->assertEquals($this->subscription->getUnitPrice(), $order_item->getUnitPrice());
    $this->assertEquals($this->variation, $order_item->getPurchasedEntity());
    $this->assertEquals($next_billing_period, $order_item->get('billing_period')->first()->toBillingPeriod());
    $this->assertEquals(3600, $next_billing_period->getDuration());
  }

}
