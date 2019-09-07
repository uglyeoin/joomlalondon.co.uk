(function ($, scope, undefined) {

    function NextendSmartSliderCarousel(sliderElement, parameters) {

        this.type = 'carousel';
        this.responsiveClass = 'NextendSmartSliderResponsiveCarousel';

        parameters = $.extend({
            maxPaneWidth: 980
        }, parameters);

        NextendSmartSliderAbstract.prototype.constructor.call(this, sliderElement, parameters);
    };


    NextendSmartSliderCarousel.prototype = Object.create(NextendSmartSliderAbstract.prototype);
    NextendSmartSliderCarousel.prototype.constructor = NextendSmartSliderCarousel;

    NextendSmartSliderCarousel.prototype.initMainAnimation = function () {
        this.mainAnimation = new NextendSmartSliderMainAnimationCarousel(this, this.parameters.mainanimation);


        this.sliderElement.one('SliderResize', $.proxy(function () {
            if (!this.isAdmin) {
                if (this.parameters.layerMode.playFirstLayer) {
                    //mainAnimationStartIn event not triggered in play on load, so we need to reset the layers manually
                    this.callOnSlide(this.slides.eq(this.currentSlideIndex), 'setStart');
                    this.ready($.proxy(function () {
                        n2c.log('Play first slide');
                        this.callOnSlide(this.slides.eq(this.currentSlideIndex), 'playIn');
                    }, this));
                }
            }
        }, this));

        this.sliderElement.on('mainAnimationStartIn', $.proxy(function (e, animation, previousSlideIndex, currentSlideIndex) {
            this.callOnSlide(this.slides.eq(currentSlideIndex), 'setStart');
        }, this));
    };

    NextendSmartSliderCarousel.prototype.findSlides = function () {

        this.realSlides = this.sliderElement.find('.n2-ss-slide');


        this.slidesInGroup = 1;
        this.slides = this.sliderElement.find('.n2-ss-slide-group');

        this.currentSlide = this.realSlides.filter('.n2-ss-slide-active');
    };

    NextendSmartSliderCarousel.prototype.calibrateGroup = function (slidesInGroup) {
        if (this.slidesInGroup != slidesInGroup) {

            var oldActiveSlides = this.slides.eq(this.currentSlideIndex).find('.n2-ss-slide');

            var parent = this.slides.parent(),
                groups = $();
            this.realSlides.each($.proxy(function (i, el) {
                if (i % slidesInGroup == 0) {
                    groups = groups.add($('<div class="n2-ss-slide-group"></div>').appendTo(parent));
                }
                groups.eq(Math.floor(i / slidesInGroup)).append(el);
            }));
            this.slides.remove();
            this.slides = groups;
            this.slidesInGroup = slidesInGroup;

            this.currentSlideIndex = 0;

            if (this.isAdmin) {
                this.currentSlideIndex = this.currentSlide.parent().index();
            } else if (this.readyDeferred.state() == 'resolved') {
                var activeSlides = this.slides.eq(this.currentSlideIndex).find('.n2-ss-slide');
                oldActiveSlides.not(activeSlides).each(function (i, el) {
                    $(el).data('slide').reset();
                });
                activeSlides.not(oldActiveSlides).each(function (i, el) {
                    $(el).data('slide').setStart();
                    $(el).data('slide').playIn();
                });
            } else {
                this.currentSlideIndex = this.currentSlide.parent().index();
            }
            this.mainAnimation.setActiveSlide(this.slides.eq(this.currentSlideIndex));
            this.setActiveSlide(this.slides.eq(this.currentSlideIndex));

            this.ready($.proxy(function () {
                this.sliderElement.trigger('slideCountChanged', [this.slides.length, this.slidesInGroup]);
                this.sliderElement.trigger('sliderSwitchTo', [this.currentSlideIndex, this.getRealIndex(this.currentSlideIndex)]);
            }, this));
        }
    };

    NextendSmartSliderCarousel.prototype.initSlides = function () {
        if (this.layerMode) {
            for (var i = 0; i < this.realSlides.length; i++) {
                new NextendSmartSliderSlide(this, this.realSlides.eq(i), 0);
            }

            var staticSlide = this.findStaticSlide();
            if (staticSlide.length) {
                new NextendSmartSliderSlide(this, staticSlide, true, true);
            }
        }
    };

    NextendSmartSliderCarousel.prototype.callOnSlide = function (slide, functionName) {
        slide.find('.n2-ss-slide').each(function (i, el) {
            $(el).data('slide')[functionName]();
        });
    };

    NextendSmartSliderCarousel.prototype.getRealIndex = function (index) {
        return index * this.slidesInGroup;
    };

    NextendSmartSliderCarousel.prototype.directionalChangeToReal = function (nextSlideIndex) {
        this.directionalChangeTo(Math.floor(nextSlideIndex / this.slidesInGroup));
    };

    NextendSmartSliderCarousel.prototype.adminGetCurrentSlideElement = function () {
        if (this.parameters.isStaticEdited) {
            return this.findStaticSlide();
        }
        return this.realSlides.filter('.n2-ss-slide-active');
    };

    scope.NextendSmartSliderCarousel = NextendSmartSliderCarousel;

})(n2, window);
(function ($, scope, undefined) {
    function NextendSmartSliderResponsiveCarousel(slider) {
        this.slideInGroup = 1;
        this.maximumPaneWidthRatio = 0;

        slider.sliderElement.on('SliderResize', $.proxy(this.onSliderResize, this));

        NextendSmartSliderResponsive.prototype.constructor.apply(this, arguments);
        this.fixedEditRatio = 0;
    };

    NextendSmartSliderResponsiveCarousel.prototype = Object.create(NextendSmartSliderResponsive.prototype);
    NextendSmartSliderResponsiveCarousel.prototype.constructor = NextendSmartSliderResponsiveCarousel;

    NextendSmartSliderResponsiveCarousel.prototype.storeDefaults = function () {
        NextendSmartSliderResponsive.prototype.storeDefaults.apply(this, arguments);

        if (this.slider.parameters.maxPaneWidth > 0) {
            this.maximumPaneWidthRatio = this.slider.parameters.maxPaneWidth / this.responsiveDimensions.startWidth;
        }
    };


    NextendSmartSliderResponsiveCarousel.prototype.addResponsiveElements = function () {

        this._sliderHorizontal = this.addResponsiveElement(this.sliderElement, ['width', 'marginRight', 'marginLeft'], 'w', 'slider');
        this._sliderVertical = this.addResponsiveElement(this.sliderElement, ['height', 'marginTop', 'marginBottom'], 'h', 'slider');
        this.addResponsiveElement(this.sliderElement, ['fontSize'], 'fontRatio', 'slider');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slider-1'), ['width', 'paddingLeft', 'paddingRight', 'borderLeftWidth', 'borderRightWidth'], 'w');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slider-1'), ['height', 'paddingTop', 'paddingBottom', 'borderTopWidth', 'borderBottomWidth'], 'h');

        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slider-pane'), ['width'], 'paneW', 'pane');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slider-pane'), ['height'], 'h', 'pane').setCentered();

        this.addResponsiveElement(this.sliderElement.find('.n2-ss-slide'), ['width'], 'slideW', 'slideouter');
        this.helperElements.canvas = this.addResponsiveElement(this.sliderElement.find('.n2-ss-slide'), ['height'], 'slideH', 'slideouter');

        this.addResponsiveElement(this.sliderElement.find('.n2-ss-layers-container'), ['width'], 'slideW', 'slide');
        this.addResponsiveElement(this.sliderElement.find('.n2-ss-layers-container'), ['height'], 'slideH', 'slide')
            .setCentered();

        var backgroundImages = this.slider.backgroundImages.getBackgroundImages();
        for (var i = 0; i < backgroundImages.length; i++) {
            this.addResponsiveElementBackgroundImageAsSingle(backgroundImages[i].image, backgroundImages[i], []);
        }
    };

    NextendSmartSliderResponsiveCarousel.prototype.getCanvas = function () {
        return this.helperElements.canvas;
    };

    NextendSmartSliderResponsiveCarousel.prototype._buildRatios = function (ratios, dynamicHeight, nextSlideIndex) {

        if (this.maximumPaneWidthRatio > 0 && ratios.w > this.maximumPaneWidthRatio) {
            ratios.paneW = this.maximumPaneWidthRatio;
        } else {
            ratios.paneW = ratios.w;
        }

        var sliderWidth = this.responsiveDimensions.startWidth * ratios.paneW - 40;

        if (sliderWidth < this.responsiveDimensions.startSlideWidth) {
            var test = sliderWidth / this.responsiveDimensions.startSlideWidth;
            ratios.h = test;
            ratios.slideW = test;
            ratios.slideH = test;
        } else {
            ratios.h = 1;
            ratios.slideW = 1;
            ratios.slideH = 1;
        }


        var wH = $(window).height() - 100;
        if (ratios.h * this.responsiveDimensions.startHeight > wH) {
            ratios.slideW = ratios.slideH = ratios.h = wH / this.responsiveDimensions.startHeight;
        }

        this.slideInGroup = Math.max(1, Math.floor(sliderWidth / (this.responsiveDimensions.startSlideWidth * ratios.slideW)));
        this.slider.calibrateGroup(this.slideInGroup);

        NextendSmartSliderResponsive.prototype._buildRatios.apply(this, arguments);
    };


    NextendSmartSliderResponsiveCarousel.prototype.onSliderResize = function () {

        var sideMargin = Math.floor((this.responsiveDimensions.pane.width - this.responsiveDimensions.slide.width * this.slider.slidesInGroup) / this.slider.slidesInGroup / 2);
        this.slider.realSlides.css({
            marginLeft: sideMargin,
            marginRight: sideMargin,
            marginTop: parseInt((this.responsiveDimensions.pane.height - this.responsiveDimensions.slide.height) / 2)
        });
    };

    scope.NextendSmartSliderResponsiveCarousel = NextendSmartSliderResponsiveCarousel;

})(n2, window);
(function ($, scope, undefined) {


    function NextendSmartSliderMainAnimationCarousel(slider, parameters) {

        parameters = $.extend({
            delay: 0,
            type: 'horizontal'
        }, parameters);
        parameters.delay /= 1000;

        NextendSmartSliderMainAnimationAbstract.prototype.constructor.apply(this, arguments);

        this.setActiveSlide(this.slider.slides.eq(this.slider.currentSlideIndex));

        this.animations = [];

        switch (this.parameters.type) {
            case 'fade':
                this.animations.push(this._mainAnimationFade);
                break;
            case 'vertical':
                this.animations.push(this._mainAnimationVertical);
                break;
            case 'no':
                this.animations.push(this._mainAnimationNo);
                break;
            case 'fade':
                this.animations.push(this._mainAnimationFade);
                break;
            case 'fade':
                this.animations.push(this._mainAnimationFade);
                break;
            default:
                this.animations.push(this._mainAnimationHorizontal);
        }
    };

    NextendSmartSliderMainAnimationCarousel.prototype = Object.create(NextendSmartSliderMainAnimationAbstract.prototype);
    NextendSmartSliderMainAnimationCarousel.prototype.constructor = NextendSmartSliderMainAnimationCarousel;


    /**
     * Used to hide non active slides
     * @param slide
     */
    NextendSmartSliderMainAnimationCarousel.prototype.setActiveSlide = function (slide) {
        var notActiveSlides = this.slider.slides.not(slide);
        for (var i = 0; i < notActiveSlides.length; i++) {
            this._hideSlide(notActiveSlides.eq(i));
        }
    };

    /**
     * Hides the slide, but not the usual way. Simply positions them outside of the slider area.
     * If we use the visibility or display property to hide we would end up corrupted YouTube api.
     * If opacity 0 might also work, but that might need additional resource from the browser
     * @param slide
     * @private
     */
    NextendSmartSliderMainAnimationCarousel.prototype._hideSlide = function (slide) {
        NextendTween.set(slide.get(0), {
            left: '-100000px'
        });
    };

    NextendSmartSliderMainAnimationCarousel.prototype._showSlide = function (slide) {
        NextendTween.set(slide.get(0), {
            left: 0
        });
    };

    NextendSmartSliderMainAnimationCarousel.prototype._getAnimation = function () {
        return $.proxy(this.animations[Math.floor(Math.random() * this.animations.length)], this);
    };

    NextendSmartSliderMainAnimationCarousel.prototype._initAnimation = function (currentSlideIndex, currentSlide, nextSlideIndex, nextSlide, reversed) {
        var animation = this._getAnimation();

        animation(currentSlide, nextSlide, reversed);
    };

    NextendSmartSliderMainAnimationCarousel.prototype.onChangeToComplete = function (previousSlideIndex, currentSlideIndex, isSystem) {

        this._hideSlide(this.slider.slides.eq(previousSlideIndex));

        NextendSmartSliderMainAnimationAbstract.prototype.onChangeToComplete.apply(this, arguments);
    };

    NextendSmartSliderMainAnimationCarousel.prototype._mainAnimationNo = function (currentSlide, nextSlide) {

        this._showSlide(nextSlide);

        this.slider.unsetActiveSlide(currentSlide);

        nextSlide.css('opacity', 0);

        this.slider.setActiveSlide(nextSlide);

        this.timeline.set(currentSlide, {
            opacity: 0
        }, 0);

        this.timeline.set(nextSlide, {
            opacity: 1
        }, 0);

        this.sliderElement.on('mainAnimationComplete.n2-simple-no', $.proxy(function () {
            this.sliderElement.off('mainAnimationComplete.n2-simple-no');
            currentSlide
                .css('opacity', '');
            nextSlide
                .css('opacity', '');
        }, this));
    };

    NextendSmartSliderMainAnimationCarousel.prototype._mainAnimationFade = function (currentSlide, nextSlide) {
        currentSlide.css('zIndex', 5);
        this._showSlide(nextSlide);

        this.slider.unsetActiveSlide(currentSlide);
        this.slider.setActiveSlide(nextSlide);

        this.timeline.to(currentSlide.get(0), this.parameters.duration, {
            opacity: 0,
            ease: this.getEase()
        }, 0);

        nextSlide.css('opacity', 1);

        this.sliderElement.on('mainAnimationComplete.n2-simple-fade', $.proxy(function () {
            this.sliderElement.off('mainAnimationComplete.n2-simple-fade');
            currentSlide
                .css('zIndex', '')
                .css('opacity', '');
            nextSlide
                .css('opacity', '');
        }, this));
    };

    NextendSmartSliderMainAnimationCarousel.prototype._mainAnimationHorizontal = function (currentSlide, nextSlide, reversed) {
        this.__mainAnimationDirection(currentSlide, nextSlide, 'horizontal', reversed);
    };

    NextendSmartSliderMainAnimationCarousel.prototype._mainAnimationVertical = function (currentSlide, nextSlide, reversed) {
        this._showSlide(nextSlide);
        this.__mainAnimationDirection(currentSlide, nextSlide, 'vertical', reversed);
    };

    NextendSmartSliderMainAnimationCarousel.prototype.__mainAnimationDirection = function (currentSlide, nextSlide, direction, reversed) {
        var property = '',
            propertyValue = 0;

        if (direction == 'horizontal') {
            property = 'left';
            propertyValue = currentSlide.width();
        } else if (direction == 'vertical') {
            property = 'top';
            propertyValue = currentSlide.height();
        }


        if (reversed) {
            propertyValue *= -1;
        }

        nextSlide.css(property, propertyValue);


        nextSlide.css('zIndex', 5);

        currentSlide.css('zIndex', 4);


        this.slider.unsetActiveSlide(currentSlide);
        this.slider.setActiveSlide(nextSlide);

        var inProperties = {
            ease: this.getEase()
        };
        inProperties[property] = 0;

        this.timeline.to(nextSlide.get(0), this.parameters.duration, inProperties, 0);

        var outProperties = {
            ease: this.getEase()
        };
        outProperties[property] = -propertyValue;
        this.timeline.to(currentSlide.get(0), this.parameters.duration, outProperties, 0);


        this.sliderElement.on('mainAnimationComplete.n2-simple-fade', $.proxy(function () {
            this.sliderElement.off('mainAnimationComplete.n2-simple-fade');
            nextSlide
                .css('zIndex', '')
                .css(property, '');
            currentSlide
                .css('zIndex', '');
        }, this));
    };

    scope.NextendSmartSliderMainAnimationCarousel = NextendSmartSliderMainAnimationCarousel;

})(n2, window);
