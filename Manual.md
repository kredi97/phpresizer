<p>Гибкий контроль графики на сайте</p>

<h2>Использование</h2>
Перенаправим все запросы к изображениям на php скрипт
Пример .htaccess файла

.htaccess
```

<IfModule mod_rewrite.c>
RewriteEngine on
RewriteRule    (.*\.)(jpg|jpeg|bmp|gif|png|tiff|tif) images.php?file=$1$2 [L,NC,QSA]


Unknown end tag for &lt;/IfModule&gt;



```

Тепперь запрос:
```
http://example.com/testimage.jpg?type=avatar```
будет обрабатываться как:
```
http://example.com/images.php?file=testimage.jpg&type=avatar```

<h4>Определяем логику обработки изображения</h4>
images.php

```

<?php
$options = array (
"avatar"=>array(
"width"=>100, // желаемая ширина аватарки
"height"=>100, // желаемая высота аватарки
"aspect"=>false, // сохранять ли пропорции исходного изображения
"zoomSmallImage", //Увеличивать ли изображение если исходник меньше чем требуемая высота, ширина
"crop"=>95, //часть центральной части которую следует увеличить
"quality" => 75 // качество JPEG или степень ужатия PNG файла
"background" => "ff00ae" // цвет котором стоит залить свободные области при $aspect = true;
)
);

/* На этом этапе, при необходимости, проверяем разрешено ли пользователю
просматривать изображение. В данном примере это пропущено. */

require "/PhpResizer/Autoloader.php";
new PhpResizer_Autoloader();

try {
$resizer = new PhpResizer_PhpResizer(array (
"engine"=>PhpResizer_PhpResizer::ENGINE_IMAGEMAGICK,
"cache"=>true,
"cacheDir"=>__DIR__."/cache/",
"cacheBrowser"=>true,
'tmpDir'=>__DIR__.'/cache/',
)
);
$resizer->resize(__DIR__."/".$file, $opt, false);
}catch(Exception $e) {
// обрабатываем ошибки
}
```


<h4>Параметры передаваемые в конструктор new PhpResizer_PhpResizer()</h4>

<b> engine</b> - движок используемый для ужатия. Доступные значения:

```
PhpResizer_PhpResizer::ENGINE_GD2``` (выбрано по умолчанию)- GD (Graphics Draw) library
```
PhpResizer_PhpResizer::ENGINE_IMAGEMAGICK ```- <a href='http://www.imagemagick.org/script/index.php'>ImageMagick</a>
```
PhpResizer_PhpResizer::ENGINE_GRAPHIKSMAGICK``` - <a href='http://www.graphicsmagick.org/'>GraphicsMagick</a>

> <b>cache</b> - управление кешированием ужатых изображений на строне сервера. Допустимые значения: true|false по умочанию true

> <b>cacheDir</b> - абсолютный путь к папке где будут храниться закешированные ужатые файлы. По умолчанию /tmp/resizerCache/

> <b>cacheBrowser</b> - управление кеширования ужатых изображений в браузере. Допустимые значения: true|false


<h2>Очиста кеша</h2>

В данном примере мы удаляем из папки "/var/www/resizer/cache/" все файлы к которым никто не обращался уже целую неделю. (Внимание!!!, не ошибись с путём к папке)
```

<?php
$resizer = new PhpResizer_PhpResizer(array (
"cacheDir"=>__DIR__."/cache/"));
$resizer->clearCache(60*24*7);
```

<h2>Вопрос - ответ</h2>

<h4>Хочу что изображения сервер отдавал непосредственно, а не через через php-скрипт?</h4>
В метод resize третим параметром передайте true, в этом случае скрипт вернёт абсолютный путь к ужатому файлу.
например ```
/var/www/site/design/images/PhpResizerCache/4k/33sdfsdf4wesd34rsf43.jpg```

<h2>Совет</h2>
Чтоб при запросе пользователя  не заставлять его ждать пока PhpResizer выполнит пережатия изображения, можно использую планировщик заданий crontab готовить ужатые изображения в фоне, сразу как только появляется новое изображение.