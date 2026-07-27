// Seedling page namespace: keep page-specific state/routes in one place so
// the Livewire view, this file, and create-handsontable.js do not rely on
// scattered globals.
const seedlingRoutes = window.seedlingRoutes || {};
const seedlingConfig = window.seedlingConfig || {};
const resolveSeedlingEntry = () => {
    if (seedlingConfig.entry !== undefined && seedlingConfig.entry !== null && seedlingConfig.entry !== "") {
        return `${seedlingConfig.entry}`;
    }

    if (typeof window !== "undefined") {
        const match = window.location.pathname.match(/\/entry(\d+)(?:\/|$)/);
        if (match?.[1]) {
            return match[1];
        }
    }

    return null;
};

const resolveSeedlingUser = () => {
    if (seedlingConfig.user !== undefined && seedlingConfig.user !== null) {
        return seedlingConfig.user;
    }

    return "";
};

function seedlingAjaxErrorDetail(err, fallback = "儲存失敗") {
    const response = err?.response || err?.xhr?.responseJSON || {};
    const directMessage =
        response?.datasavenote ||
        response?.message ||
        response?.error ||
        response?.seedssavenote ||
        response?.finishnote;

    if (directMessage) {
        return directMessage;
    }

    const responseText = err?.xhr?.responseText || "";
    if (responseText) {
        const text = $("<div>").html(responseText).text().replace(/\s+/g, " ").trim();
        if (text) {
            return text.length > 500 ? `${text.slice(0, 500)}...` : text;
        }
    }

    const responseJson = Object.keys(response).length ? JSON.stringify(response) : "";
    if (responseJson && responseJson !== "{}") {
        return "後端回傳錯誤但沒有訊息：" + responseJson;
    }

    if (err?.error && err.error !== "Save error") {
        return err.error;
    }

    if (err?.xhr?.status) {
        return `HTTP ${err.xhr.status} ${err?.error && err.error !== "Save error" ? err.error : fallback}`;
    }

    return fallback;
}

const seedlingPage = (window.seedlingPage = window.seedlingPage || {
    routes: {
        base: seedlingRoutes.base || "/admin/fushan/seedling",
        recordPdfBase:
            seedlingRoutes.recordPdfBase ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/pdf/record`,
        saveCov:
            seedlingRoutes.saveCov ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/cov`,
        saveData:
            seedlingRoutes.saveData ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/data`,
        saveRecruit:
            seedlingRoutes.saveRecruit ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/recruit`,
        alternoteSave:
            seedlingRoutes.alternoteSave ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/alternote`,
        finishBase:
            seedlingRoutes.finishBase ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/finish`,
        slrollBase:
            seedlingRoutes.slrollBase ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/slroll`,
        dataDeleteBase:
            seedlingRoutes.dataDeleteBase ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/data`,
        alternoteBase:
            seedlingRoutes.alternoteBase ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/alternote`,
        alterDeleteBase:
            seedlingRoutes.alterDeleteBase ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/alter`,
        updateData:
            seedlingRoutes.updateData ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/update-data`,
        updateDataDelete:
            seedlingRoutes.updateDataDelete ||
            `${seedlingRoutes.base || "/admin/fushan/seedling"}/update-data/delete`,
    },
    context: {
        entry: resolveSeedlingEntry(),
        user: resolveSeedlingUser(),
        plotType: seedlingConfig.plotType || "fsseedling",
    },
    state: {
        thispage: 1,
        pps: 20,
        covs: [],
        record: [],
        emptytable: [],
        realemptytable: [],
        maxid: null,
        slroll: [],
        csplist: [],
    },
    route(name, ...segments) {
        const routeMap = {
            section: this.routes.base,
            recordPdf: this.routes.recordPdfBase,
            saveCov: this.routes.saveCov,
            saveData: this.routes.saveData,
            saveRecruit: this.routes.saveRecruit,
            alternoteSave: this.routes.alternoteSave,
            finish: this.routes.finishBase,
            slroll: this.routes.slrollBase,
            dataDelete: this.routes.dataDeleteBase,
            alternote: this.routes.alternoteBase,
            alterDelete: this.routes.alterDeleteBase,
            updateData: this.routes.updateData,
            updateDataDelete: this.routes.updateDataDelete,
        };
        const base = routeMap[name];

        if (!base) {
            return "";
        }

        const normalizedBase = `${base}`.replace(/\/+$/g, "");
        const normalizedSegments = segments
            .filter((segment) => segment !== null && segment !== undefined && segment !== "")
            .map((segment) => `${segment}`.replace(/^\/+|\/+$/g, ""));

        return [normalizedBase, ...normalizedSegments].join("/");
    },
    syncState(payload = {}) {
        this.state = {
            ...this.state,
            ...payload,
        };
    },
    noteTone(message) {
        const text = `${message || ""}`.trim();

        if (text === "") {
            return "";
        }

        const successKeywords = [
            "已儲存",
            "已刪除",
            "輸入完成",
            "完成",
        ];

        return successKeywords.some((keyword) => text.includes(keyword))
            ? "success"
            : "error";
    },
    resolveNoteTone(message, explicitTone = "") {
        if (explicitTone === "success" || explicitTone === "error") {
            return explicitTone;
        }

        return this.noteTone(message);
    },
    notePayload(response, fieldName) {
        return {
            message: response?.[fieldName] || "",
            tone: response?.[`${fieldName}_type`] || "",
        };
    },
    applyNoteTone(target, message, explicitTone = "") {
        if (!target || !target.length) {
            return;
        }

        const tone = this.resolveNoteTone(message, explicitTone);
        target.removeClass("app-feedback-note--success app-feedback-note--error");

        if (tone === "success") {
            target.addClass("app-feedback-note--success");
        } else if (tone === "error") {
            target.addClass("app-feedback-note--error");
        }
    },
    clearNotes() {
        const notes = $(".savenote");
        notes.html("");
        notes.removeClass("app-feedback-note--success app-feedback-note--error");
    },
    setNote(selector, message, tone = "") {
        const target = $(selector);
        target.html(message || "");
        this.applyNoteTone(target, message, tone);
    },
    currentSite() {
        return (
            this.state?.record?.[0]?.trap ||
            this.state?.covs?.[0]?.trap ||
            null
        );
    },
    scope(type, site = null) {
        const resolvedSite = site ?? this.currentSite();
        const selectors = {
            cov: `[data-seedling-cov-site="${resolvedSite}"]`,
            data: `[data-seedling-data-site="${resolvedSite}"]`,
            recruit: `[data-seedling-recruit-site="${resolvedSite}"]`,
            roll: `[data-seedling-roll-site="${resolvedSite}"]`,
            alternote: `[data-seedling-alternote-site="${resolvedSite}"]`,
        };

        if (!resolvedSite || !selectors[type]) {
            return $();
        }

        return $(selectors[type]).first();
    },
    scopedNoteSelector(type, site = null) {
        const scope = this.scope(type, site);
        const noteClassMap = {
            cov: ".covsavenote",
            data: ".datasavenote",
            recruit: ".recruitsavenote",
            roll: ".slrollsavenote",
            alternote: ".altersavenote",
            finish: ".finishnote",
        };

        if (type === "finish") {
            return $(noteClassMap[type]);
        }

        if (!scope.length || !noteClassMap[type]) {
            return $();
        }

        return scope.find(noteClassMap[type]);
    },
    setScopedNote(type, site, message, tone = "") {
        const target = this.scopedNoteSelector(type, site);
        if (target.length) {
            target.html(message || "");
            this.applyNoteTone(target, message, tone);
            return;
        }

        const fallbackClassMap = {
            cov: ".covsavenote",
            data: ".datasavenote",
            recruit: ".recruitsavenote",
            roll: ".slrollsavenote",
            alternote: ".altersavenote",
            finish: ".finishnote",
        };
        this.setNote(fallbackClassMap[type] || "", message, tone);
    },
    paginationElements(site = null) {
        const scope = this.scope("data", site);
        if (!scope.length) {
            return {
                scope: $(),
                pages: $(".pages").first(),
                totalnum: $(".totalnum").first(),
                pagenote: $(".pagenote").first(),
                prev: $(".prev").first(),
                next: $(".next").first(),
                pageSize: $("[data-seedling-page-size]").first(),
                pageSizeSelect: $("[data-seedling-page-size-select]").first(),
            };
        }

        return {
            scope,
            pages: scope.find("[data-seedling-pages]").first(),
            totalnum: scope.find("[data-seedling-totalnum]").first(),
            pagenote: scope.find("[data-seedling-pagenote]").first(),
            prev: scope.find("[data-seedling-prev]").first(),
            next: scope.find("[data-seedling-next]").first(),
            pageSize: scope.find("[data-seedling-page-size]").first(),
            pageSizeSelect: scope.find("[data-seedling-page-size-select]").first(),
        };
    },
    bindRollDeleteButtons() {
        $(".deleteroll")
            .off("click.seedling")
            .on("click.seedling", function () {
                const id = $(this).attr("deleteid");
                const tag = $(this).attr("tag");
                const entryValue = $(this).attr("entry");
                const trap = $(this).attr("trap");
                deleteroll(tag, id, entryValue, trap);
            });
    },
});

var plotType = seedlingPage.context.plotType;
var thispage = seedlingPage.state.thispage;

$(".button1")
    .off("click.seedling")
    .on("click.seedling", function (event) {
    event.preventDefault();

    const startRaw = `${$("#start").val() ?? ""}`.trim();
    const endRaw = `${$("#end").val() ?? ""}`.trim();

    if (startRaw === "" || endRaw === "") {
        alert("請輸入起始與結束樣站。");
        if (startRaw === "") {
            $("#start").trigger("focus");
        } else {
            $("#end").trigger("focus");
        }
        return;
    }

    if (!/^\d+$/.test(startRaw) || !/^\d+$/.test(endRaw)) {
        alert("樣站範圍請輸入正整數。");
        if (!/^\d+$/.test(startRaw)) {
            $("#start").trigger("focus");
        } else {
            $("#end").trigger("focus");
        }
        return;
    }

    const start = Number(startRaw);
    const end = Number(endRaw);

    if (start < 1 || end < 1) {
        alert("樣站範圍需大於 0。");
        if (start < 1) {
            $("#start").trigger("focus");
        } else {
            $("#end").trigger("focus");
        }
        return;
    }

    if (start > end) {
        alert("起始樣站不可大於結束樣站。");
        $("#start").trigger("focus");
        return;
    }

    const url = seedlingPage.route("recordPdf", start, end);
    const link = document.createElement("a");
    link.href = url;
    link.target = "_blank";
    link.rel = "noopener";
    link.style.display = "none";
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    });

$(".listlink")
    .off("click.seedling")
    .on("click.seedling", function () {
        let type = $(this).attr("type");
        if (typeof type != "undefined") {
            location.href = seedlingPage.route("section", type);
        }
    });

// 使用
handleHoverEvents(".list4", ".list4inner");
handleHoverEvents(".list3", ".list3inner");

document.addEventListener("livewire:init", () => {
    if (window.__boundSeedlingEvents) return;
    window.__boundSeedlingEvents = true;

    // initTablesorter
    Livewire.on("initTablesorter", ({ tag }) => {
        // tag = event.detail.tag;
        window.tag = tag; // 如果你其他地方還要用 tag，就存成全域比較安全
        requestAnimationFrame(() => {
            $(`#progressTable${tag}`).tablesorter();
        });
    });

    // data
    Livewire.on(
        "data",
        ({ covs, record, emptytable, maxid, slroll, csplist }) => {
            // covs = event.detail.covs;
            // data = event.detail.record;
            // ...

            seedlingPage.syncState({
                covs,
                record,
                emptytable,
                realemptytable: deepCopy(emptytable),
                maxid,
                slroll,
                csplist,
                thispage: 1,
            });
            // $(".save2").unbind();
            $(".save2").off(); // 建議用 off()（jQuery 新寫法）

            fscovtable(covs);

            if (record?.[0]?.tag !== "無") {
                fsseedlingtable(record, 1, 20, maxid);
            }

            recruittable(record, emptytable, csplist);
            fsslrolltable(slroll, covs);
        },
    );
});

