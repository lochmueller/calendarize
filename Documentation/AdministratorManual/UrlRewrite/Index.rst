..  include:: /Includes.txt

..  _urlrewrite:

URL Rewrite / Speaking URLs
===========================

An introduction and a reference for configuration can be found in the :ref:`official documentation<t3coreapi:routing>`.

Exemplary / Standard configuration
----------------------------------

The extension ships with an exemplary routeEnhancer.
This can be included by adding the following import to the site configuration.

..  code-block:: yaml

    imports:
      - { resource: "EXT:calendarize/Configuration/Yaml/RouteEnhancers.yaml" }

You may want to adapt these configurations to your own needs with e.g. different year ranges.
For this copy the relevant sections to your own configuration file.

..  warning::

    | Keep in mind, that the range of all possible combination is limited 10000 items per route enhancer!
    Due to this the year is limited to a **fixed span**.
    So all links with dates outside this range result in a **Page Not Found** exception.

    Additionally a user can access pages by changing the url, which may lay inside or outside the configured :ts:`dateLimitBrowser*` range.
