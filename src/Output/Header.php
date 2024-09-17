<?php

namespace Sail\CMP_GTM\Output;

use Sail\CMP_GTM\Field\Option;
use Sail\CMP_GTM\Admin;

class Header
{
  private $language_code;
  private $gtm_id;

    public function __construct()
    {
      $this->language_code = substr(get_locale(), 0, 2);
      $this->gtm_id  = Option::get_gtm_id();

      if ( ! Admin\Preview::is_preview() ) {
        $this->cookieinformation();

        if ( Option::get_gtm_consent() ) {
          echo $this->script_tag( $this->consent(Option::get_wait_time()) );
        }

        if( ! empty($this->gtm_id) ) {
          if ( Option::get_load_delay() !== 'none' ) {
            echo $this->script_tag($this->delay_listener(Option::get_load_delay(), $this->gtm_script($this->gtm_id)) );
          } else {
            echo $this->script_tag( $this->gtm_script($this->gtm_id) );
          }
        }
      }
    }

    private function cookieinformation() {
      echo "<script id='CookieConsent' src='https://policy.app.cookieinformation.com/uc.js' data-culture='{$this->language_code}' data-gcm-version='2.0' type='text/javascript'></script>";
    }

    public static function pre_connect() {
      echo "<link rel='preconnect' href='https://www.googletagmanager.com' crossorigin>
      <link rel='preconnect' href='https://policy.app.cookieinformation.com' crossorigin>";
    }

    private function delay_listener($consent, $loader_script) {
      return "window.addEventListener('CookieInformationConsentGiven', function(event) {
        if (CookieInformation.getConsentGivenFor('cookie_cat_{$consent}')) {
          {$loader_script}
        }
      }, false);";
    }

    private function gtm_script($gtm_id) {
      return "var scriptElement = document.createElement('script');
      scriptElement.innerHTML = \"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtm_id}');\";
      document.head.appendChild(scriptElement);";
    }

    private function gtag($tag_id) {
      return "<script async src='https://www.googletagmanager.com/gtag/js?id={$tag_id}'></script>";
    }

    private function consent($wait_time = 500) {
      return "window.dataLayer = window.dataLayer || [];

      function gtag(){ dataLayer.push(arguments); }

      gtag('consent', 'default', {
      'ad_storage': 'denied',
      'ad_user_data': 'denied',
      'ad_personalization': 'denied',
      'analytics_storage': 'denied',
      'wait_for_update': {$wait_time}
      });

      gtag('set', 'ads_data_redaction', true);";
    }

    private function script_tag($script) {
      return "<script>{$script}</script>";
    }
}
