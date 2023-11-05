..  include:: /Includes.txt

Breadcrumb menu
---------------

To add the calendarize link to the breadcrumb use this user function

..  code-block:: typoscript

    [traverse(request?.getQueryParams(), 'tx_calendarize_calendar/index') > 0]
    lib.myBreadcrumbMenu.999 = USER
    lib.myBreadcrumbMenu.999.userFunc = HDNET\Calendarize\Service\BreadcrumbService->generate
    lib.myBreadcrumbMenu.999.doNotLinkIt = 1
    lib.myBreadcrumbMenu.999.wrap = <li>|</li>
    [end]
