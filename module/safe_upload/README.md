#用法：
======================

上传有两种方式：

1. 需要权限验证的用于后台功能的上传，路径是：/admin/safe/upload

2. 是用于前台公开的上传，路径是：/public/safe/upload

本模块支持图片上传成功后的各种操作，就是兼有Drupal的image style功能，可以直接对上传的图片进行裁切，缩放等处理。

分别的使用示例是：

###前台公开上传：

比如可用于匿名用户的投稿

safe_key参数是必须的，是config.php里配置的安全key, 以达到简单安全防护的目的，部署到正式环境使用时一定记得修改此key

style参数是非必须的，是需要对上传图片进行处理的style名称，具体在config.php有配置，

```
public/safe/upload?safe_key=6kUxOb&style=small

```


###后台上传：

可不带任何参数

fullpath 是指返回上传成功后的绝对地址，可用于api远程调用上传功能后，返回给客户端完整的图片地址，以用于在客户端正确显示图片

compress 是指是否对上传的图片进行压缩处理，一般用于上传几M大小的相机图片，上传成功后压缩可以不影响图片显示的情况，尽量压缩图片的大小
具体在config.php也有配置

```
admin/safe/upload?fullpath=yes&compress=yes

```

###最后给一个layui里ajax上传的简单例子：

```
//文件上传
var picxmlUpload = upload.render({
  elem: '#picxmlbtn' //绑定元素
  ,url: '/admin/safe/upload' //上传接口
  ,field: 'arinfo-picxml'
  ,accept: 'file' //允许上传的文件类型
  ,exts: 'xml' //允许上传的文件扩展名
  ,size: 0 //最大允许上传的文件大小
  ,data: {accept: 'file', exts: 'xml', notvalidate: 'yes'}
  ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。
    layer.load(); //上传loading
  }
  ,done: function(result){
    layer.closeAll('loading'); //关闭loading
    document.getElementById('picxml').value = result.data.full_path_new_name;
  }
  ,error: function(){
    layer.closeAll('loading'); //关闭loading
    layer.msg('上传失败!', {
        icon: 5
    });
  }
});

```

注意其中data传参里支持几种参数，可适用于不同情况
notvalidate 是指在某些情况下，你可以肯定上传是安全的情况，可以传该参数用于跳过文件类型的验证
accept 是指上传的文件类型，支持file,images,video,audio, 默认不传则是images
exts   是指支持的文件类型后缀，如果指定了则只支持指定的类型，如果没有传这个参数，则会根据accept类型支持默认文件类型，分别是：

images：jpg|png|gif|bmp|jpeg

file: doc|pdf|txt|xls|zip|rar

video: mp4|flv|mpg|3gp

audio: mp3|wma|wav
