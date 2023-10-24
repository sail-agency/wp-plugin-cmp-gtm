<?php

namespace Sail\CMP_GTM\Output;

use Sail\CMP_GTM\Field\Option;

class Header
{
    public function __construct()
    {
        $language_code = substr(get_locale(), 0, 2);
        $gtm_id = Option::get_gtm_id();

        echo "<script id='CookieConsent' src='https://policy.app.cookieinformation.com/uc.js' data-culture='{$language_code}' type='text/javascript'></script>";

        if(!empty($gtm_id)) {
            echo "
  <script>
  window.addEventListener('CookieInformationConsentGiven', function(event) {
          if (CookieInformation.getConsentGivenFor('cookie_cat_functional') || CookieInformation.getConsentGivenFor('cookie_cat_statistic')) {
      var scriptElement = document.createElement('script');
  scriptElement.innerHTML = \"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtm_id}');\";
  document.head.appendChild(scriptElement);
          }
  }, false); 
  </script>
  
  ";
        }
    }
}
