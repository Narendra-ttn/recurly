<?php

namespace Drupal\custom_recurly;

use Drupal\Component\Utility\Html;
use Drupal\recurly\RecurlyPreprocess;

/**
 * Service to abstract preprocess hooks.
 */
class CustomRecurlyPreprocess extends RecurlyPreprocess {

  /**
   * Implements hook_preprocess_recurly_subscription_plan_select().
   */
  public function preprocessRecurlySubscriptionPlanSelect(array &$variables) {
    $plans = $variables['plans'];
    $currency = $variables['currency'];
    $entity_type = $variables['entity_type'];
    $entity = $variables['entity'];
    $subscriptions = $variables['subscriptions'];
    $subscription_id = $variables['subscription_id'];

    $current_subscription = NULL;
    foreach ($subscriptions as $subscription) {
      if ($subscription->uuid === $subscription_id) {
        $current_subscription = $subscription;
        break;
      }
    }

    // If currency is undefined, use the subscription currency.
    if ($current_subscription && empty($currency)) {
      $currency = $current_subscription->currency;
      $variables['currency'] = $currency;
    }

    // Prepare an easy to loop-through list of subscriptions.
    $variables['filtered_plans'] = [];
    foreach ($plans as $plan_code => $plan) {
      $setup_fee_amount = NULL;
      foreach ($plan->setup_fee_in_cents as $setup_currency) {
        if ($setup_currency->currencyCode === $currency) {
          $setup_fee_amount = $this->recurlyFormatter->formatCurrency($setup_currency->amount_in_cents, $setup_currency->currencyCode, TRUE);
          break;
        }
      }
      $unit_amount = NULL;
      foreach ($plan->unit_amount_in_cents as $unit_currency) {
        if ($unit_currency->currencyCode === $currency) {
          $unit_amount = $this->recurlyFormatter->formatCurrency($unit_currency->amount_in_cents, $unit_currency->currencyCode, TRUE);
          break;
        }
      }
      $variables['filtered_plans'][$plan_code] = [
        'plan_code' => Html::escape($plan_code),
        'name' => Html::escape($plan->name),
        'description' => Html::escape($plan->description),
        'setup_fee' => $setup_fee_amount,
        'amount' => $unit_amount,
        'plan_interval' => $this->recurlyFormatter->formatPriceInterval($unit_amount, $plan->plan_interval_length, $plan->plan_interval_unit, TRUE),
        'trial_interval' => $plan->trial_interval_length ? $this->recurlyFormatter->formatPriceInterval(NULL, $plan->trial_interval_length, $plan->trial_interval_unit, TRUE) : NULL,
        'signup_url' => recurly_url('subscribe', [
          'entity_type' => $entity_type,
          'entity' => $entity,
          'plan_code' => $plan_code,
          'currency' => $currency,
        ]),
        'change_url' => $current_subscription ? recurly_url('change_plan', [
            'entity_type' => $entity_type,
            'entity' => $entity,
            'subscription' => $current_subscription,
            'plan_code' => $plan_code,
          ]) : NULL,
        'selected' => FALSE,
      ];

      // If we have a pending subscription, make that its shown as selected
      // rather than the current active subscription. This should allow users to
      // switch back to a previous plan after making a pending switch to another
      // one.
      foreach ($subscriptions as $subscription) {
        if (!empty($subscription->pending_subscription)) {
          if ($subscription->pending_subscription->plan->plan_code === $plan_code) {
            $variables['filtered_plans'][$plan_code]['selected'] = TRUE;
          }
        } elseif ($subscription->plan->plan_code === $plan_code) {
          $variables['filtered_plans'][$plan_code]['selected'] = TRUE;
        }
      }
    }

    // Check if this is an account that is creating a new subscription.
    $variables['expired_subscriptions'] = FALSE;
    if (!empty($entity) && recurly_account_load(['entity_type' => $entity_type, 'entity_id' => $entity->id()])) {
      $variables['expired_subscriptions'] = empty($subscriptions);
    }
    echo "<pre>";
    print_r($variables['filtered_plans']);die;
  }
}
