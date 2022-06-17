<?php
namespace AbcStart\Utilities;

use Package;
use Loader;
use Group;
use Page;
use PageType;
use BlockType;
use PermissionKey;
use TaskPermission;
use Concrete\Core\File\Filesystem;
use Concrete\Core\Tree\Node\Type\Group as GroupTreeNode;
use Concrete\Core\Permission\Access\Entity\GroupEntity as GroupPermissionAccessEntity;
use Concrete\Core\Permission\Access\Entity\PageOwnerEntity as PageOwnerPermissionAccessEntity;
use Concrete\Core\Permission\Key\FileSetKey as FileSetPermissionKey;
use Concrete\Core\Permission\Access\Access as PermissionAccess;

defined('C5_EXECUTE') or die("Access Denied.");

class Permissions
{
  public static function setPagePermissions($includeModerator = true) {
    $gAdministrators = Group::getByName('Administrators');

    $permissionsNone = array();
    $permissionsView = array('view_page');
    $permissionsEdit = array('view_page',
    'view_page_versions',
    'view_page_in_sitemap',
    'edit_page_properties',
    'edit_page_contents',
    'edit_page_speed_settings',
    'edit_page_theme',
    'edit_page_type',
    'edit_page_permissions',
    'delete_page',
    'delete_page_versions',
    'approve_page_versions');
    
    $dashboardHidePages = array('/dashboard/system',
                                '/dashboard/pages',
                                '/dashboard/blocks',
                                '/dashboard/extend',
                                '/dashboard/reports/logs');

    if($includeModerator == true) {
      $gModerators = Group::getByName('Moderators');

      $dashboard = Page::getByPath("/dashboard");
      $dashboard->assignPermissions($gModerators, $permissionsView);

      $drafts = Page::getByPath("/!drafts");
      $drafts->assignPermissions($gModerators, $permissionsEdit);

      if(!empty($dashboardHidePages)) {
        foreach($dashboardHidePages as $page) {
          $system = Page::getByPath($page);
          $system->assignPermissions($gModerators, $permissionsNone);
        }
      }

      $home = Page::getByID(1, "RECENT");
      $home->assignPermissions(
          $gModerators,
          array(
              'view_page_versions',
              'view_page_in_sitemap',
              'edit_page_properties',
              'edit_page_contents',
              'edit_page_multilingual_settings',
              'delete_page',
              'delete_page_versions',
              'approve_page_versions',
              'add_subpage',
              'move_or_copy_page'
          )
      );
    }

    if(!empty($dashboardHidePages)) {
      foreach($dashboardHidePages as $page) {
        $system = Page::getByPath($page);
        $system->assignPermissions($gAdministrators, $permissionsEdit);
      }
    }
  }

  public static function setPageTypePermissions($includeModerator = true) {
    $gModerators = Group::getByName('Moderators');
    $gAdministrators = Group::getByName('Administrators');

    $modGroupEntity = GroupPermissionAccessEntity::getOrCreate($gModerators);
    $adminGroupEntity = GroupPermissionAccessEntity::getOrCreate($gAdministrators);

    $defaultPageType = PageType::getByHandle('page');
    $defaultPageTypeSettings = array(
      'ptLaunchInComposer' => true
    );
    $defaultPageType->update($defaultPageTypeSettings);

    $pk = PermissionKey::getByHandle('add_page_type');
    if (is_object($pk)) {
      $pk->setPermissionObject($defaultPageType);
      $pt = $pk->getPermissionAssignmentObject();
      $pa = PermissionAccess::create($pk);
      $pa->addListItem($adminGroupEntity);
      if($includeModerator == true) {
        $pa->addListItem($modGroupEntity);
      }
      $pt->assignPermissionAccess($pa);
    }
  }

  public static function setFilePermissions($includeModerator = true) {
    $gModerators = Group::getByName('Moderators');
    $gAdministrators = Group::getByName('Administrators');
    $gGuests = Group::getByName('Guest');

    $modGroupEntity = GroupPermissionAccessEntity::getOrCreate($gModerators);
    $adminGroupEntity = GroupPermissionAccessEntity::getOrCreate($gAdministrators);
    $guestGroupEntity = GroupPermissionAccessEntity::getOrCreate($gGuests);

    $root = (new Filesystem())->getRootFolder();
    $tp = new TaskPermission();
    if ($tp->canAccessTaskPermissions()) {
      $permissions = PermissionKey::getList('file_folder');
      foreach ($permissions as $pk) {
        $pk->setPermissionObject($root);
        $pt = $pk->getPermissionAssignmentObject();
        $pt->clearPermissionAssignment();
        $pa = PermissionAccess::create($pk);
        $pa->addListItem($adminGroupEntity);
        if($includeModerator == true) {
          $pa->addListItem($modGroupEntity);
        }
        if($pk->getPermissionKeyHandle() == 'view_file_folder_file') {
          $pa->addListItem($guestGroupEntity);
        }
        $pt->assignPermissionAccess($pa);
      }
    }
  }

