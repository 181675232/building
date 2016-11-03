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
                    width : 60,
                    checkbox : true
                },
                {
                    field : 'title',
                    title : '楼层名称',
                    align : 'center',
                    width : 150
                },
                {
                    field : 'count',
                    title : '层数',
                    align : 'center',
                    width : 150
                },
                {
                    field : 'area',
                    title : '面积（m2）',
                    align : 'center',
                    width : 80
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
                // {
                //     field : 'mp3',
                //     title : '音频',
                //     width : 80,
                //     fixed : true,
                //     align : 'center',
                //     sortable : true,
                //     formatter : function (value, row) {
                //         if (value){
                //             return  '<audio src="'+value+'" controls="controls">Your browser does not support the audio element. </audio>';
                //         }else {
                //             return '无';
                //         }
                //     }
                // },
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
            width : 600,
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
                    //window.add.sync();
                    if ($('#'+NAME+'-add').form('validate')) {
                        $.ajax({
                            url : ThinkPHP['MODULE'] + '/'+NAME+'/add',
                            type : 'POST',
                            data : {
                                title : $('input[name="'+NAME+'_title_add"]').val(),
                                uid : $('input[name="'+NAME+'_uid_add"]').val(),
                                area : $('input[name="'+NAME+'_area_add"]').val(),
                                areas : $('input[name="'+NAME+'_areas_add"]').val(),
                                floor : $('input[name="'+NAME+'_floor_add"]').val(),
                                simg : $('input[name="'+NAME+'_simg_add"]').val(),
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
                    $('#'+NAME+'-uid-add').val('');
                    $('#'+NAME+'-simg-add').prev().attr('src','');
                    $('#'+NAME+'-simg-add').val('');
                    $('#'+NAME+'-add').dialog('close');
                }
            }],
            onClose : function () {
                $('#'+NAME+'-add').form('reset');
                //window.add.html('');
            }
        });

        $('#'+NAME+'-edit').dialog({
            width : 400,
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
                    //window.editor.sync();
                    if ($('#'+NAME+'-edit').form('validate')) {
                        $.ajax({
                            url : ThinkPHP['MODULE'] + '/'+NAME+'/edit',
                            type : 'POST',
                            data : {
                                id : $('input[name="'+NAME+'_id_edit"]').val(),
                                title : $('input[name="'+NAME+'_title_edit"]').val(),
                                area : $('input[name="'+NAME+'_area_edit"]').val(),
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
                    $('#'+NAME+'-uid-edit').val('');
                    $('#'+NAME+'-simg-edit').prev().attr('src','');
                    $('#'+NAME+'-simg-edit').val('');
                    $('#'+NAME+'-edit').dialog('close');
                }
            }],
            onClose : function () {
                $('#'+NAME+'-edit').form('reset');
                //window.editor.html('');
            }
        });
        /* ---------------------------弹出框样式-------------------------------------------*/
        $('#'+NAME+'-title-add,#'+NAME+'-title-edit').textbox({
            width : 220,
            height : 32,
            required : true,
            validType : 'length[2,20]',
            missingMessage : '请输入标题',
            invalidMessage : '名称2-20位'
        });
        $('#'+NAME+'-areas-add,#'+NAME+'-areas-edit').numberbox({
            width : 80,
            height : 32,
        });
        $('#'+NAME+'-area-add,#'+NAME+'-area-edit').numberbox({
            width : 80,
            height : 32,
            required : true,
            precision : 2,
            //validType : 'length[2,20]',
            missingMessage : '请输入建筑面积',
            //invalidMessage : '名称2-20位'
        });
        $('#'+NAME+'-floor-add,#'+NAME+'-floor-edit').numberbox({
            width : 80,
            height : 32,
            required : true,
            //precision : 2,
            //validType : 'length[2,20]',
            missingMessage : '请输入一共有多少层',
            //invalidMessage : '名称2-20位'
        });
        //新增和修改公司名称
        $('#'+NAME+'-username-add,#'+NAME+'-username-edit').textbox({
            width : 140,
            height : 32,
            editable : false,
            icons: [{
                iconCls:'icon-zoom',
                handler: function(){
                    $('#'+NAME+'-client').dialog('open');
                }
            }],
            //required : true,
            missingMessage : '请点击放大镜选择默认负责人',
            //invalidMessage : '请选择负责人'
        });
        //弹出选择公司名称
        $('#'+NAME+'-client').dialog({
            width: 550,
            height: 380,
            title: '选择分包',
            iconCls: 'icon-zoom',
            modal: true,
            closed: true,
            maximizable: true,
            onOpen : function () {
                $('#'+NAME+'-client-search-keywords').textbox();
                $('#'+NAME+'-client-search-button').linkbutton();
                $('#'+NAME+'-client-search-refresh').linkbutton();
                $('#'+NAME+'-client-search-reset').linkbutton();
                $('#'+NAME+'-search-client').datagrid({
                    url : ThinkPHP['MODULE'] + '/Admin/getall/type/79',
                    fit : true,
                    fitColumns : true,
                    striped : true,
                    rownumbers : true,
                    border : false,
                    pagination : true,
                    pageSize : 10,
                    pageList : [10, 20, 30, 40, 50],
                    pageNumber : 1,
                    sortName : 'addtime',
                    sortOrder : 'DESC',
                    toolbar : '#'+NAME+'-client-tool',
                    columns : [[
                        {
                            field : 'username',
                            title : '名称',
                            width : 100
                        },
                        {
                            field : 'level_name',
                            title : '职位',
                            width : 100
                        },
                        {
                            field : 'phone',
                            title : '电话',
                            width : 100
                        },
                        {
                            field : 'select',
                            title : '选择',
                            width : 60,
                            formatter : function (value, row) {
                                return '<a href="javascript:void(0)" class="select-button" style="height: 18px;margin-left:2px;" onclick="PUBLIC_TOOL.'+NAME+'_client_tool.select(\'' + row.id + '\', \'' + row.username + '\');">选择</a>';
                            }
                        },
                    ]],
                    onLoadSuccess : function() {
                        $('.select-button').linkbutton({
                            iconCls : 'icon-tick',
                            plain : true
                        });
                    },
                    onClickCell : function (index) {
                        $('#'+NAME+'-search-client').datagrid('selectRow', index);
                    }
                });
            }
        });
        // $('#'+NAME+'-uid-add,#'+NAME+'-uid-edit').combobox({
        //     width : 140,
        //     height : 'auto',
        //     url : ThinkPHP['MODULE'] + '/admin/getall',
        //     editable : false,
        //     required : true,
        //     validType : 'length[1,20]',
        //     valueField : 'id',
        //     textField : 'title',
        //     missingMessage : '请选择负责人',
        //     hasDownArrow : true
        // });
        //加载新增编辑器
        window.add = KindEditor.create('#'+NAME+'-content-add,', {
            width : '94%',
            height : '200px',
            resizeType : 0,
            items : [
                'source', 'wordpaste','|',
                'formatblock', 'fontname', 'fontsize','|',
                'forecolor', 'hilitecolor', 'bold','italic', 'underline', 'link', 'removeformat', '|',
                'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist','|',
                'emoticons', 'image','baidumap','|',
                'fullscreen'
            ]
        });
        window.editor = KindEditor.create('#'+NAME+'-content-edit', {
            width : '94%',
            height : '200px',
            resizeType : 0,
            items : [
                'source', 'wordpaste','|',
                'formatblock', 'fontname', 'fontsize','|',
                'forecolor', 'hilitecolor', 'bold','italic', 'underline', 'link', 'removeformat', '|',
                'justifyleft', 'justifycenter', 'justifyright', '|', 'insertorderedlist', 'insertunorderedlist','|',
                'emoticons', 'image','baidumap','|',
                'fullscreen'
            ]
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
                beforeSend : function () {
                    $.messager.progress({
                        text : '正在获取信息...'
                    });
                },
                success : function(data) {
                    $.messager.progress('close');
                    if (data) {
                        var PUCLIC_JSON= eval('({'+
                            NAME+'_id_edit:data.id,'+
                            NAME+'_title_edit:data.title,'+
                            NAME+'_area_edit:data.area,'+
                            '})');
                        $('#'+NAME+'-edit').form('load', PUCLIC_JSON);
                        window.editor.html(data.content);
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

//工具栏操作模块
PUBLIC_TOOL[PUBLIC_STR_NAME+'_client_tool'] = (function  (NAME) {
    return{
    search : function () {
        $('#'+NAME+'-search-client').datagrid('load', {
            keywords: $.trim($('input[name="'+NAME+'_client_search_keywords"]').val())
        });
    },
    details : function (id) {
        $('#'+NAME+'-dialog').
        dialog('open').
        dialog('setTitle', '入库产品详情').
        dialog('refresh', ThinkPHP['MODULE'] + '/Inlib/getDetails/?id=' + id);
    },
    select : function (id, username) {
        if ($('#'+NAME+'-add').dialog('dialog').css('display') == 'block') {
            $('#'+NAME+'-uid-add').val(id);
            $('#'+NAME+'-username-add').textbox('setValue', username);
        } else if ($('#'+NAME+'-edit').dialog('dialog').css('display') == 'block') {
            $('#'+NAME+'-uid-edit').val(id);
            $('#'+NAME+'-username-edit').textbox('setValue', username);
        }
        $('#'+NAME+'-client').dialog('close');
        this.reset();
    },
    redo : function () {
        $('#'+NAME+'').datagrid('unselectAll');
    },
    reset : function () {
        $('#'+NAME+'-client-search-keywords').textbox('clear');
        $('#'+NAME+'-search-client').datagrid('resetSort', {
            sortName : 'addtime',
            sortOrder : 'desc'
        });
        this.search();
    }
    }
})(PUBLIC_STR_NAME);