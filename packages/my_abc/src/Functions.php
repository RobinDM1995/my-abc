<?php
namespace Concrete\Package\MyAbc\Src;

use  Localization;

class Functions{
  public function setLang($lang){
    switch($lang){
      case 'nl':
      case 'Nederlands':
      default:
      $locale = 'nl_BE';
      $localeTrustpilot = 'nl-BE';
      $langCode = 'nl';
      break;

      case 'fr':
      case 'French':
      $locale = 'fr_FR';
      $localeTrustpilot = 'fr-FR';
      $langCode = 'fr';
      break;

      case 'en':
      case 'English':
      $locale = 'en_GB';
      $localeTrustpilot = 'en-GB';
      $langCode = 'en';
      break;
    }
    Localization::changeLocale($locale);
  }
}
 ?>
