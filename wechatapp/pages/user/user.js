//index.js
//获取应用实例

var api = require('../../api.js');
var app = getApp();
var mta = require('../../analysis/mta_analysis.js');

Page({


    /**
     * 页面的初始数据
     */
    data: {
        contact_tel: "",
        show_customer_service: 0,
        user_center_bg: "/images/img-user-bg.png",
        parent_id: 0,
        userInfo: {},
        user_info: 0,

        // motto: 'Hello World',
        // hasUserInfo: false,
        // canIUse: wx.canIUse('button.open-type.getUserInfo'),
        // list:[
        //     { text: "成为商家" }, { text: "我的好友" }, { text: "我的二维码" }, { text: "意见反馈" }, { text: "系统设置" },
        // ]
        seckill: {'name': "(达标主播奖励7天)",'rest_time': "10"},
        seckill_show:false,
    },
        /**
         * 生命周期函数--监听页面加载
         */
        onLoad: function (options) {
            getApp().login();
            app.pageOnLoad(this);
            this.loadData(options);
            // this.setParent_id(options);
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
            console.log('console.log(parent_id)------------------' + parent_id + 'user_id' + user_id)
            wx.setStorageSync('parent_id', parent_id);
            console.log(parent_id)
            //第一次种下 判断如果没有登入
            if (parent_id == undefined || parent_id == "undefined" || parent_id == 0) {
                parent_id = app.globalData.parent_id;
            }
            if (parent_id != undefined && parent_id != "undefined" && parent_id != 0) {
                app.globalData.parent_id = parent_id;
                var access_token = wx.getStorageSync("access_token");
                if (access_token == '') {
                    //没有登入就种下

                } else {
                    app.loginBindParent({parent_id: parent_id});
                }
            }
            console.log('查看parent_id');
            console.log(parent_id);
            console.log(app.globalData.parent_id);
            mta.Event.stat('loginBindParent', {'scene': 'true'})
            mta.Event.stat('loginBindParent', {'userid': 'true'})
            mta.Event.stat('loginBindParent', {'parentid': 'true'})
        },
        /**
         * 绑定获取parent_id
         */
        getParent_idAndBind: function () {
            //绑定
            var parent_id = wx.getStorageSync('parent_id');
            console.log(parent_id);
            if (parent_id != 0) {
                app.loginBindParent({parent_id: parent_id});
            }

        },
        /**
         * 绑定获取 parent_id 改造的种下 parent_id  可能转发 可能扫码 --然后在登入所以先种下来 parent_id
         */
        setParent_id: function (options) {
            var parent_id = 0;
            var share_user_id = options.user_id;//转发过来的推荐人id
            var scene = decodeURIComponent(options.scene);//二维码推荐人id
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

            //得到推荐者的id
            if (share_user_id != undefined && share_user_id != 'undefined' && share_user_id != 0) {
                parent_id = share_user_id;
                console.log('share_user_id != undefined------------------' + parent_id)
            }
            if (scene != undefined && scene != 'undefined' && scene != 0) {
                parent_id = scene;
                console.log('scene != undefined------------------' + parent_id)
            }
            if (parent_id != 0 && parent_id != undefined && parent_id != 'undefined') {
                //绑定
                wx.setStorageSync('parent_id', parent_id);
            } else {
                parent_id = wx.getStorageSync('parent_id');
            }


            if (parent_id != 0 && parent_id != undefined && parent_id != 'undefined') {
                app.loginBindParent({parent_id: parent_id});
            }

            var user_info = wx.getStorageSync('user_info');
            var user_id = user_info.id;

            console.log('console.log(share_user_id)------------------' + share_user_id)
            console.log('console.log(user_id)------------------' + user_id)
            console.log('console.log(parent_id)------------------' + parent_id)
            console.log('console.log(loginBindParent)------------------')

        },
        loadData: function (options) {
            var page = this;
            page.setData({
                store: wx.getStorageSync('store'),
            });
            var pages_user_user = wx.getStorageSync('pages_user_user');
            if (pages_user_user) {
                page.setData(pages_user_user);
            }
        },
        /**
         * 生命周期函数--监听页面初次渲染完成
         */
        onReady: function () {

        },

        /**
         * 生命周期函数--监听页面显示
         */
        onShow: function (options) {
            app.pageOnShow(this);
            var page = this;
            page.loadData();
            app.request({
                url: api.user.index,
                success: function (res) {
                    if (res.code == 0) {
                        page.setData(res.data);
                        wx.setStorageSync('pages_user_user', res.data);
                        wx.setStorageSync("share_setting", res.data.share_setting);
                        wx.setStorageSync("user_info", res.data.user_info);
                        page.setData({
                            user_info: res.data.user_info,
                        });
                        console.log(page.data.seckill)
                        if(page.data.seckill.rest_time>0){
                            page.setData({
                                seckill_show: true,
                            });
                            page.seckillTimer();
                        }


                    }
                },
                complete: function (res) {
                    if (res.data.code === -1) {
                        var user_info = wx.getStorageSync('user_info');
                        console.log('user_info-1')
                        console.log(user_info)
                        //本地的缓存数据作为access_token登入不了那么晴空数据
                        wx.setStorageSync("user_info", {});
                    }
                }
            });
        },
        callTel: function (e) {
            var tel = e.currentTarget.dataset.tel;
            wx.makePhoneCall({
                phoneNumber: tel, //仅为示例，并非真实的电话号码
            });
        },
        apply: function (e) {
            getApp().login();
            app.loginBindParent({parent_id: app.globalData.parent_id});
            var page = this;
            var share_setting = wx.getStorageSync("share_setting");
            var user_info = wx.getStorageSync("user_info");
            if (share_setting.share_condition == 1) {
                wx.navigateTo({
                    url: '/pages/add-share/index',
                })
            } else if (share_setting.share_condition == 0 || share_setting.share_condition == 2) {
                if (user_info.is_distributor == 0) {
                    wx.showModal({
                        title: "进入积分",
                        content: "是否申请？",
                        success: function (r) {
                            if (r.confirm) {
                                wx.showLoading({
                                    title: "正在加载",
                                    mask: true,
                                });
                                app.request({
                                    url: api.share.join,
                                    methods: "POST",
                                    success: function (res) {
                                        if (res.code == 0) {
                                            if (share_setting.share_condition == 0) {
                                                user_info.is_distributor = 2;
                                                wx.navigateTo({
                                                    url: '/pages/add-share/index',
                                                })
                                            } else {
                                                user_info.is_distributor = 1;
                                                wx.navigateTo({
                                                    url: '/pages/share/index',
                                                })
                                            }
                                            wx.setStorageSync("user_info", user_info);
                                        }
                                    },
                                    complete: function () {
                                        wx.hideLoading();
                                    }
                                });
                            }
                        },
                    })
                } else {
                    wx.navigateTo({
                        url: '/pages/add-share/index',
                    })
                }
            }
        },
        verify: function (e) {
            wx.scanCode({
                onlyFromCamera: false,
                success: function (res) {
                    console.log(res)
                    wx.navigateTo({
                        url: '/' + res.path,
                    })
                }, fail: function (e) {
                    wx.showToast({
                        title: '失败'
                    });
                }
            });
        },
        member: function () {
            wx.navigateTo({
                url: '/pages/member/member',
            })
        },
        integral: function () {
            wx.navigateTo({
                url: '/pages/fair/fair',
            })
        },
        card: function () {
            wx.navigateTo({
                url: '/pages/coupon-merchant-mall-list/index',
            })
        },
        //事件处理函数
        bindViewTap: function (e) {
            var page = this;
            var user_info = wx.getStorageSync("user_info");
            console.log(user_info)
            var parent_id = wx.getStorageSync('parent_id');
            console.log(e)
            console.log(e.detail.userInfo)
            if (e.detail.userInfo) {
                var page = this;
                var _this = this;
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
                                                console.log('api.user.index000000000000000000000000000000000');
                                                wx.setStorageSync("access_token", res.data.access_token);
                                                wx.setStorageSync("user_info", {
                                                    nickname: res.data.nickname,
                                                    avatar_url: res.data.avatar_url,
                                                    is_distributor: res.data.is_distributor,
                                                    parent: res.data.parent,
                                                    id: res.data.id,
                                                    is_clerk: res.data.is_clerk
                                                });
                                                app.request({
                                                    url: api.user.index,
                                                    success: function (res) {
                                                        if (res.code == 0) {
                                                            page.setData(res.data);
                                                            page.setData({
                                                                contact_tel: res.data.contact_tel,
                                                                menu_list: res.data.menu_list,
                                                                next_level: res.data.next_level,
                                                                order_count: res.data.order_count,
                                                                share_setting: res.data.share_setting,
                                                                show_customer_service: res.data.show_customer_service,
                                                                user_center_bg: res.data.user_center_bg,
                                                                user_info: res.data.user_info,
                                                            });
                                                            console.log('api.user.index000000000000000000000000000000000loginBindParent');
                                                            console.log(page.data);
                                                            app.loginBindParent({parent_id: app.globalData.parent_id});
                                                        }
                                                    }
                                                });

                                            } else {
                                                wx.showToast({title: res.msg});
                                            }
                                        }
                                    });
                                },
                                fail: function (res) {
                                    wx.showToast({
                                        title: '请登入',
                                        image: "/images/icon-warning.png",
                                    });
                                    console.log('getUserInfo')
                                    wx.hideLoading();
                                }
                            });
                        } else {
                            //console.log(res);
                        }

                    }
                });

            } else {
                //用户按了拒绝按钮
                wx.showModal({
                    title: '警告',
                    content: '您点击了拒绝授权，将无法进入小程序，请授权之后再进入!!!',
                    showCancel: false,
                    confirmText: '返回授权',
                    success: function (res) {
                        if (res.confirm) {
                            console.log('用户点击了“返回授权”')
                        }
                    }
                })
            }
            // console.log('api.user.index000000000000000000000000000000000');
            if (Object.keys(user_info).length === 0 || user_info == "" || user_info == {}) {

            }
        },
        siteInfo: require('../../siteinfo.js'),
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


        getTimesBySecond: function (s) {
            s = parseInt(s);
            if (isNaN(s))
                return {
                    d: '7',
                    h: '10',
                    m: '9',
                    s: '0',
                };
            var _d = parseInt(s / (3600*24));
            var _h = parseInt(s / 3600);
            var _m = parseInt((s % 3600) / 60);
            var _s = s % 60;
            return {
                d: _d < 10 ? ('0' + _d) : ('' + _d),
                h: _h < 10 ? ('0' + _h) : ('' + _h),
                m: _m < 10 ? ('0' + _m) : ('' + _m),
                s: _s < 10 ? ('0' + _s) : ('' + _s),
            };

        },
        seckillTimer: function () {
            var page = this;
            if (!page.data.seckill || !page.data.seckill.rest_time)
                return;
            console.log(page.data.seckill);
            var timer = setInterval(function () {
                if (page.data.seckill.rest_time > 0) {
                    page.data.seckill.rest_time = page.data.seckill.rest_time - 1;
                } else {
                    clearInterval(timer);
                    page.setData({
                        seckill_show: false,
                    });
                    return;
                }
                page.data.seckill.times = page.getTimesBySecond(page.data.seckill.rest_time);
                page.setData({
                    seckill: page.data.seckill,
                });
            }, 1000);

        }
        /**
         * 用户点击右上角分享
         */
        // onShareAppMessage: function (options) {
        //     var page = this;
        //     var user_info = wx.getStorageSync("user_info");
        //     //申请完集市可以转发
        //     var share_setting = wx.getStorageSync("share_setting");
        //     if (share_setting.share_condition == 0 || share_setting.share_condition == 2) {
        //         return {
        //             path: "/pages/user/user?user_id=" + user_info.id,
        //             success: function (e) {
        //                 share_count++;
        //                 if (share_count == 1)
        //                     app.shareSendCoupon(page);
        //             }
        //         };
        //     }
        // }
});
