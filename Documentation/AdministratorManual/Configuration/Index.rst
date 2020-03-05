Configuration
-------------

The configuration of the calendarize is very straightforward. Please install the extension and include the Static Extension TypoScript to your TypoScript. After that, the extension is working in the frontend.

Via the constant editor, you can adapt template path and feed options. There are also options for date and time formats and limits for browesable views like the month view. Please check the constant editor for all options.

The last step is, place the plugins on your pages. The view is always related to the configured PIDs. That means, if you create a month view and set a detailPid, the template will render detail links to events.
If you leave the detailPid empty and select the monthPid, no events are in the output and the month view is a "small month view". Just try same combinations :)

For a easy editor workflow you can active the time selection wizard by the given Page TS Config.::

           tx_calendarize.timeSelectionWizard {
             1 = 9:00
             2 = 12:00
             3 = 18:00
             4 = 20:15
           }



XML Sitemap for TYPO3 > 9.0
---------------------------

.. code-block::

  plugin.tx_seo {
    config {
      xmlSitemap {
        sitemaps {
          ext_calendarize {
            provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
            config {
              table = tx_calendarize_domain_model_index
              pid = xxxxxx
              url {
                pageId = xxxxxx
                fieldToParameterMap {
                  uid = tx_calendarize_calendar[index]
                }
                useCacheHash = 1
              }
            }
          }
        }
      }
    }
  }

Breadcrumb menu
---------------

To add the calendarize link to the breadcrumb use this user function

  [globalVar = GP:tx_calendarize_calendar|index > 0]
  # [request.getQueryParams()['tx_calendarize_calendar']['index'] > 0] # TYPO3 >= 9
  lib.myBreadcrumbMenu.999 = USER
  lib.myBreadcrumbMenu.999.userFunc = HDNET\Calendarize\Service\BreadcrumbService->generate
  lib.myBreadcrumbMenu.999.doNotLinkIt = 1 # (enable or disable the link => 0)
  lib.myBreadcrumbMenu.999.wrap = <li>|</li>
  [end]
