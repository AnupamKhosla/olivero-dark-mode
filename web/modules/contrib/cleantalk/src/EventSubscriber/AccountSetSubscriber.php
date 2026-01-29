<?php

namespace Drupal\cleantalk\EventSubscriber;

use Drupal\cleantalk\CleantalkFuncs;
use Drupal\Core\Session\AccountEvents;
use Drupal\Core\Session\AccountSetEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class AccountSetSubscriber implements EventSubscriberInterface
{
  /**
   * @inerhitDoc
   */
  public static function getSubscribedEvents()
  {
    return [
      AccountEvents::SET_USER => 'setAdminCookie',
    ];
  }

  /**
   * @param AccountSetEvent $event
   * @return void
   */
  public function setAdminCookie(AccountSetEvent $event)
  {
    if ( $event->getAccount()->isAuthenticated() && in_array('administrator', $event->getAccount()->getRoles()) ) {
      // set SFW pass flag
      if ( ! headers_sent() ) {
        $api_key = \Drupal::config('cleantalk.settings')->get('cleantalk_authkey');
        CleantalkFuncs::apbct_setcookie('apbct_admin_logged_in', md5($api_key . '_admin'), true);
      }
    }
  }
}
