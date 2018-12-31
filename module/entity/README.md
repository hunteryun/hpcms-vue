#用法：
======================

###单条查询：

```
entity_load('news', $nid);

```

###多个查询：

```
entity_load_many('news', $ids); // 根据多个id查询

entity_load_many('news', array(), array('pager' => true, 'pager_page' => $parms['page'])); //查询所有并带分页

entity_load_many('news', array(), array('range' => 10)); //查询前10条

entity_load_many('news', array(), array('range' => 10, 'orderby' => array('created' => 'DESC'))); //查询前10条，并依创建时间排序

entity_load_many('news', array(), array('conditions' => array('cid' => 3)); //带条件查询cid等于3的记录

entity_load_many('news', array(), array('conditions' => array('cid' => array('operator' => '>', 'value' => 3)));  //带条件

```

###创建：

```
entity_create('news', array('title' => 'xxx', 'content' => 'xxxx'));

```

###更新：

```
entity_update('news', (object) array('nid' => $nid, 'title' => 'xxxx')); //根据id更新标题

```

###删除：

```
entity_delete('news', $id); //单个删除
entity_delete_many('news', array($ids)); //多个删除

```
