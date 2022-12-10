/**
 * функсиця за обхождане на дървото от листове, събирайки данните за подредбата и вложеността им.
 *
 * @param collected
 * @param $list
 * @param config
 * @param id_in
 * @returns {*[]}
 */
let serialiseSort = function (collected, $list, config, id_in = null) {
    let arr = [];

        ['primaryColumn', 'sortColumn'].forEach(function (property) {
            if (!config.hasOwnProperty(property)) {
                throw  `"${property}" property is mandatory !`;
            }
        });

        let primaryColumn = config.primaryColumn;
        let primaryParentColumn;
        let sortColumn = config.sortColumn;

        if (config.primaryParentColumn) {
            primaryParentColumn = config.primaryParentColumn;
        }

        $list.children().each(function (index, element) {
            $element = $(element);

            let union = $element.data('union');
            let union_id = $element.data('union_id');
            let isUnion = union && config.union[union];
            let primary = isUnion ? union_id : $(element).data(primaryColumn);

            if (isUnion) {
                primaryColumn = config.union[union].primaryColumn;
                sortColumn = config.union[union].sortColumn;

                if (config.union[union].primaryParentColumn) {
                    primaryParentColumn = config.union[union].primaryParentColumn;
                }
            }
            let item = {};

            if (jQuery.inArray(primary, collected) === -1) {

                if (isUnion) {
                    item['union'] = union;
                    item['union_id'] = union_id;
                }
                item['name'] = $element.data('name');
                item[`${primaryColumn}`] = $element.data(primaryColumn);
                item[`${sortColumn}`] = index;

                if (primaryParentColumn) {
                    item[`${primaryParentColumn}`] = id_in;
                }

                arr.push(item);
                collected.push(primary);
            }

            if ($element.find('.st_list_instance').children().length) {
                serialiseSort(collected, $element.find('.st_list_instance'), config, isUnion ? union_id : item[`${primaryColumn}`]).forEach(function (result) {
                    arr.push(result);
                });
            }
        });

        return arr;

};

let getCfg = function (string) {
    let cfg = window[string];
    if (typeof cfg === 'undefined') {
        throw Error('invalid config');
    }

    return cfg;
}

/**
 *  при натискане на запази бутон се обхожда дървото и се изпращат данните за записване.
 */
function st_sortable_submit(list, config, csrf_param) {

    let orders = serialiseSort([], $(`#${list}`), config);

    if (orders) {

        let data = {}
        data.orders = JSON.stringify(orders);
        data.config = JSON.stringify(config);
        data[csrf_param] = csrf_value;

        $.ajax({
            url: config.url,
            method: "POST",
            data: data,
            success: function (data) {
                alert(data);
            }
        });
    }
}

function onMove(evt) {
    let to = $(evt.to);
    let item = $(evt.dragged || evt.item);

    getCfg(item.closest('[data-config]').attr('data-config'))

    let config = eval(item.closest('[data-config]').attr('data-config'));

    if (config.maxDept < 1) {
        return true;
    }

    let childMaxDept = getChildMaxDept(item);
    let parentDept = getParentDept(to);

    if (to.length) {
        if (parentDept + childMaxDept <= config.maxDept) {
            removeDisabled(item);
            return true;
        }
    }

    addDisabled(item);

    return false;
}

function onEnd(evt) {
    removeDisabled($(evt.item));
}

function getParentDept(to) {
    let level = 1;
    let ParentList = to.parent().parent();

    if (ParentList.hasClass('st_list_instance')) {
        level += getParentDept(ParentList);
    }

    return level;
}

function getChildMaxDept(element) {
    $element = $(element);
    $children = $element.find('.st_list_instance').children();

    let subLevel = 0;
    if ($children.length) {

        $children.each(function (i, e) {
            let temp = getChildMaxDept(e);

            if (temp > subLevel) {
                subLevel = temp;
            }
        });

        subLevel++;
    }

    return subLevel
}

function removeDisabled(item) {
    item.removeClass('disabled');
}

function addDisabled(item) {
    item.addClass('disabled');
}
