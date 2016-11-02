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
        pagination : false,
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
                field : 'titles',
                title : '名称',
                width : 100,
                halign : 'center'
            },
            {
                field : 'ord',
                title : '排序',
                align : 'center',
                width : 100,
            },
            {
                field : 'state',
                title : '状态',
                width : 100,
                align : 'center',
                fixed : true,
                sortable : true,
                formatter : function (value, row) {
                    var state = '';
                    switch (value) {
                        case '1' :
                            state = '<a href="javascript:void(0)" '+NAME+'-id="' + row.id + '" '+NAME+'-state="1" title="正常" class="'+NAME+'-state '+NAME+'-state-1" style="height: 18px;margin-left:4px;"></a>';
                            break;
                        case '2' :
                            state = '<a href="javascript:void(0)" '+NAME+'-id="' + row.id + '" '+NAME+'-state="2" title="隐藏" class="'+NAME+'-state '+NAME+'-state-2" style="height: 18px;margin-left:4px;"></a>';
                            break;
                    }
                    return state;
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
            $('.'+NAME+'-state-1').linkbutton({
                iconCls : 'icon-xianshi',
                plain : true
            });
            $('.'+NAME+'-state-2').linkbutton({
                iconCls : 'icon-yincang',
                plain : true
            });
            $('.'+NAME+'-state').click(function () {
                var id = $(this).attr(''+NAME+'-id'),
                    state = $(this).attr(''+NAME+'-state');

                switch (state) {
                    case '2' :
                        $.messager.confirm('操作确认','确认显示？',function(flag) {
                            if (flag){
                                $.ajax({
                                    url : ThinkPHP['MODULE'] + '/'+NAME+'/state',
                                    type : 'POST',
                                    data : {
                                        id : id,
                                        state : '1'
                                    },
                                    beforeSend : function () {
                                        $.messager.progress({
                                            text : 'loading...'
                                        });
                                    },
                                    success : function(data) {
                                        $.messager.progress('close');
                                        if (data > 0) {
                                            $.messager.show({
                                                title : '操作提醒',
                                                msg : '操作成功！'
                                            });
                                            $('#'+NAME+'').datagrid('reload');
                                        }
                                    }
                                });
                            }
                        });
                        break;
                    case '1' :
                        $.messager.confirm('操作确认','确认隐藏？',function(flag) {
                            if (flag){
                                $.ajax({
                                    url : ThinkPHP['MODULE'] + '/'+NAME+'/state',
                                    type : 'POST',
                                    data : {
                                        id : id,
                                        state : '2'
                                    },
                                    beforeSend : function () {
                                        $.messager.progress({
                                            text : 'loading...'
                                        });
                                    },
                                    success : function(data) {
                                        $.messager.progress('close');
                                        if (data > 0) {
                                            $.messager.show({
                                                title : '操作提醒',
                                                msg : '操作成功！'
                                            });
                                            $('#'+NAME+'').datagrid('reload');
                                        }
                                    }
                                });
                            }
                        });
                        break;
                }
            });
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
                            name : $('input[name="'+NAME+'_name_add"]').val(),
                            simg : $('input[name="'+NAME+'_simg_add"]').val(),
                            url : $('input[name="'+NAME+'_url_add"]').val(),
                            ord : $('input[name="'+NAME+'_ord_add"]').val(),
                            pid : $('input[name="'+NAME+'_pid_add"]').val(),
                            auth :(function(){var arry_data=[]; $('input[name="'+NAME+'_auth_add"]:checked').each(function () {
                                arry_data.push($(this).val());
                            });return arry_data.join(',');}),
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
                            ord : $('input[name="'+NAME+'_ord_edit"]').val(),
                            name : $('input[name="'+NAME+'_name_edit"]').val(),
                            simg : $('input[name="'+NAME+'_simg_edit"]').val(),
                            url : $('input[name="'+NAME+'_url_edit"]').val(),
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
    $('#'+NAME+'-name-add,#'+NAME+'-name-edit').textbox({
        width : 220,
        height : 32,
        required : true,
        validType : 'length[2,20]',
        missingMessage : '请输入名称',
        invalidMessage : '名称2-20位'
    });
    $('#'+NAME+'-url-add,#'+NAME+'-url-edit,#'+NAME+'-simg-add,#'+NAME+'-simg-edit').textbox({
        width : 220,
        height : 32,
    });

    $('#'+NAME+'-ord-add,#'+NAME+'-ord-edit').numberbox({
        width : 60,
        height : 32,
        required : true,
        //precision : 2,
        //validType : 'length[2,20]',
        missingMessage : '不能为空',
        //invalidMessage : '名称2-20位'
    });
    //下拉类别
    $('#'+NAME+'-pid-add').combogrid({
        url : ThinkPHP['MODULE'] + '/'+NAME+'/getall',
        width : 120,
        panelWidth: 220,
        showHeader: false,
        panelHeight: 'auto',
        panelMaxHeight : 227,
        fitColumns : true,
        striped : true,
        border : false,
        idField:'id',
        textField:'title',
        editable : false,
        remoteSort : false,
        columns : [[
            {
                field : 'titles',
                title : '　　<span class="folder-open"></span>无上级类别',
                width : 80
            }
        ]],
        onShowPanel : function () {
            $(this).combogrid('panel').panel('resize', {
                width : '220px'
            });
        }
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

    // $('#'+NAME+'-state-add,#'+NAME+'-state-edit').switchbutton({
    //     checked: true,
    //     onChange: function(checked){
    //         console.log(checked);
    //     }
    // })
    //选择时间触发验证
    $('#'+NAME+'-search-date-from, #'+NAME+'-search-date-to').datebox({
        onSelect : function () {
            if ($('#'+NAME+'-search-date').combobox('enableValidation').combobox('isValid') == false) {
                $('#'+NAME+'-search-date').combobox('showPanel');
            }
        }
    })

})(PUBLIC_STR_NAME);
});

//工具栏操作模块
PUBLIC_TOOL[PUBLIC_STR_NAME+'_tool'] = (function  (NAME){
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
        $('#'+NAME+'-type-add').combogrid('grid').datagrid('reload');
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
                        NAME+'_title_edit:data.title,'+
                        NAME+'_ord_edit:data.ord,'+
                       NAME+'_name_edit:data.name,'+
                       NAME+'_url_edit:data.url,'+
                       NAME+'_simg_edit:data.simg,'+
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