function handleSuccessAllTable(res, tableType, handsontable) {
    var noteProperty = `${tableType}savenote`;
    const notePayload = seedlingPage.notePayload(res, noteProperty);
    const site =
        res?.trap ||
        res?.data?.[0]?.trap ||
        res?.recruit?.[0]?.trap ||
        res?.covs?.[0]?.trap ||
        seedlingPage.currentSite();

    if (notePayload.message != "") {
        const noteTypeMap = {
            cov: "cov",
            data: "data",
            recruit: "recruit",
            roll: "roll",
            alternote: "alternote",
        };
        const mappedType = noteTypeMap[tableType];
        if (mappedType) {
            seedlingPage.setScopedNote(mappedType, site, notePayload.message, notePayload.tone);
        } else {
            seedlingPage.setNote(`.${noteProperty}`, notePayload.message, notePayload.tone);
        }
    }

    if (tableType === "data") {
        // handsontable.updateData(res.data);
    } else if (tableType === "recruit") {
        handsontable.updateData(res.nonsavelist);
        if (res.recruit.length != 0) {
            reloadSeedlingDataTable(
                res.recruit,
                res.thispage,
                seedlingPage.state.pps,
                res.maxid,
                true,
            );
        }
    } else if (tableType === "alternote") {
        const alternotePayload = seedlingPage.notePayload(res, "datasavenote");
        if (alternotePayload.message != "") {
            seedlingPage.setScopedNote("alternote", site, alternotePayload.message, alternotePayload.tone);
        }
        seedlingPage.setScopedNote("data", site, "");
        fsseedlingtableupdate(
            res.data,
            res.thispage,
            seedlingPage.state.pps,
            res.maxid,
        );
        $(".deletealternotebutton").show();
    } else if (tableType === "roll") {
        fsrolltableupdate(res.data, res.trap);
    }
}