  public static function setUserPermissions($includeModerator = true) {
    $gModerators = Group::getByName('Moderators');
    $gAdministrators = Group::getByName('Administrators');

    $modGroupEntity = GroupPermissionAccessEntity::getOrCreate($gModerators);
    $adminGroupEntity = GroupPermissionAccessEntity::getOrCreate($gAdministrators);

    $pkHandles = array('access_sitemap',
                       'access_page_defaults',
                       'customize_themes',
                       'manage_layout_presets',
                       'empty_trash',
                       'remove_topic_tree',
                       'add_topic_tree',
                       'view_in_maintenance_mode',
                       'edit_user_properties',
                       'view_user_attributes',
                       'activate_user',
                       'delete_user',
                       'access_user_search_export',
                       'access_user_search',
                       'access_group_search',
                       'add_block',
                       'add_stack');

    if(!empty($pkHandles)) {
      foreach($pkHandles as $pkHandle) {
        if (is_object(PermissionKey::getByHandle($pkHandle))) {
         $pk = PermissionKey::getByHandle($pkHandle);
         $pa = PermissionAccess::create($pk);
         $pa->addListItem($adminGroupEntity);
         if($includeModerator == true) {
           $pa->addListItem($modGroupEntity);
         }
         $pt = $pk->getPermissionAssignmentObject();
         $pt->assignPermissionAccess($pa);
        }
      }
    }
  }

  public static function setUserGroupPermissions($includeModerator = true) {
    $gModerators = Group::getByName('Moderators');
    $gAdministrators = Group::getByName('Administrators');
    $gRegisteredUsers = Group::getByName('Registered Users');
    $gGuests = Group::getByName('Guest');

    $modGroupEntity = GroupPermissionAccessEntity::getOrCreate($gModerators);
    $adminGroupEntity = GroupPermissionAccessEntity::getOrCreate($gAdministrators);

    $uHandles = array('search_users_in_group',
                      'edit_group',
                      'assign_group',
                      'add_sub_group');

    $userGroups = array($gModerators,
                        $gRegisteredUsers,
                        $gGuests);

    if(!empty($userGroups)) {
      foreach($userGroups as $group) {
        $node = GroupTreeNode::getTreeNodeByGroupID($group->getGroupID());
        $node->setTreeNodePermissionsToOverride();

        if(!empty($uHandles)) {
          foreach($uHandles as $uHandle) {
            $pk = PermissionKey::getByHandle($uHandle);
            if (is_object($pk)) {
              $pk->setPermissionObject($node);
              $pt = $pk->getPermissionAssignmentObject();
              $pa = PermissionAccess::create($pk);
              $pa->addListItem($adminGroupEntity);
              if($includeModerator == true) {
                $pa->addListItem($modGroupEntity);
              }
              $pt->assignPermissionAccess($pa);
            }
          }
        }
      }
    }
  }

  public static function setUserGroupBlockPermissions($blocks) {
    $gModerators = Group::getByName('Moderators');

    $pk = PermissionKey::getByHandle('add_block'); ;
    $pa = PermissionAccess::getByID($pk->getPermissionAccessID(), $pk);

    $blocksPermitted = array();
    if(!empty($blocks)) {
      foreach($blocks as $block => $value) {
        $bt = BlockType::getByHandle($block);
        if(is_object($bt)) {
          $blocksPermitted[] = $bt->getBlockTypeID();
        }
      }
    }

    $blockPermissions = array();
    $blockPermissions['paID'] = $pa->getPermissionAccessID();
    $blockPermissions['blockTypesIncluded'] = array();
    $blockPermissions['btIDInclude'] = array();

    $included = $pa->getAccessListItems(PermissionKey::ACCESS_TYPE_INCLUDE);
    foreach ($included as $assignment) {
        $entity = $assignment->getAccessEntityObject();
        if($entity->getAccessEntityLabel() == "Moderators") {
          $blockPermissions['blockTypesIncluded'][$entity->getAccessEntityID()] = 'C';
          $blockPermissions['btIDInclude'][$entity->getAccessEntityID()] = $blocksPermitted;
        } else {
          $blockPermissions['blockTypesIncluded'][$entity->getAccessEntityID()] = 'A';
        }
    }
    $pa->save($blockPermissions);
  }
}
