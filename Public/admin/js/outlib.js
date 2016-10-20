/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {


    $('#outlib').datagrid({
        url : ThinkPHP['MODULE'] + '/Outlib/getList',
        fit : true,
        fitColumns : true,
        striped : true,
        rownumbers : true,
        border : false,
        pagination : true,
        pageSize : 20,
        pageList : [10, 20, 30, 40, 50],
        pageNumber : 1,
        sortName : 'create_time',
        sortOrder : 'DESC',
        toolbar : '#outlib-tool',
        columns : [[
            {
                field : 'id',
                title : '编号',
                width : 60,
                checkbox : true
            },
            {
                field : 'sn',
                title : '产品编号',
                width : 60
            },
            {
                field : 'name',
                title : '产品名称',
                width : 120
            },
            {
                field : 'sell_price',
                title : '销售价格',
                width : 60
            },
            {
                field : 'number',
                title : '出货量',
                width : 40
            },
            {
                field : 'order_sn',
                title : '所属订单',
                width : 80
            },
            {
                field : 'clerk',
                title : '发货员',
                width : 60
            },
            {
                field : 'keyboarder',
                title : '录入员',
                width : 60
            },
            {
                field : 'state',
                title : '状态',
                width : 60
            },
            {
                field : 'dispose_time',
                title : '出库时间',
                width : 100,
                sortable : true
            },
            {
                field : 'create_time',
                title : '创建时间',
                width : 100,
                sortable : true
            }
        ]],
        onClickCell : function (index, field) {
            if (field == 'state' || field == 'details') {
                $('#product').datagrid('selectRow', index);
            }
        }
    });



    //时间搜索
    $('#outlib-search-date').combobox({
        width : 90,
        data : [{
            id : 'create_time',
            text : '创建时间'
        }],
        editable : false,
        valueField : 'id',
        textField : 'text',
        required : true,
        missingMessage : '选择时间类型',
        panelHeight : 'auto',
        tipPosition : 'left',
        novalidate : true
    });

    //选择时间触发验证
    $('#outlib-search-date-from, #outlib-search-date-to').datebox({
        onSelect : function () {
            if ($('#outlib-search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#outlib-search-date').combobox('showPanel');
            }
        }
    });


});

//工具栏操作模块
var outlib_tool = {
    search : function () {
        if ($('#outlib-tool').form('validate')) {
            $('#outlib').datagrid('load', {
                keywords: $.trim($('input[name="outlib_search_keywords"]').val()),
                date: $('input[name="outlib_search_date"]').val(),
                date_from: $('input[name="outlib_search_date_from"]').val(),
                date_to: $('input[name="outlib_search_date_to"]').val()
            });
        } else {
            $('#outlib-search-date').combobox('showPanel');
        }
    },
    details : function (id) {
        $('#details-dialog').
            dialog('open').
            dialog('setTitle', '产品信息详情').
            dialog('refresh', ThinkPHP['MODULE'] + '/Outlib/getDetails/?id=' + id);
    },
    deliver : function () {
        var rows = $('#outlib').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('确认操作', '您要批量发货 <strong>' + rows.length + '</strong> 件产品吗？', function (flag) {
                if (flag) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i ++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Outlib/deliver',
                        type : 'POST',
                        data : {
                            ids : ids.join(',')
                        },
                        beforeSend : function () {
                            $('#outlib').datagrid('loading');
                        },
                        success : function(data, response, status) {
                            if (data) {
                                $('#outlib').datagrid('loaded');
                                $('#outlib').datagrid('reload');
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : data + '件产品成功出库！'
                                });
                            }
                        }
                    });
                }
            });

        } else {
            $.messager.alert('警告操作', '批量发货最少需要指定一件！', 'warning');
        }
    },
    repeal : function () {
        var rows = $('#outlib').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('确认操作', '您要批量撤销发货 <strong>' + rows.length + '</strong> 件产品吗？', function (flag) {
                if (flag) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i ++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Outlib/repeal',
                        type : 'POST',
                        data : {
                            ids : ids.join(',')
                        },
                        beforeSend : function () {
                            $('#outlib').datagrid('loading');
                        },
                        success : function(data, response, status) {
                            if (data) {
                                $('#outlib').datagrid('loaded');
                                $('#outlib').datagrid('reload');
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : data + '件产品成功撤销出库！'
                                });
                            }
                        }
                    });
                }
            });

        } else {
            $.messager.alert('警告操作', '批量发货最少需要指定一件！', 'warning');
        }
    },
    reload : function () {
        $('#outlib').datagrid('reload');
    },
    reset : function () {
        $('#outlib-search-keywords').textbox('clear');
        $('#outlib-search-date').combobox('clear').combobox('disableValidation');
        $('#outlib-search-date-from').datebox('clear');
        $('#outlib-search-date-to').datebox('clear');
        $('#outlib').datagrid('resetSort', {
            sortName : 'create_time',
            sortOrder : 'desc'
        });
        this.search();
    }
};



