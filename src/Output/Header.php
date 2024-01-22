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

        if( ! empty($this->gtm_id) ) {
          $this->gtm();
        }
      }
    }

    private function cookieinformation() {
      echo "<script id='CookieConsent' src='https://policy.app.cookieinformation.com/uc.js' data-culture='{$this->language_code}' data-gcm-version='2.0' type='text/javascript'></script>";
    }

    private function gtm() {
      echo "
      <script>
      window.addEventListener('CookieInformationConsentGiven', function(event) {
              if (CookieInformation.getConsentGivenFor('cookie_cat_functional') || CookieInformation.getConsentGivenFor('cookie_cat_statistic')) {
          var scriptElement = document.createElement('script');
      scriptElement.innerHTML = \"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$this->gtm_id}');\";
      document.head.appendChild(scriptElement);
              }
      }, false); 
      </script>
      
      ";
    }
}