function cellfunction(tableType, container, row, col, prop) {
    var cellProperties = {};
    if (tableType == "data") {
        var curData = container.handsontable("getData")[row][10]; //column 10 is
        const currentMaxid = seedlingPage.state.maxid ?? window.maxid ?? null;
        if (container.handsontable("getData")[row][16] > currentMaxid) {
            cellProperties.readOnly = false;
            if (col == 8 || col == 14) {
                cellProperties.readOnly = true;
            }
        }

        if (col == 11 || col == 12) {
            //column needs to be read
            if (curData === "TRUE") {
                cellProperties.readOnly = true;
            }
        }

        if (col == 13 || col == 14) {
            cellProperties.className = "fs08";
        }
        return cellProperties;
    }

    if (tableType == "seedlingUpdate") {
        const source = container.handsontable("getSourceDataAtRow", row) || {};
        if (source.source === "master" && prop === "tag") {
            cellProperties.readOnly = true;
            cellProperties.className = ` seedling-readonly-cell`.trim();
        }
    }

    return cellProperties;
}

function reloadSeedlingDataTable(data, thispage, pps, maxid, forceRebuild = false) {
    if (!Array.isArray(data) || data.length === 0) {
        return;
    }

    const site = `${data[0].trap}`;
    const panel = seedlingPage.scope("data", site);
    const container = $(`#datatable${site}`);
    const emptyNote = panel.find(".seedling-empty-note").first();
    const tableShell = panel.find(".seedling-data-table-shell").first();

    if (emptyNote.length) {
        emptyNote.hide();
    }

    if (tableShell.length) {
        tableShell.show();
    }

    if (!forceRebuild && container.length && container.data("handsontable")) {
        fsseedlingtableupdate(data, thispage, pps, maxid);
        return;
    }

    if (container.length && container.handsontable("getInstance")) {
        container.handsontable("destroy");
    }

    requestAnimationFrame(() => {
        fsseedlingtable(data, thispage, pps, maxid);
    });
}

function showSeedlingEmptyState(site) {
    const panel = seedlingPage.scope("data", site);
    const container = $(`#datatable${site}`);
    const emptyNote = panel.find(".seedling-empty-note").first();
    const tableShell = panel.find(".seedling-data-table-shell").first();
    const pagination = seedlingPage.paginationElements(site);

    if (container.length && container.handsontable("getInstance")) {
        container.handsontable("destroy");
    }

    if (tableShell.length) {
        tableShell.hide();
    }

    if (emptyNote.length) {
        emptyNote.show();
    }

    pagination.totalnum.html("");
    pagination.pagenote.hide().html("");
    pagination.prev.hide().removeAttr("thispage");
    pagination.next.hide().removeAttr("thispage");
    pagination.pageSize.hide();
    pagination.pages.hide();

    seedlingPage.syncState({
        record: [],
        thispage: 1,
    });
}

function toggleSeedlingRecruitPanel(site = null) {
    const resolvedSite = site ?? seedlingPage.currentSite();
    const panel = seedlingPage.scope("recruit", resolvedSite);

    if (!panel.length) {
        $(".recruittableout").toggle();
        return;
    }

    const willOpen = !panel.is(":visible");
    panel.toggle();

    if (!willOpen) {
        return;
    }

    seedlingPage.setScopedNote("recruit", resolvedSite, "");
}

function toggleSeedlingRollPanel(site = null) {
    const resolvedSite = site ?? seedlingPage.currentSite();
    const panel = seedlingPage.scope("roll", resolvedSite);

    if (!panel.length) {
        $(".slrolltableout").toggle();
        return;
    }

    const willOpen = !panel.is(":visible");
    panel.toggle();

    if (!willOpen) {
        return;
    }

    seedlingPage.setScopedNote("roll", resolvedSite, "");
}

window.toggleSeedlingRecruitPanel = toggleSeedlingRecruitPanel;
window.toggleSeedlingRollPanel = toggleSeedlingRollPanel;

function fscovtable(covs) {
    // console.log(covs);
    // console.log(cov);
    var site = `${covs[0].trap}`;

    var container = $(`#covtable${site}`);

    var saveButtonName = `covsave${site}`;
    var tableType = "cov";

    for (let i = 0; i < covs.length; i++) {
        if (covs[i]["date"] === "0000-00-00") {
            covs[i]["date"] = "";
        }
    }

    var columns = [
        { data: "date", dateFormat: "YYYY-MM-DD", type: "date" },
        { data: "trap", readOnly: true },
        { data: "plot", readOnly: true },
        { data: "cov", type: "numeric" },
        {
            data: "canopy",
            type: "dropdown",
            source: ["U", "I", "G"],
            allowInvalid: false,
        },
        { data: "note" },
        { data: "id" },
    ];

    var colWidths = [120, 50, 50, 80, 120, 200];
    var colHeaders = ["Date", "Trap", "Plot", "覆蓋度", "樣區上方光度", "Note"];

    var hiddenColumns = {
        columns: [6],
    };
    return createHandsontable(
        container,
        columns,
        covs,
        saveButtonName,
        seedlingPage.route("saveCov"),
        tableType,
        colWidths,
        hiddenColumns,
        colHeaders,
        thispage,
    );
}

function deleteid(tag, entry, thispage) {
    if (confirm("確定刪除 " + tag + " 新增小苗資料??")) {
        const site = seedlingPage.currentSite();
        const pagination = seedlingPage.paginationElements(site);
        const resolvedThispage =
            seedlingPage.state.thispage
            || pagination.prev.attr("thispage")
            || pagination.next.attr("thispage")
            || thispage
            || 1;
        seedlingPage.setScopedNote("data", site, "");

        var saveUrl = seedlingPage.route("dataDelete", tag, entry, resolvedThispage);
        var ajaxData = { _method: "DELETE" };
        var ajaxType = "post";

        function handleSuccess(res) {
            const notePayload = seedlingPage.notePayload(res, "datasavenote");
            if (notePayload.message != "") {
                seedlingPage.setScopedNote(
                    "data",
                    res?.recruit?.[0]?.trap || site,
                    notePayload.message,
                    notePayload.tone,
                );
            }

            if (!Array.isArray(res.recruit) || res.recruit.length === 0) {
                showSeedlingEmptyState(site);
                return;
            }

            fsseedlingtableupdate(
                res.recruit,
                res.thispage,
                seedlingPage.state.pps,
                res.maxid,
            );
        }
        makeAjaxRequest(
            saveUrl,
            ajaxData,
            ajaxType,
            handleSuccess,
            function () {},
        );
    }
}

//data為原始資料
//data1為切割後得資料

