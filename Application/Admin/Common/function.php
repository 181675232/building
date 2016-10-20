<?php
//得到当前时间
function get_time() {
    return date('Y-m-d H:i:s');
}

//生成以当前时间的字符串
function get_time_string() {
    return date('YmdHis');
}