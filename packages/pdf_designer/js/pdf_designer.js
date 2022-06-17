/**
 * @project:   PDFDesigner (concrete5 add-on)
 * 
 * @author     Fabian Bitter
 * @copyright  (C) 2016 Fabian Bitter (www.bitter.de)
 * @version    1.0.0
 * 
 * @requires: jQuery, jQuery UI, Mustache, gridBuilder, concreteFileSelector
 */

// First, checks if it isn't implemented yet.
if (!String.prototype.format) {
    String.prototype.format = function () {
        var args = arguments;
        return this.replace(/{(\d+)}/g, function (match, number) {
            return typeof args[number] != 'undefined'
                    ? args[number]
                    : match
                    ;
        });
    };
}

var pdfDesigner = {
    actionHandler: "",
    templateId: "",
    canvasEl: null,
    activeBox: false,
    mouseX: 0,
    mouseY: 0,
    mouseHoverElement: false,
    urls: {
        dialogEditBox: ""
    },
    i18n: {
        menuDelete: "Delete",
        menuEditBox: "Edit Content",
        menuClose: "Close",
        menuChangeBoxType: "Box Type",
        menuChangePosition: "Position",
        dialogTitleEditBox: "Edit Content",
        dialogTitleBoxType: "Change Box Type",
        dialogTitlePosition: "Change Position",
        toolbarButton: "Template Settings",
        transmissionErrorMessage: "There was an error while saving.",
        pageTitle: "PDF Designer - Edit Template - {0}",
        chooseFileText: "Choose File",
        confirmQuestion: "Are you sure?",
        dialogTitleImportGoogleFont: "Import Font from Google",
        googleFontInstalled: "The font was successfully installed.",
        toolbarButtonAddBox: "Add Box",
        dialogTitleAddBox: "Add Box"
    },
    document: null,
    boxResize: false,
    boxMove: false,
    isDialogOpen: false,
    isSidebarOpen: false,
    checkAjaxTimer: null,
    tolerancePx: 5,

    /**
     * 
     * @param {Object} settings
     */
    init: function (settings) {
        if (typeof settings.actionHandler !== "undefined") {
            this.actionHandler = settings.actionHandler;
        }

        if (typeof settings.templateId !== "undefined") {
            this.templateId = settings.templateId;
        }

        if (typeof settings.canvasEl !== "undefined") {
            this.canvasEl = $(settings.canvasEl);
        }

        if (typeof settings.urls !== "undefined") {
            this.urls = settings.urls;
        }

        if (typeof settings.i18n !== "undefined") {
            this.i18n = settings.i18n;
        }

        ConcretePanelManager.getPanels()[0].hide();

        this.__loadDocument();
    },

    __removeDialogs: function () {
        $("[data-dialog-form]").remove();
    },

    __getDocumentWidth: function () {
        if (this.document.portraitMode) {
            return this.document.documentWidth;
        } else {
            return this.document.documentHeight;
        }
    },

    __getDocumentHeight: function () {
        if (this.document.portraitMode) {
            return this.document.documentHeight;
        } else {
            return this.document.documentWidth;
        }
    },

    __bindResizeHandler: function () {
        var self = this;

        $(window).bind("resize", function () {
            self.__onResize();
        });
    },

    __getUsableHeight: function () {
        var windowHeight = $(window).height();
        var headerHeight = $(".ccm-dashboard-page-header").position().top + $(".ccm-dashboard-page-header").height() + 64 + 30;
        var marginBottom = 120;

        return windowHeight - headerHeight - marginBottom;
    },

    __getNeededHeight: function () {
        return this.__mmToPx(this.__getDocumentHeight());
    },

    __getRatio: function () {
        return this.__getUsableHeight() / this.__getNeededHeight();
    },

    __onResize: function () {
        if (this.boxResize === false) {
            this.__resizeCanvasAndBoxes();
        }
    },

    __centerCanvas: function () {
        this.canvasEl.css({
            "left": "50%",
            "marginLeft": (this.canvasEl.width() / 2 * -1),
            "top": "auto"
        });

    },

    __initDimensionSwitcher: function () {
        this.__switchDimension(this.document.useMm);

        var self = this;

        $(".recalculate-dimension").each(function () {

            $(this).bind("change", function () {
                if ($(this).parent().hasClass("hidden") === false) {
                    var $dest = $("#" + $(this).data("copyDimension"));

                    if ($(this).data("copyDimensionType") === "inch") {
                        $dest.val(self.__mmToInch($(this).val()));
                    } else {
                        $dest.val(self.__inchToMm($(this).val()));
                    }
                }
            });
        });
    },

    __switchDimension: function (useMm) {
        if (useMm) {
            $(".dimension-mm").removeClass("hidden");
            $(".dimension-inch").addClass("hidden");
        } else {
            $(".dimension-mm").addClass("hidden");
            $(".dimension-inch").removeClass("hidden");
        }
    },

    __inchToMm: function (mm) {
        return (mm / 0.03937007874015748);
    },

    __mmToInch: function (mm) {
        return (mm * 0.03937007874015748);
    },

    __resizeCanvasAndBoxes: function () {
        var r = this.__getRatio();

        this.canvasEl.css({
            "width": this.__mmToPx(this.__getDocumentWidth() * r),
            "height": this.__mmToPx(this.__getDocumentHeight() * r)
        });

        this.__centerCanvas();

        this.canvasEl.find(".pdfDesignerBox").each(function () {
            $(this).css("left", $(this).data("left") * r);
            $(this).css("top", $(this).data("top") * r);
            $(this).css("width", $(this).data("width") * r);
            $(this).css("height", $(this).data("height") * r);
        });

        this.__updatejQueryGrids();
        this.__drawGutter();
    },

    __initFileSelectors: function () {
        var self = this;

        $(".ccm-file-selector").each(function () {
            if ($(this).html().trim() === "") {
                var newUniqueId = parseInt(Date.now());

                $(this).attr("id", "file-" + newUniqueId);

                $(this).concreteFileSelector({
                    'inputName': $(this).data("fileSelector"),
                    'chooseText': self.i18n.chooseFileText,
                    'fID': $(this).data("fileId"),
                    'filters': [
                        {
                            "field": "type",
                            "type": 1
                        }
                    ]
                });
            }
        });

    },

    __reloadDocument: function (completeClb) {
        var self = this;

        this.__callApiGetDocument(function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.document === "undefined") {

                self.__transmissionError();

            } else {
                // @todo: Dokument aktualisieren

                completeClb();
            }
        });
    },

    __injectLeftPanel: function () {
        var self = this;

        var html = Mustache.render($('#leftPanel').html(), this.document);

        $("#ccm-dashboard-page").prepend(html);

        $("#ccm-dashboard-page #ccm-panel-page a").bind("click", function () {
            $("#ccm-dashboard-page #ccm-panel-page a").removeClass("ccm-panel-menu-item-active");

            $(this).addClass("ccm-panel-menu-item-active");

            self.__showPage($(this).data("panelPage"));
        });
        ;
    },

    __injectLeftPanelForAddingBoxes: function () {
        var self = this;

        var html = Mustache.render($('#leftPanelBoxTypes').html(), this.document);

        $("#ccm-dashboard-page").prepend(html);

        $("#ccm-panel-add-block a").bind("click", function () {
            var boxType = $(this).data("boxType");
            
            self.isDialogOpen = true;

            var dialog = $.fn.dialog;

            dialog.open({
                href: self.urls.dialogAddBox + "?templateId=" + self.templateId + "&boxType=" + encodeURI(boxType),
                title: self.i18n.dialogTitleAddBox,
                width: '400',
                height: '500',
                modal: true,
                close: function () {
                    self.isDialogOpen = false;

                    self.__reloadDocument();
                    
                    self.__closeAddingBoxesMenu();

                    self.__removeDialogs();
                }
            });
        
        });
        
    },

    __hidePdfDesigner: function () {
        $("#ccm-dashboard-content").css("display", "none");
    },

    __showPdfDesigner: function () {
        $("#ccm-dashboard-content").css("display", "block");
    },

    __hidePage: function () {
        $(".panel-page").addClass("hidden");
    },

    __closeActiveLaunchPanels: function () {
        $(".pull-right .ccm-launch-panel-active").click();
    },

    __refresh: function () {
        this.__resizeCanvasAndBoxes();
        this.__updateBoxes();
    },

    __loadPages: function () {
        $("#ccm-dashboard-page").prepend(Mustache.render($("#templateSettings").html(), this.document));

        this.__initFileSelectors();
        this.__selectDrodownDefaultValues();
        this.__bindPageActionHandler();
        this.__initDimensionSwitcher();
        this.__loadFonts();
        this.__initJsonEditor();
    },

    __initJsonEditor: function () {
        var myjson = $.parseJSON(this.document.sampleData);
        var opt = {
            change: function (data) {
                $('#sampleData').val(JSON.stringify(data));
            }
        };

        $('#jsonEditor').jsonEditor(myjson, opt);
    },

    __selectDrodownDefaultValues: function () {
        $("#ccm-panel-detail-page select").each(function () {
            var defaultValue = $(this).data("setValue");
            $(this).val(defaultValue);
        });
    },

    /**
     * @param {String} pageName
     * 
     * @returns {Boolean}
     */
    __showPage: function (pageName) {
        this.__hidePage();

        $(".panel-page." + pageName).removeClass("hidden");

        return true;
    },

    __saveSettings: function () {
        var self = this;

        this.__callApiSaveDocument($("#templateSettingsContainer form").serialize(), function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.document === "undefined") {

                self.__transmissionError();

            } else {
                self.__updateDocument(json.response.document);

                self.__closeSideMenu();

                self.__refresh();
            }
        });
    },

    __bindPageActionHandler: function () {
        var self = this;

        $("#ccm-panel-detail-page input").unbind().bind("keypress", function (e) {
            if (e.which == 13) {
                self.__saveSettings();
                return false;
            }
        });


        $("#ccm-panel-detail-form-actions-wrapper .btn-success").unbind().bind("click", function () {
            self.__saveSettings();
        });

        $(".changeDimensionControl").bind("change", function () {
            var useMm = parseInt($(this).val()) === 1;

            self.__switchDimension(useMm);
        });

        $("#addFont").bind("click", function () {
            self.__addFont();
        });

        $("#importGoogleFont").bind("click", function () {
            self.__importGoogleFont();
        });
    },

    __removeFont: function (id) {
        var self = this;

        self.__callApiRemoveFont(id, function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.success === "undefined" ||
                    json.response.success === false) {

                self.__transmissionError();
            } else {
                $("#font-" + id).remove();
            }
        });
    },

    __loadFonts: function () {
        var self = this;

        self.__callApiGetFonts(function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.fonts === "undefined") {

                self.__transmissionError();
            } else {
                var html = Mustache.render($('#fontsTable').html(), json.response);

                $("#fontsTableView").html(html);

                self.__initFileSelectors();

                $(".deleteFont").bind("click", function () {
                    var id = $(this).data("id");

                    if (confirm(self.i18n.confirmQuestion)) {
                        self.__removeFont(id);
                    }
                });
            }
        });
    },

    __installGoogleFont: function (fontName) {
        var self = this;

        this.__callApiImportGoogleFont(fontName, function (json) {

            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.font === "undefined") {

                self.__transmissionError();
            } else {
                $.fn.dialog.closeTop();

                self.isDialogOpen = false;

                var html = Mustache.render($('#fontsTable').html(), {fonts: [json.response.font]});

                $("#fontsTableView").append(html);

                // only unprocessed
                self.__initFileSelectors();

                $("#font-" + json.response.font.id + " .deleteFont").bind("click", function () {
                    var id = $(this).data("id");

                    if (confirm(self.i18n.confirmQuestion)) {
                        self.__removeFont(id);
                    }
                });

                alert(self.i18n.googleFontInstalled);
            }
        });
    },

    __importGoogleFont: function () {

        var self = this;

        self.isDialogOpen = true;

        var dialog = $.fn.dialog;

        dialog.open({
            href: this.urls.dialogImportGoogleFont + "?templateId=" + self.templateId,
            title: this.i18n.dialogTitleImportGoogleFont,
            width: '400',
            height: '500',
            modal: true,
            close: function () {
                self.isDialogOpen = false;

                self.__removeDialogs();
            }
        });
    },

    __addFont: function () {
        var self = this;

        self.__callApiAddFont(function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.font === "undefined") {

                self.__transmissionError();
            } else {
                var html = Mustache.render($('#fontsTable').html(), {fonts: [json.response.font]});

                $("#fontsTableView").append(html);

                // only unprocessed
                self.__initFileSelectors();

                $("#font-" + json.response.font.id + " .deleteFont").bind("click", function () {
                    var id = $(this).data("id");

                    if (confirm(self.i18n.confirmQuestion)) {
                        self.__removeFont(id);
                    }
                });
            }
        });

    },

    __showFirstPage: function () {
        this.__showPage($("#ccm-dashboard-page #ccm-panel-page a").first().data("panelPage"));
    },

    __openSideMenu: function () {
        this.isSidebarOpen = true;

        this.__hidePdfDesigner();
        this.__closeActiveLaunchPanels();

        $("#ccm-panel-page").show("slide", {direction: "left"}, 500);

        this.__loadPages();
        this.__showFirstPage();

        $("#ccm-dashboard-page #ccm-panel-page a").removeClass("ccm-panel-menu-item-active");
        $("#ccm-dashboard-page #ccm-panel-page a").first().addClass("ccm-panel-menu-item-active");
    },

    __closeSideMenu: function () {
        this.isSidebarOpen = false;

        this.__showPdfDesigner();
        this.__hidePage();
        this.__removePages();

        $(".ccm-launch-panel-active").removeClass("ccm-launch-panel-active").addClass("ccm-launch-panel");

        $("#ccm-panel-page").hide("slide", {direction: "left"}, 500);
    },

    __removePages: function () {
        $("#templateSettingsContainer").remove();
    },

    __injectToolbarButton: function () {
        var self = this;

        var $menuButton = $("<li></li>");
        var $menuButtonLink = $("<a></a>");
        var $menuButtonSpan = $("<span></span>");
        var $menuButtonI = $("<i></i>");

        // <i>
        $menuButtonI.addClass("fa");
        $menuButtonI.addClass("fa-cog");
        $menuButtonLink.append($menuButtonI);

        // <span>
        $menuButtonSpan.addClass("ccm-toolbar-accessibility-title");
        $menuButtonSpan.addClass("ccm-toolbar-accessibility-title-settings");
        $menuButtonSpan.html(this.i18n.toolbarButton);

        $menuButtonLink.append($menuButtonSpan);

        // <a>
        $menuButtonLink.addClass("ccm-launch-panel");
        $menuButtonLink.attr("id", "editGeneralSettings");
        $menuButtonLink.attr("title", this.i18n.toolbarButton);
        $menuButtonLink.css("cursor", "pointer");
        $menuButtonLink.bind("click", function () {
            if ($(this).hasClass("ccm-launch-panel")) {

                self.__closeAddingBoxesMenu();
                
                self.__openSideMenu();
                
                $(this).removeClass("ccm-launch-panel");
                $(this).addClass("ccm-launch-panel-active");
            } else {

                self.__closeAddingBoxesMenu();
                
                self.__closeSideMenu();
                
                $(this).removeClass("ccm-launch-panel-active");
                $(this).addClass("ccm-launch-panel");
            }

            return false;
        });

        // <li>
        $menuButton.addClass("pull-left");
        $menuButton.append($menuButtonLink);
        $($menuButton).insertAfter(".ccm-toolbar-account");
    },

    __injectToolbarButtonForAddingBoxes: function () {
        var self = this;

        var $menuButton = $("<li></li>");
        var $menuButtonLink = $("<a></a>");
        var $menuButtonSpan = $("<span></span>");
        var $menuButtonI = $("<i></i>");

        // <i>
        $menuButtonI.addClass("fa");
        $menuButtonI.addClass("fa-plus");
        $menuButtonLink.append($menuButtonI);

        // <span>
        $menuButtonSpan.addClass("ccm-toolbar-accessibility-title");
        $menuButtonSpan.addClass("ccm-toolbar-accessibility-title-add");
        $menuButtonSpan.html(this.i18n.toolbarButtonAddBox);

        $menuButtonLink.append($menuButtonSpan);

        // <a>
        $menuButtonLink.addClass("ccm-launch-panel");
        $menuButtonLink.attr("id", "addBoxesButton");
        $menuButtonLink.attr("title", this.i18n.toolbarButton);
        $menuButtonLink.css("cursor", "pointer");
        $menuButtonLink.bind("click", function () {
            if ($(this).hasClass("ccm-launch-panel")) {

                self.__closeSideMenu();
                
                self.__openAddingBoxesMenu();
                
                $(this).removeClass("ccm-launch-panel");
                $(this).addClass("ccm-launch-panel-active");
            } else {

                self.__closeSideMenu();
                
                self.__closeAddingBoxesMenu();
                
                $(this).removeClass("ccm-launch-panel-active");
                $(this).addClass("ccm-launch-panel");
            }

            return false;
        });

        // <li>
        $menuButton.addClass("pull-left");
        $menuButton.append($menuButtonLink);
        $($menuButton).insertAfter(".ccm-toolbar-account");
    },
    
    __openAddingBoxesMenu: function() {

        $("#ccm-panel-add-block").show("slide", {direction: "left"}, 500);

    },
    
    __closeAddingBoxesMenu: function() {

        $(".ccm-launch-panel-active").removeClass("ccm-launch-panel-active").addClass("ccm-launch-panel");
        
        $("#ccm-panel-add-block").hide("slide", {direction: "left"}, 500);
        
    },

    __updateTitle: function () {
        $(".ccm-dashboard-page-header h1").html(this.i18n.pageTitle.format(this.document.templateTitle));
    },

    __updateDocument: function (document) {
        this.document = document;

        this.__updateTitle();
        this.__updateBoxes();
    },

    __updateBoxes: function () {
        for (var i in this.document.boxes) {
            var box = this.document.boxes[i];

            this.__updateBox(box.boxId, box.xPos, box.yPos, box.width, box.height);
        }
    },

    /**
     * 
     * @param {Integer} boxId
     * @param {Integer} x
     * @param {Integer} y
     * @param {Integer} width
     * @param {Integer} height
     */
    __updateBox: function (boxId, x, y, width, height) {
        var self = this;
        var foundBox = false;
        
        this.canvasEl.find(".pdfDesignerBox").each(function () {
            if (parseInt($(this).data("boxId")) === parseInt(boxId)) {
                $(this).css({
                    left: self.__mmToPx(x) * self.__getRatio(),
                    top: self.__mmToPx(y) * self.__getRatio(),
                    width: self.__mmToPx(width) * self.__getRatio(),
                    height: self.__mmToPx(height) * self.__getRatio()
                });
                
                foundBox = true;
            }
        });
        
        if (!foundBox) {
            this.canvasEl.append("<div class=\"pdfDesignerBox\"></div>");

            var $newBox = this.canvasEl.find(".pdfDesignerBox:last-of-type");

            $newBox.data("boxId", boxId);
            
            $newBox.css({
                left: self.__mmToPx(x) * self.__getRatio(),
                top: self.__mmToPx(y) * self.__getRatio(),
                width: self.__mmToPx(width) * self.__getRatio(),
                height: self.__mmToPx(height) * self.__getRatio()
            });

            $newBox.bind("click", function (e) {
                e.preventDefault();

                self.__showPopupMenu();

                return false;
            });
            
            $newBox.draggable({
                containment: "parent",
                grid: self.__getGridSettignsForjQuery(),
                start: function () {
                    self.__removeContextMenu();

                    self.boxMove = true;
                },
                stop: function () {
                    setTimeout(function () {
                        self.boxMove = false;
                    }, 50);

                    $(this).data({
                        top: $(this).position().top / self.__getRatio(),
                        left: $(this).position().left / self.__getRatio()
                    });

                    self.__callApiMoveBox(
                            $(this).data("boxId"),
                            $(this).position().left / self.__getRatio(),
                            $(this).position().top / self.__getRatio(),
                            function (json) {
                                if (typeof json === "undefined" ||
                                        typeof json.response === "undefined" ||
                                        typeof json.response.success === "undefined" ||
                                        json.response.success === false) {

                                    self.__transmissionError();
                                }
                            }
                    );
                }

            }).resizable({
                containment: "parent",
                grid: self.__getGridSettignsForjQuery(),
                start: function () {
                    self.__removeContextMenu();

                    self.boxResize = true;
                    self.boxMove = true;
                },

                stop: function () {
                    self.boxResize = false;

                    setTimeout(function () {
                        self.boxMove = false;
                    }, 50);

                    $(this).data({
                        width: $(this).width() / self.__getRatio(),
                        height: $(this).height() / self.__getRatio()
                    });

                    self.__callApiResizeBox(
                            $(this).data("boxId"),
                            $(this).width() / self.__getRatio(),
                            $(this).height() / self.__getRatio(),
                            function (json) {
                                if (typeof json === "undefined" ||
                                        typeof json.response === "undefined" ||
                                        typeof json.response.success === "undefined" ||
                                        json.response.success === false) {

                                    self.__transmissionError();
                                }
                            }
                    );
                }
            });

            $newBox.data({
                left: $newBox.position().left / this.__getRatio(),
                top: $newBox.position().top / this.__getRatio(),
                width: $newBox.width() / this.__getRatio(),
                height: $newBox.height() / this.__getRatio()
            });
        }
    },

    __createDocument: function (document) {
        this.document = document;

        this.__createCanvas(this.canvasEl);

        this.__drawGutter();
        this.__centerCanvas();

        this.__bindResizeHandler();
    },

    __drawGutter: function () {

        if (this.document.showGrid) {
            var canvasSize = this.__mmToPx(this.document.gridSize) * this.__getRatio();
            var height = this.canvasEl.height();
            var width = this.canvasEl.width();

            var canvas = $('<canvas/>').attr({width: width, height: height}).appendTo(this.canvasEl);

            var context = canvas.get(0).getContext("2d");

            context.clearRect(0, 0, width, height);

            for (var x = 0; x <= width; x += canvasSize) {
                context.moveTo(x, 0);
                context.lineTo(x, height);
            }

            for (var y = 0; y <= height; y += canvasSize) {
                context.moveTo(0, y);
                context.lineTo(width, y);
            }

            context.strokeStyle = "#fff";
            context.stroke();

            var dataUrl = canvas[0].toDataURL();

            this.canvasEl.find("canvas").remove();

            this.canvasEl.css("backgroundImage", "url(" + dataUrl + ")");
        } else {
            this.canvasEl.css("backgroundImage", "none");
        }
    },

    __handleMouseEvent: function () {
        return this.isSidebarOpen === false && this.isDialogOpen === false;
    },

    __createCanvas: function () {

        var self = this;

        $("body, #ccm-dashboard-content").bind("click", function () {
            if (!self.__handleMouseEvent())
                return;

            self.__removeContextMenu();
        });

        this.canvasEl.
                css({
                    width: this.__mmToPx(this.__getDocumentWidth()) * this.__getRatio(),
                    height: this.__mmToPx(this.__getDocumentHeight()) * this.__getRatio()
                }).
                addClass("pdfDesignerTemplate").
                bind(
                        "click",
                        function () {
                            if (!self.__handleMouseEvent())
                                return;

                            if (self.__checkCollesion() === false) {
                                self.__removeContextMenu();
                            }
                        }
                ).
                bind(
                        "mousedown",
                        function (e) {
                            if (!self.__handleMouseEvent())
                                return;

                            if (e.which === 1) {
                                self.__createBox();
                            }
                        }
                ).
                bind(
                        "mouseup",
                        function (e) {
                            if (!self.__handleMouseEvent())
                                return;

                            if (e.which === 1) {
                                self.__saveBox();
                            }
                        }
                ).
                bind(
                        "mousemove",
                        function (e) {
                            if (!self.__handleMouseEvent())
                                return;

                            self.mouseX = e.pageX;
                            self.mouseY = e.pageY;
                            self.mouseHoverElement = e.target;

                            self.__resizeBox();
                        }
                ).contextmenu(function () {
            return false;
        });

    },

    __reloadDocument: function() {
        var self = this;

        this.__callApiGetDocument(function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.document === "undefined") {

                self.__transmissionError();

            } else {
                self.__updateDocument(json.response.document);
                
                self.__refresh();
            }
        });
    },
    
    __loadDocument: function () {
        var self = this;

        this.__callApiGetDocument(function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.document === "undefined") {

                self.__transmissionError();

            } else {
                self.__createDocument(json.response.document);

                if (typeof json.response.document.boxes !== "undefined") {
                    self.__loadBoxes(json.response.document.boxes);
                }

                self.__injectToolbarButtonForAddingBoxes();
                self.__injectToolbarButton();
                self.__injectLeftPanel();
                self.__injectLeftPanelForAddingBoxes();
            }
        });
    },

    __transmissionError: function () {
        alert(this.i18n.transmissionErrorMessage);
    },

    /**
     * 
     * @param {Object} boxes
     */
    __loadBoxes: function (boxes) {
        for (var i in boxes) {
            var box = boxes[i];

            this.__loadBox(box);
        }
    },

    /**
     * 
     * @param {Object} box
     * 
     */
    __loadBox: function (box) {
        var self = this;

        this.canvasEl.append("<div class=\"pdfDesignerBox\"></div>");

        var $box = this.canvasEl.find(".pdfDesignerBox:last-of-type");

        $box.css({
            position: "absolute",
            left: this.__mmToPx(box.xPos) * this.__getRatio(),
            top: this.__mmToPx(box.yPos) * this.__getRatio(),
            width: this.__mmToPx(box.width) * this.__getRatio(),
            height: this.__mmToPx(box.height) * this.__getRatio()
        });

        $box.data({
            left: this.__mmToPx(box.xPos),
            top: this.__mmToPx(box.yPos),
            width: this.__mmToPx(box.width),
            height: this.__mmToPx(box.height)
        });

        $box.data("boxId", box.boxId);

        $box.bind("click", function (e) {
            e.preventDefault();

            self.__showPopupMenu();

            return false;
        });

        if ($box.hasClass("ui-resizable")) {
            $box.resizable('destroy');
        }

        if ($box.hasClass("ui-draggable")) {
            $box.draggable('destroy');
        }

        $box.draggable({
            containment: "parent",
            grid: self.__getGridSettignsForjQuery(),
            start: function () {
                self.__removeContextMenu();

                self.boxMove = true;
            },
            stop: function () {
                setTimeout(function () {
                    self.boxMove = false;
                }, 50);

                $(this).data({
                    top: $(this).position().top / self.__getRatio(),
                    left: $(this).position().left / self.__getRatio()
                });

                self.__callApiMoveBox(
                        $(this).data("boxId"),
                        $(this).position().left / self.__getRatio(),
                        $(this).position().top / self.__getRatio(),
                        function (json) {
                            if (typeof json === "undefined" ||
                                    typeof json.response === "undefined" ||
                                    typeof json.response.success === "undefined" ||
                                    json.response.success === false) {

                                self.__transmissionError();
                            }
                        }
                );
            }

        }).resizable({
            containment: "parent",
            grid: self.__getGridSettignsForjQuery(),
            start: function () {
                self.__removeContextMenu();

                self.boxResize = true;
                self.boxMove = true;
            },

            stop: function () {
                self.boxResize = false;

                setTimeout(function () {
                    self.boxMove = false;
                }, 50);

                $(this).data({
                    width: $(this).width() / self.__getRatio(),
                    height: $(this).height() / self.__getRatio()
                });

                self.__callApiResizeBox(
                        $(this).data("boxId"),
                        $(this).width() / self.__getRatio(),
                        $(this).height() / self.__getRatio(),
                        function (json) {
                            if (typeof json === "undefined" ||
                                    typeof json.response === "undefined" ||
                                    typeof json.response.success === "undefined" ||
                                    json.response.success === false) {

                                self.__transmissionError();
                            }
                        }
                );
            }
        });
    },

    __removeContextMenu: function () {

        // Remove existing instance
        $("#ccm-popover-menu-container").remove();

        // remove hover style
        this.canvasEl.find(".pdfDesignerBox").removeClass("active");
    },

    /**
     * 
     * @param {Object} menuItems
     */
    __createContextMenu: function (menuItems) {

        var self = this;

        self.__removeContextMenu();

        // Create menu item hover element
        $(self.mouseHoverElement).addClass("active");

        // Create menu markup
        var menuHtml = "";

        menuHtml += "<ul class=\"dropdown-menu\">";

        for (var i in menuItems) {
            var menuItem = menuItems[i];

            if (menuItem.label === "-") {
                menuHtml += "<li class=\"divider\"></li>";
            } else {
                menuHtml += "<li><a href=\"javascript:void(0);\">" + menuItem.label + "</a></li>";
            }
        }

        // Create container markup
        menuHtml += "</ul>";

        var $popoverContainer = $('<div />')
                .attr("id", "ccm-popover-menu-container")
                .addClass("ccm-ui")
                .appendTo('#pdfDesigner');

        $popoverContainer.html('<div class="popover ccm-edit-mode-block-menu bottom"><div class="arrow"></div><div class="popover-inner">' + menuHtml + '</div></div>');

        var $popover = $popoverContainer.find(".popover");

        // Adjust position

        $popover.css({
            display: "block",
            left: "0px !important",
            top: "0px !important"
        });

        $popoverContainer.css({
            position: "absolute",
            top: this.mouseY - this.canvasEl.position().top,
            left: this.mouseX - this.canvasEl.offset().left - ($popover.width() / 2)
        });

        // Add menu item handlers
        var i = 0;

        $popoverContainer.find(".dropdown-menu li").each(function () {
            var menuItem = menuItems[i];

            if ($(this).find("a").length === 1) {
                $(this).find("a").data("boxId", $(self.mouseHoverElement).data("boxId"));

                $(this).find("a").bind("click", function () {
                    var boxId = $(this).data("boxId");

                    self.__removeContextMenu();

                    if (typeof menuItem.handler !== "undefined") {
                        menuItem.handler(boxId);
                    }
                });
            }

            i++;
        });
    },

    __updatejQueryGrids: function () {
        var self = this;

        this.canvasEl.find(".pdfDesignerBox").each(function () {
            $(this).resizable("option", "grid", self.__getGridSettignsForjQuery());
            $(this).draggable("option", "grid", self.__getGridSettignsForjQuery());
        });
    },

    __getGridSettignsForjQuery: function () {
        return false;

        // currently this feature is in development
        // 
//        if (this.document.showGrid) {
//            var canvasSize = this.__mmToPx(this.document.gridSize) * this.__getRatio();
//            
//            return new Array(canvasSize, canvasSize);
//        }
//        return false;
    },

    __showPopupMenu: function () {

        var self = this;

        if (self.boxMove === true) {
            /*
             * This bux was moved, not clicked
             */
            return;
        }

        if (this.activeBox === false) {
            if (this.__checkCollesion() === true) {
                this.__createContextMenu(new Array({
                    label: self.i18n.menuDelete,
                    handler: function (boxId) {
                        self.__removeBox(boxId);
                    }
                }, {
                    label: self.i18n.menuEditBox,
                    handler: function (boxId) {
                        self.__editBoxContent(boxId);
                    }
                }, {
                    label: "-"
                }, {
                    label: self.i18n.menuChangeBoxType,
                    handler: function (boxId) {
                        self.__editBoxType(boxId);
                    }
                }, {
                    label: self.i18n.menuChangePosition,
                    handler: function (boxId) {
                        self.__changePosition(boxId);
                    }
                }, {
                    label: "-"
                }, {
                    label: self.i18n.menuClose
                }));
            }
        }
    },

    /**
     * 
     * @param {Integer} boxId
     */
    __removeBox: function (boxId) {

        this.__callApiRemoveBox(boxId, function (json) {
            if (typeof json === "undefined" ||
                    typeof json.response === "undefined" ||
                    typeof json.response.success === "undefined" ||
                    json.response.success === false) {

                self.__transmissionError();

            } else {
                $(".pdfDesignerBox").each(function () {
                    if ($(this).data("boxId") === boxId) {
                        $(this).remove();
                    }
                });
            }
        });
    },

    /**
     * 
     * @param {Integer} boxId
     */
    __editBoxContent: function (boxId) {

        var self = this;

        self.isDialogOpen = true;

        var dialog = $.fn.dialog;

        dialog.open({
            href: this.urls.dialogEditBox + "?templateId=" + this.templateId + "&boxId=" + boxId,
            title: this.i18n.dialogTitleEditBox,
            width: '400',
            height: '500',
            modal: true,
            close: function () {
                self.isDialogOpen = false;

                self.__removeDialogs();
            }
        });
    },

    /**
     * 
     * @param {Integer} boxId
     */
    __editBoxType: function (boxId) {

        var self = this;

        self.isDialogOpen = true;

        var dialog = $.fn.dialog;

        dialog.open({
            href: this.urls.dialogChangeBoxType + "?templateId=" + this.templateId + "&boxId=" + boxId,
            title: this.i18n.dialogTitleBoxType,
            width: '400',
            height: '500',
            modal: true,
            close: function () {
                self.isDialogOpen = false;

                self.__removeDialogs();
            }
        });
    },

    __waitForAjaxComplete: function (clb) {
        var self = this;

        this.checkAjaxTimer = setInterval(function () {
            if ($.active === 0) {
                clearInterval(self.checkAjaxTimer);

                clb();
            }
        }, 50);
    },

    /**
     * 
     * @param {Integer} boxId
     */
    __changePosition: function (boxId) {

        var self = this;

        self.isDialogOpen = true;

        var dialog = $.fn.dialog;

        dialog.open({
            href: this.urls.dialogChangePosition + "?templateId=" + this.templateId + "&boxId=" + boxId,
            title: this.i18n.dialogTitlePosition,
            width: '400',
            height: '500',
            modal: true,
            close: function () {
                self.isDialogOpen = false;

                self.__removeDialogs();

                self.__waitForAjaxComplete(function () {
                    self.__callApiGetDocument(function (json) {
                        if (typeof json === "undefined" ||
                                typeof json.response === "undefined" ||
                                typeof json.response.document === "undefined") {

                            self.__transmissionError();

                        } else {
                            self.__updateDocument(json.response.document);
                        }
                    });
                });
            }
        });

    },

    __checkCollesion: function () {

        return this.mouseHoverElement !== this.canvasEl.get(0);
    },

    __createBox: function () {

        if (this.activeBox === false) {
            if (this.__checkCollesion() === false) {

                this.canvasEl.append("<div class=\"pdfDesignerBox\"></div>");

                this.activeBox = this.canvasEl.find(".pdfDesignerBox:last-of-type");

                this.activeBox.css({
                    top: this.mouseY - this.canvasEl.offset().top,
                    left: this.mouseX - this.canvasEl.offset().left,
                    width: 0,
                    height: 0
                });

                this.activeBox.data({
                    top: (this.mouseY - this.canvasEl.offset().top) / this.__getRatio(),
                    left: (this.mouseX - this.canvasEl.offset().left) / this.__getRatio()
                });
            }
        }
    },

    __saveBox: function () {

        var self = this;

        if (this.activeBox !== false) {

            if (this.activeBox.width() <= this.tolerancePx || this.activeBox.height() <= this.tolerancePx) {
                this.activeBox.remove();
                this.activeBox = false;
                return;
            }

            if (this.activeBox.hasClass("ui-resizable")) {
                this.activeBox.resizable('destroy');
            }

            if (this.activeBox.hasClass("ui-draggable")) {
                this.activeBox.draggable('destroy');
            }

            this.activeBox.bind("click", function (e) {
                e.preventDefault();

                self.__showPopupMenu();

                return false;
            });

            this.activeBox.draggable({
                containment: "parent",
                grid: self.__getGridSettignsForjQuery(),
                start: function () {
                    self.__removeContextMenu();

                    self.boxMove = true;
                },
                stop: function () {
                    setTimeout(function () {
                        self.boxMove = false;
                    }, 50);

                    $(this).data({
                        top: $(this).position().top / self.__getRatio(),
                        left: $(this).position().left / self.__getRatio()
                    });

                    self.__callApiMoveBox(
                            $(this).data("boxId"),
                            $(this).position().left / self.__getRatio(),
                            $(this).position().top / self.__getRatio(),
                            function (json) {
                                if (typeof json === "undefined" ||
                                        typeof json.response === "undefined" ||
                                        typeof json.response.success === "undefined" ||
                                        json.response.success === false) {

                                    self.__transmissionError();
                                }
                            }
                    );
                }

            }).resizable({
                containment: "parent",
                grid: self.__getGridSettignsForjQuery(),
                start: function () {
                    self.__removeContextMenu();

                    self.boxResize = true;
                    self.boxMove = true;
                },

                stop: function () {
                    self.boxResize = false;

                    setTimeout(function () {
                        self.boxMove = false;
                    }, 50);

                    $(this).data({
                        width: $(this).width() / self.__getRatio(),
                        height: $(this).height() / self.__getRatio()
                    });

                    self.__callApiResizeBox(
                            $(this).data("boxId"),
                            $(this).width() / self.__getRatio(),
                            $(this).height() / self.__getRatio(),
                            function (json) {
                                if (typeof json === "undefined" ||
                                        typeof json.response === "undefined" ||
                                        typeof json.response.success === "undefined" ||
                                        json.response.success === false) {

                                    self.__transmissionError();
                                }
                            }
                    );
                }
            });

            this.activeBox.data({
                left: this.activeBox.position().left / this.__getRatio(),
                top: this.activeBox.position().top / this.__getRatio(),
                width: this.activeBox.width() / this.__getRatio(),
                height: this.activeBox.height() / this.__getRatio()
            });

            this.__callApiAddBox(
                    this.activeBox.position().left / this.__getRatio(),
                    this.activeBox.position().top / this.__getRatio(),
                    this.activeBox.width() / this.__getRatio(),
                    this.activeBox.height() / this.__getRatio(),
                    function (json) {
                        if (typeof json === "undefined" ||
                                typeof json.response === "undefined" ||
                                typeof json.response.boxId === "undefined") {

                            self.__transmissionError();
                        } else {
                            self.activeBox.data("boxId", json.response.boxId);
                            self.activeBox = false;
                        }
                    }
            );

        }
    },

    __resizeBox: function () {

        if (this.activeBox !== false) {
            this.activeBox.css({
                width: this.mouseX - this.activeBox.position().left - this.canvasEl.offset().left,
                height: this.mouseY - this.activeBox.position().top - this.canvasEl.offset().top
            });
        }
    },

    __blockCanvas: function () {
        $("body").LoadingOverlay("show");
    },

    __releaseCanvas: function () {
        $("body").LoadingOverlay("hide");
    },

    /**
     * 
     * @param {String} methodName
     * @param {Object} params
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiMethod: function (methodName, params, clb) {

        var self = this;

        self.__blockCanvas();

        $.ajax({
            dataType: "json",
            method: "post",
            url: this.actionHandler + "/" + methodName + "/" + this.templateId,
            data: params,
            success: function (json) {
                self.__releaseCanvas();
                clb(json);
            },
            error: function () {
                self.__transmissionError();
                self.__releaseCanvas();
            }
        });

        return true;
    },

    /**
     * 
     * @param {Integer} mm
     * 
     * @returns {Integer}
     */
    __mmToPx: function (mm) {

        var div = document.createElement('div');
        div.style.display = 'block';
        div.style.height = '1mm';
        document.body.appendChild(div);
        var convert = div.offsetHeight * mm;
        div.parentNode.removeChild(div);
        return parseInt(convert);
    },

    /**
     * 
     * @param {Integer} px
     * 
     * @returns {Integer}
     */
    __pxToMm: function (px) {

        var div = document.createElement('div');
        div.style.display = 'block';
        div.style.height = '1mm';
        document.body.appendChild(div);
        var convert = px / div.offsetHeight;
        div.parentNode.removeChild(div);
        return parseInt(convert);
    },

    /**
     * 
     * @param {Integer} x
     * @param {Integer} y
     * @param {Integer} width
     * @param {Integer} height
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiAddBox: function (x, y, width, height, clb) {

        return this.__callApiMethod(
                "addBox",
                {
                    x: this.__pxToMm(x),
                    y: this.__pxToMm(y),
                    width: this.__pxToMm(width),
                    height: this.__pxToMm(height)
                },
                clb
                );
    },

    /**
     * 
     * @param {Integer} boxId
     * @param {Integer} x
     * @param {Integer} y
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiMoveBox: function (boxId, x, y, clb) {

        return this.__callApiMethod(
                "moveBox",
                {
                    boxId: boxId,
                    x: this.__pxToMm(x),
                    y: this.__pxToMm(y)
                },
                clb
                );
    },

    /**
     * 
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiGetDocument: function (clb) {

        return this.__callApiMethod(
                "getDocument",
                {},
                clb
                );
    },

    /**
     * 
     * @param {Integer} boxId
     * @param {Integer} width
     * @param {Integer} height
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiResizeBox: function (boxId, width, height, clb) {

        return this.__callApiMethod(
                "resizeBox",
                {
                    boxId: boxId,
                    width: this.__pxToMm(width),
                    height: this.__pxToMm(height)
                },
                clb
                );
    },

    /**
     * 
     * @param {Integer} boxId
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiRemoveBox: function (boxId, clb) {

        return this.__callApiMethod(
                "removeBox",
                {
                    boxId: boxId
                },
                clb
                );
    },

    /**
     * 
     * @param {Object} formData
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiSaveDocument: function (formData, clb) {
        return this.__callApiMethod(
                "saveDocument",
                formData,
                clb
                );
    },

    /**
     * 
     * @param {Integer} fileId
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiAddFont: function (clb) {
        return this.__callApiMethod(
                "addFont",
                {
                },
                clb
                );
    },

    /**
     * 
     * @param {Integer} fileId
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiRemoveFont: function (id, clb) {
        return this.__callApiMethod(
                "removeFont",
                {
                    id: id
                },
                clb
                );
    },

    /**
     * 
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiGetFonts: function (clb) {
        return this.__callApiMethod(
                "getFonts",
                {},
                clb
                );
    },

    /**
     * 
     * @param {Integer} fileId
     * @param {Function} clb
     * 
     * @returns {Boolean}
     */
    __callApiImportGoogleFont: function (fontName, clb) {
        return this.__callApiMethod(
                "importGoogleFont",
                {
                    fontName: fontName
                },
                clb
                );
    }
};