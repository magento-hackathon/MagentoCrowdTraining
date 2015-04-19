app.directive('mtTutorialLinks', function ($compile, $http, $anchorScroll, $location) {
    var Link = function ($scope, element, attr, controller) {
        $scope.initConfig = JSON.parse($(element).attr('mt-tutorial-links'));
        $scope.element = element;

        $scope.pageRoute = $scope.initConfig.pageRoute;
        $scope.pageTutorials = $scope.initConfig.pageTutorials;
        $scope.otherTutorials = $scope.initConfig.otherTutorials;
        $scope.mode = 'list';
        $scope.autoEditTutorial = $scope.initConfig.autoEditTutorial;

        $scope.tutCategories = $scope.initConfig.tutCategories;

        $scope.activeTutorial = {};
        $scope.activePage = {};
        $scope.activeStep = {};

        $scope.newPagePrototype = {
            id: null,
            tutorial_id: null,
            use_expose: true,
            url_key: $scope.pageRoute,
            steps: []
        };

        $scope.newStepPrototype = {
            id: 1,
            step_title: null,
            step_content: null,
            target_element_id: '#page:main-container',
            tip_location: 'bottom',
            pre_step_callback: null,
            post_step_callback: null
        };


        $scope.selectableElements = 'a, div, input, button, select';


        $scope.startTutorial = function (tutorialId) {
            $('#tutorials-step-container').scope().startTutorial(tutorialId);

        };

        $scope.setMode = function (newMode, tut_id) {
            $scope.mode = newMode;
            $('#editNotification').remove();
            switch (newMode) {
                case "new":
                    var newPage = angular.copy($scope.newPagePrototype);
                    $scope.activeTutorial = {
                        id: 'new',
                        label: 'New Tutorial',
                        category: 'Dashboard',
                        use_expose: true,
                        pages: [
                            newPage
                        ]
                    };
                    $scope.setActivePage(newPage);
                    $scope.mode = 'edit';
                    break;

                case "list":
                    $scope.stopEditSession();
                    $('.tut-editor-active-element').removeClass('tut-editor-active-element').unbind('click.tutorialEvents');
                    break;

                case "edit":
                    console.log($scope);
                    $scope.activeTutorial = $scope.pageTutorials[tut_id];
                    $scope.startEditSession(tut_id);
                    break;
            }
            if ($scope.mode == 'edit') {
                var notifcationElement = $('<div id="editNotification" class="tut-editor-edit-notification has-tip" data-tooltip title="You are currently editing tutorial: ' + $scope.activeTutorial.label + '." onclick="jQuery(\'.off-canvas-wrap\').foundation(\'offcanvas\', \'show\', \'move-left\')"><i class="fi-pencil"></i></div>');
                $('body').append(notifcationElement);

            }
            $(document).foundation();

        };

        $scope.addStep = function () {
            var newStep = angular.copy($scope.newStepPrototype);
            $scope.activePage.steps.push(newStep);
        };

        $scope.addPage = function () {
            var newPage = angular.copy($scope.newPagePrototype);
            $scope.activeTutorial.pages.push(newPage);
        };

        $scope.startElementSelector = function (step, event) {
            try {
                event.preventDefault();
                event.stopPropagation();
                step = $scope.activePage.steps[step];

                $($scope.selectableElements).addClass('tut-selectable');
                $($scope.selectableElements).on('click.tutorialEvents', function (event) {
                    event.preventDefault();
                    event.stopPropagation();
                    $('.off-canvas-wrap').foundation('offcanvas', 'show', 'move-left');
                    $('.tut-selectable').removeClass('tut-selectable').unbind('click.tutorialEvents');
                    step.target_element_id = $(event.target).getPath();
                    $scope.setActiveStep(step);
                    $scope.$apply();
                });

                $('.off-canvas-wrap').foundation('offcanvas', 'hide', 'move-left');

            } catch (e) {
                console.log(e);
            }
        };

        $scope.setActiveStep = function (step) {
            $scope.activeStep = step;
            console.log(step);
            $('.tut-editor-active-element').removeClass('tut-editor-active-element');
            var targetElement = $(step.target_element_id);
            if (targetElement) {
                targetElement.addClass('tut-editor-active-element');
            }
        };

        $scope.setActivePage = function (page) {
            console.log(page);
            $scope.activePage = page;
            $(document).foundation();
        };

        $scope.startEditSession = function (tut_id) {
            console.log('Starting Edit Session ');
            $.get('<?= Mage::helper("adminhtml")->getUrl("tutorials/adminhtml_post/startEditing"); ?>?isAjax=1', {
                tutorial_id: tut_id
            });
            console.log('START EDIT SESSION END');
        };

        $scope.stopEditSession = function () {
            console.log('Stopping Edit Session ');
            $.get('<?= Mage::helper("adminhtml")->getUrl("tutorials/adminhtml_post/stopEditing"); ?>?isAjax=1', {
                isAjax: 1
            });
        };

        $scope.getPageAccordionClass = function (page) {
            if (page.url_key == $scope.pageRoute) {
                return 'active';
            }
            return null;
        };

        $scope.gotoPage = function (page) {
            console.log(page);
            console.log(page.full_url_key + '/' + page.params);
            window.location = page.full_url_key + page.params;
        };
        if ($scope.autoEditTutorial) {
            console.log('AUTO EDIT!!! - ' + $scope.autoEditTutorial);
            $('.off-canvas-wrap').foundation('offcanvas', 'show', 'move-left')
            $scope.setMode('edit', $scope.autoEditTutorial);
        }

        console.log('Tutorial Links Link Function');
        console.log($scope);

    };

    var Controller = function ($scope) {

    };

    return {
        scope: true,
        link: Link,
        controller: Controller
    };
});

