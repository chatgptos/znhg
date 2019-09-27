var api = require('../../api.js');
var app = getApp();
var share_count = 0;
var width = 260;
var int = 1;
Page({
    data: {
        x: wx.getSystemInfoSync().windowWidth,
        y: wx.getSystemInfoSync().windowHeight,
        left: 0,
        show_notice: false,
        animationData: {},
    },

    /**
     * 生命周期函数--监听页面加载
     */
    onLoad: function (options) {
        app.pageOnLoad(this);
        this.loadData(options);
        var page = this;
        var parent_id = 0;
        var user_id = options.user_id;
        var scene = decodeURIComponent(options.scene);
        if (user_id != undefined) {
            parent_id = user_id;
        }
        else if (scene != undefined) {
            parent_id = scene;
        }

        console.log(options)
        console.log('console.log(parent_id)------------------'+parent_id+'user_id'+user_id)
        app.loginBindParent({ parent_id: parent_id });
        console.log('console.log(loginBindParent)------------------')
    },

    /**
     * 加载页面数据
     */
    loadData: function (options) {
        var page = this;
        var pages_index_index = wx.getStorageSync('pages_index_index');
        if (pages_index_index) {
            page.setData(pages_index_index);
        }
        app.request({
            url: api.default.index,
            success: function (res) {
                if (res.code == 0) {
                    page.setData(res.data);
                    wx.setStorageSync('pages_index_index', res.data);
                    wx.setStorageSync('store', res.data.store);
                    page.seckillTimer();
                }
            },
            complete: function () {
                wx.stopPullDownRefresh();
            }
        });

    },

    /**
     * 生命周期函数--监听页面显示
     */
    onShow: function () {
        app.pageOnShow(this);
        share_count = 0;
        var store = wx.getStorageSync("store");
        if (store && store.name) {
            wx.setNavigationBarTitle({
                title: store.name,
            });
        }
        clearInterval(int);
        this.notice();
    },

    /**
     * 页面相关事件处理函数--监听用户下拉动作
     */
    onPullDownRefresh: function () {
        this.loadData();
    },

    /**
     * 用户点击右上角分享
     */
    onShareAppMessage: function (options) {
        var page = this;
        var user_info = wx.getStorageSync("user_info");
        return {
            path: "/pages/index/index?user_id=" + user_info.id,
            success: function (e) {
                share_count++;
                if (share_count == 1)
                    app.shareSendCoupon(page);
            }
        };
    },
    receive: function (e) {
        var page = this;
        var id = e.currentTarget.dataset.index;
        wx.showLoading({
            title: '领取中',
            mask: true,
        })
        if (!page.hideGetCoupon) {
            page.hideGetCoupon = function (e) {
                var url = e.currentTarget.dataset.url || false;
                page.setData({
                    get_coupon_list: null,
                });
                if (url) {
                    wx.navigateTo({
                        url: url,
                    });
                }
            };
        }
        app.request({
            url: api.coupon.receive,
            data: { id: id },
            success: function (res) {
                wx.hideLoading();
                if (res.code == 0) {
                    page.setData({
                        get_coupon_list: res.data.list,
                        coupon_list: res.data.coupon_list
                    });
                } else {
                    wx.showToast({
                        title: res.msg,
                        duration: 2000
                    })
                    page.setData({
                        coupon_list: res.data.coupon_list
                    });
                }
            },
            // complete: function () {
            //   wx.hideLoading();
            // }
        });
    },

    navigatorClick: function (e) {
        var page = this;
        var open_type = e.currentTarget.dataset.open_type;
        var url = e.currentTarget.dataset.url;
        if (open_type != 'wxapp')
            return true;
        //console.log(url);
        url = parseQueryString(url);
        url.path = url.path ? decodeURIComponent(url.path) : "";
        console.log("Open New App");
        wx.navigateToMiniProgram({
            appId: url.appId,
            path: url.path,
            complete: function (e) {
                console.log(e);
            }
        });
        return false;

        function parseQueryString(url) {
            var reg_url = /^[^\?]+\?([\w\W]+)$/,
                reg_para = /([^&=]+)=([\w\W]*?)(&|$|#)/g,
                arr_url = reg_url.exec(url),
                ret = {};
            if (arr_url && arr_url[1]) {
                var str_para = arr_url[1], result;
                while ((result = reg_para.exec(str_para)) != null) {
                    ret[result[1]] = result[2];
                }
            }
            return ret;
        }
    },
    closeCouponBox: function (e) {
        this.setData({
            get_coupon_list: ""
        });
    },

    notice: function () {
        var page = this;
        var notice = page.data.notice;
        if (notice == undefined) {
            return;
        }
        var length = notice.length * 14;
        return ;
        var left = 0;
        var right = 260;
        var new_width = width * (page.data.x) / 375;
        console.log(length)
        if (length < new_width) {
            return;
        }
        int = setInterval(function () {
            // if (left + new_width >= length) {
            //     left = 0;
            // } else {
            //     left += 10;
            // }
            // page.setData({
            //     left: -left
            // });

            left += 2;
            if (left + new_width >= length) {
                var l = left + new_width;
                right -= 2;
                page.setData({
                    show_second: true,
                });
                if (right <= 0) {
                    left = 0;
                    right = 260;
                    page.setData({
                        show_second: false,
                    });
                }
            }
            page.setData({
                left: -left,
                right: right
            });
        }, 250);
    },
    seckillTimer: function () {
        var page = this;
        if (!page.data.seckill || !page.data.seckill.rest_time)
            return;
        var timer = setInterval(function () {
            if (page.data.seckill.rest_time > 0) {
                page.data.seckill.rest_time = page.data.seckill.rest_time - 1;
            } else {
                clearInterval(timer);
                return;
            }
            page.data.seckill.times = page.getTimesBySecond(page.data.seckill.rest_time);
            page.setData({
                seckill: page.data.seckill,
            });
        }, 1000);

    },

    onHide: function () {
        app.pageOnHide(this);
        clearInterval(int);
    },
    onUnload: function () {
        app.pageOnUnload(this);
        clearInterval(int);
    },
    showNotice: function () {
        this.setData({
            show_notice: true
        });
    },
    closeNotice: function () {
        this.setData({
            show_notice: false
        });
    },

    getTimesBySecond: function (s) {
        s = parseInt(s);
        if (isNaN(s))
            return {
                h: '00',
                m: '00',
                s: '00',
            };
        var _h = parseInt(s / 3600);
        var _m = parseInt((s % 3600) / 60);
        var _s = s % 60;
        return {
            h: _h < 10 ? ('0' + _h) : ('' + _h),
            m: _m < 10 ? ('0' + _m) : ('' + _m),
            s: _s < 10 ? ('0' + _s) : ('' + _s),
        };

    },

});
