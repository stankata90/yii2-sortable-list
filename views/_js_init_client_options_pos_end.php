<?php
    /* @var $this \yii\web\View */
    /* @var $widget \stankata90\yii2SortableList\SortableList */

    echo <<< JS
    let sortebleList = []
    let nestedSortable = [].slice.call(document.querySelectorAll('.st_list_instance'));
    for (var i = 0; i < nestedSortable.length; i++) {
        sortebleList[ $(nestedSortable[i]).data('id')] = new Sortable(nestedSortable[i], {$widget->_hashClientOptions});
    }
JS;
?>