..  include:: /Includes.txt

..  _plugin:

Plugin
======


Filter by categories
--------------------

The listed events in a plugin can be filtered by categories.
The categories can be selected in the categories tab of the content element (not in the plugin options) or in the Plugin Configuration.

..  figure:: /Images/User/plugin-categories.png
    :alt: Plugin configuration with categories tab highlighted
    :class: with-shadow

Category conjunction
~~~~~~~~~~~~~~~~~~~~

..  versionadded:: 13

    It is possible to define how multiple categories are handled.
    If the event requires at least one (OR) or all (AND) categories to be displayed.

    ..  figure:: /Images/User/plugin-category-conjunction.png
        :alt: Category conjunction setting with 'all', 'or' and 'and'
        :class: with-shadow


..  confval:: categoryConjunction

    :type: string
    :Default: or
    :Scope: Plugin

    The category conjunction defines if and how the categories are treated.
    If no category is configured, no filter is applied (all events are shown).

    `[all]` (Don't care, show all)
        There is no restriction based on categories, even if categories are defined.
    `[or]` (Show items with selected categories (OR))
        All event records which belong to at least one of the selected categories are shown.
    `[and]` (Show items with selected categories (AND))
        All event records which belong to all selected categories are shown.
