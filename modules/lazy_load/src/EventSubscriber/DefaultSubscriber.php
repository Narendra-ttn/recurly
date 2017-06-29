<?php

namespace Drupal\lazy_load\EventSubscriber;

use Drupal\Core\Database\Driver\mysql\Connection;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Class DefaultSubscriber.
 *
 * @package Drupal\lazy_load
 */
class DefaultSubscriber implements EventSubscriberInterface {

  /**
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var \Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct(Connection $database) {
    $this->database = $database;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events['kernel.request'] = ['fe_kernel_request'];
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    return $events;
  }

  /**
   * Replace all legacy,node/{nodeid} url to Drupal alias
   *
   * @param FilterResponseEvent $event
   */
  public function onRespond(FilterResponseEvent $event) {
    //$negotiator = \Drupal::service('domain.negotiator');
    //$domainID = $negotiator->getActiveDomain()->id();
    //check the Role Also
    $current_user = \Drupal::currentUser();
    $roles = $current_user->getRoles();
    //check on the domain basis
    //if ($domainID == 'dev_fe_family_education_qa6_tothenew_net'){// && !in_array('administrator',$roles) && !in_array('editor',$roles)) {
    $response = $event->getResponse();
    $content = $response->getContent();

    //get all the image tag from the data
    preg_match_all('/<img[^>]+>/i', $content, $result);
    $blazyClass = "b-lazy";
    foreach ($result[0] as $imgTag) {
      preg_match_all('/(class|src)=("[^"]*")/i', $imgTag, $imageData);
      if (!empty($imageData)) {
        $newImageTag = $imgTag;
        //get the key of the SRC attribute
        $srcKey = array_search("src", $imageData[1]);
        $src = (isset($imageData[1][$srcKey]) && $imageData[1][$srcKey] == 'src') ? true : false;

        //get the key of the Class attribute
        $classKey = array_search("class", $imageData[1]);
        $class = (isset($imageData[1][$classKey]) && $imageData[1][$classKey] == 'class') ? true : false;

        $classValue = '';
        if (!$class) {
          $classValue = 'class="' . $blazyClass . '"';
        }

        if ($src) {
          $srcValue = 'src="/core/themes/bartik/images/image_loading.gif"';
          $dataSrcValue = 'data-src="' . str_replace('"', "", $imageData[2][$srcKey]) . '"';
          $newImageTag = str_replace($imageData[0][$srcKey], $srcValue . " " . $dataSrcValue . " " . $classValue, $newImageTag);
          if ($classKey) {
            $newClassValue = 'class="' . $blazyClass . ' ' . str_replace('"', "", $imageData[2][$classKey]) . '"';
            $newImageTag = str_replace($imageData[0][$classKey], $newClassValue, $newImageTag);
          }
          $content = str_replace($imgTag, $newImageTag, $content);
        }
      }
    }
    $response->setContent($content);

    /*//get the class of the body
    $bodyClass = '';
    preg_match('<body(.+)?class="(.+)">', $content, $matches);
    if (!empty($matches)) {
      if (!empty($matches[2])) {
        $bodyClass = preg_replace('/[^-a-zA-Z0-9_. ]/', '', $matches[2]);
      } else {
        $bodyClass = $matches[0];
        $bodyClass = str_replace("body", "", $bodyClass);
        $bodyClass = str_replace("class", "", $bodyClass);
        $bodyClass = str_replace('"', "", $bodyClass);
        $bodyClass = str_replace('=', "", $bodyClass);
        $bodyClass = preg_replace('/[^-a-zA-Z0-9_. ]/', '', $bodyClass);
      }
    }

    //remove the image those are added in the admin configuration
    $image_url = \Drupal::config('lazy_load_configuration.image_data')->get('lazy_loading_image_urls');
    $imageURL = array_map('trim', explode("\n", $image_url));

    //update the all image tag with the default src and blazy class
    $doc = new \DOMDocument();
    @$doc->loadHTML($content);
    $mybody = $doc->getElementsByTagName('body')->item(0);
    $mybody->setAttribute('class', $bodyClass);
    $tags2 = $mybody->getElementsByTagName('img');
    $flag = false;
    for ($i = 0; $i < $tags2->length; $i++) {
      $src = $tags2->item($i)->getAttribute('src');
      //not include those image which are existing in the admin setting
      if (in_array($src, $imageURL)) {
        continue;
      } else {
        //update the image class and remove src and add a new data-src in which src value be there
        $class = $tags2->item($i)->getAttribute('class');
        $tags2->item($i)->setAttribute("data-src", $src);
        $tags2->item($i)->setAttribute("src", '/core/themes/bartik/images/image_loading.gif');
        $tags2->item($i)->setAttribute("class", $class . " b-lazy");
        $flag = true;
      }
    }
    if ($flag) {
      //save the html and set to response
      $response->setContent(@$doc->saveHTML());
    }*/
    //}
  }

  /**
   * This method is called whenever the kernel.request event is
   * dispatched.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   */
  public function fe_kernel_request(GetResponseEvent $event) {

  }
}
