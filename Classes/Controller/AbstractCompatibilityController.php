<?php

/**
 * Abstract controller.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Controller;

use JetBrains\PhpStorm\NoReturn;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\RequestInterface;

/**
 * AbstractCompatibilityController.
 */
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
            if ($request->hasArgument('hmac')) {
                $hmac = $request->getArgument('hmac');
                if ($this->validatePluginHmac($hmac)) {
                    $this->sendHeaderAndFilename(
                        $response,
                        $this->feedFormats[$request->getFormat()],
                        $request->getFormat()
                    );
                }

                return $response;
            }
            $this->sendHeaderAndFilename($response, $this->feedFormats[$request->getFormat()], $request->getFormat());
        }

        return $response;
    }

    /**
     * Send the content type header and the right file extension in front of the content.
     */
    #[NoReturn]
    protected function sendHeaderAndFilename(
        ResponseInterface $response,
        string $contentType,
        string $fileExtension
    ): void {
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
                echo str_replace("\n", "\r\n", $response->getBody()->getContents());
                break;

            default:
                echo $response->getBody()->getContents();
                break;
        }

        header('HTTP/1.1 200 OK');
        die();
    }
}