function fsseedlingtable(data, thispage, pps, maxid) {
    // datapage=pages(data, thispage);
    // console.log(datapage);
    var site = `${data[0].trap}`;
    const pagination = seedlingPage.paginationElements(site);
    pagination.totalnum.html(`共有 ${data.length} 筆資料。`);

    var container = $(`#datatable${site}`);
    $(`button[name=datasave${site}]`).off();
    var saveButtonName = `datasave${site}`;
    var tabletype = "data";
    seedlingPage.syncState({ pps, maxid, thispage, record: data });
    window.ppsall = pps;
    pps = pps;
    var data2 = processDataTable(data, thispage, pps, site, plotType);
    const speciesOptions = seedlingPage.state.csplist || window.csplist || [];

    var columns = [
        {
            data: "date",
            dateFormat: "YYYY-MM-DD",
            type: "date",
            allowInvalid: false,
        },
        { data: "trap", readOnly: true },
        { data: "plot", readOnly: true },
        { data: "tag", readOnly: true },
        {
            data: "csp",
            readOnly: true,
            type: "autocomplete",
            source: speciesOptions,
            strict: false,
            visibleRows: 10,
        },
        { data: "ht", type: "numeric", allowInvalid: false },
        { data: "cotno", type: "numeric", allowInvalid: false },
        { data: "leafno", type: "numeric", allowInvalid: false },
        {
            data: "status",
            type: "dropdown",
            source: ["A", "G", "D", "N"],
            allowInvalid: false,
        },
        { data: "recruit", readOnly: true },
        { data: "sprout", readOnly: true },
        { data: "x", type: "numeric", allowInvalid: false },
        { data: "y", type: "numeric", allowInvalid: false },
        { data: "note" },
        { data: "alternotetable", readOnly: true, renderer: "html" },
        { data: "entry" },
        { data: "id" },
        { data: "user" },
    ];

    var colWidths = [
        120, 40, 40, 100, 120, 50, 50, 50, 40, 40, 60, 35, 35, 160, 160,
    ];
    var colHeaders = [
        "Date",
        "Trap",
        "Plot",
        "Tag",
        "種類",
        "長度",
        "子葉",
        "真葉",
        "狀態",
        "新舊",
        "萌櫱",
        "X",
        "Y",
        "Note",
        "特殊修改",
    ];

    var hiddenColumns = {
        columns: [15, 16, 17],
    };

    return createHandsontable(
        container,
        columns,
        data2,
        saveButtonName,
        seedlingPage.route("saveData"),
        tabletype,
        colWidths,
        hiddenColumns,
        colHeaders,
        thispage,
    );
}

function fsseedlingtableupdate(data, thispage, pps, maxid) {
    seedlingPage.setScopedNote("finish", null, "");
    if (!Array.isArray(data) || data.length === 0) {
        return;
    }

    var site = `${data[0].trap}`;
    seedlingPage.syncState({ thispage, pps, maxid, record: data });
    var container = $(`#datatable${site}`);

    if (!container.length || !container.data("handsontable")) {
        reloadSeedlingDataTable(data, thispage, pps, maxid, true);
        return;
    }

    var tableType = "data";
    dataTableUpdate(data, thispage, pps, plotType, tableType, site);
}

function recruittable(data, emptytable, csplist) {
    // console.log(entry);
    var site = data[0].trap;
    var thispage = 1;
    $(`button[name=recruitsave${site}]`).off();
    var container = $(`#recruittable${site}`);
    var saveButtonName = `recruitsave${site}`;
    var tabletype = "recruit";
    // var emptytable=emptytable;

    const plotValidator = (value, callback) => {
        if (value == 1 || value == 3 || value == 2 || value == "") {
            callback(true);
        } else {
            callback(false);
        }
    };

    var columns = [
        {
            data: "date",
            dateFormat: "YYYY-MM-DD",
            type: "date",
            allowInvalid: false,
        },
        { data: "trap", type: "numeric", allowInvalid: false },
        {
            data: "plot",
            type: "numeric",
            allowInvalid: false,
            validator: plotValidator,
        },
        { data: "tag" },
        {
            data: "csp",
            type: "autocomplete",
            source: csplist,
            strict: false,
            visibleRows: 10,
        },
        { data: "ht", type: "numeric", allowInvalid: false },
        { data: "cotno", type: "numeric", allowInvalid: false },
        { data: "leafno", type: "numeric", allowInvalid: false },

        {
            data: "recruit",
            type: "dropdown",
            source: ["R", "O", "T"],
            allowInvalid: false,
            visibleRows: 10,
        },
        {
            data: "sprout",
            type: "dropdown",
            source: ["FALSE", "TRUE"],
            allowInvalid: false,
            visibleRows: 10,
        },
        { data: "x", type: "numeric", allowInvalid: false },
        { data: "y", type: "numeric", allowInvalid: false },
        { data: "note" },
        {
            data: "tofix",
            type: "checkbox",
            checkedTemplate: "1",
            uncheckedTemplate: "",
        },
    ];

    var colWidths = [120, 40, 40, 80, 120, 50, 50, 50, 40, 90, 35, 35, 160, 50];
    var colHeaders = [
        "Date",
        "Trap",
        "Plot",
        "Tag",
        "種類",
        "長度",
        "子葉",
        "真葉",
        "新舊",
        "萌櫱",
        "X",
        "Y",
        "Note",
        "漏資料",
    ];

    var hiddenColumns = [];
    return createHandsontable(
        container,
        columns,
        emptytable,
        saveButtonName,
        seedlingPage.route("saveRecruit"),
        tabletype,
        colWidths,
        hiddenColumns,
        colHeaders,
        thispage,
    );
}

function deleteroll(tag, id, entry, trap) {
    if (confirm("確定刪除 " + tag + " 撿到環資料??")) {
        seedlingPage.setScopedNote("roll", trap, "");

        var saveUrl = seedlingPage.route("slroll", tag, id, entry, trap);
        var ajaxData = { _method: "DELETE" };
        var ajaxType = "post";

        function handleSuccess(res) {
            const notePayload = seedlingPage.notePayload(res, "slrollsavenote");
            if (notePayload.message != "") {
                seedlingPage.setScopedNote("roll", res.trap || trap, notePayload.message, notePayload.tone);
            }
            fsrolltableupdate(res.data, res.trap);
        }
        makeAjaxRequest(
            saveUrl,
            ajaxData,
            ajaxType,
            handleSuccess,
            function () {},
        );
    }
}

function fsrolltableupdate(data, trap) {
    var container = $(`#slrolltable${trap}`);
    var handsontable = container.data("handsontable");

    handsontable.updateData(buildSeedlingRollDisplayRows(data, trap));
    seedlingPage.bindRollDeleteButtons();
}

function fsslrolltable(slroll, covs) {
    var site = covs[0]["trap"];

    var container = $(`#slrolltable${site}`);

    var saveButtonName = `slrollsave${site}`;
    var tableType = "roll";
    var thispage = 1;

    var columns = [
        { data: "date", dateFormat: "YYYY-MM-DD", type: "date" },
        { data: "trap", type: "numeric", allowInvalid: false },
        { data: "plot", type: "numeric", allowInvalid: false },
        { data: "tag" },
        { data: "note" },
        { data: "delete", renderer: "html" },
        { data: "id" },
        { data: "year" },
        { data: "month" },
    ];

    var colWidths = [120, 50, 50, 80, 120, 50];
    var colHeaders = ["Date", "Trap", "Plot", "Tag", "Note", ""];

    var hiddenColumns = {
        columns: [6, 7, 8],
    };
    var handsontable = createHandsontable(
        container,
        columns,
        buildSeedlingRollDisplayRows(slroll, site),
        saveButtonName,
        seedlingPage.route("slroll", seedlingPage.context.entry, site),
        tableType,
        colWidths,
        hiddenColumns,
        colHeaders,
        thispage,
    );

    seedlingPage.bindRollDeleteButtons();

    return handsontable;
}

