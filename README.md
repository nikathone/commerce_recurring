Provides recurring billing for Drupal Commerce, powered by Advanced queue.

The successor to Commerce Recurring and Commerce License Billing for D7.

Features:
- Configurable billing intervals (charge every N days/weeks/months/years).
- Fixed and rolling interval types (charge on the 1st of the month VS 1 month from the subscription date)
- Prepaid and postpaid billing types (charge at the beginning or at the end of the billing period).
- Free trial of any interval (14 days followed by a regular monthly subscription, etc)
- Prorating (adjusting the charged price based on the duration of its usage)
- Usage tracking (track bandwidth and charge per GB, etc).

## Use cases

1) Recurring membership (via commerce_license)

Prepaid billing for a license (usually of type "role").

2) Recurring SaaS subscription

Postpaid billing, with optional usage, for a license.

3) Donations

Prepaid billing for a product variation (no license).
Customers can usually select between multiple billing schedules (monthly/yearly, etc).

Future use cases: Physical products (Dollar Shave Club, etc)

## General idea

Each product variation represents a subscription plan and can be used to start a subscription.
A product can have multiple variations, representing multiple plans the customer can choose from.
For example, Basic and Premium memberships.

Each variation can have one or more billing schedules, which determine how the subscription will be billed (interval, interval type, billing type, free trial settings, etc).

On the add to cart form, the customer chooses a plan and a billing schedule (hidden if there's only 1 possible option), which are stored on the order item in the purchased_entity and billing_schedule fields. 
If the billing schedule dictates "postpayment" as the billing type, the initial unit price of the order item is 0.The customer provides their billing information and completes checkout. When the order is placed, a subscription is open for each order item with a specified billing_schedule. As soon as the subscription is activated, the billing schedule generates a billing period, and opens a recurring order for that billing period.

Once the billing period ends ($order->billing_period->end_date < time()), the module's hook_cron() implementation queues the order for closing and renewal. The queue worker then processes each order, collecting payment (via $subscription->payment_method), and opening the next recurring order.

## Setup

1) Go to /admin/commerce/config/billing-schedule/ and create a billing schedule.
2) Edit your product variation type and enable the "Allow subscriptions" trait.
3) Create a product variation with a "Subscription type" and a "Billing schedule" selected.

That's it! Each time your product variation is purchased, a subscription will be created for it.
By default, subscriptions are renewed on cron. This can be changed to a Drush/Drupal Console
daemon by editing the queue at /admin/config/system/queues/manage/commerce_recurring.
