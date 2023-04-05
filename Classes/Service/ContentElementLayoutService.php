<?php

declare(strict_types=1);

namespace HDNET\Calendarize\Service;

/**
 * Layout for BE content elements.
 */
class ContentElementLayoutService extends AbstractService
{
    /**
     * Title of the element.
     */
    protected string $title = '';

    /**
     * Table information.
     */
    protected array $table = [];

    /**
     * Set the title.
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
        $this->table = [];
    }

    /**
     * Add one row to the table.
     */
    public function addRow(string $label, mixed $value): void
    {
        $this->table[] = [
            $label,
            $value,
        ];
    }

    /**
     * Render the settings as table for Web>Page module
     * System settings are displayed in mono font.
     */
    public function render(): string
    {
        if (!$this->table) {
            return '';
        }
        $content = '<p><strong>' . $this->title . '</strong></p>';
        foreach ($this->table as $line) {
            $content .= '<strong>' . $line[0] . ':</strong> ' . $line[1] . '<br />';
        }

        return '<pre style="white-space:normal">' . $content . '</pre>';
    }
}
