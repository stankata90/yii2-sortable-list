<?php
    namespace stankata90\yii2SortableList;



    use yii\web\AssetBundle;

    class SortableListAsset extends AssetBundle
    {

        public $sourcePath = __DIR__ . '/assets';

        public $css = [
            'css/Sortable_widget.css',
        ];

        public $js = [
            'js/Sortable.js',
            'js/Sortable_widget.js'
        ];

    }