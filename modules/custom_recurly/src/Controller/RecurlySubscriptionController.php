<?php

namespace Drupal\custom_recurly\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;


/**
 * Controller routines for Teachervision subscriptions.
 */
class RecurlySubscriptionController extends ControllerBase {

  /** @var \Symfony\Component\HttpFoundation\Request */
  private $request;

  /** @var \Drupal\Core\Database\Connection */
  private $database;

  /** @var \Drupal\Core\Routing\RouteMatchInterface */
  protected $routeMatch;

  /** @noinspection PhpMissingParentCallCommonInspection */
  /**
   * @inheritdoc
   *
   * @see ControllerBase::create()
   */
  public static function create(ContainerInterface $container) {
    /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
    return new static(
      $container->get('current_user'),
      $container->get('request_stack')->getCurrentRequest(),
      $container->get('database'),
      $container->get('current_route_match'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Returns render array listing available BillingPlans from Vindicia.
   */
  public function listplans(RouteMatchInterface $route_match, $currency = NULL, $subscription_id = NULL) {
    $entity_type_id = $this->config('recurly.settings')->get('recurly_entity_type');

    // Redirect authenticated users to the authenticated signup page if they're
    // on the unauthenticated one.
    /* if (\Drupal::currentUser()->isAuthenticated() && !$route_match->getParameters()->count()) {
       $authenticated_route_name = "entity.$entity_type_id.recurly_signup";
       $authenticated_route = \Drupal::service('router.route_provider')->getRouteByName($authenticated_route_name);
       return $this->redirect($authenticated_route_name, [
         'user' => \Drupal::currentUser()->id(),
       ], $authenticated_route->getOptions());
     }*/
    $userId = \Drupal::currentUser()->id();
    $entity = \Drupal\user\Entity\User::load(\Drupal::currentUser()->id());
    //$entity = $route_match->getParameter($entity_type_id);
    $entity_type = \Drupal::entityTypeManager()->getDefinition($entity_type_id)->getLowercaseLabel();

    // Initialize the Recurly client with the site-wide settings.
    if (!recurly_client_initialize()) {
      return ['#markup' => $this->t('Could not initialize the Recurly client.')];
    }

    $mode = $subscription_id ? "change" : "signup";
    $subscriptions = [];

    // If loading an existing subscription.
    if ($subscription_id) {
      if ($subscription_id === 'latest') {
        $local_account = recurly_account_load(['entity_type' => $entity_type, 'entity_id' => $entity->id()], TRUE);
        $subscriptions = recurly_account_get_subscriptions($local_account->account_code, 'active');
        $subscription = reset($subscriptions);
        $subscription_id = $subscription->uuid;
      } else {
        try {
          $subscription = \Recurly_Subscription::get($subscription_id);
          $subscriptions[$subscription->uuid] = $subscription;
        } catch (\Recurly_NotFoundError $e) {
          throw new NotFoundHttpException($this->t('Subscription not found'));
        }
      }
      $currency = $subscription->plan->currency;
    } // If signing up to a new subscription, ensure the user doesn't have a plan.
    elseif (\Drupal::currentUser()->isAuthenticated()) {
      $currency = isset($currency) ? $currency : $this->config('recurly.settings')->get('recurly_default_currency');
      $account = recurly_account_load(['entity_type' => $entity_type, 'entity_id' => $entity->id()]);
      if ($account) {
        $subscriptions = recurly_account_get_subscriptions($account->account_code, 'active');
      }
    }

    // Make the list of subscriptions based on plan keys, rather than uuid.
    $plan_subscriptions = [];
    foreach ($subscriptions as $subscription) {
      $plan_subscriptions[$subscription->plan->plan_code] = $subscription;
    }

    $all_plans = _get_all_subscription_plans();
    $enabled_plan_keys = $this->config('recurly.settings')->get('recurly_subscription_plans') ? : [];
    $enabled_plans = [];
    foreach ($enabled_plan_keys as $plan_code => $enabled) {
      foreach ($all_plans as $plan) {
        if ($enabled && $plan_code == $plan->plan_code) {
          $enabled_plans[$plan_code] = $plan;
        }
      }
    }
    return [
      '#theme' => [
        'custom_recurly_subscription_plan_select__' . $mode,
        'custom_recurly_subscription_plan_select'
      ],
      '#plans' => $enabled_plans,
      '#entity_type' => $entity_type,
      '#entity' => $entity,
      '#currency' => $currency,
      '#mode' => $mode,
      '#subscriptions' => $plan_subscriptions,
      '#subscription_id' => $subscription_id,
    ];
  }
}
