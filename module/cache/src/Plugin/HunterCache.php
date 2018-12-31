<?php

/**
 * @file
 *
 * 缓存类
 */

namespace Hunter\cache\Plugin;

use Desarrolla2\Cache\Cache;
use Desarrolla2\Cache\Adapter\Predis;
use Desarrolla2\Cache\Adapter\Memcached;
use Desarrolla2\Cache\Adapter\Memcache;
use Desarrolla2\Cache\Adapter\NotCache;
use Desarrolla2\Cache\Adapter\Apcu;
use Desarrolla2\Cache\Adapter\File;
use Desarrolla2\Cache\Adapter\Mongo;
use \Memcached as MemcachedBackend;
use \Memcache as MemcacheBackend;

class HunterCache {

  //获取缓存实例
  public static function getConnection($target = 'memcache') {

      switch ($target)
      {
      case 'memcache':
        $backend = new MemcacheBackend();
        $backend->connect('127.0.0.1', 11211);
        $cache = new Cache(new Memcache($backend));
        break;
      case 'memcached':
        $backend = new MemcachedBackend();
        $backend->addServer('127.0.0.1', 11211, 0);
        $cache = new Cache(new Memcached($backend));
        break;
      case 'redis':
        $adapter = new Predis();
        $cache = new Cache($adapter);
        break;
      case 'apcu':
        $adapter = new Apcu();
        $adapter->setOption('ttl', 3600);
        $cache = new Cache($adapter);
        break;
      case 'file':
        global $cache_dir;
        $adapter = new File($cache_dir);
        $adapter->setOption('ttl', 3600);
        $cache = new Cache($adapter);
        break;
      default:
        $cache = new Cache(new NotCache());
      }

      return $cache;
  }

}
