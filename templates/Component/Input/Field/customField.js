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

    $(".taxonomySelect").click(function () {
        const il_signal = $(this).attr("modal-signal");

        $(this).trigger(il_signal,
            {
                'id' : il_signal, 'event' : 'click',
                'triggerer' : $(this),
                'options' : JSON.parse('[]')
            }
        );
    });

    $(".taxonomyReset").click(function () {
        const taxonomy = $(this).parent().attr("taxonomy");
        const $cont = $("#" + taxonomy + "_cont");

        $cont.find(".taxonomyResult").val(JSON.stringify([])).trigger("input");
        $cont.find(".tax-node").prop("checked", false);
    });

    $(".taxonomyResult").on("input", function () {
        const taxonomy = $(this).parent().attr("taxonomy");
        try {
            const value = JSON.parse($(this).val());

            let txt = "";

            if (value) {
                for (let i = 0; i < value.length; i++) {
                    if (i > 0) {
                        txt += ", ";
                    }
                    txt += value[i].title;
                }
            }

            $("#" + taxonomy + "_cont_txt").html(txt);
        } catch (e) {
            console.log("Invalid JSON: " + e);
        }
    }).each(function() {
        $(this).trigger("input");
    });

    $(document).on("change", ".tax-node", function() {
        const taxonomy = $(this).attr("taxonomy-id");

        const value = $(this).is(":checked");
        const $result = $("#" + taxonomy + "_cont").find(".taxonomyResult");
        let result = $result.val();

        try {
            result = JSON.parse(result);
        } catch (e) {
            result = [];
        }

        result = result.filter(function (item) {
            return parseInt(item.id) !== parseInt($(this).attr("node-id"));
        }.bind(this));

        if (value) {
            result.push({
                id: parseInt($(this).attr("node-id")),
                title: $(this).attr("node-title")
            });
        }

        result.sort(function (a, b) {
            return a.id - b.id;
        });

        $result.val(JSON.stringify(result)).trigger("input");
    });

    new MutationObserver((mutations, obs) => {
        if ($(".tax-node").length > 0) {
            obs.disconnect();

            setTimeout(function() {
                $(".taxonomyResult").each(function() {
                    const taxonomy = $(this).parent().attr("taxonomy");

                    try {
                        const value = JSON.parse($(this).val());

                        if (value) {
                            for (let i = 0; i < value.length; i++) {
                                const node = $("#" + taxonomy + "_cont").find(".tax-node[taxonomy-id='" + taxonomy + "'][node-id='" + value[i].id + "']");
                                if (node.length > 0) {
                                    node.prop("checked", true);
                                }
                            }
                        }
                    } catch (e) {
                        console.log("Invalid JSON: " + e);
                    }
                });
            }, 500);
        }
    }).observe(document.body, {
        childList: true,
        subtree: true
    });
});