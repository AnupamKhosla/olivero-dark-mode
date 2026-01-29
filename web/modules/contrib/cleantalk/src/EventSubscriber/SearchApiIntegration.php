<?php

namespace Drupal\cleantalk\EventSubscriber;

use Drupal\cleantalk\CleantalkFuncs;
use Drupal\search_api\Event\QueryPreExecuteEvent;
use Drupal\search_api\Event\SearchApiEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class SearchApiIntegration implements EventSubscriberInterface {

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    if ( class_exists('Drupal\search_api\Event\SearchApiEvents') && class_exists('Drupal\search_api\Event\QueryPreExecuteEvent') ) {
      return [
        SearchApiEvents::QUERY_PRE_EXECUTE => 'queryPreExecute',
      ];
    }
    return [];
  }

  /**
   * Reacts to the query pre-execute event.
   *
   * @param QueryPreExecuteEvent $event The query pre-execute event.
   */
  public function queryPreExecute(QueryPreExecuteEvent $event) {
    if (\Drupal::config('cleantalk.settings')->get('cleantalk_check_search_form')) {
      CleantalkFuncs::doSearchProtection($event->getQuery()->getOriginalKeys());
    }
  }
}
