<?php
namespace Abcstart\Utilities\Uninstall;

use Package;
use Core;
use User;
use UserInfo;
use Group;

defined('C5_EXECUTE') or die("Access Denied.");

class Uninstaller
{
  public static function uninstallUserGroups() {
    self::uninstallUserGroup('Moderators');
  }

  public static function uninstallUsers() {
    self::uninstallUser('demo');
  }

  public static function uninstallUserGroup($gName) {
    $group = Group::getByName($gName);
    if(is_object($group)) {
      $group->delete();
    }
  }

  public static function uninstallUser($uName) {
    $ui = UserInfo::getByUserName($uName);
    if(is_object($ui)){
      $ui->delete();
    }
  }
}
