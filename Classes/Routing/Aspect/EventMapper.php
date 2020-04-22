<?php
/**
 * EventMapper.
 */

declare(strict_types=1);

namespace HDNET\Calendarize\Routing\Aspect;

use HDNET\Calendarize\Service\Url\Typo3Route;
use TYPO3\CMS\Core\Routing\Aspect\PersistedMappableAspectInterface;
use TYPO3\CMS\Core\Routing\Aspect\StaticMappableAspectInterface;
use TYPO3\CMS\Core\Routing\RouteNotFoundException;
use TYPO3\CMS\Core\Site\SiteLanguageAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * EventMapper.
 */
class EventMapper implements PersistedMappableAspectInterface, StaticMappableAspectInterface
{
    use SiteLanguageAwareTrait;

    /**
     * @param string $value
     *
     * @return string|null
     */
    public function generate(string $value): ?string
    {
        $route = GeneralUtility::makeInstance(Typo3Route::class);
        $speaking = $route->convert(['generate' => $value], null);

        return $speaking;
    }

    /**
     * @param string $value
     *
     * @throws \Exception
     *
     * @return string|null
     */
    public function resolve(string $value): ?string
    {
        $route = GeneralUtility::makeInstance(Typo3Route::class);
        $id = (string)$route->convert(['resolve' => $value], null);

        if ($this->generate($id) !== $value) {
            throw new RouteNotFoundException('Wrong realurl segment', 12378);
        }

        return $id;
    }
}
