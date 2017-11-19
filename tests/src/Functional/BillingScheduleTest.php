<?php

namespace Drupal\Tests\commerce_recurring\Functional;

use Drupal\commerce_recurring\Entity\BillingSchedule;
use Drupal\commerce_recurring\Entity\BillingScheduleInterface;
use Drupal\Tests\commerce\Functional\CommerceBrowserTestBase;

/**
 * Tests the billing schedule UI.
 *
 * @group commerce_recurring
 */
class BillingScheduleTest extends CommerceBrowserTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'commerce_product',
    'commerce_recurring',
  ];

  /**
   * {@inheritdoc}
   */
  protected function getAdministratorPermissions() {
    return [
      'administer commerce_billing_schedule',
    ] + parent::getAdministratorPermissions();
  }

  /**
   * Tests creating a billing schedule.
   */
  public function testBillingScheduleCreation() {
    $this->drupalGet('admin/commerce/config/billing-schedules');
    $this->getSession()->getPage()->clickLink('Add billing schedule');
    $this->assertSession()->addressEquals('admin/commerce/config/billing-schedules/add');

    $values = [
      'label' => 'Test',
      'displayLabel' => 'Awesome test',
      'billingType' => BillingScheduleInterface::BILLING_TYPE_POSTPAID,
      'plugin' => 'fixed',
      'configuration[fixed][interval][number]' => '2',
      'configuration[fixed][interval][unit]' => 'month',
      // Setting the 'id' can fail if focus switches to another field.
      // This is a bug in the machine name JS that can be reproduced manually.
      'id' => 'test',
    ];
    $this->submitForm($values, 'Save');
    $this->assertSession()->addressEquals('admin/commerce/config/billing-schedules');
    $this->assertSession()->responseContains('Test');

    $billing_schedule = BillingSchedule::load('test');
    $this->assertEquals('test', $billing_schedule->id());
    $this->assertEquals('Test', $billing_schedule->label());
    $this->assertEquals('Awesome test', $billing_schedule->getDisplayLabel());
    $this->assertEquals(BillingScheduleInterface::BILLING_TYPE_POSTPAID, $billing_schedule->getBillingType());
    $this->assertEquals('fixed', $billing_schedule->getPluginId());
    $this->assertEquals([
      'interval' => [
        'number' => '2',
        'unit' => 'month',
      ],
    ], $billing_schedule->getPluginConfiguration());
    $this->assertEquals($billing_schedule->getPluginConfiguration(), $billing_schedule->getPlugin()->getConfiguration());
  }

  /**
   * Tests editing a billing schedule.
   */
  public function testBillingScheduleEditing() {
    $billing_schedule = BillingSchedule::create([
      'id' => 'test',
      'label' => 'Test',
      'displayLabel' => 'Awesome test',
      'billingType' => BillingScheduleInterface::BILLING_TYPE_POSTPAID,
      'plugin' => 'fixed',
      'configuration' => [
        'interval' => [
          'number' => '2',
          'unit' => 'month',
        ],
      ],
    ]);
    $billing_schedule->save();

    $this->drupalGet('admin/commerce/config/billing-schedules/manage/' . $billing_schedule->id());
    $this->submitForm([
      'label' => 'Test (Modified)',
      'displayLabel' => 'Awesome test (Modified)',
      'billingType' => BillingScheduleInterface::BILLING_TYPE_PREPAID,
      'plugin' => 'fixed',
      'configuration[fixed][interval][number]' => '1',
      'configuration[fixed][interval][unit]' => 'year',
    ], 'Save');

    \Drupal::entityTypeManager()->getStorage('commerce_billing_schedule')->resetCache();
    $billing_schedule = BillingSchedule::load('test');
    $this->assertEquals('test', $billing_schedule->id());
    $this->assertEquals('Test (Modified)', $billing_schedule->label());
    $this->assertEquals('Awesome test (Modified)', $billing_schedule->getDisplayLabel());
    $this->assertEquals(BillingScheduleInterface::BILLING_TYPE_PREPAID, $billing_schedule->getBillingType());
    $this->assertEquals('fixed', $billing_schedule->getPluginId());
    $this->assertEquals([
      'interval' => [
        'number' => '1',
        'unit' => 'year',
      ],
    ], $billing_schedule->getPluginConfiguration());
    $this->assertEquals($billing_schedule->getPluginConfiguration(), $billing_schedule->getPlugin()->getConfiguration());
  }

  /**
   * Tests deleting a billing schedule.
   */
  public function testBillingScheduleDeletion() {
    $billing_schedule = BillingSchedule::create([
      'id' => 'test',
      'label' => 'Test',
      'displayLabel' => 'Awesome test',
      'billingType' => BillingScheduleInterface::BILLING_TYPE_POSTPAID,
      'plugin' => 'fixed',
      'configuration' => [
        'interval' => [
          'number' => '2',
          'unit' => 'month',
        ],
      ],
    ]);
    $billing_schedule->save();
    $this->drupalGet('admin/commerce/config/billing-schedules/manage/' . $billing_schedule->id() . '/delete');
    $this->submitForm([], 'Delete');
    $this->assertSession()->addressEquals('admin/commerce/config/billing-schedules');

    $billing_schedule_exists = (bool) BillingSchedule::load('test');
    $this->assertEmpty($billing_schedule_exists);
  }

}