function openSeedlingAlternote(tag, entry, thispage) {
    const scope = getSeedlingAlternoteScope();
    const currentSite = seedlingPage.currentSite();
    const pagination = seedlingPage.paginationElements(currentSite);
    const resolvedThispage =
        seedlingPage.state.thispage
        || pagination.prev.attr("thispage")
        || pagination.next.attr("thispage")
        || thispage
        || scope.data("alternote-thispage")
        || 1;

    var saveUrl = `${seedlingPage.route("alternote", tag, entry, resolvedThispage)}?_=${Date.now()}`;
    handleAlternote(tag, entry, resolvedThispage, saveUrl);
}

function canOpenSeedlingAlternoteAfterDataSave(res) {
    const notePayload = seedlingPage.notePayload(res, "datasavenote");
    return notePayload.tone !== "error";
}

function saveSeedlingDataBeforeAlternote(tag, entry, thispage) {
    const currentSite = seedlingPage.currentSite();
    const resolvedEntry = seedlingPage.context.entry || entry;
    const pagination = seedlingPage.paginationElements(currentSite);
    const resolvedThispage =
        seedlingPage.state.thispage
        || pagination.prev.attr("thispage")
        || pagination.next.attr("thispage")
        || thispage
        || 1;
    const container = $(`#datatable${currentSite}`);
    const handsontable = container.data("handsontable");

    if (!handsontable) {
        openSeedlingAlternote(tag, resolvedEntry, resolvedThispage);
        return;
    }

    seedlingPage.setScopedNote("data", currentSite, "");

    const ajaxData = {
        data: handsontable.getSourceData(),
        entry: resolvedEntry,
        user: seedlingPage.context.user,
        plotType: seedlingPage.context.plotType,
        thispage: resolvedThispage,
    };

    makeAjaxRequest(
        seedlingPage.route("saveData"),
        ajaxData,
        "post",
        function (res) {
            handleSuccessAllTable(res, "data", handsontable);

            if (!canOpenSeedlingAlternoteAfterDataSave(res)) {
                return;
            }

            setTimeout(() => {
                openSeedlingAlternote(tag, resolvedEntry, resolvedThispage);
            }, 30);
        },
        function (err) {
            const detail = err?.xhr?.responseJSON?.datasavenote
                || err?.xhr?.responseJSON?.message
                || err?.xhr?.responseText
                || `${err?.status || ""} ${err?.error || "儲存失敗"}`;
            const tone = err?.xhr?.responseJSON?.datasavenote_type || "error";
            seedlingPage.setScopedNote("data", currentSite, detail, tone);
        },
    );
}

function alternote(tag, entry, thispage, envet) {
    saveSeedlingDataBeforeAlternote(tag, entry, thispage);
}

function deletealternote(tag, thispage) {
    const scope = getSeedlingAlternoteScope();
    const currentSite = seedlingPage.currentSite();
    const pagination = seedlingPage.paginationElements(currentSite);
    const resolvedTag = tag || scope.data("alternote-tag") || scope.find(".deletealternotebutton").attr("tag");
    const resolvedThispage =
        seedlingPage.state.thispage
        || pagination.prev.attr("thispage")
        || pagination.next.attr("thispage")
        || thispage
        || scope.data("alternote-thispage")
        || 1;

    var saveUrl = seedlingPage.route(
        "alterDelete",
        resolvedTag,
        seedlingPage.context.entry,
        resolvedThispage,
    );
    handleDeleteAlternote(resolvedTag, plotType, saveUrl);
}

const seedlingAlternoteFieldOptions = [
    ["Tag", "Tag"],
    ["Trap", "Trap"],
    ["Plot", "Plot"],
    ["csp", "種類"],
    ["原長度", "原長度"],
    ["原葉片數", "原葉片數"],
    ["狀態", "狀態"],
    ["other", "other"],
];

function getSeedlingAlternoteScope() {
    return window.seedlingPage?.scope
        ? window.seedlingPage.scope("alternote")
        : $(".seedling-alternote-panel").first();
}

function resetSeedlingAlternoteModal(scope = getSeedlingAlternoteScope()) {
    if (!scope || !scope.length) {
        return;
    }

    scope.find(".seedling-alternote-rows").empty();
    scope.find(".seedling-alternote-form").hide();
    scope.find("#alternotetable").hide().empty();
    scope.find(".alterstemid").html("");
    scope.find(".altertag").html("");
    scope.removeData("alternote-tag");
    scope.removeData("alternote-thispage");
    scope.removeData("alternote-id");
    scope.hide();
}

function closeSeedlingAlternoteModal(button) {
    const scope = button
        ? $(button).closest(".alternotetalbeouter")
        : getSeedlingAlternoteScope();
    resetSeedlingAlternoteModal(scope);
}

window.closeSeedlingAlternoteModal = closeSeedlingAlternoteModal;

function buildSeedlingAlternoteRow(field = "", value = "") {
    const optionsHtml = [
        `<option value="">選擇欄位</option>`,
        ...seedlingAlternoteFieldOptions.map(
            ([optionValue, label]) =>
                `<option value="${optionValue}" ${optionValue === field ? "selected" : ""}>${label}</option>`,
        ),
    ].join("");

    return `
        <div class="seedling-alternote-form-row">
            <select class="seedling-alternote-field">${optionsHtml}</select>
            <input type="text" class="seedling-alternote-value" value="${$("<div>").text(value ?? "").html()}" />
            <button type="button" class="seedling-alternote-remove">X</button>
        </div>
    `;
}

function bindSeedlingAlternoteForm(scope) {
    scope.find(".seedling-alternote-add")
        .off("click.seedling")
        .on("click.seedling", function () {
            scope.find(".seedling-alternote-rows").append(buildSeedlingAlternoteRow());
            bindSeedlingAlternoteForm(scope);
            refreshSeedlingAlternoteRemoveButtons(scope);
        });

    scope.find(".seedling-alternote-remove")
        .off("click.seedling")
        .on("click.seedling", function () {
            const rows = scope.find(".seedling-alternote-form-row");
            if (rows.length <= 1) {
                $(this).closest(".seedling-alternote-form-row").find("select").val("");
                $(this).closest(".seedling-alternote-form-row").find("input").val("");
                return;
            }
            $(this).closest(".seedling-alternote-form-row").remove();
            refreshSeedlingAlternoteRemoveButtons(scope);
        });
}

function refreshSeedlingAlternoteRemoveButtons(scope) {
    scope.find(".seedling-alternote-form-row").each(function (index) {
        $(this)
            .find(".seedling-alternote-remove")
            .toggleClass("is-hidden", index === 0);
    });
}

