(function ($, scope, undefined) {


    function NextendSmartSliderMainAnimationBlock(slider, parameters) {

        this.postBackgroundAnimation = false;

        NextendSmartSliderMainAnimationAbstract.prototype.constructor.apply(this, arguments);

        if (!slider.isAdmin && this.slider.parameters.postBackgroundAnimations != false) {
            this.postBackgroundAnimation = new NextendSmartSliderPostBackgroundAnimation(slider, this);
        }
    };

    NextendSmartSliderMainAnimationBlock.prototype = Object.create(NextendSmartSliderMainAnimationAbstract.prototype);
    NextendSmartSliderMainAnimationBlock.prototype.constructor = NextendSmartSliderMainAnimationBlock;


    NextendSmartSliderMainAnimationBlock.prototype.changeTo = function (currentSlideIndex, currentSlide, nextSlideIndex, nextSlide, reversed, isSystem) {
        if (this.postBackgroundAnimation) {
            this.postBackgroundAnimation.start(currentSlideIndex, nextSlideIndex);
        }

        NextendSmartSliderMainAnimationAbstract.prototype.changeTo.apply(this, arguments);
    };

    NextendSmartSliderMainAnimationBlock.prototype.hasBackgroundAnimation = function () {
        return false;
    };

    scope.NextendSmartSliderMainAnimationBlock = NextendSmartSliderMainAnimationBlock;

})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderBlock(elementID, parameters) {

        this.type = 'block';
        this.responsiveClass = 'NextendSmartSliderResponsiveBlock';

        NextendSmartSliderAbstract.prototype.constructor.call(this, elementID, parameters);
    };

    NextendSmartSliderBlock.prototype = Object.create(NextendSmartSliderAbstract.prototype);
    NextendSmartSliderBlock.prototype.constructor = NextendSmartSliderBlock;

    NextendSmartSliderBlock.prototype.initMainAnimation = function () {
        this.mainAnimation = new NextendSmartSliderMainAnimationBlock(this, {});
    };

    scope.NextendSmartSliderBlock = NextendSmartSliderBlock;

})(n2, window);
(function ($, scope, undefined) {

    function NextendSmartSliderResponsiveBlock() {
        NextendSmartSliderResponsive.prototype.constructor.apply(this, arguments);
    };

    NextendSmartSliderResponsiveBlock.prototype = Object.create(NextendSmartSliderResponsive.prototype);
    NextendSmartSliderResponsiveBlock.prototype.constructor = NextendSmartSliderResponsiveBlock;

    NextendSmartSliderResponsiveBlock.prototype.addResponsiveElements = function () {
        this.helperElements = {};

        this._sliderHorizontal = this.addResponsiveElement(this.sliderElement, ['width', 'marginLeft', 'marginRight'], 'w', 'slider');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slider-1'), ['width', 'paddingLeft', 'paddingRight', 'borderLeftWidth', 'borderRightWidth'], 'w');

        this._sliderVertical = this.addResponsiveElement(this.sliderElement, ['height', 'marginTop', 'marginBottom'], 'h', 'slider');
        this.addResponsiveElement(this.sliderElement, ['fontSize'], 'fontRatio', 'slider');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slider-1'), ['height', 'paddingTop', 'paddingBottom', 'borderTopWidth', 'borderBottomWidth'], 'h');

        this.helperElements.canvas = this.addResponsiveElement(this.sliderElement.find('.n2-ss-slide'), ['width'], 'w', 'slideouter');

        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slide'), ['height'], 'h', 'slideouter');

        this.addResponsiveElement(this.sliderElement.find('.n2-ss-layers-container'), ['width'], 'slideW', 'slide');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-layers-container'), ['height'], 'slideH', 'slide').setCentered();

        var backgroundImages = this.slider.backgroundImages.getBackgroundImages();
        for (var i = 0; i < backgroundImages.length; i++) {
            this.addResponsiveElementBackgroundImageAsSingle(backgroundImages[i].image, backgroundImages[i], []);
        }


        var video = this.sliderElement.find('.n2-ss-slider-background-video');
        if (video.length) {
            if (video[0].videoWidth > 0) {
                this.videoPlayerReady(video);
            } else {
                video[0].addEventListener('error', $.proxy(this.videoPlayerError, this, video), true);
                video[0].addEventListener('canplay', $.proxy(this.videoPlayerReady, this, video));
            }
        }
    };

    NextendSmartSliderResponsiveBlock.prototype.getCanvas = function () {
        return this.helperElements.canvas;
    };

    NextendSmartSliderResponsiveBlock.prototype.videoPlayerError = function (video) {
        video.remove();
    };

    NextendSmartSliderResponsiveBlock.prototype.videoPlayerReady = function (video) {
        video.data('ratio', video[0].videoWidth / video[0].videoHeight);
        video.addClass('n2-active');

        this.slider.ready($.proxy(function () {
            this.slider.sliderElement.on('SliderResize', $.proxy(this.resizeVideo, this, video));
            this.resizeVideo(video);
        }, this));
    };

    NextendSmartSliderResponsiveBlock.prototype.resizeVideo = function (video) {

        var mode = video.data('mode'),
            ratio = video.data('ratio'),
            slideOuter = this.slider.dimensions.slideouter || this.slider.dimensions.slide,
            slideOuterRatio = slideOuter.width / slideOuter.height;

        if (mode == 'fill') {
            if (slideOuterRatio > ratio) {
                video.css({
                    width: '100%',
                    height: 'auto'
                });
            } else {
                video.css({
                    width: 'auto',
                    height: '100%'
                });
            }
        } else if (mode == 'fit') {
            if (slideOuterRatio < ratio) {
                video.css({
                    width: '100%',
                    height: 'auto'
                });
            } else {
                video.css({
                    width: 'auto',
                    height: '100%'
                });
            }
        }
    };

    scope.NextendSmartSliderResponsiveBlock = NextendSmartSliderResponsiveBlock;

})(n2, window);
