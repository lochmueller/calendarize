<?php

/**
 * Abstract controller.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Information\Typo3Version;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/*
 * AbstractCompatibilityController.
 * @todo smarter integration for TYPO3 v11
 */
if (GeneralUtility::makeInstance(Typo3Version::class)->getMajorVersion() > 10) {
    // TYPO3 v11
    abstract class AbstractCompatibilityController extends AbstractController
    {
        /**
         * Calls the specified action method and passes the arguments.
         *
         * If the action returns a string, it is appended to the content in the
         * response object. If the action doesn't return anything and a valid
         * view exists, the view is rendered automatically.
         *
         * @api
         */
        protected function callActionMethod(RequestInterface $request): ResponseInterface
        {
            $response = parent::callActionMethod($request);
            if (isset($this->feedFormats[$request->getFormat()])) {
                $response = $this->sendHeaderAndFilename($response, $this->feedFormats[$request->getFormat()], $request->getFormat());
                if ($request->hasArgument('hmac')) {
                    $hmac = $request->getArgument('hmac');
                    if ($this->validatePluginHmac($hmac)) {
                        $response = $this->sendHeaderAndFilename($response, $this->feedFormats[$request->getFormat()], $request->getFormat());
                    }

                    return $response;
                }
                $response = $this->sendHeaderAndFilename($response, $this->feedFormats[$request->getFormat()], $request->getFormat());
            }

            return $response;
        }

        /**
         * Send the content type header and the right file extension in front of the content.
         *
         * @param $contentType
         * @param $fileExtension
         */
        protected function sendHeaderAndFilename(ResponseInterface $response, $contentType, $fileExtension): ResponseInterface
        {
            $testMode = (bool)$this->settings['feed']['debugMode'];
            if ($testMode) {
                header('Content-Type: text/plain; charset=utf-8');
            } else {
                header('Content-Type: ' . $contentType . '; charset=utf-8');
                header('Content-Disposition: inline; filename=calendar.' . $fileExtension);
            }
            switch ($this->request->getFormat()) {
                case 'ics':
                    // Use CRLF, see https://tools.ietf.org/html/rfc5545#section-3.1
                    echo str_replace("\n", "\r\n", (string)$response->getBody()->getContents());
                    break;
                default:
                    echo (string)$response->getBody()->getContents();
                    break;
            }
            HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_200);
        }
    }
} else {
    // TYPO3 v10
    abstract class AbstractCompatibilityController extends AbstractController
    {
        /**
         * Calls the specified action method and passes the arguments.
         *
         * If the action returns a string, it is appended to the content in the
         * response object. If the action doesn't return anything and a valid
         * view exists, the view is rendered automatically.
         *
         * @api
         */
        protected function callActionMethod()
        {
            parent::callActionMethod();
            if (isset($this->feedFormats[$this->request->getFormat()])) {
                $this->sendHeaderAndFilename($this->feedFormats[$this->request->getFormat()], $this->request->getFormat());
                if ($this->request->hasArgument('hmac')) {
                    $hmac = $this->request->getArgument('hmac');
                    if ($this->validatePluginHmac($hmac)) {
                        $this->sendHeaderAndFilename($this->feedFormats[$this->request->getFormat()], $this->request->getFormat());
                    }

                    return;
                }
                $this->sendHeaderAndFilename($this->feedFormats[$this->request->getFormat()], $this->request->getFormat());
            }
        }

        /**
         * Send the content type header and the right file extension in front of the content.
         *
         * @param $contentType
         * @param $fileExtension
         */
        protected function sendHeaderAndFilename($contentType, $fileExtension)
        {
            $testMode = (bool)$this->settings['feed']['debugMode'];
            if ($testMode) {
                header('Content-Type: text/plain; charset=utf-8');
            } else {
                header('Content-Type: ' . $contentType . '; charset=utf-8');
                header('Content-Disposition: inline; filename=calendar.' . $fileExtension);
            }
            switch ($this->request->getFormat()) {
                case 'ics':
                    // Use CRLF, see https://tools.ietf.org/html/rfc5545#section-3.1
                    echo str_replace("\n", "\r\n", $this->response->getContent());
                    break;
                default:
                    echo $this->response->getContent();
                    break;
            }
            HttpUtility::setResponseCodeAndExit(HttpUtility::HTTP_STATUS_200);
        }
    }
}
