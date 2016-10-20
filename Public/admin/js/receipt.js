/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {

    $('#receipt').datagrid({
        url : ThinkPHP['MODULE'] + '/Receipt/getList',
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
        toolbar : '#receipt-tool',
        columns : [[
            {
                field : 'id',
                title : '产品编号',
                width : 60,
                checkbox : true
            },
            {
                field : 'sn',
                title : '付款编号',
                width : 100
            },
            {
                field : 'order_title',
                title : '订单标题',
                width : 150
            },
            {
                field : 'order_amount',
                title : '付款金额',
                width : 80
            },
            {
                field : 'way',
                title : '付款方式',
                width : 80
            },
            {
                field : 'remark',
                title : '备注',
                width : 100
            },
            {
                field : 'enter',
                title : '录入员',
                width : 80
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
                    return '<a href="javascript:void(0)" class="client-details" style="height: 18px;margin-left:2px;" onclick="client_tool.details(' + row.id + ');"></a>';
                }
            }
        ]],
        onLoadSuccess : function() {
            $('.client-details').linkbutton({
                iconCls : 'icon-text',
                plain : true
            });
        },
        onClickCell : function (index, field) {
            if (field == 'details') {
                $('#client').datagrid('selectRow', index);
            }
        }
    });

    //时间搜索
    $('#receipt-search-date').combobox({
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
    $('#receipt-search-date-from, #receipt-search-date-to').datebox({
        onSelect : function () {
            if ($('#receipt-search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#receipt-search-date').combobox('showPanel');
            }
        }
    });


    //新增和修改订单
    $('#receipt-order-title-add, #receipt-title-edit').textbox({
        width : 240,
        height : 32,
        editable : false,
        icons: [{
            iconCls:'icon-zoom',
            handler: function(){
                $('#receipt-order').dialog('open');
            }
        }],
        required : true,
        missingMessage : '请点击放大镜图标选择跟单',
        invalidMessage : '跟单记录不得为空'
    });

    //弹出选择订单记录
    $('#receipt-order').dialog({
        width: 550,
        height: 380,
        title: '选择订单',
        iconCls: 'icon-zoom',
        modal: true,
        closed: true,
        maximizable: true,
        onOpen : function () {
            $('#receipt-order-search-keywords').textbox();
            $('#receipt-order-search-button').linkbutton();
            $('#receipt-order-search-refresh').linkbutton();
            $('#receipt-order-search-reset').linkbutton();
            $('#receipt-order-table').datagrid({
                url : ThinkPHP['MODULE'] + '/Order/getList',
                queryParams : {
                    neg : true
                },
                fit : true,
                fitColumns : true,
                striped : true,
                rownumbers : true,
                border : false,
                pagination : true,
                pageSize : 10,
                pageList : [10, 20, 30, 40, 50],
                pageNumber : 1,
                sortName : 'create_time',
                sortOrder : 'DESC',
                toolbar : '#receipt-order-tool',
                columns : [[
                    {
                        field : 'sn',
                        title : '订单编号',
                        width : 100
                    },
                    {
                        field : 'title',
                        title : '订单标题',
                        width : 150
                    },
                    {
                        field : 'amount',
                        title : '订单金额',
                        width : 80
                    },
                    {
                        field : 'select',
                        title : '选择订单',
                        width : 60,
                        formatter : function (value, row) {
                            return '<a href="javascript:void(0)" class="select-button" style="height: 18px;margin-left:2px;" onclick="receipt_order_tool.select(\'' + row.id + '\', \'' + row.title + '\', \'' + row.amount + '\');">选择</a>';
                        }
                    },
                    {
                        field : 'create_time',
                        title : '创建时间',
                        width : 60,
                        hidden : true
                    }
                ]],
                onLoadSuccess : function() {
                    $('.select-button').linkbutton({
                        iconCls : 'icon-tick',
                        plain : true
                    });
                },
                onClickCell : function (index) {
                    $('#documentary-search-client').datagrid('selectRow', index);
                }
            });
        }
    });
	
	//新增和修改订单金额
    $('#receipt-order-amount-add, #receipt-order-amount-edit').textbox({
        width : 240,
        height : 32,
        editable : false
    });
	
	//新增和修改联系人名称
    $('#client-name-add, #client-name-edit').textbox({
        width : 240,
        height : 32,
        required : true,
        validType : 'length[2,20]',
        missingMessage : '请输入联系人',
        invalidMessage : '公司名称2-20位'
    });
	
	//新增和修改移动电话
    $('#client-tel-add, #client-tel-edit').textbox({
        width : 240,
        height : 32,
        required : true,
        validType : 'tel',
        missingMessage : '请输入移动电话',
        invalidMessage : '移动电话11位'
    });
	
    //客户来源
    $('#client-source-add, #client-source-edit').combobox({
        width : 120,
		height : 32,
        data : [{
            id : '广告媒体',
            text : '广告媒体'
        }, {
            id : '电话营销',
            text : '电话营销'
        }, {
            id : '主动联系',
            text : '主动联系'
		}],
        editable : false,
        valueField : 'id',
        textField : 'text',
        panelHeight : 'auto'
    });
	
    //新增面板
    $('#receipt-add').dialog({
        width : 420,
        height : 300,
        title : '新增收款项',
        iconCls : 'icon-add-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            size : 'large',
            iconCls : 'icon-accept',
            handler : function () {
                if ($('#receipt-add').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Receipt/register',
                        type : 'POST',
                        data : {
                            order_id : $('input[name="receipt_order_id_add"]').val(),
                            order_title : $.trim($('input[name="receipt_order_title_add"]').val()),
                            order_amount : $('input[name="receipt_order_amount_add"]').val(),
                            way : $.trim($('input[name="receipt_way_add"]').val()),
                            remark : $('input[name="receipt_remark_add"]').val()
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
                                    msg : '添加收款记录成功！'
                                });
                                $('#receipt-add').dialog('close');
                                $('#receipt').datagrid('load');
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
                $('#receipt-add').dialog('close');
            }
        }],
        onClose : function () {
            $('#receipt-add').form('reset');
        }
    });
	
    $('#client-edit').dialog({
        width : 420,
        height : 300,
        title : '修改客户信息',
        iconCls : 'icon-edit-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            size : 'large',
            iconCls : 'icon-accept',
            handler : function () {
                if ($('#client-edit').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Client/update',
                        type : 'POST',
                        data : {
                            id : $('input[name="client_id_edit"]').val(),
                            company : $('input[name="client_company_edit"]').val(),
							name : $('input[name="client_name_edit"]').val(),
							tel : $('input[name="client_tel_edit"]').val(),
							source : $('input[name="client_source_edit"]').val()
                        },
                        beforeSend : function () {
                            $.messager.progress({
                                text : '正在尝试保存...',
                            });
                        },
                        success : function(data) {
                            $.messager.progress('close');
                            if (data > 0) {
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : '修改客户成功！'
                                });
                                $('#client-edit').dialog('close');
                                $('#client').datagrid('load');
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
                $('#client-edit').dialog('close');
            }
        }],
        onClose : function () {
            $('#client-edit').form('reset');
        }
    });


    //付款方式
    $('#receipt-way-add, #receipt-way-edit').combobox({
        width : 150,
        height : 32,
        data : [{
            id : '银行转账',
            text : '银行转账'
        }, {
            id : '支付宝',
            text : '支付宝'
        }, {
            id : '现金支付',
            text : '现金支付'
        }],
        editable : false,
        valueField : 'id',
        textField : 'text',
        panelHeight : 'auto'
    });

    $('#receipt-remark-add, #receipt-remark-edit').textbox({
        width: 240,
        height: 32
    });

});

