/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {


    $('#log').datagrid({
        url : ThinkPHP['MODULE'] + '/Log/getList',
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
        toolbar : '#log-tool',
        columns : [[
            {
                field : 'user',
                title : '操作员',
                width : 80
            },
            {
                field : 'type',
                title : '操作类型',
                width : 80
            },
            {
                field : 'module',
                title : '操作模块',
                width : 80
            },
            {
                field : 'ip',
                title : '操作IP',
                width : 100
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
                $('#log').datagrid('selectRow', index);
            }
        }
    });



    //时间搜索
    $('#log-search-date').combobox({
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
    $('#log-search-date-from, #log-search-date-to').datebox({
        onSelect : function () {
            if ($('#log-search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#log-search-date').combobox('showPanel');
            }
        }
    });


});

//工具栏操作模块
var log_tool = {
    search : function () {
        if ($('#log-tool').form('validate')) {
            $('#log').datagrid('load', {
                keywords: $.trim($('input[name="log_search_keywords"]').val()),
                date: $('input[name="log_search_date"]').val(),
                date_from: $('input[name="log_search_date_from"]').val(),
                date_to: $('input[name="log_search_date_to"]').val()
            });
        } else {
            $('#outlib-search-date').combobox('showPanel');
        }
    },
    reload : function () {
        $('#log').datagrid('reload');
    },
    reset : function () {
        $('#log-search-keywords').textbox('clear');
        $('#log-search-date').combobox('clear').combobox('disableValidation');
        $('#log-search-date-from').datebox('clear');
        $('#log-search-date-to').datebox('clear');
        $('#log').datagrid('resetSort', {
            sortName : 'create_time',
            sortOrder : 'desc'
        });
        this.search();
    }
};



