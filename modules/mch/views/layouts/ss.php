<?php
/**
 * User: Xany <762632258@qq.com>
 * Date: 2018/1/9
 * Time: 17:41
 */
?>
<style>
    .form-control {
        height: 34px;
    }

    .form-group > div:first-child {
        padding-right: 4px;
        text-align: right;
        line-height: 34px;
    }

    .form-group > div:last-child {
        padding-left: 1rem;
    }

    .middle-center {
        line-height: 34px;
    }

    .w-20 {
        width: 20rem;
    }

    .w-12 {
        width: 12rem;
    }

    .status .nav-item,.bg-shaixuan {
        background-color: #f8f8f8;
    }

    .status .nav-item .nav-link {
        color: #464a4c;
        border: 1px solid #ddd;
        border-radius: 0;
    }

    .status .nav-item .nav-link.active {
        border-color: #ddd #ddd #fff;
        background-color: #fff;
    }
</style>
<script>
    jQuery.datetimepicker.setLocale('zh');
    jQuery('#date_start').datetimepicker({
        datepicker: true,
        timepicker: false,
        format: 'Y-m-d',
        dayOfWeekStart: 1,
        scrollMonth: false,
        scrollTime: false,
        scrollInput: false,
        onShow: function (ct) {
            this.setOptions({
                maxDate: jQuery('#date_end').val() ? jQuery('#date_end').val() : false
            })
        }
    });
    $(document).on('click', '#show_date_start', function () {
        $('#date_start').datetimepicker('show');
    });
    jQuery('#date_end').datetimepicker({
        datepicker: true,
        timepicker: false,
        format: 'Y-m-d',
        dayOfWeekStart: 1,
        scrollMonth: false,
        scrollTime: false,
        scrollInput: false,
        onShow: function (ct) {
            this.setOptions({
                minDate: jQuery('#date_start').val() ? jQuery('#date_start').val() : false
            })
        }
    });
    $(document).on('click', '#show_date_end', function () {
        $('#date_end').datetimepicker('show');
    });
    $(document).on('click', '.new-day', function () {
        var index = $(this).data('index');
        var myDate = new Date();
        var mydate = new Date(myDate.getTime() - index * 24 * 60 * 60 * 1000);
        jQuery('#date_start').datetimepicker('setOptions', {value: mydate});
        jQuery('#date_end').datetimepicker('setOptions', {value: myDate});
    });
</script>