//工具栏操作模块
var receipt_tool = {
    search : function () {
        if ($('#client-tool').form('validate')) {
            $('#client').datagrid('load', {
                keywords: $.trim($('input[name="client_search_keywords"]').val()),
                date: $('input[name="client_search_date"]').val(),
                date_from: $('input[name="client_search_date_from"]').val(),
                date_to: $('input[name="client_search_date_to"]').val()
            });
        } else {
            $('#client-search-date').combobox('showPanel');
        }
    },
    details : function (id) {
        $('#details-dialog').
            dialog('open').
            dialog('setTitle', '客户信息详情').
            dialog('refresh', ThinkPHP['MODULE'] + '/Client/getDetails/?id=' + id);
    },
    reload : function () {
        $('#receipt').datagrid('reload');
    },
	add : function () {
        $('#receipt-add').dialog('open');
    },
    remove : function () {
        var rows = $('#client').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('确认操作', '您真的要删除所选的<strong>' + rows.length + '</strong>条记录吗？', function (flag) {
                if (flag) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i ++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Client/remove',
                        type : 'POST',
                        data : {
                            ids : ids.join(',')
                        },
                        beforeSend : function () {
                            $('#client').datagrid('loading');
                        },
                        success : function(data, response, status) {
                            if (data) {
                                $('#client').datagrid('loaded');
                                $('#client').datagrid('reload');
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : data + '个客户被成功删除！'
                                });
                            }
                        }
                    });
                }
            });

        } else {
            $.messager.alert('警告操作', '删除操作必须至少指定一个记录！', 'warning');
        }
    },
    edit : function () {
        var rows = $('#client').datagrid('getSelections');
        if (rows.length > 1) {
            $.messager.alert('警告操作', '编辑记录只能选定一条数据！', 'warning');
        } else if (rows.length == 1) {
            $('#client-edit').dialog('open');
            $.ajax({
                url : ThinkPHP['MODULE'] + '/Client/getClient',
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
                        $('#client-edit').form('load', {
                            client_company_edit : data.company,
							client_id_edit : data.id,
							client_name_edit : data.name,
							client_tel_edit : data.tel,
							client_source_edit : data.source
                        });
                    }
                }
            });
        } else if (rows.length == 0) {
            $.messager.alert('警告操作', '编辑记录必须选定一条数据！', 'warning');
        }
    },
    reset : function () {
        $('#client-search-keywords').textbox('clear');
        $('#client-search-date').combobox('clear').combobox('disableValidation');
        $('#client-search-date-from').datebox('clear');
        $('#client-search-date-to').datebox('clear');
        $('#client-search-type').combobox('clear');
        $('#client').datagrid('resetSort', {
            sortName : 'create_time',
            sortOrder : 'desc'
        });
        this.search();
    }
};

//工具栏操作模块
var receipt_order_tool = {
    search : function () {
        $('#order-documentary-table').datagrid('load', {
            keywords: $.trim($('input[name="receipt_order_search_keywords"]').val()),
            neg : true
        });
    },
    select : function (id, title, amount) {
        $('#receipt-order-title-add').textbox('setValue', title);
        $('#receipt-order-id-add').val(id);
        $('#receipt-order-amount-add').textbox('setValue', amount);
        $('#receipt-order').dialog('close');
        this.reset();
    },
    reset : function () {
        $('#order-documentary-search-keywords').textbox('clear');
        $('#order-documentary-table').datagrid('resetSort', {
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