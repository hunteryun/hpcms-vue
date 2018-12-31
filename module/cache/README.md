usage

存储一个元素
  cache()->set($key, $value, $expiration = 0);

获取一个元素
  cache()->get($key);

删除一个元素
  cache()->delete($key);

检查元素是否存在
  cache()->has($key);  

删除缓存中的所有过期元素
  cache()->clearCache();  

删除缓存中的所有元素
  cache()->dropCache();
