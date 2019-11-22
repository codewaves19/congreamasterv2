/**
 * Color setting for congrea
 *
 * You can have a rather longer description of the file as well,
 * if you like, and it can span multiple lines.
 *
 * @package    mod_Congrea
 * @copyright  2018 Ravi Kumar
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/modal_factory', 'core/notification'], function($, ModalFactory) {
    return {
        presetColor: function() {
            $(".form-select.defaultsnext #id_s_mod_congrea_preset").change(function() {
                var val = this.value;
                $('.admin_colourpicker .currentcolour').css('background-color', val);
                $('#id_s_mod_congrea_colorpicker').val(val);
            });

        },
        congreaOnlinePopup: function() {
            $('#overrideform').submit(function() {
                var newTab = window.open('', 'popupVc');
                if (window.newTab && window.newTab.closed === false) {
                    newTab.focus();
                    return false;
                }
                $(this).attr('target', 'popupVc');
                if (newTab) {
                    newTab.focus();
                    return newTab;
                }
                return true;
            });
        },
        congreaPlayRecording: function() {
            $('.playAct').submit(function() {
                var newTab = window.open('', 'popupVc');
                if (window.newTab && window.newTab.closed === false) {
                    newTab.focus();
                    return false;
                }
                $(this).attr('target', 'popupVc');
                if (newTab) {
                    newTab.focus();
                    return newTab;
                }
                return true;
            });
        },
        setSelectedDate: function() {
            let current = this;
            current.checkDay();
            $('#id_fromsessiondate_day, #id_fromsessiondate_month, #id_fromsessiondate_year').on('change', function() {
                current.checkDay();
            });
        },
        checkDay: function() {
            let day =$('#id_fromsessiondate_day').val();
            let month =$('#id_fromsessiondate_month').val();
            let year =$('#id_fromsessiondate_year').val();
            let weekday = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"];
            let date = new Date(month + '/' + day + '/' + year);
            let selectedDay = weekday[date.getDay()];
            let selectedId  = 'id_days_' + selectedDay;
            $('.form-check-input').prop('checked', false);
            $('#' + selectedId).prop('checked', true);
        },
        disableRepeatTill() {
            let value = $('#id_radiogroup_repeattill_1').attr('value');
            let current = this;
            $('#id_radiogroup_repeattill_1').click(function (elem){
                $('div[data-groupname="radiogroup[repeatdatetill]"]').css({pointerEvents:'visible', opacity:1})
                $('#id_radiogroup_occurances').prop('disabled', true);
            }); 

            $('#id_radiogroup_repeattill_2').click(function (elem){
                $('#id_radiogroup_occurances').prop('disabled', false);
                $('div[data-groupname="radiogroup[repeatdatetill]"]').css({pointerEvents:'none', opacity:0.5})
            });   
            
        },
        attachFunction: function () {
            //alert('ss1');
            $('.iconsmall').on('click', function(e) {
                var clickedLink = $(e.currentTarget);
                ModalFactory.create({
                    type: ModalFactory.types.SAVE_CANCEL,
                    title: 'Delete item',
                    body: 'Do you really want to delete?',
                })
                .then(function(modal) {
                    modal.setSaveButtonText('Delete');
                    var root = modal.getRoot();
                    // root.on(ModalEvents.save, function() {
                    //     var elementid = clickedLink.data('id');
                    //     // Do something to delete item
                    // });
                    modal.show();
                });
            });
        },
    };
});