app.directive('mtTutorialSteps', function ($compile, $http, $anchorScroll, $location) {
    var Link = function ($scope, element, attr, controller) {
        $scope.initConfig = JSON.parse($(element).attr('mt-tutorial-steps'));
        $scope.element = element;

        $scope.tutorials = $scope.initConfig.tutorials;
        $scope.pageRoute = $scope.initConfig.pageRoute;
        $scope.autoStartTutorial = $scope.initConfig.autoStartTutorial;
        $scope.currentPageIndex = $scope.initConfig.currentPageIndex;

        $scope.currentTutorial = null;
        $scope.currentTutorialPage = null;

        $scope.setCurrentTutorial = function (tutorialId) {
            $scope.currentTutorial = $scope.tutorials[tutorialId];

            if (!$scope.currentPageIndex) {
                $scope.currentPageIndex = 0;
                for (var pIndex in $scope.currentTutorial.pages) {
                    var page = $scope.currentTutorial.pages[pIndex];
                    if (page.url_key == $scope.pageRoute) {
                        $scope.currentPageIndex = pIndex;
                    }
                }
            }
            $scope.currentPageIndex = parseInt($scope.currentPageIndex);
            $scope.currentTutorialPage = $scope.currentTutorial.pages[$scope.currentPageIndex];
        };

        $scope.getTargetElementId = function (step) {
            console.log('looking for ' + step.target_element_id);
            var targetElement = $(step.target_element_id);
            var targetElementId = null;
            if (targetElement.length > 0) {
                console.log('FOUND ELEMENT');
                console.log(targetElement);
                if (targetElementId = targetElement.attr('id')) {
                    console.log('Element has ID!' + targetElement.attr('id'));
                } else {
                    console.log('Element does not have an id, making one up!');
                    targetElementId = "tutorial_" + $scope.currentTutorialPage.tutorial_id + "_page_" + $scope.currentTutorialPage.id + "_step_" + step.id;
                    targetElement.attr('id', targetElementId);
                    targetElement.addClass('tmpId');
                }
                console.log(targetElementId);
            } else {
                console.log('Unable to find step ' + step.target_element_id);
            }
            return targetElementId;
        };

        $scope.startTutorial = function (tutorialId) {
            $scope.setCurrentTutorial(tutorialId);

            var useExpose = false;
            if ($scope.currentTutorial.use_expose == '1') {
                useExpose = true;
            }


            $('.off-canvas-wrap').foundation('offcanvas', 'hide', 'move-left');
            $.get('<?= Mage::helper("adminhtml")->getUrl("tutorials/adminhtml_post/startTutorial"); ?>?isAjax=1',
                {
                    tutorial_id: tutorialId
                }
            ).success(function (response) {
                    window.setTimeout(function () {
                        $(document).foundation();
                        $(document).foundation('joyride', 'start', {

                            pre_step_callback: function (stepIndex, stepElement) {
                                $scope.runPreStepCallback(stepIndex, stepElement);
                            },
                            post_step_callback: function (stepIndex, stepElement) {
                                $scope.runPostStepCallback(stepIndex, stepElement);
                            },
                            pre_ride_callback: function (stepIndex, stepElement) {
                                $scope.runPrePageCallback();
                            },
                            post_ride_callback: function (stepIndex, stepElement) {
                                $scope.runPostPageCallback();
                            },

                            expose: true,
                            modal: true,
                            expose_add_class: "tut-current-element",
                            template : { // HTML segments for tip layout
                                link: '<a href="#close" class="joyride-close-tip" onclick="jQuery(\'#tutorials-step-container\').scope().stopTutorial()">&times;</a>',
                                timer       : '<div class="joyride-timer-indicator-wrap"><span class="joyride-timer-indicator"></span></div>',
                                tip         : '<div class="joyride-tip-guide"><span class="joyride-nub"></span></div>',
                                wrapper     : '<div class="joyride-content-wrapper"></div>',
                                button      : '<a href="#" class="small button joyride-next-tip"></a>',
                                prev_button : '<a href="#" class="small button joyride-prev-tip"></a>',
                                modal       : '<div class="joyride-modal-bg"></div>',
                                expose      : '<div class="joyride-expose-wrapper"></div>',
                                expose_cover: '<div class="joyride-expose-cover"></div>'
                            }
                        });
                        $(window).trigger('resize.fndtn.joyride');
                    }, 1000)
                });
        };


        $scope.runPreStepCallback = function (stepIndex, stepElement) {
            console.log('Pre Step Callback - ' + stepIndex);
            console.log($scope.currentTutorialPage.steps[stepIndex]);
            var step = $scope.currentTutorialPage.steps[stepIndex];
            if (step && step.pre_step_callback) {
                eval(step.pre_step_callback);
            }
        };

        $scope.runPostStepCallback = function (stepIndex, stepElement) {
            console.log('Post Step Callback');
            console.log($scope.currentTutorial);
            var step = $scope.currentTutorialPage.steps[stepIndex];
            if (step && step.post_step_callback) {
                eval(step.post_step_callback);
            }
        };

        $scope.runPrePageCallback = function () {
            console.log('Pre Page Callback');
            if($scope.currentTutorialPage.pre_page_callback){
                eval($scope.currentTutorialPage.pre_page_callback);
            }
        };

        $scope.runPostPageCallback = function () {
            $(".tmpId").removeClass("tmpId").attr('id', null);
            console.log('Post Page Callback');
            if($scope.currentTutorialPage.post_page_callback){
                eval($scope.currentTutorialPage.post_page_callback);
            }

            // CHECK IF THERE IS ANOTHER PAGE TO MOVE TO
            var nextIndex = $scope.currentPageIndex + 1;
            var nextPage = $scope.currentTutorial.pages[nextIndex];
            if (nextPage) {
                window.location = nextPage.full_url_key.replace(/\/+$/, '') + "/autoStartTutorial/" + $scope.currentTutorial.id + "/tutorialPageIndex/" + nextIndex + '/' + nextPage.params;
            } else {
                $scope.stopTutorial();
            }
        };


        $scope.stopTutorial = function () {
            $.get('<?= Mage::helper("adminhtml")->getUrl("tutorials/adminhtml_post/stopTutorial"); ?>?isAjax=1', {});
        };

        console.log('Tutorial Steps Link Function');
        console.log($scope);

        if ($scope.autoStartTutorial) {
            $scope.startTutorial($scope.autoStartTutorial);
        }
    };

    var Controller = function ($scope) {
    };

    return {
        scope: true,
        link: Link,
        controller: Controller
    };
});

$(document).ready(function () {
    $('li.has-submenu > a:first-of-type').on('click', function (event) {
        $(event.target).siblings('.right-submenu').toggleClass('move-left');
    });
    $(document).foundation();
});

jQuery.fn.extend({
    getPath: function () {
        if (jQuery(this).attr('id')) {
            return "#" + jQuery(this).attr('id');
        }
        var path, node = this;
        while (node.length) {
            var realNode = node[0], name = realNode.localName;
            if (!name) break;
            name = name.toLowerCase();

            var parent = node.parent();

            var sameTagSiblings = parent.children(name);
            if (sameTagSiblings.length > 1) {
                allSiblings = parent.children();
                var index = allSiblings.index(realNode) + 1;
//                    if (index > 1) {
                name += ':nth-child(' + index + ')';
//                    }
            }

            path = name + (path ? '>' + path : '');
            node = parent;
        }

        return path;
    }
});