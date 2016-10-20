/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {
    //浏览器改变大小时触发
    $(window).resize(function () {
        $('#add').dialog('center');
        $('#edit').dialog('center');
    });

    $('#info').datagrid({
        url : ThinkPHP['MODULE'] + '/Level/show',
        fit : true,
        autoRowHeight : false,
        RowHeight : 35,
        fitColumns : true,
        striped : true,
        rownumbers : true,
        border : false,
        pagination : true,
        pageSize : 20,
        pageList : [10, 20, 30, 40, 50],
        pageNumber : 1,
        sortName : 'addtime',
        sortOrder : 'DESC',
        toolbar : '#tool',
        columns : [[
            {
                field : 'id',
                title : '编号',
                align : 'center',
                width : 100,
                checkbox : true
            },
            {
                field : 'name',
                title : '职位名称',
                align : 'center',
                width : 100
            },
            {
                field : 'addtime',
                title : '创建时间',
                align : 'center',
                width : 100,
                sortable : true,
                formatter : function (value,row) {
                    return formatDate(new Date(value * 1000));
                }
            },
            {
                field: 'details',
                title: '操作',
                width: 40,
                align : 'center',
                fixed : true,
                formatter : function (value,row) {
                    return '<a href="javascript:void(0)" class="alarm-details" style="height: 18px;margin-left:2px;" onclick="alarm_tool.details(' + row.id + ');"></a>';
                }
            }
        ]]
    });

    $('#add').dialog({
        width : 400,
        height : 190,
        title : '新增职位',
        iconCls : 'icon-add-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            iconCls : 'icon-accept',
            size : 'large',
            handler : function () {
                if ($('#add').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Level/register',
                        type : 'POST',
                        data : {
                            name : $('input[name="name_add"]').val()
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
                                    msg : '添加职位成功！'
                                });
                                $('#add').dialog('close');
                                $('#info').datagrid('load');
                            } else if (data == -1) {
                                $.messager.alert('添加失败！', '职位名称已存在！', 'warning', function () {
                                    $('#name-add').siblings('span').find('input').select();
                                });
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
                $('#add').dialog('close');
            }
        }],
        onClose : function () {
            $('#add').form('reset');
        }
    });

    $('#edit').dialog({
        width : 390,
        height : 190,
        title : '修改职位',
        iconCls : 'icon-edit-new',
        modal : true,
        closed : true,
        maximizable : true,
        buttons : [{
            text : '保存',
            size : 'large',
            iconCls : 'icon-accept',
            handler : function () {
                if ($('#edit').form('validate')) {
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Post/update',
                        type : 'POST',
                        data : {
                            id : $('input[name="id_edit"]').val(),
                            name : $('input[name="name_edit"]').val()
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
                                    msg : '修改职位成功！'
                                });
                                $('#edit').dialog('close');
                                $('#info').datagrid('load');
                            } else if (data == -1) {
                                $.messager.alert('修改失败！', '职位名称已存在！', 'warning', function () {
                                    $('#name-edit').siblings('span').find('input').select();
                                });
                            } else if (data == 0) {
                                $.messager.alert('修改失败！', '职位尚未修改！', 'warning', function () {
                                    $('#name-edit').siblings('span').find('input').select();
                                });
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
                $('#edit').dialog('close');
            }
        }],
        onClose : function () {
            $('#edit').form('reset');
        }
    });

    $('#name-add,#name-edit').textbox({
        width : 220,
        height : 32,
        required : true,
        validType : 'length[2,20]',
        missingMessage : '请输入职位名称',
        invalidMessage : '职位名称2-20位'
    });

    //时间搜索
    $('#search-date').combobox({
        width : 90,
        data : [{
            id : 'addtime',
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
    $('#search-date-from, #search-date-to').datebox({
        onSelect : function () {
            if ($('#search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#search-date').combobox('showPanel');
            }
        }
    });

});

//工具栏操作模块
var tool = {
    search : function () {
        if ($('#tool').form('validate')) {
            $('#info').datagrid('load', {
                keywords: $.trim($('input[name="keywords"]').val()),
                datetype: $('input[name="datetype"]').val(),
                starttime: $('input[name="starttime"]').val(),
                stoptime: $('input[name="stoptime"]').val()
            });
        }else {
            $('#search-date').combobox('showPanel');
        }
    },
    add : function () {
        $('#add').dialog('open');
        $('#name-add').siblings('span').find('input').select();
    },
    remove : function () {
        var rows = $('#info').datagrid('getSelections');
        if (rows.length > 0) {
            $.messager.confirm('确认操作', '您真的要删除所选的<strong>' + rows.length + '</strong>条记录吗？', function (flag) {
                if (flag) {
                    var ids = [];
                    for (var i = 0; i < rows.length; i ++) {
                        ids.push(rows[i].id);
                    }
                    $.ajax({
                        url : ThinkPHP['MODULE'] + '/Post/remove',
                        type : 'POST',
                        data : {
                            ids : ids.join(',')
                        },
                        beforeSend : function () {
                            $('#info').datagrid('loading');
                        },
                        success : function(data, response, status) {
                            if (data) {
                                $('#info').datagrid('loaded');
                                $('#info').datagrid('reload');
                                $.messager.show({
                                    title : '操作提醒',
                                    msg : data + '个职位被成功删除！'
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
        var rows = $('#info').datagrid('getSelections');
        if (rows.length > 1) {
            $.messager.alert('警告操作', '编辑记录只能选定一条数据！', 'warning');
        } else if (rows.length == 1) {
            $('#edit').dialog('open');
            $.ajax({
                url : ThinkPHP['MODULE'] + '/Post/getPost',
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
                        $('#edit').form('load', {
                            id_edit : data.id,
                            name_edit : data.name
                        });
                        $('#name-edit').siblings('span').find('input').select();
                    }
                }
            });
        } else if (rows.length == 0) {
            $.messager.alert('警告操作', '编辑记录必须选定一条数据！', 'warning');
        }
    },
    redo : function () {
        $('#info').datagrid('unselectAll');
    },
    reload : function () {
        $('#info').datagrid('reload');
    },
    refresh : function () {
        var currentTabs = $('#tabs').tabs('getSelected');
        currentTabs.panel('refresh', ThinkPHP['MODULE'] + '/Post/index');
    },
    reset : function () {
        $('#search-name').textbox('clear');
        $('#search-date').combobox('clear').combobox('disableValidation');
        $('#search-date-from').datebox('clear');
        $('#search-date-to').datebox('clear');
        $('#info').datagrid('resetSort', {
            sortName : 'create_time',
            sortOrder : 'desc'
        });
        this.search();
    }
};
