// noinspection JSPotentiallyInvalidUsageOfThis
window.lazyLoad = function () {
    //initialize as not active
    this.active = false;

    //gather all lazy load images
    this.getLazyElements = function () {
        return [].slice.call(document.querySelectorAll(".lazyload"));
    }

    //find which device we are using
    this.getDeviceType = function () {
        let type = 'desktop';

        if (window.outerWidth <= 1024) {
            type = 'tablet';
        }

        if (window.outerWidth <= 767) {
            type = 'mobile';
        }

        return type;
    }

    //check if the element is visible on screen
    this.isElementVisible = function (element) {
        return (
            (
                element.getBoundingClientRect().top <= window.innerHeight
                && element.getBoundingClientRect().bottom >= 0
            )
            && getComputedStyle(element).display !== 'none'
        )
    }

    //lazy load the element
    this.lazyLoadImage = function (lazyImage) {
        let src = lazyImage.dataset.src;
        let srcset = lazyImage.dataset.srcset;

        if (typeof lazyImage.dataset[this.getDeviceType() + 'src'] != 'undefined') {
            src = lazyImage.dataset[this.getDeviceType() + 'src'];
        }

        if (typeof lazyImage.dataset[this.getDeviceType() + 'srcset'] != 'undefined') {
            srcset = lazyImage.dataset[this.getDeviceType() + 'srcset'];
        }

        if (lazyImage.nodeName === 'IMG') {
            lazyImage.src = src;
            lazyImage.srcset = srcset;
        } else {
            lazyImage.style.backgroundImage = 'url(' + src + ')';
        }

        lazyImage.classList.remove('lazyload');
    }

    //if active do not overlap
    if (this.active === false) {
        // set as active to avoid overlapping
        this.active = true;

        //for each lazy load element
        this.getLazyElements().forEach(function (lazyImage) {
            //validate if is visible
            if (this.isElementVisible(lazyImage)) {
                //than lazy load it
                this.lazyLoadImage(lazyImage);

                //for performance remove events when all elements have been lazy loaded
                if (this.getLazyElements().length === 0) {
                    document.removeEventListener("scroll", lazyLoad);
                    window.removeEventListener("resize", lazyLoad);
                    window.removeEventListener("orientationchange", lazyLoad);
                }
            }
        });

        //remove overlapping flag
        this.active = false;
    }
}
//generate main events
document.addEventListener('DOMContentLoaded', function () {
    setTimeout(function () {
        window.lazyLoad();

        document.addEventListener('scroll', window.lazyLoad);
        window.addEventListener('resize', window.lazyLoad);
        window.addEventListener('orientationchange', window.lazyLoad);
    }, 500);
});