function renderSeedlingAlternoteForm(alterdata, tag, thispage) {
    const scope = getSeedlingAlternoteScope();
    const form = scope.find(".seedling-alternote-form");
    const rowsContainer = scope.find(".seedling-alternote-rows");
    const tableContainer = scope.find("#alternotetable");
    const normalizedData = alterdata || {};
    const id = normalizedData.id || "";

    scope.data("alternote-tag", tag);
    scope.data("alternote-thispage", thispage);
    scope.data("alternote-id", id);
    seedlingPage.syncState({ thispage });
    scope.find(".deletealternotebutton").attr({ tag: tag });

    tableContainer.hide().empty();
    form.show();
    rowsContainer.empty();

    const rows = Object.entries(normalizedData)
        .filter(([key, value]) => key !== "id" && value !== null && value !== undefined && value !== "")
        .map(([key, value]) => ({ field: key, value: `${value}` }));

    if (rows.length === 0) {
        rows.push({ field: "", value: "" });
    }

    rows.forEach((row) => {
        rowsContainer.append(buildSeedlingAlternoteRow(row.field, row.value));
    });

    bindSeedlingAlternoteForm(scope);
    refreshSeedlingAlternoteRemoveButtons(scope);

    scope.find("button[name=alternotesave]")
        .off("click.seedlingAlternote")
        .on("click.seedlingAlternote", function () {
            const payload = { id };

            scope.find(".seedling-alternote-form-row").each(function () {
                const field = $(this).find(".seedling-alternote-field").val();
                const value = $(this).find(".seedling-alternote-value").val();

                if (field && value !== "") {
                    payload[field] = value;
                }
            });

            const ajaxData = {
                data: [payload],
                entry: seedlingPage.context.entry,
                user: seedlingPage.context.user,
                plotType: seedlingPage.context.plotType,
                thispage,
            };

            makeAjaxRequest(
                seedlingPage.route("alternoteSave"),
                ajaxData,
                "post",
                function (res) {
                    if (res.datasavenote != "") {
                        seedlingPage.setScopedNote("alternote", seedlingPage.currentSite(), res.datasavenote);
                    }
                    seedlingPage.setScopedNote("data", seedlingPage.currentSite(), "");
                    fsseedlingtableupdate(
                        res.data,
                        res.thispage,
                        seedlingPage.state.pps,
                        res.maxid,
                    );
                    scope.find(".deletealternotebutton").show();
                },
                function () {},
            );
        });
}

function alternotetable(alterdata, tag, entry, thispage) {
    if (seedlingPage.context.plotType === "fsseedling") {
        renderSeedlingAlternoteForm(alterdata, tag, thispage);
        return null;
    }

    $("button[name=alternotesave]").off();
    $(".deletealternotebutton").attr({ tag: tag });
    var container = window.seedlingPage?.scope
        ? window.seedlingPage.scope("alternote").find("#alternotetable")
        : $("#alternotetable");

    if (container.handsontable("getInstance")) {
        container.handsontable("destroy");
    }

    var saveButtonName = "alternotesave";
    var tableType = "alternote";
    const speciesOptions = seedlingPage.state.csplist || window.csplist || [];

    var columns = [
        { data: "Trap", type: "numeric" },
        { data: "Plot", type: "numeric" },
        { data: "Tag" },
        {
            data: "csp",
            type: "autocomplete",
            source: speciesOptions,
            strict: false,
            visibleRows: 10,
            allowInvalid: false,
        },
        { data: "原長度" },
        { data: "原葉片數" },
        { data: "other" },
        { data: "狀態" },
        { data: "id" },
    ];

    var colWidths = [40, 40, 80, 120, 60, 60, 100];
    var colHeaders = [
        "Trap",
        "Plot",
        "Tag",
        "種類",
        "原長度",
        "原葉片數",
        "other",
        "狀態",
    ];

    var hiddenColumns = {
        columns: [7, 8],
    };
    const handsontable = createHandsontable(
        container,
        columns,
        alterdata,
        saveButtonName,
        seedlingPage.route("alternoteSave"),
        tableType,
        colWidths,
        hiddenColumns,
        colHeaders,
        thispage,
    );

    const refreshAlternoteTable = () => {
        const instance = container.data("handsontable");
        if (!instance) {
            return;
        }

        if (typeof instance.render === "function") {
            instance.render();
        }

        if (typeof instance.refreshDimensions === "function") {
            instance.refreshDimensions();
        }
    };

    requestAnimationFrame(() => {
        refreshAlternoteTable();
        setTimeout(refreshAlternoteTable, 40);
    });

    return handsontable;
}

function finish(entry) {
    var saveUrl = seedlingPage.route("finish", entry);
    var ajaxData = {};
    var ajaxType = "post";

    function handleSuccess(res) {
        if (res.finishnote != "") {
            seedlingPage.setScopedNote(
                "finish",
                null,
                res.finishnote,
                res.finishnote_type || "",
            );
        }
    }
    makeAjaxRequest(saveUrl, ajaxData, ajaxType, handleSuccess, function () {});
}

// 後端資料修改：特殊修改與個別 tag 共用同一組 Handsontable。
function buildSeedlingUpdateColumns(csplist) {
    return [
        { data: "census", type: "numeric", allowInvalid: false },
        { data: "year", type: "numeric", allowInvalid: false },
        { data: "month", type: "numeric", allowInvalid: false },
        { data: "date", dateFormat: "YYYY-MM-DD", type: "date", allowInvalid: false },
        { data: "trap", type: "numeric", allowInvalid: false },
        { data: "plot", type: "numeric", allowInvalid: false },
        { data: "tag" },
        { data: "mtag" },
        {
            data: "csp",
            type: "autocomplete",
            source: csplist || [],
            strict: false,
            visibleRows: 10,
        },
        { data: "ht", type: "numeric", allowInvalid: false },
        { data: "cotno", type: "numeric", allowInvalid: false },
        { data: "leafno", type: "numeric", allowInvalid: false },
        { data: "ind", type: "numeric", allowInvalid: false },
        { data: "recruit" },
        {
            data: "status",
            type: "dropdown",
            source: ["A", "G", "D", "N", ""],
            allowInvalid: false,
        },
        {
            data: "sprout",
            type: "dropdown",
            source: ["TRUE", "FALSE"],
            allowInvalid: false,
        },
        { data: "x", type: "numeric", allowInvalid: false },
        { data: "y", type: "numeric", allowInvalid: false },
        { data: "note" },
        { data: "alternote" },
        { data: "delete_action", renderer: "html", readOnly: true },
        { data: "source" },
        { data: "work_id" },
        { data: "record_id" },
        { data: "individual_id" },
        { data: "stem_id" },
        { data: "original_tag" },
        { data: "original_mtag" },
    ];
}

