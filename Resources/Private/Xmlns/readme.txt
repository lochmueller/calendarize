This files The xsd file is based on EXT:schemaker generation.
Documentation at: https://fluidtypo3.org/documentation/templating-manual/appendix/fluid-autocompletion.html

Execute:
export TYPO3_PATH=/path/to/installation/without/last/slash
rm -rf $TYPO3_PATH/typo3conf/ext/calendarize/Resources/Private/Xmlns/Calendarize.xsd
$TYPO3_PATH/typo3/cli_dispatch.phpsh extbase schema:generate HDNET.Calendarize > $TYPO3_PATH/typo3conf/ext/calendarize/Resources/Private/Xmlns/Calendarize.xsd

Example in the Template:
<div xmlns="http://www.w3.org/1999/xhtml" lang="en"
	 xmlns:f="http://typo3.org/ns/TYPO3/Fluid/ViewHelpers"
	 xmlns:c="http://typo3.org/ns/HDNET/Calendarize/ViewHelpers">

	<f:layout name="XXXX" />

	<f:section name="Main">
	    Your Code here!!!!
	</f:section>
</div>
