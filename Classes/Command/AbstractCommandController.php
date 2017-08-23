<?php
/**
 * Command controller abstraction.
 */
namespace HDNET\Calendarize\Command;

use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\CommandController;

/**
 * Command controller abstraction.
 */
abstract class AbstractCommandController extends CommandController
{
    /**
     * Flash message service.
     *
     * @var \TYPO3\CMS\Core\Messaging\FlashMessageService
     * @inject
     */
    protected $flashMessageService;

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
            if ($title === '') {
                $title = 'Exception: ' . $message->getCode();
            }
            $message = '"' . $message->getMessage() . '"' . LF . 'In ' . $message->getFile() . ' at line ' . $message->getLine() . '!';
            $this->enqueueMessage($message, $title, FlashMessage::ERROR);

            return;
        } elseif (!is_scalar($message)) {
            $message = var_export($message, true);
        }
        if (defined('TYPO3_cliMode') && TYPO3_cliMode) {
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
        $message = GeneralUtility::makeInstance(FlashMessage::class, nl2br($message), $title, $severity);
        $this->flashMessageService->getMessageQueueByIdentifier()
            ->enqueue($message);
    }
}
