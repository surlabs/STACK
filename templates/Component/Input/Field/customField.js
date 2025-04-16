$(document).ready(function() {
    $('textarea').not('.yesRTEditor').addClass('noRTEditor');

    $(".tab-button").click(function () {
        let $section_id = $(this).attr('data-section-id');
        $(this).parent().find(".tab-button.active[data-section-id='" + $section_id + "']").removeClass("active");
            $(this).parent().parent().find(".tab-panel.active[data-section-id='" + $section_id + "']").removeClass("active");

        $(this).addClass("active");

        const target = $(this).data("tab");

        $(this).parent().parent().find("[data-tab-panel='" + target + "']").addClass("active");
    });

    $(".taxonomySelect").click(function (e) {
        const il_signal = $(this).attr("modal-signal");

        $(this).trigger(il_signal,
            {
                'id' : il_signal, 'event' : 'click',
                'triggerer' : $(this),
                'options' : JSON.parse('[]')
            }
        );
    });

    $(".taxonomyReset").click(function (e) {
        const taxonomy = $(this).parent().attr("taxonomy");

        $("#" + taxonomy + "_cont").find(".taxonomyResult").val(JSON.stringify([])).trigger("input");
    });

    $(".taxonomyResult").on("input", function (e) {
        const taxonomy = $(this).parent().attr("taxonomy");
        try {
            const value = JSON.parse($(this).val());

            $("#" + taxonomy + "_cont_txt").html(value.join(", "));
        } catch (e) {
            console.log("Invalid JSON: " + e);
        }
    });
});