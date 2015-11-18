/**
 * Created by jimmy on 2015/11/18.
 */
/*
 *��չDate�������õ���ʽ����������ʽ ������ʲô��ʽ��֧��
 *date.format('yyyy-MM-dd')��date.format('yyyy/MM/dd'),date.format('yyyy.MM.dd')
 *date.format('dd.MM.yy'), date.format('yyyy.dd.MM'), date.format('yyyy-MM-dd HH:mm')   �ȵȶ�����
 *ʹ�÷��� ���£�
 *                       var date = new Date();
 *                       var todayFormat = date.format('yyyy-MM-dd'); //���Ϊ2015-2-3
 *Parameters:
 *format - {string} Ŀ���ʽ ����('yyyy-MM-dd')
 *Returns - {string} ��ʽ��������� 2015-2-3
 *
 */
Date.prototype.format = function (format) {
    format=format || 'yyyy-MM-dd';
    var o = {
        "M+": this.getMonth() + 1, //month
        "d+": this.getDate(), //day
        "h+": this.getHours(), //hour
        "m+": this.getMinutes(), //minute
        "s+": this.getSeconds(), //second
        "q+": Math.floor((this.getMonth() + 3) / 3), //quarter
        "S": this.getMilliseconds() //millisecond
    }
    if (/(y+)/.test(format)) format = format.replace(RegExp.$1,
        (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o) if (new RegExp("(" + k + ")").test(format))
        format = format.replace(RegExp.$1,
            RegExp.$1.length == 1 ? o[k] :
                ("00" + o[k]).substr(("" + o[k]).length));
    return format;
}

/*
 *��չstring�ķ�����ȥ�����˿ո�
 */
String.prototype.trim = function () {
    return this.replace(/^\s+|\s+$/g,'');
};

