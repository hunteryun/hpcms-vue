<?php

namespace Hunter\entity\Plugin;

use Exception;

class EntityConfig {

  /**
   * 所有Entity的配置信息
   *
   * @var array
   */
  protected static $entityInfo = array();

  /**
   * 获取EntityInfo
   */
  public static function getEntityInfo($type, $reset = FALSE) {
      if(empty(self::$entityInfo) || $reset) {
        global $app;
        foreach ($app->getModuleHandle()->getImplementations('entity_info') as $module) {
          $module_entity = $app->getModuleHandle()->invoke($module, 'entity_info');
          self::register($module_entity['name'], $module_entity);
        }
      }

      if (!isset(self::$entityInfo[$type])) {
          throw new Exception('No such Entity Type ' . $type);
      }
      if (!isset(self::$entityInfo['baseSchema'])) {
          self::$entityInfo[$type]['baseSchema']['fields'] = self::getEntityPrimaryFields($type);
      }

      return self::$entityInfo[$type];
  }

  /**
   * 注册Entity
   */
  public static function register($type, array $info = array()) {
      if (isset(self::$entityInfo[$type])) {
          throw new EntityException('Entity Type ' . $type . ' is already existed.');
      }
      self::$entityInfo[$type] = $info;
  }

  //获取Entity的主表信息
  public static function getEntityPrimaryFields($type) {
      $table = self::$entityInfo[$type]['baseTable'];
      return db_get_fields($table);
  }

}
