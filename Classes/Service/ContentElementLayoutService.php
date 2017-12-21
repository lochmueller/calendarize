<?php

/**
 * Layout for BE content elements.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Service;

/**
 * Layout for BE content elements.
 */
class ContentElementLayoutService extends AbstractService
{
    /**
     * Title of the element.
     *
     * @var string
     */
    protected $title = '';

    /**
     * Table information.
     *
     * @var array
     */
    protected $table = [];

    /**
     * Set the title.
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
        $this->table = [];
    }

    /**
     * Add one row to the table.
     *
     * @param string $label
     * @param mixed  $value
     */
    public function addRow($label, $value)
    {
        $this->table[] = [
            $label,
            $value,
        ];
    }

    /**
     * Render the settings as table for Web>Page module
     * System settings are displayed in mono font.
     *
     * @return string
     */
    public function render()
    {
        if (!$this->table) {
            return '';
        }
        $content = '<p><strong>' . $this->title . '</strong></p>';
        foreach ($this->table as $line) {
            $content .= '<strong>' . $line[0] . ':</strong>' . ' ' . $line[1] . '<br />';
        }

        return '<pre style="white-space:normal">' . $content . '</pre>';
    }
}
