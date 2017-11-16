<?php

namespace Drupal\Tests\commerce_recurring;

use Drupal\commerce\PurchasableEntityInterface;
use Drupal\commerce_price\Price;
use Drupal\commerce_recurring\BillingPeriod;
use Drupal\commerce_recurring\Charge;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\KernelTests\KernelTestBase;

/**
 * @coversDefaultClass \Drupal\commerce_recurring\Charge
 * @group commerce_recurring
 */
class ChargeTest extends KernelTestBase {

  /**
   * @covers ::__construct
   */
  public function testMissingProperty() {
    $this->setExpectedException(\InvalidArgumentException::class, 'Missing required property "billing_period".');
    $charge = new Charge([
      'title' => 'My subscription',
      'unit_price' => new Price('99.99', 'USD'),
    ]);
  }

  /**
   * @covers ::__construct
   */
  public function testInvalidPurchasedEntity() {
    $this->setExpectedException(\InvalidArgumentException::class, 'The "purchased_entity" property must be an instance of Drupal\commerce\PurchasableEntityInterface.');
    $charge = new Charge([
      'purchased_entity' => 'INVALID',
      'title' => 'My subscription',
      'unit_price' => new Price('99.99', 'USD'),
      'billing_period' => new BillingPeriod(
        DrupalDateTime::createFromFormat('Y-m-d', '2017-01-01'),
        DrupalDateTime::createFromFormat('Y-m-d', '2017-01-31')
      ),
    ]);
  }

  /**
   * @covers ::__construct
   */
  public function testInvalidUnitPrice() {
    $this->setExpectedException(\InvalidArgumentException::class, 'The "unit_price" property must be an instance of Drupal\commerce_price\Price.');
    $charge = new Charge([
      'title' => 'My subscription',
      'unit_price' => 'INVALID',
      'billing_period' => new BillingPeriod(
        DrupalDateTime::createFromFormat('Y-m-d', '2017-01-01'),
        DrupalDateTime::createFromFormat('Y-m-d', '2017-01-31')
      ),
    ]);
  }

  /**
   * @covers ::__construct
   */
  public function testInvalidBillingPeriod() {
    $this->setExpectedException(\InvalidArgumentException::class, 'The "billing_period" property must be an instance of Drupal\commerce_recurring\BillingPeriod.');
    $charge = new Charge([
      'title' => 'My subscription',
      'unit_price' => new Price('99.99', 'USD'),
      'billing_period' => 'INVALID',
    ]);
  }

  /**
   * @covers ::__construct
   * @covers ::getTitle
   * @covers ::getQuantity
   * @covers ::getUnitPrice
   * @covers ::getBillingPeriod
   */
  public function testCharge() {
    $purchased_entity = $this->prophesize(PurchasableEntityInterface::class)->reveal();
    $billing_period = new BillingPeriod(
      new DrupalDateTime('2017-01-01 00:00:00'),
      new DrupalDateTime('2017-02-01 00:00:00')
    );
    $charge = new Charge([
      'purchased_entity' => $purchased_entity,
      'title' => 'My subscription',
      'quantity' => '2',
      'unit_price' => new Price('99.99', 'USD'),
      'billing_period' => $billing_period,
    ]);

    $this->assertEquals($purchased_entity, $charge->getPurchasedEntity());
    $this->assertEquals('My subscription', $charge->getTitle());
    $this->assertEquals('2', $charge->getQuantity());
    $this->assertEquals(new Price('99.99', 'USD'), $charge->getUnitPrice());
    $this->assertEquals($billing_period, $charge->getBillingPeriod());
  }

}
