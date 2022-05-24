(window.webpackJsonp = window.webpackJsonp || []).push([
    ["load-tricks"], { cwGB: function(n, a) { $(document).ready((function() { var n = 0;
                $(window).scroll((function() { var n = $(window).scrollTop();
                    $(".up").hasClass("d-flex") && n <= 250 && ($(".up").removeClass("d-flex"), $(".up").addClass("d-none already")), $(".up").hasClass("already") && n > 250 && ($(".up").removeClass("d-none"), $(".up").addClass("d-flex")) }));
                $("#show-trick-js").click((function() { var a, e, s, t;
                    n++, a = n, e = $(this).data("path"), s = $("#number-trick-js").html(), t = $("#name-category").html(), $.ajax({ url: e, type: "POST", dataType: "json", data: { compteur: a, number: s, category: t } }).done((function(a) { $("#list-tricks-js").append(a.content), a.number <= 0 && $("#number-trick-js").parent().remove(), 1 === n && $(".up").toggleClass("d-flex d-none"), $("#number-trick-js").html(a.number) })).fail((function(n) { alert(n) })) })) })) } },
    [
        ["cwGB", "runtime"]
    ]
]);