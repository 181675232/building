/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {

    $('#work').datagrid({
        url : ThinkPHP['MODULE'] + '/Work/getList',
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
        toolbar : '#work-tool',
        columns : [[
            {
                field : 'id',
                title : '自动编号',
                width : 60,
                checkbox : true
            },
            {
                field : 'title',
                title : '工作名称',
                width : 120
            },
            {
                field : 'type',
                title : '业务类型',
                width : 80
            },
            {
                field : 'stage',
                title : '完成阶段',
                width : 100
            },
            {
                field : 'state',
                title : '状态',
                width : 80
            },
            {
                field : 'user',
                title : '实行人',
                width : 80
            },
            {
                field : 'start_date',
                title : '开始时间',
                width : 120
            },
            {
                field : 'end_date',
                title : '结束时间',
                width : 120
            },
            {
                field : 'create_time',
                title : '创建时间',
                width : 120,
                sortable : true
            },
            {
                field: 'details',
                title: '详情',
                width: 40,
                fixed : true,
                formatter : function (value,row) {
                    return '<a href="javascript:void(0)" class="work-details" style="height: 18px;margin-left:2px;" onclick="client_tool.details(' + row.id + ');"></a>';
                }
            }
        ]],
        onLoadSuccess : function() {
            $('.work-details').linkbutton({
                iconCls : 'icon-text',
                plain : true
            });
        },
        onClickCell : function (index, field) {
            if (field == 'details') {
                $('#work').datagrid('selectRow', index);
            }
        }
    });

    //状态搜索
    $('#work-search-state').combobox({
        width : 70,
        data : [{
            id : '进行中',
            text : '进行中'
        }, {
            id : '已完成',
            text : '已完成'
        }, {
            id : '作废',
            text : '作废'
        }],
        editable : false,
        valueField : 'id',
        textField : 'text',
        panelHeight : 'auto'
    });

    //状态搜索
    $('#work-search-type').combobox({
        width : 70,
        data : [{
            id : '业务',
            text : '业务'
        }, {
            id : '内勤',
            text : '内勤'
        }],
        editable : false,
        valueField : 'id',
        textField : 'text',
        panelHeight : 'auto'
    });

    //时间搜索
    $('#work-search-date').combobox({
        width : 90,
        data : [{
            id : 'start_date',
            text : '开始时间'
        }, {
            id : 'end_date',
            text : '结束时间'
        }, {
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
    $('#work-search-date-from, #work-search-date-to').datebox({
        onSelect : function () {
            if ($('#work-search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#work-search-date').combobox('showPanel');
            }
        }
    });

    $('#work-title-edit, #work-type-edit, #work-start-date-edit, #work-end-date-edit').textbox({
        width : 240,
        height : 32,
        disabled : true
    });

	
	//新增工作计划
    $('#work-title-add').textbox({
        width : 240,
        height : 32,
        required : true,
        validType : 'length[2,20]',
        missingMessage : '请输入工作计划',
        invalidMessage : '工作计划字数2-20位'
    });
	
    //工作类型
    $('#work-type-add').combobox({
        width : 120,
		height : 32,
        data : [{
            id : '业务',
            text : '业务'
        }, {
            id : '内勤',
            text : '内勤'
        }],
        required : true,
        editable : false,
        valueField : 'id',
        textField : 'text',
        panelHeight : 'auto'
    });

    //工作类型
    $('#work-start-date-add, #work-end-date-add').datebox({
        width : 180,
        height : 32,
        required : true,
        editable : false
    });

    //工作完成阶段列表
    $('#work-stage-button-add').linkbutton({
        iconCls : 'icon-add-new',
        onClick : function () {
            $('#work-stage-list').datagrid('appendRow', {

            }).datagrid('beginEdit', $('#work-stage-list').datagrid('getRows').length - 1);

            $('#work-stage-save-add').show();
            $('#work-stage-cancel-add').show();
            $('#work-stage-button-add').hide();
            $('#work-stage-finish-add').hide();
        }
    });

    $('#work-stage-finish-add').linkbutton({
        iconCls : 'icon-accept',
        onClick : function () {
            $.ajax({
                url : ThinkPHP['MODULE'] + '/Work/finish',
                type : 'POST',
                data : {
                    work_id : $('#work-id-edit').val()
                },
                success : function(data) {
                    if (data) {
                        $('#work-stage-list').datagrid('reload');
                        $('#work-stage-save-add').hide();
                        $('#work-stage-cancel-add').hide();
                        $('#work-stage-button-add').hide();
                        $('#work-stage-finish-add').hide();
                    }
                }
            });
        }
    });

    $('#work-stage-save-add').linkbutton({
        iconCls : 'icon-accept',
        onClick : function () {
            $('#work-stage-list').datagrid('endEdit', $('#work-stage-list').datagrid('getRows').length - 1);
        }
    });

    $('#work-stage-cancel-add').linkbutton({
        iconCls : 'icon-redo',
        onClick : function () {
            $('#work-stage-list').datagrid('rejectChanges');
            $('#work-stage-save-add').hide();
            $('#work-stage-cancel-add').hide();
            $('#work-stage-button-add').show();
            $('#work-stage-finish-add').show();
        }
    });



    //新增面板
    $('#work-add').dialog({
        width : 420,
        height : 300,
        title : '创建工作',
        iconCls : 'icon-add-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            size : 'large',
            iconCls : 'icon-accept',
            handler : function () {
                if ($('#work-add').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Work/register',
                        type : 'POST',
                        data : {
                            title : $.trim($('input[name="work_title_add"]').val()),
                            type : $('input[name="work_type_add"]').val(),
                            start_time : $.trim($('input[name="work_start_time_add"]').val()),
                            end_time : $('input[name="work_end_time_add"]').val()
                        },
                        beforeSend : function () {
                            $.messager.progress({
                                text : '正在尝试保存...'
                            });
                        },
                        success : function(data) {
                            $.messager.progress('close');
                            if (data > 0) {
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : '添加工作计划成功！'
                                });
                                $('#work-add').dialog('close');
                                $('#work').datagrid('load');
                            }
                        }
                    });
                }
            }
        },{
            text : '取消',
            size : 'large',
            iconCls : 'icon-cross',
            handler : function () {
                $('#work-add').dialog('close');
            }
        }],
        onClose : function () {
            $('#work-add').form('reset');
        }
    });
	
    $('#work-edit').dialog({
        width : 780,
        height : 500,
        title : '更新工作阶段',
        iconCls : 'icon-edit-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '关闭',
            size : 'large',
            iconCls : 'icon-cross',
            handler : function () {
                $('#work-edit').dialog('close');
            }
        }],
        onClose : function () {
            $('#work-stage-save-add').hide();
            $('#work-stage-cancel-add').hide();
            $('#work-stage-button-add').show();
            $('#work-stage-finish-add').show();
            $('#work-edit').form('reset');
        }
    });

	
	
});

