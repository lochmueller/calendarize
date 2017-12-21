<?php

/**
 * AbstractBookingRequest.
 */
declare(strict_types=1);

namespace HDNET\Calendarize\Property\TypeConverter;

use HDNET\Calendarize\Domain\Model\Request\DefaultBookingRequest;
use HDNET\Calendarize\Register;
use TYPO3\CMS\Extbase\Property\PropertyMappingConfigurationInterface;
use TYPO3\CMS\Extbase\Property\TypeConverter\AbstractTypeConverter;
use TYPO3\CMS\Extbase\Reflection\ObjectAccess;

/**
 * AbstractBookingRequest.
 */
class AbstractBookingRequest extends AbstractTypeConverter
{
    /**
     * Source types.
     *
     * @var array
     */
    protected $sourceTypes = ['array'];

    /**
     * Target Type.
     *
     * @var string
     */
    protected $targetType = \HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest::class;

    /**
     * Priority.
     *
     * @var int
     */
    protected $priority = 1;

    /**
     * Current configurations.
     *
     * @var array
     */
    protected static $configurations = [];

    /**
     * Set configurations.
     *
     * @param array $configurations
     */
    public static function setConfigurations($configurations)
    {
        self::$configurations = $configurations;
    }

    /**
     * Actually convert from $source to $targetType, taking into account the fully
     * built $convertedChildProperties and $configuration.
     *
     * The return value can be one of three types:
     * - an arbitrary object, or a simple type (which has been created while mapping).
     *   This is the normal case.
     * - NULL, indicating that this object should *not* be mapped (i.e. a "File Upload" Converter could return NULL if no file has been uploaded, and a silent failure should occur.
     * - An instance of \TYPO3\CMS\Extbase\Error\Error -- This will be a user-visible error message later on.
     * Furthermore, it should throw an Exception if an unexpected failure (like a security error) occurred or a configuration issue happened.
     *
     * @param mixed                                 $source
     * @param string                                $targetType
     * @param array                                 $convertedChildProperties
     * @param PropertyMappingConfigurationInterface $configuration
     *
     * @throws \TYPO3\CMS\Extbase\Property\Exception\TypeConverterException thrown in case a developer error occurred
     *
     * @return mixed|\TYPO3\CMS\Extbase\Error\Error the target type, or an error object if a user-error occurred
     *
     * @api
     */
    public function convertFrom(
        $source,
        $targetType,
        array $convertedChildProperties = [],
        PropertyMappingConfigurationInterface $configuration = null
    ) {
        $bookingRequest = $this->getBookingRequestModel();
        foreach ($source as $key => $value) {
            ObjectAccess::setProperty($bookingRequest, $key, $value);
        }

        return $bookingRequest;
    }

    /**
     * Get the right request model.
     *
     * @return \HDNET\Calendarize\Domain\Model\Request\AbstractBookingRequest
     */
    protected function getBookingRequestModel()
    {
        $register = Register::getRegister();
        foreach (self::$configurations as $configurationKey) {
            foreach ($register as $key => $configuration) {
                if ($key === $configurationKey) {
                    if (isset($configuration['overrideBookingRequestModel']) && \class_exists($configuration['overrideBookingRequestModel'])) {
                        $class = $configuration['overrideBookingRequestModel'];

                        return new $class();
                    }
                }
            }
        }

        return new DefaultBookingRequest();
    }
}
