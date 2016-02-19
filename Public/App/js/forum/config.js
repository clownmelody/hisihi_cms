/**
 * Created by jimmy on 2015/12/28.
 */

requirejs.config({
    baseUrl: window.hisihiUrlObj.js,
    paths: {
        zepto:'../sharecommon/zepto.min',
        common:'hisihi_news_common',
        toppostdetailv2:'toppostdetailv2',
        sharemain:'../sharecommon/main',
    },
    shim: {
        zepto:{
            output:'Zepto'
        },
        common:{
            output:'MyCommon'
        },
        sharemain:{
            output:'Sharemain'
        },
    }
});


require(['zepto','common','toppostdetailv2','sharemain'],function(Zepto,MyCommon,toppostdetailv2,sharemain){
    var totalReplayCount = 0;
    var curRepalyCount = 10;
    var postid = 0;
    var page = 2;
    var svgloading = '';
    var repalybtn = '';
    var detailedlist = '';

    totalReplayCount = parseInt($('#replyTotalCount').val());
    postid = $('#postid').val();

    svgloading = $('.loadingMoreResultTips');
    repalybtn = $('a.box-btn');
    detailedlist = $('ul.detailed-list');
    if(totalReplayCount > 10){
        $('#loadMoreContainer').show();
    }

    $(document).on('click','.loadError',function(){
        $(this).hide().prev().show();
        getMoreReplay();
    });
    $(document).on('click','.box-btn',getMoreReplay);

    function getMoreReplay(){
        repalybtn.hide();
        svgloading.addClass('active').show();
        $.ajax({
            type: 'GET',
            url:window.hisihiUrlObj.server_url + '/forum/ajaxPostReplyList/post_id/' + postid +'/page/' + page,
            dataType: 'json',
            success: function(data){
                if(data['error_code'] == 0 && data != null){
                    var replay = data['data'];
                    var replayarray = [];
                    for(var i in replay){
                        var userInfo = replay[i]['userInfo'];
                        replayarray.push('<li>');
                        replayarray.push('<img src="' + userInfo['avatar128'] +'" alt="" class="detailed-item-img">');
                        replayarray.push('<div class="detailed-item-body">');
                        replayarray.push('<p class="detailed-item-name">' + userInfo['nickname'] +'</p>');
                        replayarray.push('<p class="detailed-item-time">' + dateParse(replay[i]['create_time']) +'</p>');
                        replayarray.push('<p class="detailed-item-txt">' + replay[i]['content'] +'</p>');
                        replayarray.push('</div></li>');
                    }
                    svgloading.hide().removeClass('active');
                    repalybtn.show();
                    detailedlist.append(replayarray.join(''));
                    page += 1;
                    curRepalyCount += replay.length;
                    if(curRepalyCount >= totalReplayCount){
                        repalybtn.text('没有更多评论了');
                        repalybtn.attr('onclick','return false;');
                    }else{
                        repalybtn.text('点击加载更多');
                        repalybtn.attr('onclick','getMoreReplay();');
                    }
                }
            },
            error: function(xhr, type){
                svgloading.hide().removeClass('active').next().show();
            }
        })
    }
    function dateParse(date){
        var newDate = new Date(parseInt(date) * 1000).format('MM-dd hh:mm');
        return newDate;
    }

    new commentObj($('#comment-box'),window.hisihiUrlObj);

    var type=$('.moreRecommend').data('type');
    new sharemain(type);
});