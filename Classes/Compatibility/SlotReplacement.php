<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Compatibility;

use HDNET\Calendarize\Command\ImportCommandController;
use HDNET\Calendarize\Event\ImportSingleIcalEvent;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\SignalSlot\Dispatcher;

/**
 * @extensionScannerIgnoreFile
 */
class SlotReplacement
{
    /**
     * @var Dispatcher
     */
    protected $signalSlotDispatcher;

    /**
     * Flash message service.
     *
     * @var FlashMessageService
     */
    protected $flashMessageService;

    public function __construct(
        Dispatcher $signalSlotDispatcher,
        FlashMessageService $flashMessageService
    ) {
        $this->signalSlotDispatcher = $signalSlotDispatcher;
        $this->flashMessageService = $flashMessageService;
    }

    public function emitImportCommand(ImportSingleIcalEvent $event)
    {
        $icalEvent = $event->getEvent();
        $this->signalSlotDispatcher->dispatch(
            ImportCommandController::class,
            'importCommand',
            [
                'event' => [
                    'uid' => $icalEvent->getUid(),
                    'start' => $icalEvent->getStartDate()->add(\DateInterval::createFromDateString($icalEvent->getStartTime() . ' seconds')),
                    'end' => ($icalEvent->getEndDate() ?? $icalEvent->getStartDate())->add(\DateInterval::createFromDateString($icalEvent->getEndTime() . ' seconds')),
                    'title' => $icalEvent->getTitle(),
                    'description' => $icalEvent->getDescription(),
                    'location' => $icalEvent->getLocation(),
                ],
                'commandController' => $this, // Implements all public functions
                'pid' => $event->getPid(),
                'handled' => false,
            ]
        );
    }

    //--------------------------------------------------------------------
    // Public functions from AbstractCommandController for compatibility
    //--------------------------------------------------------------------

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
        if (!is_scalar($message)) {
            $message = var_export($message, true);
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
        $message = GeneralUtility::makeInstance(FlashMessage::class, nl2br($message), $title, $severity);
        $this->flashMessageService->getMessageQueueByIdentifier()
            ->enqueue($message);
    }
}
