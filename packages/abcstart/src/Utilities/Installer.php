<?php
namespace AbcStart\Utilities;

use Package;
use Core;
use Config;
use User;
use UserInfo;
use Group;
use BlockTypeSet;
use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Theme\Concrete\PageTheme;
use Concrete\Core\File\Image\Thumbnail\Type\Type as ThumbnailType;
use Concrete\Core\Entity\File\Image\Thumbnail\Type\Type as ThumbnailEntity;
use Concrete\Core\Tree\Type\Group as GroupTree;
use Concrete\Core\Tree\Node\Type\Group as GroupTreeNode;
use Concrete\Core\Tree\Node\Node as TreeNode;

class Installer
{
  protected $pkgHandle = 'abcstart';

  public static function installBlocks($pkg)
  {
    self::installBlock("call_to_action", $pkg);
  }

  public static function installUserGroups($pkg) {
    self::installUserGroup('Moderators', 'Default moderators group (edit content and limited administrative capabilities)', $pkg);
  }

  public static function installUsers() {
    $group = Group::getByName('Moderators');
    self::installUser('demo', 'devs@abcitweb.be', 'demo123', $group);
  }

  public static function installTheme($pkg) {
    PageTheme::add('abcbasic', $pkg);
  }

  public static function installThumbnails() {
    $small = ThumbnailType::getByHandle('small');
    if (!is_object($small)) {
      $type = new ThumbnailEntity();
      $type->setName('Small');
      $type->setHandle('small');
      $type->setWidth(750);
      $type->save();
    }

    $medium = ThumbnailType::getByHandle('medium');
    if (!is_object($medium)) {
      $type = new ThumbnailEntity();
      $type->setName('Medium');
      $type->setHandle('medium');
      $type->setWidth(990);
      $type->save();
    }

    $large = ThumbnailType::getByHandle('large');
    if (!is_object($large)) {
      $type = new ThumbnailEntity();
      $type->setName('Large');
      $type->setHandle('large');
      $type->setWidth(1200);
      $type->save();
    }
  }

  public static function installAutoResizeImages() {
    Config::save('concrete.file_manager.restrict_uploaded_image_sizes', true);
    Config::save('concrete.file_manager.restrict_max_width', 1920);
    Config::save('concrete.file_manager.restrict_max_height', 1080);
    Config::save('concrete.file_manager.restrict_resize_quality', 90);
  }

  public static function installBlockSet($bsName, $bsHandle, $pkg) {
    $bts = BlockTypeSet::getByHandle($bsHandle);
    if (!is_object($bts)) {
        BlockTypeSet::add($bsHandle, $bsName, $pkg);
    }
  }


  public static function installBlock($handle, $pkg) {
    $blockType = BlockType::getByHandle($handle);
    if (!is_object($blockType)) {
        BlockType::installBlockType($handle, $pkg);
    }
  }

  public static function installUserGroup($gName, $gDescription, $pkg) {
    $group = Group::getByName($gName);
    if(!is_object($group)) {
      $parentGroupNode = TreeNode::getByID(1);
      if (is_object($parentGroupNode) && $parentGroupNode instanceof GroupTreeNode) {
        $parentGroup = $parentGroupNode->getTreeNodeGroupObject();
      }
      Group::add($gName, $gDescription, $parentGroup, $pkg);
    }
  }

  public static function installUser($uName, $uEmail, $uPassword, $group) {
    $ui = UserInfo::getByUserName($uName);
    if(!is_object($ui)){
      $data = array();
      $data['uName'] = $uName;
      $data['uEmail'] = $uEmail;
      $data['uPassword'] = $uPassword;

      $newUserInfoObject = UserInfo::add($data);
      $uID = $newUserInfoObject->getUserID();
      $u = User::getByUserID($uID);
      if(is_object($group)) {
        $u->enterGroup($group);
      }
    } else {
      $u = User::getByUserID($ui->getUserID());
      if(is_object($group)) {
        $u->enterGroup($group);
      }
    }
  }
}
