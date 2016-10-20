/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {

    //浏览器改变大小时触发
    $(window).resize(function () {
        $('#user-add').dialog('center');
        $('#post-edit').dialog('center');
    });

    //帐号列表
    $('#user').datagrid({
        url : ThinkPHP['MODULE'] + '/User/getList',
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
        toolbar : '#user-tool',
        columns : [[
            {
                field : 'id',
                title : '自动编号',
                width : 100,
                checkbox : true
            },
            {
                field : 'accounts',
                title : '帐号名称',
                width : 80
            },
            {
                field : 'name',
                title : '关联档案',
                width : 80
            },
            {
                field : 'email',
                title : '电子邮件',
                width : 120
            },
            {
                field : 'last_login_time',
                title : '登录时间',
                width : 110,
                sortable : true
            },
            {
                field : 'last_login_ip',
                title : '登录IP',
                width : 80
            },
            {
                field : 'login_count',
                title : '登录次数',
                width : 60,
                sortable : true
            },
            {
                field : 'create_time',
                title : '创建时间',
                width : 100,
                sortable : true
            },
            {
                field : 'state',
                title : '状态',
                width : 43,
                fixed : true,
                sortable : true,
                formatter : function (value, row) {
                    var state = '';
                    switch (value) {
                        case '冻结' :
                            state = '<a href="javascript:void(0)" user-id="' + row.id + '" user-state="冻结" class="user-state user-state-1" style="height: 18px;margin-left:4px;"></a>';
                            break;
                        case '正常' :
                            state = '<a href="javascript:void(0)" user-id="' + row.id + '" user-state="正常" class="user-state user-state-2" style="height: 18px;margin-left:4px;"></a>';
                            break;
                    }
                    return state;
                }
            },
            {
                field: 'details1',
                title: '详情',
                width: 40,
                fixed : true,
                formatter : function (value,row) {
                    return '<a href="javascript:void(0)" class="user-details" style="height: 18px;margin-left:2px;" onclick="user_tool.details(' + row.id + ');"></a>';
                }
            }
        ]],
        onLoadSuccess : function(){
            $('.user-state-1').linkbutton({
                iconCls : 'icon-login',
                plain : true
            });
            $('.user-state-2').linkbutton({
                iconCls : 'icon-ok',
                plain : true
            });
            $('.user-state').click(function () {
                var id = $(this).attr('user-id'),
                    state = $(this).attr('user-state');

                switch (state) {
                    case '冻结' :
                        $.messager.confirm('确认','通过审核？',function(flag) {
                            if (flag){
                                $.ajax({
                                    url : ThinkPHP['MODULE'] + '/User/state',
                                    type : 'POST',
                                    data : {
                                        id : id,
                                        state : '正常'
                                    },
                                    beforeSend : function () {
                                        $.messager.progress({
                                            text : '正在通过审核...'
                                        });
                                    },
                                    success : function(data) {
                                        $.messager.progress('close');
                                        if (data > 0) {
                                            $.messager.show({
                                                title : '操作提醒',
                                                msg : '帐号审核通过！'
                                            });
                                            $('#user').datagrid('reload');
                                        }
                                    }
                                });
                            }
                        });
                        break;
                    case '正常' :
                        $.messager.confirm('确认','冻结帐号？',function(flag) {
                            if (flag){
                                $.ajax({
                                    url : ThinkPHP['MODULE'] + '/User/state',
                                    type : 'POST',
                                    data : {
                                        id : id,
                                        state : '冻结'
                                    },
                                    beforeSend : function () {
                                        $.messager.progress({
                                            text : '正在冻结帐号...'
                                        });
                                    },
                                    success : function(data) {
                                        $.messager.progress('close');
                                        if (data > 0) {
                                            $.messager.show({
                                                title : '操作提醒',
                                                msg : '帐号被冻结！'
                                            });
                                            $('#user').datagrid('reload');
                                        }
                                    }
                                });
                            }
                        });
                        break;
                }
            });
            $('.user-details').linkbutton({
                iconCls : 'icon-text',
                plain : true
            });
        },
        onClickCell : function (index, field) {
            if (field == 'state' || field == 'details') {
                $('#user').datagrid('selectRow', index);
            }
        }
    });

    //新增面板
    $('#user-add').dialog({
        width : 420,
        height : 300,
        title : '新增帐号',
        iconCls : 'icon-add-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            size : 'large',
            iconCls : 'icon-accept',
            handler : function () {
                if ($('#user-add').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/User/register',
                        type : 'POST',
                        data : {
                            accounts : $.trim($('input[name="user_accounts_add"]').val()),
                            password : $('input[name="user_password_add"]').val(),
                            email : $.trim($('input[name="user_email_add"]').val()),
                            state : $('input[name="user_state_add"]').val(),
                            uid : $('input[name="user_staff_add"]').val(),
                            name : $('#user-staff-add').combogrid('getText')
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
                                    msg : '添加帐号成功！'
                                });
                                $('#user-add').dialog('close');
                                $('#user').datagrid('load');
                            } else if (data == -1) {
                                $.messager.alert('添加失败！', '帐号名称已存在！', 'warning', function () {
                                    $('#user-accounts-add').textbox('textbox').select();
                                });
                            } else if (data == -2) {
                                $.messager.alert('添加失败！', '邮箱已存在！', 'warning', function () {
                                    $('#user-email-add').textbox('textbox').select();
                                });
                            } else {
                                $.messager.alert('添加失败！', '未知错误！', 'warning');
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
                $('#user-add').dialog('close');
            }
        }],
        onClose : function () {
            $('#user-add').form('reset');
        }
    });

    //修改面板
    $('#user-edit').dialog({
        width : 420,
        height : 330,
        title : '修改帐号',
        iconCls : 'icon-edit-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            size : 'large',
            iconCls : 'icon-accept',
            handler : function () {
                if ($('#user-edit').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/User/update',
                        type : 'POST',
                        data : {
                            id : $('input[name="user_id_edit"]').val(),
                            password : $('input[name="user_password_edit"]').val(),
                            email : $.trim($('input[name="user_email_edit"]').val()),
                            state : $('input[name="user_state_edit"]').val(),
                            name : $('input[name="user_name_edit"]').val(),
                            uid : $('input[name="user_staff_edit"]').val(),
                            user_name : $('#user-staff-edit').combogrid('getText')
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
                                    msg : '修改帐号成功！'
                                });
                                $('#user-edit').dialog('close');
                                $('#user').datagrid('reload');
                            } else if (data == -1) {
                                $.messager.alert('修改失败！', '密码长度不合法！', 'warning', function () {
                                    $('#user-password-edit').textbox('textbox').select();
                                });
                            } else if (data == -2) {
                                $.messager.alert('修改失败！', '邮箱已存在！', 'warning', function () {
                                    $('#user-email-edit').textbox('textbox').select();
                                });
                            } else {
                                $.messager.alert('修改失败！', '未修改或未知错误！', 'warning');
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
                $('#user-edit').dialog('close');
            }
        }],
        onClose : function () {
            $('#user-edit').form('reset');
        }
    });

    //新增和修改帐号
    $('#user-accounts-add, #user-accounts-edit').textbox({
        width : 240,
        height : 32,
        required : true,
        validType : 'length[2,20]',
        missingMessage : '请输入帐号名称',
        invalidMessage : '帐号名称2-20位'
    });

    //新增密码
    $('#user-password-add').textbox({
        width : 240,
        height : 32,
        required : true,
        validType : 'length[6,30]',
        missingMessage : '请输入帐号密码',
        invalidMessage : '帐号密码6-30位'
    });

    //修改密码
    $('#user-password-edit').textbox({
        width : 240,
        height : 32,
        validType : 'length[6,30]',
        invalidMessage : '密码需6-30位'
    });

    //新增和修改邮箱
    $('#user-email-add, #user-email-edit').textbox({
        width : 240,
        height : 32,
        validType : 'email',
        missingMessage : '请输入电子邮件',
        invalidMessage : '电子邮件格式不合法'
    });

    //关联档案
    $('#user-staff-add, #user-staff-edit').combogrid({
        width : 120,
        height : 32,
        url : ThinkPHP['MODULE'] + '/Staff/getNotRelationList',
        panelWidth: 450,
        panelHeight: 'auto',
        panelMaxHeight : 227,
        fitColumns : true,
        striped : true,
        rownumbers : true,
        border : false,
        idField:'id',
        textField:'name',
        editable : false,
        remoteSort : false,
        columns : [[
            {
                field : 'id',
                title : '编号',
                width : 50,
                hidden : true
            },
            {
                field : 'name',
                title : '姓名',
                width : 80
            },
            {
                field : 'number',
                title : '工号',
                width : 50,
                sortable : true
            },
            {
                field : 'gender',
                title : '性别',
                width : 50,
                sortable : true
            },
            {
                field : 'id_card',
                title : '身份证',
                width : 150
            },
            {
                field : 'post',
                title : '职位',
                width : 50
            }
        ]],
        onShowPanel : function () {
            $(this).combogrid('panel').panel('resize', {
                width : '450px'
            });
        }
    });

    //修改状态
    $('#user-state-edit').switchbutton({
        width: 65,
        onText : '正常',
        offText : '冻结',
        onChange: function(checked){
            if (checked) {
                $('input[name="user_state_edit"]').val('正常');
            } else {
                $('input[name="user_state_edit"]').val('冻结');
            }
        }
    });

    //状态搜索
    $('#user-search-state').combobox({
        width : 70,
        data : [{
            id : '正常',
            text : '正常'
        }, {
            id : '冻结',
            text : '冻结'
        }],
        editable : false,
        valueField : 'id',
        textField : 'text',
        panelHeight : 'auto'
    });

    //时间搜索
    $('#user-search-date').combobox({
        width : 90,
        data : [{
            id : 'last_login_time',
            text : '登录时间'
        },{
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
    $('#user-search-date-from, #user-search-date-to').datebox({
        onSelect : function () {
            if ($('#user-search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#user-search-date').combobox('showPanel');
            }
        }
    });

    //新增时的随机密码
    $('.rand-add').click(function() {
        $('#user-password-add').textbox('setValue', getRandPassword(8, 16));
    });

    //修改时的随机密码
    $('.rand-edit').click(function() {
        $('#user-password-edit').textbox('setValue', getRandPassword(8, 16));
    });

});

//工具栏操作模块
var user_tool = {
    search : function () {
        if ($('#user-tool').form('validate')) {
            $('#user').datagrid('load', {
                keywords: $.trim($('input[name="user_search_keywords"]').val()),
                date: $('input[name="user_search_date"]').val(),
                date_from: $('input[name="user_search_date_from"]').val(),
                date_to: $('input[name="user_search_date_to"]').val(),
                state: $('input[name="user_search_state"]').val()
            });
        } else {
            $('#user-search-date').combobox('showPanel');
        }
    },
    details : function (id) {
        $('#details-dialog').
                            dialog('open').
                            dialog('setTitle', '登录帐号详情').
                            dialog('refresh', ThinkPHP['MODULE'] + '/User/getDetails/?id=' + id);
    },
    add : function () {
        $('#user-add').dialog('open');
        $('#user-staff-add').combogrid('grid').datagrid('reload');
    },
    remove : function () {
        var rows = $('#user').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('确认操作', '您真的要删除所选的<strong>' + rows.length + '</strong>条记录吗？', function (flag) {
                if (flag) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i ++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/User/remove',
                        type : 'POST',
                        data : {
                            ids : ids.join(',')
                        },
                        beforeSend : function () {
                            $('#user').datagrid('loading');
                        },
                        success : function(data, response, status) {
                            if (data) {
                                $('#user').datagrid('loaded');
                                $('#user').datagrid('reload');
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : data + '个帐号被成功删除！'
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
        $('#user-staff-edit').combogrid('grid').datagrid('reload');
        var rows = $('#user').datagrid('getSelections');
        if (rows.length > 1) {
            $.messager.alert('警告操作', '编辑记录只能选定一条数据！', 'warning');
        } else if (rows.length == 1) {
            $('#user-edit').dialog('open');
            $.ajax({
                url : ThinkPHP['MODULE'] + '/User/getUser',
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
                        $('#user-edit').form('load', {
                            user_id_edit : data.id,
                            user_accounts_edit : data.accounts,
                            user_email_edit : data.email,
                            user_state_edit : data.state,
                            user_staff_edit : data.name,
                            user_name_edit : data.name
                        });
                        if (data.state == '正常') {
                            $('#user-state-edit').switchbutton('check');
                        } else {
                            $('#user-state-edit').switchbutton('uncheck');
                        }
                    }
                }
            });
        } else if (rows.length == 0) {
            $.messager.alert('警告操作', '编辑记录必须选定一条数据！', 'warning');
        }
    },
    redo : function () {
        $('#user').datagrid('unselectAll');
    },
    reload : function () {
        $('#user').datagrid('reload');
    },
    refresh : function () {
        var currentTabs = $('#tabs').tabs('getSelected');
        currentTabs.panel('refresh', ThinkPHP['MODULE'] + '/User/index');
    },
    reset : function () {
        $('#user-search-keywords').textbox('clear');
        $('#user-search-state').combobox('clear');
        $('#user-search-date').combobox('clear').combobox('disableValidation');
        $('#user-search-date-from').datebox('clear');
        $('#user-search-date-to').datebox('clear');
        $('#user').datagrid('resetSort', {
            sortName : 'create_time',
            sortOrder : 'desc'
        });
        this.search();
    }
};

//扩展获取随机密码
var getRandPassword = function (min,max)  {
    var source = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789',
        length = Math.ceil(Math.random() * (max - min) + min),
        password = '';

    for(var i = 0;i < length; i++)  {
        password += source.charAt(Math.ceil(Math.random() * 1000) % source.length);
    }
    return password;
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

//combox提示修正
// .siblings('span').find('input').focus(function () {
//    for (var i = 0; i < $('.tooltip-content').length; i ++) {
//        if ($('.tooltip-content').eq(i).text() == '请点击选择职位') {
//            $('.tooltip-content').eq(i).parent().css('margin-left', '18px');
//        }
//    }
//}).hover(function () {
//    for (var i = 0; i < $('.tooltip-content').length; i ++) {
//        if ($('.tooltip-content').eq(i).text() == '请点击选择职位') {
//            $('.tooltip-content').eq(i).parent().css('margin-left', '18px');
//        }
//    }
//}