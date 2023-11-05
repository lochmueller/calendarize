..  include:: /Includes.txt

..  _dataProcessing_languageMenuProcessor:


..  Some of this source code is based on the Typo3 extension news,
..  which can be found under https://github.com/georgringer/news.
..  The project is licensed under GNU General Public License 2.

=========================================
Language menu on calendarize detail pages
=========================================

If a language menu is rendered on a detail page and the languages are configured to use a strict mode, the following snippet helps you to setup a proper menu.
If no translation exists, the property `available` is set to `false` - just as if the current page is not translated.

Usage
=====

..  code-block:: typoscript

    20 = TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor
    20 {
      languages = auto
      as = languageNavigation
    }
    21 = HDNET\Calendarize\DataProcessing\DisableLanguageMenuProcessor
    21 {
      menus = languageNavigation
    }

The property :typoscript:`menus` is a comma-separated list of :php:`MenuProcessor`.

If a language menu is rendered on a detail page and the
languages are configured to use a strict mode this data processor can be used:

If no translation exists, the property `available` in the
:php:`LanguageMenuProcessor` is set to `false` - just as if the current page
is not translated.
