//app.js
var util = require('./utils/utils.js');
var api;
App({
    is_on_launch: true,
    onLaunch: function () {
        // this.setApi();
        api = this.api;

        this.getNavigationBarColor();
        // console.log(wx.getSystemInfoSync());
        this.getStoreData();
        this.getCatList();
    },
    globalData:{
      parent_id:0,
    },
    getStoreData: function () {
        var page = this;
        this.request({
            url: api.default.store,
            success: function (res) {
                if (res.code == 0) {
                    wx.setStorageSync("store", res.data.store);
                    wx.setStorageSync("store_name", res.data.store_name);
                    wx.setStorageSync("show_customer_service", res.data.show_customer_service);
                    wx.setStorageSync("contact_tel", res.data.contact_tel);
                    wx.setStorageSync("share_setting", res.data.share_setting);
                }
            },
            complete: function () {
                page.login();
            }
        });
    },

    getCatList: function () {
        this.request({
            url: api.default.cat_list,
            success: function (res) {
                if (res.code == 0) {
                    var cat_list = res.data.list || [];
                    wx.setStorageSync("cat_list", cat_list);
                }
            }
        });
    },
    /**
     * 绑定获取parent_id
     */
    getParent_id: function () {
        //绑定
        var parent_id = wx.getStorageSync('parent_id');
        if (parent_id != 0) {
            page.loginBindParent({parent_id: parent_id});
        }

    },

    login: function () {
        var _this = this;
        // wx.showLoading({
        //   title: "正在登录",
        //   mask: true,
        // });


        wx.login({
            success: function (res) {
                if (res.code) {
                    var code = res.code;

                    // console.log('getUserInfo')
                    wx.getUserInfo({
                        success: function (res) {
                            console.log(res);
                            _this.request({
                                url: api.passport.login,
                                method: "post",
                                data: {
                                    code: code,
                                    user_info: res.rawData,
                                    encrypted_data: res.encryptedData,
                                    iv: res.iv,
                                    signature: res.signature
                                },
                                success: function (res) {
                                    wx.hideLoading();
                                    //授权升级了
                                    if (res.code == 0) {
                                        wx.setStorageSync("access_token", res.data.access_token);
                                        wx.setStorageSync("user_info", {
                                            nickname: res.data.nickname,
                                            avatar_url: res.data.avatar_url,
                                            is_distributor: res.data.is_distributor,
                                            parent: res.data.parent,
                                            id: res.data.id,
                                            is_clerk: res.data.is_clerk
                                        });
                                    } else {
                                        wx.showToast({ title: res.msg });
                                    }
                                }
                            });
                        },
                        fail: function (res) {
                            console.log('getUserInfo')
                            // wx.navigateTo({
                            //     url: '/pages/authorize/authorize',
                            // })
                            wx.hideLoading();
                            // getApp().getauth({
                            //     content: '需要获取您的用户信息授权，请到小程序设置中打开授权',
                            //     cancel: true,
                            //     success: function (e) {
                            //         if (e) {
                            //             getApp().login();
                            //         }
                            //     },
                            // });
                        }
                    });
                } else {
                    //console.log(res);
                }

            }
        });
    },
    request: function (object) {
        if (!object.data)
            object.data = {};
        var access_token = wx.getStorageSync("access_token");
        if (access_token) {
            object.data.access_token = access_token;
        }
        object.data.store_id = this.siteInfo.store_id;


        wx.request({
            url: object.url,
            header: object.header || {
                'content-type': 'application/x-www-form-urlencoded'
            },
            data: object.data || {},
            method: object.method || "GET",
            dataType: object.dataType || "json",
            success: function (res) {
                if (res.data.code == -1) {
                    getApp().login();
                } else {
                    if (object.success)
                        object.success(res.data);
                }
            },
            fail: function (res) {
                var app = getApp();
                if (app.is_on_launch) {
                    app.is_on_launch = false;
                    wx.showModal({
                        title: "网络请求出错",
                        content: res.errMsg,
                        showCancel: false,
                        success: function (res) {
                            if (res.confirm) {
                                if (object.fail)
                                    object.fail(res);
                            }
                        }
                    });
                } else {
                    wx.showToast({
                        title: res.errMsg,
                        image: "/images/icon-warning.png",
                    });
                    if (object.fail)
                        object.fail(res);
                }
            },
            complete: function (res) {
                if (object.complete)
                    object.complete(res);
            }
        });
    },
    saveFormId: function (form_id) {
        this.request({
            url: api.user.save_form_id,
            data: {
                form_id: form_id,
            }
        });
    },

    loginBindParent: function (object) {
        var access_token = wx.getStorageSync("access_token");
        if (access_token == '') {
            return true;
        }
        // console.log(object);
        getApp().bindParent(object);
    },
    bindParent: function (object) {
        if (object.parent_id == "undefined" || object.parent_id == 0)
            return;
        console.log("Try To Bind Parent With User Id:" + object.parent_id);
        var user_info = wx.getStorageSync("user_info");
        var share_setting = wx.getStorageSync("share_setting");
        console.log(share_setting);
        if (share_setting.level > 0) {
            var parent_id = object.parent_id;
            console.log(parent_id);
            if (parent_id != 0) {
                getApp().request({
                    url: api.share.bind_parent,
                    data: { parent_id: object.parent_id },
                    success: function (res) {
                        if (res.code == 0) {
                            user_info.parent = res.data
                            wx.setStorageSync('user_info', user_info);
                        }
                    }
                });
            }
        }
    },

    /**
     * 分享送优惠券
     * */
    shareSendCoupon: function (page) {
        wx.showLoading({
            mask: true,
        });
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
        this.request({
            url: api.coupon.share_send,
            success: function (res) {
                if (res.code == 0) {
                    page.setData({
                        get_coupon_list: res.data.list
                    });
                }
            },
            complete: function () {
                wx.hideLoading();
            }
        });
    },
    getauth: function (object) {
        wx.showModal({
            title: '是否打开设置页面重新授权',
            content: object.content,
            confirmText: '去设置',
            success: function (e) {
                if (e.confirm) {
                    wx.openSetting({
                        success: function (res) {
                            if (object.success) {
                                object.success(res);
                            }
                        },
                        fail: function (res) {
                            if (object.fail) {
                                object.fail(res);
                            }
                        },
                        complete: function (res) {
                            if (object.complete)
                                object.complete(res);
                        }
                    })
                } else {
                    if (object.cancel) {
                        getApp().getauth(object);
                    }
                }
            }
        })
    },

    api: require('api.js'),
    // setApi: function () {
    //   var siteroot = this.siteInfo.siteroot;
    //   siteroot = siteroot.replace('app/index.php', '');
    //   siteroot += 'addons/zjhj_mall/core/web/index.php?store_id=-1&r=api/';

    //   function getNewApiUri(api) {
    //     for (var i in api) {
    //       if (typeof api[i] === 'string') {
    //         api[i] = api[i].replace('{$_api_root}', siteroot);
    //       } else {
    //         api[i] = getNewApiUri(api[i]);
    //       }
    //     }
    //     return api;
    //   }

    //   this.api = getNewApiUri(this.api);
    // },
    siteInfo: require('siteinfo.js'),
    pageOnLoad: function (page) {
        // console.log('--------pageOnLoad----------');
        this.setNavigationBarColor();
        this.setPageNavbar(page);
    },
    pageOnReady: function (page) {
        // console.log('--------pageOnReady----------');

    },
    pageOnShow: function (page) {
        // console.log('--------pageOnShow----------');

    },
    pageOnHide: function (page) {
        // console.log('--------pageOnHide----------');

    },
    pageOnUnload: function (page) {
        // console.log('--------pageOnUnload----------');

    },

    setPageNavbar: function (page) {
        console.log('----setPageNavbar----');
        console.log(page);
        var navbar = wx.getStorageSync('_navbar');

        if (navbar) {
            setNavbar(navbar);
        }
        this.request({
            url: api.default.navbar,
            success: function (res) {
                if (res.code == 0) {
                    setNavbar(res.data);
                    wx.setStorageSync('_navbar', res.data);
                }
            }
        });

        function setNavbar(navbar) {
            var in_navs = false;
            var route = page.route || (page.__route__ || null);
            console.log(route);
            //判断当前也页面是否有bar
            for (var i in navbar.navs) {
                if (navbar.navs[i].url === "/" + route) {
                    navbar.navs[i].active = true;
                    in_navs = true;
                } else {
                    navbar.navs[i].active = false;
                }
            }
            in_navs = true;//所有的页面都显示只要加载了头部的文件
            if (!in_navs)
                return;
            page.setData({ _navbar: navbar });
        }

    },

    getNavigationBarColor: function () {
        var app = this;
        app.request({
            url: api.default.navigation_bar_color,
            success: function (res) {
                if (res.code == 0) {
                    wx.setStorageSync('_navigation_bar_color', res.data);
                    app.setNavigationBarColor();
                }
            }
        });
    },

    /**
     * 设置标题栏颜色
     */
    setNavigationBarColor: function () {
        var navigation_bar_color = wx.getStorageSync('_navigation_bar_color');
        if (navigation_bar_color) {
            wx.setNavigationBarColor(navigation_bar_color);
        }
    },

    //登录成功后不刷新的页面
    loginNoRefreshPage: [
        'pages/index/index',
        // 'pages/user/user',
    ],

});