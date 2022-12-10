<?php
    /* @var $this \yii\web\View */
    /* @var $widget \stankata90\yii2SortableList\SortableList */

    echo <<< JS
    var {$widget->_hashWidgetOptions} = {$encOptions};
JS;
?>