function seedlingUpdateTable(container, rows, csplist, fitToRows = false, tableShape = "work") {
    const existing = container.data("handsontable");
    if (existing && typeof existing.destroy === "function") {
        existing.destroy();
        container.removeData("handsontable");
    }
    container.empty();
    container.css({
        minHeight: "0",
        width: "100%",
    });

    const tableRows = Array.isArray(rows) ? rows : [];
    if (tableRows.length === 0) {
        container.html("<div class='tablenote'>沒有資料</div>");
        return null;
    }

    let columns = buildSeedlingUpdateColumns(csplist);
    let colHeaders = [
        "census",
        "year",
        "month",
        "date",
        "trap",
        "plot",
        "tag",
        "mtag",
        "種類",
        "長度",
        "子葉",
        "真葉",
        "ind",
        "新舊",
        "狀態",
        "萌櫱",
        "X",
        "Y",
        "Note",
        "特殊修改",
        "",
    ];
    let colWidths = [
        60, 55, 50, 115, 45, 45, 100, 100, 120, 50, 50, 50, 45, 45, 45, 90, 40, 40, 180, 220, 45,
    ];
    let hiddenColumns = { columns: [12, 21, 22, 23, 24, 25, 26, 27] };

    if (tableShape === "identity") {
        columns = [
            { data: "trap", type: "numeric", allowInvalid: false },
            { data: "plot", type: "numeric", allowInvalid: false },
            { data: "tag" },
            { data: "mtag" },
            { data: "csp", type: "autocomplete", source: csplist || [], strict: false, visibleRows: 10 },
            { data: "sprout", type: "dropdown", source: ["TRUE", "FALSE"], allowInvalid: false },
            { data: "x", type: "numeric", allowInvalid: false },
            { data: "y", type: "numeric", allowInvalid: false },
            { data: "deletion_note", readOnly: true },
            { data: "source" },
            { data: "work_id" },
            { data: "record_id" },
            { data: "individual_id" },
            { data: "stem_id" },
            { data: "original_tag" },
            { data: "original_mtag" },
        ];
        colHeaders = ["trap", "plot", "tag", "mtag", "種類", "萌櫱", "X", "Y", "備註"];
        colWidths = [55, 55, 120, 120, 140, 100, 50, 50, 180];
        hiddenColumns = { columns: [9, 10, 11, 12, 13, 14, 15] };
    } else if (tableShape === "records") {
        columns = [
            { data: "census", type: "numeric", allowInvalid: false },
            { data: "year", type: "numeric", allowInvalid: false },
            { data: "month", type: "numeric", allowInvalid: false },
            { data: "date", dateFormat: "YYYY-MM-DD", type: "date", allowInvalid: false },
            { data: "tag", readOnly: true },
            { data: "ht", type: "numeric", allowInvalid: false },
            { data: "cotno", type: "numeric", allowInvalid: false },
            { data: "leafno", type: "numeric", allowInvalid: false },
            { data: "recruit" },
            { data: "status", type: "dropdown", source: ["A", "G", "D", "N", ""], allowInvalid: false },
            { data: "note" },
            { data: "deletion_note", readOnly: true },
            { data: "delete_action", renderer: "html", readOnly: true },
            { data: "source" },
            { data: "work_id" },
            { data: "record_id" },
            { data: "individual_id" },
            { data: "stem_id" },
            { data: "original_tag" },
            { data: "original_mtag" },
        ];
        colHeaders = ["census", "year", "month", "date", "tag", "長度", "子葉", "真葉", "新舊", "狀態", "Note", "備註", ""];
        colWidths = [60, 55, 55, 115, 120, 60, 55, 55, 55, 55, 260, 180, 45];
        hiddenColumns = { columns: [13, 14, 15, 16, 17, 18, 19] };
    }

    if (tableShape === "work") {
        tableRows.forEach((row) => {
            row.delete_action = "<button type=\"button\" class=\"seedling-update-row-delete\" data-delete-table=\"work\">X<\/button>";
        });
    } else if (tableShape === "records") {
        tableRows.forEach((row, rowIndex) => {
            row.delete_action = rowIndex === 0
                ? ""
                : "<button type=\"button\" class=\"seedling-update-row-delete\" data-delete-table=\"records\">X<\/button>";
        });
    }

    const visibleHiddenColumns = Array.isArray(hiddenColumns?.columns) ? hiddenColumns.columns : [];
    const tableWidth = 40 + colWidths.reduce((total, width, index) => (visibleHiddenColumns.includes(index) ? total : total + width), 0);
    const rowHeight = 35;
    const fullTableHeight = 44 + (tableRows.length * rowHeight);
    const tableHeight = fitToRows
        ? Math.max(90, fullTableHeight)
        : Math.max(90, Math.min(360, fullTableHeight));
    container.toggleClass("seedling-update-fit-table", fitToRows);
    container.css({
        height: fitToRows ? "auto" : tableHeight + "px",
        minWidth: tableWidth + "px",
        width: tableWidth + "px",
        overflow: "visible",
    });

    const handsontable = createHandsontable(
        container,
        columns,
        tableRows,
        "__seedlingUpdateNoAutoSave",
        seedlingPage.route("updateData"),
        "seedlingUpdate",
        colWidths,
        hiddenColumns,
        colHeaders,
        1,
    );

    const refreshTable = () => {
        if (!handsontable) return;
        if (typeof handsontable.updateSettings === "function") {
            const settings = {
                width: tableWidth,
                minSpareRows: 0,
                renderAllRows: fitToRows,
                viewportRowRenderingOffset: fitToRows ? tableRows.length : undefined,
            };
            if (fitToRows) {
                settings.height = "auto";
            } else {
                settings.height = tableHeight;
            }
            handsontable.updateSettings(settings);
        }
        if (typeof handsontable.refreshDimensions === "function") {
            handsontable.refreshDimensions();
        }
        if (typeof handsontable.render === "function") {
            handsontable.render();
        }
    };

    requestAnimationFrame(() => {
        refreshTable();
        setTimeout(refreshTable, 80);
    });

    return handsontable;
}

function deleteSeedlingUpdateRows(tableType, payload, tag, from) {
    const hasRows = tableType === 'all'
        ? ['workRows', 'identityRows', 'masterRows'].some((key) => Array.isArray(payload?.[key]) && payload[key].length > 0)
        : Array.isArray(payload) && payload.length > 0;

    if (!hasRows) {
        seedlingPage.setNote('.seedlingupdatesavenote', '沒有可刪除資料。', 'error');
        return;
    }

    if (!window.confirm('確定要刪除此筆資料？')) {
        return;
    }

    const requestPayload = {
        tableType,
        tag,
        from,
        user: seedlingPage.context.user,
    };

    if (tableType === 'all') {
        Object.assign(requestPayload, payload);
    } else {
        requestPayload.rows = payload;
    }

    seedlingPage.clearNotes();
    makeAjaxRequest(
        seedlingPage.route('updateDataDelete'),
        requestPayload,
        'POST',
        function (res) {
            seedlingPage.setNote('.seedlingupdatesavenote', res.datasavenote || '已刪除此筆資料', res.datasavenote_type || 'success');
            if (window.Livewire) {
                Livewire.dispatch('seedlingUpdateSaved', {
                    data: { tag: res.tag || tag, from, note: res.datasavenote || "已刪除此筆資料", noteType: res.datasavenote_type || "success" },
                });
            }
        },
        function (err) {
            const detail = err?.response?.datasavenote || err?.error || '刪除失敗';
            seedlingPage.setNote('.seedlingupdatesavenote', detail, 'error');
        },
    );
}


function seedlingUpdateSortValue(value) {
    const numberValue = Number(value);
    return Number.isNaN(numberValue) ? 0 : numberValue;
}

