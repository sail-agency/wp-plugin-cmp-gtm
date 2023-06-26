<?php
/*
Plugin Name: CMP & GTM loader
Description: GDPR friendlier GTM loader.
Author: Are Kristoffer Lamo
Version: 0.0.1
Author URI: https://sail.no/folk/are-kristoffer-lamo/
Text Domain: cmp-gtm
*/

use Carbon_Fields\Carbon_Fields;
use Carbon_Fields\Container;
use Carbon_Fields\Field;

require_once( 'vendor/autoload.php' );

add_action( 'init', 'cmp_gtm_translation' );
add_filter( 'wp_head', 'cmp_gtm_script', 4 );

add_action( 'after_setup_theme', 'crb_load' );
function crb_load() {
    Carbon_Fields::boot();
}

add_action( 'carbon_fields_register_fields', 'carbon_flat_fields_register' );
function carbon_flat_fields_register() {
  Container::make('theme_options', 'GTM Settings')
  ->add_fields(array(
      Field::make('text', 'gtm_id', 'GTM ID'),
  ));
}




function cmp_gtm_translation() {
    load_plugin_textdomain( 'gdpr-youtube-consent', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
}

function cmp_gtm_script () {
  $languageCode = substr(get_locale(), 0, 2 );
  $gtmID = carbon_get_theme_option('gtm_id');
  
  echo "
  
  <script id='CookieConsent' src='https://policy.app.cookieinformation.com/uc.js' data-culture='{$languageCode}' type='text/javascript'></script>";


  if(empty($gtmID)) {
    return;
  }

  echo "
  <script>
  window.addEventListener('CookieInformationConsentGiven', function(event) {
          if (CookieInformation.getConsentGivenFor('cookie_cat_functional')) {
      var scriptElement = document.createElement('script');
  scriptElement.innerHTML = \"(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start': new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0], j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{$gtmID}');\";
  document.head.appendChild(scriptElement);
          }
  }, false); 
  </script>
  
  ";
  }
  