<?php
phpinfo();
header("Content-type:text/html; charset=utf-8");
$guid = htmlspecialchars($_GET["guid"]);
$user_agent = $_SERVER['HTTP_USER_AGENT'];
if (stristr($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')
    || stristr($_SERVER['HTTP_USER_AGENT'], 'MQQBrowser')
) {
    ?>
    <!doctype html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
        <meta name="Resource-type" content="Document"/>
        <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no"/>
        <meta http-equiv="X-UA-Compatible" content="IE=8">
        <meta http-equiv="Expires" content="0">
        <meta http-equiv="Pragma" content="no-cache">
        <meta http-equiv="Cache-control" content="no-cache">
        <meta http-equiv="Cache" content="no-cache">
        <title>嘿设汇</title>
    </head>
    <style>
        body {
            margin: 0;
            padding: 8px;
            background: #eee;
        }

        .text-body {
            padding: 10px;
            margin: 30px 0 100px;
            background: #f1f1f1;
            border: 1px solid #f5f5f5;
        }

        .text-danger {
            color: #a94442;
            line-height: 25px;
            display: block;
            font-size: 15px;
            -webkit-margin-before: 1em;
            -webkit-margin-after: 1em;
            -webkit-margin-start: 0px;
            -webkit-margin-end: 0px;
            margin: 0;

        }

        .icon-span {
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADwAAABBCAMAAAC6njdfAAABhlBMVEUAAACVPz2fammeV1aiaWeia2qbY2KXY2KeQ0GiRUOUPTyYTk2VT06WU1GPSkmeW1qjaGaeZGOLTk2PT06daGegammZPTyUOjikSUifQ0KcSEaWQ0KkUlGbSkmVREKmU1GUPDqcTUyVPjyYSUeNPDudVVSOQ0KbUU+mXVyTTUyWVVSkZmWTVlSXWViSUVCjammSXFufaGeIVlaeQT+cQ0CaQ0GqTkynTUugS0mpUE6SREKgTEuMNDOTREKrU1GdT06uVFOXQ0KcTEucT06bREKfUlCVRkSeU1GVPTqWRUSZUlCKOTeUSEaVSEalYF6tZWSdVVORTUudV1WcVlWpZmWcXFuHR0eMS0qbXFuYWlicZGOhbWypREKrRUKqRUOqREKtQkCsQkCpQD6sREKqPz2oOzmrREKqQD6gPjytRUOuREGqQkCjPDqoREKlPz2qPjumPTuhREKrQkCgQkCsQT+sPzyoPTusRUOlOjivRUOuQD6dPz2iPDqdOzqeODWsPTuZOTeVMjAX05lsAAAAXHRSTlMArAOIQj8bDPr2qqSOhYJ6XUszJBgQ/Pzv7drX0tHPwr+8tq2lpJyRinRqZlhNRzQoJAb79Ovj497W1M7NysnIxcTBtbW0s7GxqqempZ+YlpWVjIJrZV5NOzcuCRJ6kXMAAAIYSURBVEjH7dbXbtswFIBhVfGOV+yk2Xs3s+nee++9jvayhm15O0mTtm/eC7cR4oikpF4V8H9LfCAB8gCkevX6pwaf3fkUGN+yjOGtgHbTNFRlJSDeKFbhx6sgMrKaGOM4MEfmkl9ofzS/WCsJGgfAinWzdmPTjz1zWuaZIvxJMisL3jd/UzI4ACdWsicHPdrkdx6OV9SVyagnm3WsE6P0e3pWF/ZYOJl0mPWAUxYDbpWvkW30sgyuSYUQEYcLkjvmxGUifm9z4F71IRH3CYBImSZjDYlvk3EdhZs3iXi5isLlKzQJv5BQWLs0RMLzIgoL8TwJT7dR2KjkCHboogYQ9InlKjyg0jIEPFBoIbFMmsq3GiBrThDwbBON+cpXrD0bF9BYtdaweM1SAV3zARbfLQMmIbaNsaGCgMOM1YfBjxTAVh+NYC5ZwGPGTqIsPSWzgE/YDyNwqqQCIVabol3t+k8eiKntRTcbPldngRxfSp8cxYGY7Fhc0q9UN35+vuFYfIb4rgunxxsMHGtXLyIOXst24Z1wTNSZo1STHS7o0IkVxZazZBykXQZqae7U3xILo/r2qsx1cHXkSeJo6fH8BkXqpRLNWbudg5cnKH99OPxIXZVVDgA4+Z5PHKnF6Z3xPZsF0A/WKZ9l2tejVOipyLWUWcp3SyXh/taKzZfHEGNI+MmJjYa53/+NClI+MzPz+jNN9fr/+w3r2ayNkd5SfwAAAABJRU5ErkJggg==);
            background-size: 100%;
            background-repeat: no-repeat;
            width: 20px;
            height: 20px;
            float: left;
            margin-right: 5px;
        }

        .logo {
            background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAPoAAAD6CAYAAACI7Fo9AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyFpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDUuNS1jMDE0IDc5LjE1MTQ4MSwgMjAxMy8wMy8xMy0xMjowOToxNSAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIENDIChXaW5kb3dzKSIgeG1wTU06SW5zdGFuY2VJRD0ieG1wLmlpZDpGMzdGRjk3NzA3QjIxMUU1QTlGRUYwQjEyOTI4RUI4QSIgeG1wTU06RG9jdW1lbnRJRD0ieG1wLmRpZDpGMzdGRjk3ODA3QjIxMUU1QTlGRUYwQjEyOTI4RUI4QSI+IDx4bXBNTTpEZXJpdmVkRnJvbSBzdFJlZjppbnN0YW5jZUlEPSJ4bXAuaWlkOkYzN0ZGOTc1MDdCMjExRTVBOUZFRjBCMTI5MjhFQjhBIiBzdFJlZjpkb2N1bWVudElEPSJ4bXAuZGlkOkYzN0ZGOTc2MDdCMjExRTVBOUZFRjBCMTI5MjhFQjhBIi8+IDwvcmRmOkRlc2NyaXB0aW9uPiA8L3JkZjpSREY+IDwveDp4bXBtZXRhPiA8P3hwYWNrZXQgZW5kPSJyIj8+s8KLMwAATqFJREFUeNrs3Qe4LVdV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);
            background-size: 100%;
            background-repeat: no-repeat;
            width: 130px;
            height: 130px;
            margin: 0 auto;
        }
    </style>
    <body>
    <div class="text-body">
        <span class="icon-span"></span>
        <p class="text-danger">温馨提示：如果您使用的是 QQ、微信扫一扫，请单击右上角的分享按钮，选择在浏览器中打开。</p>
    </div>
    <div class="logo">
    </div>
    </body>
    </html>
<?php
} else {
    header('Location: hisihi://hiworks?guid=' . $guid);
}

?>