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
        $(".taxNodeListItem").each(function() {
            const nodeId = $(this).attr("node-id");
            const nodeTitle = $(this).attr("node-title");
            const taxonomy = $(this).attr("taxonomy-id");

            if ($(this).find(".tax-node[node-id='" + nodeId + "'][taxonomy-id='" + taxonomy + "']").length === 0) {
                $(this).find(".c-tree__node__line").first().prepend(
                    '<input type="checkbox" class="tax-node" node-id="' + nodeId + '" node-title="' + nodeTitle + '" taxonomy-id="' + taxonomy + '">'
                );
            }
        });

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
                                    node.parents(".expandable").not(node.parent().parent(".expandable")).attr("aria-expanded", "true");
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

function sendAIRequestWithContext(textareaId, ajaxUrl, button, e) {
    e.preventDefault();

    const context = $(button).closest(".modal").find(".context-prompt").val() || "";
    const questionId = new URLSearchParams(window.location.search).get("q_id") || 0;
    const close_button = $(button).parent().find(".btn.btn-default[data-dismiss='modal']");
    const spinner = $(button).parent().parent().find(".ilias-spinner");

    console.log(spinner);

    const formData = {};

    $("#ilContentContainer form").first()
        .find("input:not(.modal input), select:not(.modal select), textarea:not(.modal textarea)")
        .each(function() {
            const name = $(this).attr("name");

            if ($(this).is(":disabled")) {
                return;
            }

            if ($(this).attr("type") === "checkbox" && !$(this).is(":checked")) {
                return;
            }

            if ($(this).attr("aria-hidden") === "true") {
                if (typeof tinymce == "object") {
                    const editor = tinymce.get($(this).attr("id"));

                    if (editor) {
                        formData[name] = editor.getContent();
                    }
                }
                return;
            }

            formData[name] = $(this).val();
        });

    console.log("Context:", context);
    console.log("Textarea ID:", textareaId);
    console.log("AJAX URL:", ajaxUrl);
    console.log("Question ID:", questionId);
    console.log("Form Data:", formData);

    if (ajaxUrl && ajaxUrl.length > 0) {
        const payload = new FormData();
        payload.append("action", "generateWithAI");
        payload.append("formData", JSON.stringify(formData));
        payload.append("currentFieldName", $("#" + textareaId).attr("name"));
        payload.append("questionId", questionId);
        payload.append("context", context);

        spinner.removeClass("hidden");

        fetch(ajaxUrl, {
            method: "POST",
            body: payload,
        }).then(response => response.json()).then(data => {
            if (data.generated_text) {
                if (typeof tinymce == "object") {
                    const editor = tinymce.get(textareaId);

                    if (editor) {
                        editor.setContent(data.generated_text);
                    } else {
                        console.error("No TinyMCE editor found with ID: " + textareaId);
                    }
                } else {
                    const $textarea = $("#" + textareaId);

                    if ($textarea.length > 0) {
                        $textarea.text(data.generated_text);
                    } else {
                        console.error("No textarea found with ID: " + textareaId);
                    }
                }
            } else if (data.error) {
                console.error("Error: " + data.error);
            }
        }).catch(error => {
            console.error("Error:", error);
        }).finally(() => {
            close_button.click();
            spinner.addClass("hidden");
        });
    } else {
        console.log("No AJAX URL provided.");
    }
}