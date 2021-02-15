XML Sitemap for TYPO3
---------------------

.. code-block:: typoscript

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
