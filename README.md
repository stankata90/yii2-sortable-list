Sortable List
=============
quick and easy

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist stankata/yii2-sortable-list "*"
```

or add

```
"stankata/yii2-sortable-list": "*"
```

to the require section of your `composer.json` file.


Usage
-----

Once the extension is installed, simply use it in your code by  :

```php
<?= SortableList::widget( [
        'sort'                => SortableList::SORT_NESTABLE,
        'table'               => AdminMenu::tableName(),
        'url'                 => 'sort-save',
        'primaryColumn'       => 'id',
        'primaryParentColumn' => 'id_in',
        'valueColumn'         => 'name',
        'sortColumn'          => 'sort',
        'renderView'          => 'sortable-item',
        'groupOptions'        => [
            'class' => 'pt-3',
        ],
    ] ); ?>
```

Install without composer
----
Add extension in `vendor/yiisoft/extensions.php`

```php
'stankata90/yii2-sortable-list' => [
    'name'    => 'stankata90/yii2-sortable-list',
    'version' => 'dev-master',
    'alias'   => [
        '@stankata90/yii2SortableList' => $vendorDir . '/../backend/runtime/tmp-extensions/yii2-sortable-list',
    ],
],
```