//工具栏操作模块
var work_tool = {
    search : function () {
        if ($('#work-tool').form('validate')) {
            $('#work').datagrid('load', {
                keywords: $.trim($('input[name="work_search_keywords"]').val()),
                date: $('input[name="work_search_date"]').val(),
                date_from: $('input[name="work_search_date_from"]').val(),
                date_to: $('input[name="work_search_date_to"]').val(),
                type: $('input[name="work_search_type"]').val(),
                state: $('input[name="work_search_state"]').val()
            });
        } else {
            $('#work-search-date').combobox('showPanel');
        }
    },
    details : function (id) {
        $('#details-dialog').
            dialog('open').
            dialog('setTitle', '客户信息详情').
            dialog('refresh', ThinkPHP['MODULE'] + '/Client/getDetails/?id=' + id);
    },
    reload : function () {
        $('#work').datagrid('reload');
    },
    redo : function () {
        $('#work').datagrid('unselectAll');
    },
	add : function () {
        $('#work-add').dialog('open');
    },
    cancel : function () {
        var rows = $('#work').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('确认操作', '您真的要作废所选的<strong>' + rows.length + '</strong>条工作计划吗？', function (flag) {
                if (flag) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i ++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Work/cancel',
                        type : 'POST',
                        data : {
                            ids : ids.join(',')
                        },
                        beforeSend : function () {
                            $('#work').datagrid('loading');
                        },
                        success : function(data, response, status) {
                            if (data) {
                                $('#work').datagrid('loaded');
                                $('#work').datagrid('reload');
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : data + '个工作计划被成功作废！'
                                });
                            }
                        }
                    });
                }
            });

        } else {
            $.messager.alert('警告操作', '作废操作必须至少指定一个记录！', 'warning');
        }
    },
    edit : function () {
        var rows = $('#work').datagrid('getSelections');
        if (rows.length > 1) {
            $.messager.alert('警告操作', '更新工作阶段只能选定一条数据！', 'warning');
        } else if (rows.length == 1) {
            $('#work-edit').dialog('open');
            $.ajax({
                url : ThinkPHP['MODULE'] + '/Work/getOne',
                type : 'POST',
                data : {
                    id : rows[0].id
                },
                beforeSend : function () {
                    $.messager.progress({
                        text : '正在获取信息...'
                    });
                },
                success : function(data) {
                    $.messager.progress('close');
                    if (data) {
                        $('#work-edit').form('load', {
                            work_id_edit : data.id,
							work_title_edit : data.title,
							work_type_edit : data.type,
                            work_start_date_edit : data.start_date,
                            work_end_date_edit : data.end_date
                        });
                        if (data.state == '已完成') {
                            $('#work-button').hide();
                        } else {
                            $('#work-button').show();
                        }
                    }
                }
            });

            $('#work-stage-list').datagrid({
                url : ThinkPHP['MODULE'] + '/Work/getStage',
                queryParams : {
                    id : rows[0].id
                },
                fitColumns : true,
                striped : true,
                rownumbers : true,
                border : true,
                columns : [[
                    {
                        field : 'stage',
                        title : '完成阶段',
                        width : 100,
                        editor : {
                            type : 'combobox',
                            options : {
                                required : true,
                                valueField:'id',
                                textField:'text',
                                data : [{
                                    id : '跟单进行中',
                                    text : '跟单进行中'
                                }, {
                                    id : '达成订单',
                                    text : '达成订单'
                                }, {
                                    id : '订单已付款',
                                    text : '订单已付款'
                                }, {
                                    id: '申请采购资金',
                                    text: '申请采购资金'
                                }, {
                                    id: '采购货物',
                                    text: '采购货物'
                                }],
                                missingMessage : '请输入或选择工作阶段'
                            }
                        }
                    },
                    {
                        field : 'create_time',
                        title : '创建时间',
                        width : 100
                    }
                ]],
                onClickCell : function (index) {
                    $('#work-stage-list').datagrid('selectRow', index);
                },
                onAfterEdit : function (index, row, changes) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Work/addStage',
                        type : 'POST',
                        data : {
                            work_id : $('#work-id-edit').val(),
                            stage : row.stage
                        },
                        success : function(data) {
                            if (data) {
                                $('#work-stage-list').datagrid('reload');
                                $('#work-stage-save-add').hide();
                                $('#work-stage-cancel-add').hide();
                                $('#work-stage-button-add').show();
                                $('#work-stage-finish-add').show();
                            }
                        }
                    });

                }
            });

        } else if (rows.length == 0) {
            $.messager.alert('警告操作', '更新工作阶段必须选定一条数据！', 'warning');
        }
    },
    reset : function () {
        $('#work-search-keywords').textbox('clear');
        $('#work-search-date').combobox('clear').combobox('disableValidation');
        $('#work-search-date-from').datebox('clear');
        $('#work-search-date-to').datebox('clear');
        $('#work-search-type').combobox('clear');
        $('#work-search-state').combobox('clear');
        $('#work').datagrid('resetSort', {
            sortName : 'create_time',
            sortOrder : 'desc'
        });
        this.search();
    }
};

//扩展手机验证功能
$.extend($.fn.validatebox.defaults.rules, {
    tel: {
        validator: function(value){
            return /^1[0-9]{10}$/.test(value);
        },
        message: '手机格式不正确'
    }
});