/**
 * Created by jimmy on 2016/8/13.
 */

define(['photoswipe','photoswipeui','$'],function(PhotoSwipe, PhotoSwipeUI_Default){
    var MyPhotoSwipe=function(gallerySelector,config){
        this.extend(config);
        $(document).on('click',gallerySelector, $.proxy(this,'onThumbnailsClick'));
        this.appendModalDom();  //添加模态容器
    };

    MyPhotoSwipe.prototype ={

        //参数拓展
        extend:function(config){
            this.config=defaultConfig;
            if(!config){
                return;
            }

            for(var item in config){
                var val=config[item];
                if(val){
                    this.config[item]=val;
                }
            }
        },

        appendModalDom:function(){
            if($('.pswp').length>0){
                return;
            }
            var filterStr='';

            if(this.config.bgFilter){
                filterStr='<div class="filter">'+
                    '<img class="filter-img" src="" alt="">'+
                    '</div>';
            }

            var str='<div class="pswp" tabindex="-1" role="dialog" aria-hidden="true">'+
                '<div class="pswp__bg">'+filterStr+'</div>'+
                '<div class="pswp__scroll-wrap">'+
                '<div class="pswp__container">'+
                '<div class="pswp__item"></div>'+
                '<div class="pswp__item"></div>'+
                '<div class="pswp__item"></div>'+
                '</div>'+
                '<div class="pswp__ui pswp__ui--hidden">'+

                '<div class="pswp__top-bar">'+
                '<div class="pswp__counter"></div>'+
                '<button class="pswp__button pswp__button--close" title="Close (Esc)"></button>'+
                    //'<button class="pswp__button pswp__button--share" title="Share"></button>'+
                //'<button class="pswp__button pswp__button--fs" title="Toggle fullscreen"></button>'+
                '<button class="pswp__button pswp__button--zoom" title="Zoom in/out"></button>'+
                '<div class="pswp__preloader">'+
                '<div class="pswp__preloader__icn">'+
                '<div class="pswp__preloader__cut">'+
                '<div class="pswp__preloader__donut"></div>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '<div class="pswp__share-modal pswp__share-modal--hidden pswp__single-tap">'+
                '<div class="pswp__share-tooltip"></div>'+
                '</div>'+
                '<button class="pswp__button pswp__button--arrow--left" title="Previous (arrow left)"></button>'+
                '<button class="pswp__button pswp__button--arrow--right" title="Next (arrow right)"></button>'+
                '<div class="pswp__caption">'+
                '<div class="pswp__caption__center"></div>'+
                '</div>'+
                '</div>'+
                '</div>'+
                '</div>';
            $('body').append(str);
        },

        // parse slide data (url, title, size ...) from DOM elements
        // (children of gallerySelector)
        parseThumbnailElements : function(el) {
            var thumbElements = el.childNodes,
                numNodes = thumbElements.length,
                items = [],
                figureEl,
                linkEl,
                size,
                item;

            for(var i = 0; i < numNodes; i++) {

                figureEl = thumbElements[i]; // <figure> element

                // include only element nodes
                if(figureEl.nodeType !== 1) {
                    continue;
                }

                linkEl = figureEl.children[0]; // <a> element

                if(linkEl==undefined){
                    continue;
                }

                size = linkEl.getAttribute('data-size').split('x');

                // create slide object
                item = {
                    src: linkEl.getAttribute('href'),
                    w: parseInt(size[0], 10),
                    h: parseInt(size[1], 10)
                };



                if(figureEl.children.length > 1) {
                    // <figcaption> content
                    item.title = figureEl.children[1].innerHTML;
                }

                if(linkEl.children.length > 0) {
                    // <img> thumbnail element, retrieving thumbnail url
                    item.msrc = linkEl.children[0].getAttribute('src');
                }

                item.el = figureEl; // save link to element for getThumbBoundsFn
                items.push(item);
            }

            return items;
        },

        // find nearest parent element
        closest : function closest(el, fn) {
            return el && ( fn(el) ? el : closest(el.parentNode, fn) );
        },

        // triggers when user clicks on thumbnail
        onThumbnailsClick : function(e) {
            e = e || window.event;
            e.preventDefault ? e.preventDefault() : e.returnValue = false;

            var eTarget = e.target || e.srcElement;

            // find root element of slide
            var clickedListItem = this.closest(eTarget, function(el) {
                return (el.tagName && el.tagName.toUpperCase() === 'LI');
            });

            if(!clickedListItem) {
                return;
            }

            // find index of clicked item by looping through all child nodes
            // alternatively, you may define index via data- attribute
            var clickedGallery = clickedListItem.parentNode,
                childNodes = clickedListItem.parentNode.childNodes,
                numChildNodes = childNodes.length,
                nodeIndex = 0,
                index;

            for (var i = 0; i < numChildNodes; i++) {
                if(childNodes[i].nodeType !== 1) {
                    continue;
                }

                if(childNodes[i] === clickedListItem) {
                    index = nodeIndex;
                    break;
                }
                nodeIndex++;
            }



            if(index >= 0) {
                // open PhotoSwipe if valid index found
                this.openPhotoSwipe( index, clickedGallery );
            }
            return false;
        },

        // parse picture index and gallery index from URL (#&pid=1&gid=2)
        photoswipeParseHash : function() {
            var hash = window.location.hash.substring(1),
                params = {};

            if(hash.length < 5) {
                return params;
            }

            var vars = hash.split('&');
            for (var i = 0; i < vars.length; i++) {
                if(!vars[i]) {
                    continue;
                }
                var pair = vars[i].split('=');
                if(pair.length < 2) {
                    continue;
                }
                params[pair[0]] = pair[1];
            }

            if(params.gid) {
                params.gid = parseInt(params.gid, 10);
            }

            return params;
        },

        openPhotoSwipe : function(index, galleryElement, disableAnimation, fromURL) {
            var pswpElement = document.querySelectorAll('.pswp')[0],
                options,
                items;

            items = this.parseThumbnailElements(galleryElement);

            // define options (if needed)
            options = {

                // define gallery index (for URL)
                //galleryUID: galleryElement.getAttribute('data-pswp-uid'),

                galleryUID: 0,

                getThumbBoundsFn: function(index) {
                    // See Options -> getThumbBoundsFn section of documentation for more info
                    var thumbnail = items[index].el.getElementsByTagName('img')[0], // find thumbnail
                        pageYScroll = window.pageYOffset || document.documentElement.scrollTop,
                        rect = thumbnail.getBoundingClientRect();

                    return {x:rect.left, y:rect.top + pageYScroll, w:rect.width};
                }

            };

            // PhotoSwipe opened from URL
            if(fromURL) {
                if(options.galleryPIDs) {
                    // parse real index when custom PIDs are used
                    // http://photoswipe.com/documentation/faq.html#custom-pid-in-url
                    for(var j = 0; j < items.length; j++) {
                        if(items[j].pid == index) {
                            options.index = j;
                            break;
                        }
                    }
                } else {
                    // in URL indexes start from 1
                    options.index = parseInt(index, 10) - 1;
                }
            } else {
                options.index = parseInt(index, 10);
            }

            // exit if index not found
            if( isNaN(options.index) ) {
                return;
            }

            if(disableAnimation) {
                options.showAnimationDuration = 0;
            }

            // Pass data to PhotoSwipe and initialize it
            this.gallery = new PhotoSwipe( pswpElement, PhotoSwipeUI_Default, items, options);
            this.gallery.init();
            // Image loaded
            //this.gallery.listen('imageLoadComplete', $.proxy(this,'imageLoadComplete'));
            this.gallery.listen('afterChange', $.proxy(this,'afterChange'));
            this.afterChange();
        },

        imageLoadComplete:function(index,item){
            if(this.config.bgFilter){
                $('.pswp__bg .filter img').attr('src',item.src);
            }
            //pswp.currItem
            //index;
            //item;
        },

        afterChange:function(){
            if(this.config.bgFilter){
                $('.pswp__bg .filter img').attr('src',this.gallery.currItem.src);
            }

        },


        // loop through all gallery elements and bind events

    };

// execute above function
//    initPhotoSwipeFromDOM('.my-gallery');

    var defaultConfig={
        bgFilter:false, //模糊背景
    };

    return MyPhotoSwipe;
});
