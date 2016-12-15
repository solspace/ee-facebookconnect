$(function () {
    "use strict";

    var $memberGroupCheckboxes = $("input:checkbox[name^=eligible_member_groups]");
    var $fbcMemberGroupSelect  = $("select[name=member_group]");

    $memberGroupCheckboxes.on({
        click: function () {
            populateFBCMemberGroup();
        }
    });

    removeUncheckedGroups();

    function populateFBCMemberGroup() {
        var selectedValue = $fbcMemberGroupSelect.val();
        var optionString = '';

        $memberGroupCheckboxes.each(function(){
            if ($(this).is(":checked")) {
                var value = $(this).val();

                optionString += '<option value="' + value + '"' + (selectedValue == value ? ' selected' : '') + '>';
                optionString += $(this).parent().text().trim();
                optionString += '</option>';
            }
        });

        if (optionString.length == 0) {
            optionString = '<option value="">' + emptyMemberGroupLabel + '</option>';
        }

        $fbcMemberGroupSelect.empty().append(optionString);
    }

    function removeUncheckedGroups() {
        var selectedValues = $memberGroupCheckboxes.filter(':checked').map(function(){
            return $(this).val();
        }).get();

        $('option', $fbcMemberGroupSelect).each(function(){
            if (selectedValues.indexOf($(this).val()) == -1) {
                $(this).remove();
            }
        });
    }
});
