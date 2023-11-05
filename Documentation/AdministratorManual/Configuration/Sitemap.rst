..  include:: /Includes.txt

XML Sitemap for TYPO3
=====================

The sitemap includes links to all indices.

Basic sitemap
-------------

..  code-block:: typoscript

    plugin.tx_seo {
      config {
        xmlSitemap {
          sitemaps {
            ext_calendarize {
              provider = TYPO3\CMS\Seo\XmlSitemap\RecordsXmlSitemapDataProvider
              config {
                table = tx_calendarize_domain_model_index
                pid = <page id(s) containing records>
                url {
                  pageId = {$plugin.tx_calendarize.settings.defaultDetailPid}
                  fieldToParameterMap {
                    uid = tx_calendarize_calendar[index]
                  }
                  additionalGetParameters {
                    tx_calendarize_calendar.controller = Calendar
                    tx_calendarize_calendar.action = detail
                  }
                  useCacheHash = 1
                }
              }
            }
          }
        }
      }
    }
