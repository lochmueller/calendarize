<?php

/**
 * Command controller abstraction.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Command;

use Symfony\Component\Console\Command\Command;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Command controller abstraction.
 */
abstract class AbstractCommandController extends Command
{
    /**
     * Flash message service.
     *
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageService
     */
    protected $flashMessageService;

    /**
     * Inject flash message service.
     *
     * @param \TYPO3\CMS\Core\Messaging\FlashMessageService $flashMessageService
     */
    public function injectFlashMessageService(FlashMessageService $flashMessageService)
    {
        $this->flashMessageService = $flashMessageService;
    }

    /**
     * Adds a message to the FlashMessageQueue or prints it to the CLI.
     *
     * @param mixed  $message
     * @param string $title
     * @param int    $severity
     */
    public function enqueueMessage($message, $title = '', $severity = FlashMessage::INFO)
    {
        if ($message instanceof \Exception) {
            if ('' === $title) {
                $title = 'Exception: ' . $message->getCode();
            }
            $message = '"' . $message->getMessage() . '"' . LF . 'In ' . $message->getFile() . ' at line ' . $message->getLine() . '!';
            $this->enqueueMessage($message, $title, FlashMessage::ERROR);

            return;
        }
        if (!\is_scalar($message)) {
            $message = \var_export($message, true);
        }
        if (\defined('TYPO3_cliMode') && TYPO3_cliMode) {
            echo '==' . $title . ' == ' . LF;
            echo $message . LF;
        } else {
            $this->enqueueMessageGui($message, $title, $severity);
        }
    }

    /**
     * Adds a message to the FlashMessageQueue.
     *
     * @param mixed  $message
     * @param string $title
     * @param int    $severity
     */
    private function enqueueMessageGui($message, $title = '', $severity = FlashMessage::INFO)
    {
        $message = GeneralUtility::makeInstance(FlashMessage::class, \nl2br($message), $title, $severity);
        $this->flashMessageService->getMessageQueueByIdentifier()
            ->enqueue($message);
    }
}
