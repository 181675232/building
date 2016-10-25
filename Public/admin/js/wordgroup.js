/**
 * Created by ASUS on 2016/1/3.
 */
$(function () {
    //浏览器改变大小时触发
    (function  (NAME) {
        $(window).resize(function () {
            $('#'+NAME+'add').dialog('center');
            $('#'+NAME+'edit').dialog('center');
        });
        $('#'+NAME+'').datagrid({
            url : ThinkPHP['MODULE'] + '/'+NAME+'/show',
            fit : true,
            fitColumns : true,
            RowHeight : 35,
            striped : true,
            rownumbers : true,
            border : false,
            pagination : true,
            pageSize : 20,
            pageList : [10, 20, 30, 40, 50],
            pageNumber : 1,
            sortName : 'addtime',
            sortOrder : 'DESC',
            toolbar : '#'+NAME+'-tool',
            columns : [[
                {
                    field : 'id',
                    title : '编号',
                    align : 'center',
                    width : 100,
                    checkbox : true
                },
                {
                    field : 'title',
                    title : '名称',
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
                    width: 60,
                    align : 'center',
                    fixed : true,
                    formatter : function (value,row) {
                        //'<a title="详情" href="javascript:void(0)" class="'+NAME+'-details" style="height: 20px;margin: 1px" onclick="PUBLIC_TOOL.'+NAME+'_tool.details(' + row.id + ');"></a>'
                        return  '<a title="编辑" href="javascript:void(0)" class="'+NAME+'-edit" style="height: 20px;margin: 1px" onclick="PUBLIC_TOOL.'+NAME+'_tool.edit(' + row.id + ');"></a>';

                    }
                }
            ]],
            onLoadSuccess : function() {
                $('.'+NAME+'-details').linkbutton({
                    iconCls : 'icon-text',
                    plain : true
                });
                $('.'+NAME+'-edit').linkbutton({
                    iconCls : 'icon-edit-new',
                    plain : true
                });
            },
            onClickCell : function (index,field) {
                if (field == 'state' || field == 'details'){
                    $('#'+NAME).datagrid('selectRow', index);
                }
            }
        });

        $('#'+NAME+'-add').dialog({
            width : 420,
            height : 'auto',
            title : '新增信息',
            iconCls : 'icon-add-new',
            modal : true,
            closed : true,
            maximizable : true,
            buttons : [{
                text : '保存',
                size : 'large',
                iconCls : 'icon-accept',
                handler : function () {
                    if ($('#'+NAME+'-add').form('validate')) {
                        $.ajax({
                            url : ThinkPHP['MODULE'] + '/'+NAME+'/add',
                            type : 'POST',
                            data : {
                                title : $('input[name="'+NAME+'_title_add"]').val(),
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
                                        msg : '添加成功！'
                                    });
                                    $('#'+NAME+'-add').dialog('close');
                                    $('#'+NAME+'').datagrid('load');
                                } else {
                                    $.messager.alert('添加失败！', data, 'warning');
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
                    $('#'+NAME+'-add').dialog('close');
                }
            }],
            onClose : function () {
                $('#'+NAME+'-add').form('reset');
            }
        });

        $('#'+NAME+'-edit').dialog({
            width : 420,
            height : 'auto',
            title : '编辑信息',
            iconCls : 'icon-edit-new',
            modal : true,
            closed : true,
            maximizable : true,
            buttons : [{
                text : '保存',
                size : 'large',
                iconCls : 'icon-accept',
                handler : function () {
                    if ($('#'+NAME+'-edit').form('validate')) {
                        $.ajax({
                            url : ThinkPHP['MODULE'] + '/'+NAME+'/edit',
                            type : 'POST',
                            data : {
                                id : $('input[name="'+NAME+'_id_edit"]').val(),
                                title : $('input[name="'+NAME+'_title_edit"]').val(),
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
                                        msg : '编辑成功！'
                                    });
                                    $('#'+NAME+'-edit').dialog('close');
                                    $('#'+NAME+'').datagrid('load');
                                } else {
                                    $.messager.alert('编辑失败！', data, 'warning');
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
                    $('#'+NAME+'-edit').dialog('close');
                }
            }],
            onClose : function () {
                $('#'+NAME+'-edit').form('reset');
            }
        });
        /* ---------------------------弹出框样式-------------------------------------------*/
        $('#'+NAME+'-title-add,#'+NAME+'-title-edit').textbox({
            width : 220,
            height : 32,
            required : true,
            validType : 'length[2,20]',
            missingMessage : '请输入名称',
            invalidMessage : '名称2-20位'
        });
        /* ---------------------------弹出框样式-------------------------------------------*/
        //时间搜索
        $('#'+NAME+'-search-date').combobox({
            width : 80,
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
        $('#'+NAME+'-search-date-from, #'+NAME+'-search-date-to').datebox({
            onSelect : function () {
                if ($('#'+NAME+'-search-date').combobox('enableValidation').combobox('isValid') == false) {
                    $('#'+NAME+'-search-date').combobox('showPanel');
                }
            }
        });

    })(PUBLIC_STR_NAME);
});

//工具栏操作模块
PUBLIC_TOOL[PUBLIC_STR_NAME+'_tool'] = (function  (NAME) {
    return {
        search : function () {
            if ($('#'+NAME+'-tool').form('validate')) {
                $('#'+NAME+'').datagrid('load', {
                    keywords: $.trim($('input[name="'+NAME+'_search_keywords"]').val()),
                    date: $('input[name="'+NAME+'_search_date"]').val(),
                    date_from: $('input[name="'+NAME+'_search_date_from"]').val(),
                    date_to: $('input[name="'+NAME+'_search_date_to"]').val()
                });
            } else {
                $('#'+NAME+'-search-date').combobox('showPanel');
            }
        },
        details : function (id) {
            $('#details-dialog').
            dialog('open').
            dialog('setTitle', '详情信息').
            dialog('refresh', ThinkPHP['MODULE'] + '/'+NAME+'/getDetails/?id=' + id);
        },
        add : function () {
            $('#'+NAME+'-add').dialog('open');
        },
        edit : function (id) {
            //$('#user-staff-edit').combogrid('grid').datagrid('reload');
            //获取选中对象
            //var rows = $('#'+NAME+'').datagrid('getSelections');
            $('#'+NAME+'-edit').dialog('open');
            $.ajax({
                url : ThinkPHP['MODULE'] + '/'+NAME+'/getone',
                type : 'POST',
                data : {
                    id : id
                },
                // beforeSend : function () {
                //     $.messager.progress({
                //         text : '正在获取信息...'
                //     });
                // },
                success : function(data) {
                    $.messager.progress('close');
                    if (data) {
                        var PUCLIC_JSON= eval('({'+
                            NAME+'_id_edit:data.id,'+
                            NAME+'_title_edit:data.title'+
                            '})');
                        $('#'+NAME+'-edit').form('load', PUCLIC_JSON);
                        if (data.state == '正常') {
                            $('#user-state-edit').switchbutton('check');
                        } else {
                            $('#user-state-edit').switchbutton('uncheck');
                        }
                    }
                }
            });
        },
        remove : function () {
            var rows = $('#'+NAME+'').datagrid('getSelections');
            if (rows.length > 0) {
                $.messager.confirm('确认操作', '您真的要删除 <strong>' + rows.length + '</strong> 条记录吗？', function (flag) {
                    if (flag) {
                        var ids = [];
                        for (var i = 0; i < rows.length; i ++) {
                            ids.push(rows[i].id);
                        }
                        $.ajax({
                            url : ThinkPHP['MODULE'] + '/'+NAME+'/delete',
                            type : 'POST',
                            data : {
                                ids : ids.join(',')
                            },
                            beforeSend : function () {
                                $('#'+NAME+'').datagrid('loading');
                            },
                            success : function(data, response, status) {
                                if (data) {
                                    $('#'+NAME+'').datagrid('loaded');
                                    $('#'+NAME+'').datagrid('reload');
                                    $.messager.show({
                                        title : '操作提醒',
                                        msg : data + '条记录被成功删除！'
                                    });
                                }
                            }
                        });
                    }
                });

            } else {
                $.messager.alert('警告操作', '删除操作至少选择一条记录！', 'warning');
            }
        },
        reload : function () {
            $('#'+NAME+'').datagrid('reload');
        },
        reset : function () {
            $('#'+NAME+'-search-keywords').textbox('clear');
            $('#'+NAME+'-search-date').combobox('clear').combobox('disableValidation');
            $('#'+NAME+'-search-date-from').datebox('clear');
            $('#'+NAME+'-search-date-to').datebox('clear');
            $('#'+NAME+'').datagrid('resetSort', {
                sortName : 'addtime',
                sortOrder : 'desc'
            });
            this.search();
        }
    };
})(PUBLIC_STR_NAME);
