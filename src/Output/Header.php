<?php

namespace Sail\CMP_GTM\Output;

use Sail\CMP_GTM\Field\Option;

class Header
{
  private $language_code;
  private $gtm_ids;
  private $provider;

    public function __construct()
    {
      $this->language_code = substr(get_locale(), 0, 2);
      $this->gtm_ids  = Option::get_gtm_id();
      $this->provider = Option::get_cmp_provider();

      if ( $this->provider === 'cookieinformation' ) {
        $this->cookieinformation();
      }

      if ( $this->provider === 'sail-cmp' ) {
        $this->sail_cmp();
      }

      if ( Option::get_gtm_consent() ) {
        $this->script_tag( $this->consent(Option::get_wait_time()) );
      }

      if( ! empty($this->gtm_ids) ) {
        foreach($this->gtm_ids as $gtm_id) {
          if ( Option::get_load_delay() !== 'none' ) {
            $this->script_tag($this->delay_listener(Option::get_load_delay(), $this->gtm_script($gtm_id)) );
          } else {
            $this->gtm_tag($gtm_id);
          }
        }
      }
    }

    private function sail_cmp() {
      $site_id = Option::get_sail_cmp_site_id();
      $url = Option::get_sail_cmp_url( $site_id );
      
      if( ! empty($site_id) && ! empty($url) ) {
        if ( '' === $url || is_admin() || is_feed() ) {
          return;
        }
        echo "<!-- Sail CMP -->\n<!--noptimize-->";
        wp_print_script_tag( [
          'src'                     => esc_url( $url ),
          ...$this->script_tag_nodeferring()
        ] );
        echo "<!--/noptimize-->\n";
      }
    }

    private function cookieinformation() {
      echo "<script id='CookieConsent' src='https://policy.app.cookieinformation.com/uc.js' data-culture='{$this->language_code}' data-gcm-version='2.0' type='text/javascript'></script>";
    }

    public static function pre_connect() {
      if( ! empty(Option::get_gtm_id() ) ) {
        echo "<link rel='preconnect' href='https://www.googletagmanager.com' crossorigin>";
      }
      if ( Option::get_cmp_provider() === 'cookieinformation' ) {
        echo "<link rel='preconnect' href='https://policy.app.cookieinformation.com' crossorigin>";
      }
      if( Option::get_cmp_provider() === 'sail-cmp' ) {
        echo "<link rel='preconnect' href='https://consent.sail.no' crossorigin>";
      }
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

    private function gtm_tag($gtm_id) {
        wp_print_inline_script_tag(
          "(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
          new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
          j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
          'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
          })(window,document,'script','dataLayer','{$gtm_id}');",
          $this->script_tag_nodeferring()
        );
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
      'functionality_storage': 'denied',
      'personalization_storage': 'denied',
      'security_storage': 'granted',
      'wait_for_update': {$wait_time}
      });

      gtag('set', 'ads_data_redaction', true);";
    }

    private function script_tag($script) : void {
      wp_print_inline_script_tag(
        $script,
        $this->script_tag_nodeferring()
      );
    }

    private function script_tag_nodeferring() : array {
      return [
        'data-cfasync'            => 'false',
        'data-no-optimize'        => '1',
        'data-no-defer'           => '1',
        'data-no-minify'          => '1',
        'data-pagespeed-no-defer' => true,
        'nowprocket'              => true,
      ];
    }
}