function sortSeedlingUpdateRecords(masterContainer, csplist, sortKey) {
    const masterHot = masterContainer.data("handsontable");
    const rows = masterHot ? masterHot.getSourceData() : [];
    const sortedRows = [...rows].sort((a, b) => {
        if (sortKey === "tag") {
            const tagCompare = `${a.tag ?? ""}`.localeCompare(`${b.tag ?? ""}`, undefined, {
                numeric: true,
                sensitivity: "base",
            });
            if (tagCompare !== 0) return tagCompare;
        }

        const censusCompare = seedlingUpdateSortValue(a.census) - seedlingUpdateSortValue(b.census);
        if (censusCompare !== 0) return censusCompare;

        return `${a.date ?? ""}`.localeCompare(`${b.date ?? ""}`);
    });

    seedlingUpdateTable(masterContainer, sortedRows, csplist, true, "records");
}

function renderSeedlingUpdateTables(tag, workRows, identityRows, masterRows, csplist, from) {
    const workContainer = $("#seedlingUpdateWorkTable");
    const identityContainer = jQuery("#seedlingUpdateIdentityTable");
    const masterContainer = jQuery("#seedlingUpdateMasterTable");

    if (!workContainer.length || !identityContainer.length || !masterContainer.length) {
        return false;
    }

    seedlingUpdateTable(workContainer, workRows, csplist, true);
    seedlingUpdateTable(identityContainer, identityRows, csplist, true, "identity");
    seedlingUpdateTable(masterContainer, masterRows, csplist, true, "records");

    $("button[name=seedlingUpdateSortTag]")
        .off("click.seedlingUpdateSort")
        .on("click.seedlingUpdateSort", function () {
            sortSeedlingUpdateRecords(masterContainer, csplist, "tag");
        });

    $("button[name=seedlingUpdateSortCensus]")
        .off("click.seedlingUpdateSort")
        .on("click.seedlingUpdateSort", function () {
            sortSeedlingUpdateRecords(masterContainer, csplist, "census");
        });

    const saveButton = $("button[name=seedlingUpdateSave]");
    saveButton.off("click.seedlingUpdateSave").on("click.seedlingUpdateSave", function () {
        seedlingPage.clearNotes();
        const workHot = workContainer.data("handsontable");
        const identityHot = identityContainer.data("handsontable");
        const masterHot = masterContainer.data("handsontable");

        makeAjaxRequest(
            seedlingPage.route("updateData"),
            JSON.stringify({
                tag,
                from,
                user: seedlingPage.context.user,
                workRows: workHot ? workHot.getSourceData() : [],
                identityRows: identityHot ? identityHot.getSourceData() : [],
                masterRows: masterHot ? masterHot.getSourceData() : [],
            }),
            "POST",
            function (res) {
                seedlingPage.setNote(".seedlingupdatesavenote", res.datasavenote || "資料已儲存", res.datasavenote_type || "success");
                if (window.Livewire) {
                    Livewire.dispatch("seedlingUpdateSaved", {
                        data: { tag: res.tag || tag, from: res.from || from, note: res.datasavenote || "資料已儲存", noteType: res.datasavenote_type || "success" },
                    });
                }
            },
            function (err) {
                const detail = seedlingAjaxErrorDetail(err, "儲存失敗");
                seedlingPage.setNote(".seedlingupdatesavenote", detail, "error");
                console.error("seedling update save failed", err);
            },
            { contentType: "application/json", processData: false, dataType: "json" },
        );
    });

    $("button[name=seedlingUpdateDeleteAll]")
        .off("click.seedlingUpdateDelete")
        .on("click.seedlingUpdateDelete", function () {
            const workHot = workContainer.data("handsontable");
            const identityHot = identityContainer.data("handsontable");
            const masterHot = masterContainer.data("handsontable");
            deleteSeedlingUpdateRows("all", {
                workRows: workHot ? workHot.getSourceData() : [],
                identityRows: identityHot ? identityHot.getSourceData() : [],
                masterRows: masterHot ? masterHot.getSourceData() : [],
            }, tag, from);
        });


    workContainer.add(masterContainer)
        .off("click.seedlingUpdateRowDelete")
        .on("click.seedlingUpdateRowDelete", ".seedling-update-row-delete", function (event) {
            event.preventDefault();
            const button = $(this);
            const tableType = button.data("delete-table");
            const tableContainer = tableType === "work" ? workContainer : masterContainer;
            const hot = tableContainer.data("handsontable");
            if (!hot) return;

            const selectedCell = hot.getCoords(button.closest("td")[0]);
            const rowIndex = selectedCell ? selectedCell.row : -1;
            if (tableType === "records" && rowIndex === 0) {
                seedlingPage.setNote(".seedlingupdatesavenote", "seedling_records 第一筆資料不可單筆刪除，若需刪除請使用刪除全部資料。", "error");
                return;
            }

            const row = rowIndex >= 0 ? hot.getSourceDataAtRow(rowIndex) : null;
            if (!row) {
                seedlingPage.setNote(".seedlingupdatesavenote", "沒有可刪除資料。", "error");
                return;
            }

            deleteSeedlingUpdateRows(tableType, [row], tag, from);
        });

    return true;
}

function renderSeedlingUpdateTablesFromDom() {
    const payloadElement = document.getElementById("seedlingUpdatePayload");
    if (!payloadElement) {
        return false;
    }

    let payload = null;
    try {
        payload = JSON.parse(payloadElement.textContent || "{}");
    } catch (error) {
        console.error("seedling update payload parse failed", error);
        return false;
    }

    if (!payload || !payload.tag) {
        return false;
    }

    return renderSeedlingUpdateTables(
        payload.tag,
        payload.workRows || [],
        payload.identityRows || [],
        payload.masterRows || [],
        payload.csplist || [],
        payload.from || "",
    );
}

let seedlingUpdateRenderTimers = [];

function scheduleSeedlingUpdateRender(payload = null) {
    seedlingUpdateRenderTimers.forEach((timer) => clearTimeout(timer));
    seedlingUpdateRenderTimers = [];

    const render = () => {
        if (payload) {
            const rendered = renderSeedlingUpdateTables(
                payload.tag,
                payload.workRows || [],
                payload.identityRows || [],
                payload.masterRows || [],
                payload.csplist || [],
                payload.from || "",
            );
            if (rendered) return true;
        }

        return renderSeedlingUpdateTablesFromDom();
    };

    const firstDelay = payload ? 0 : 40;
    seedlingUpdateRenderTimers.push(setTimeout(() => {
        if (render()) return;

        seedlingUpdateRenderTimers.push(setTimeout(render, 80));
    }, firstDelay));
}

window.renderSeedlingUpdateTablesFromDom = renderSeedlingUpdateTablesFromDom;

document.addEventListener("livewire:init", () => {
    if (window.__boundSeedlingUpdateEvents) return;
    window.__boundSeedlingUpdateEvents = true;

    Livewire.on("seedling-update-data", ({ tag, workRows, identityRows, masterRows, csplist, from }) => {
        scheduleSeedlingUpdateRender({ tag, workRows, identityRows, masterRows, csplist, from });
    });

    scheduleSeedlingUpdateRender();
});

document.addEventListener("DOMContentLoaded", () => {
    scheduleSeedlingUpdateRender();
});
