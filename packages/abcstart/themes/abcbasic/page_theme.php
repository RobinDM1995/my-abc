<?php
namespace Concrete\Package\Abcstart\Theme\Abcbasic;

use Concrete\Core\Page\Theme\Theme;

class PageTheme extends Theme
{
  public function registerAssets(){
    $this->providesAsset('css', 'bootstrap/*');
    $this->providesAsset('css', 'blocks/form');
    $this->providesAsset('css', 'blocks/social_links');
    $this->providesAsset('css', 'blocks/share_this_page');
    $this->providesAsset('css', 'blocks/feature');
    $this->providesAsset('css', 'blocks/testimonial');
    $this->providesAsset('css', 'blocks/date_navigation');
    $this->providesAsset('css', 'blocks/topic_list');
    $this->providesAsset('css', 'blocks/faq');
    $this->providesAsset('css', 'blocks/tags');
    $this->providesAsset('css', 'core/frontend/*');
    $this->providesAsset('css', 'blocks/feature/templates/hover_description');
    $this->providesAsset('css', 'blocks/event_list');

    $this->providesAsset('javascript', 'bootstrap/*');
    $this->providesAsset('javascript-conditional', 'html5-shiv');
    $this->providesAsset('javascript-conditional', 'respond');

    $this->providesAsset('css', 'font-awesome');
    $this->requireAsset('javascript', 'jquery');
    $this->requireAsset('javascript', 'picturefill');

    $this->requireAsset('mediaelements');
  }

  protected $pThemeGridFrameworkHandle = 'bootstrap3';

  public function getThemeResponsiveImageMap()
  {
    return array(
      'large' => '1200px',
      'medium' => '992px',
      'small' => '0',
    );
  }


  public function getThemeEditorClasses(){
      // classes available in WYSIWYG
    return array(
      array('title' => t('Site Title'), 'menuClass' => 'site-title', 'spanClass' => 'site-title'),
      array('title' => t('Small Text'), 'menuClass' => 'small-text', 'spanClass' => 'small-text'),
      array('title' => t('Page Title'), 'menuClass' => 'page-title', 'spanClass' => 'page-title'),
      array('title' => t('Featured Content Title'), 'menuClass' => 'featured-content-title', 'spanClass' => 'featured-content-title'),
      array('title' => t('Lead'), 'menuClass' => 'lead', 'spanClass' => 'lead'),
      array('title' => t('Captivating Title'), 'menuClass' => 'captivating-title', 'spanClass' => 'captivating-title')
    );
  }
}
?>
