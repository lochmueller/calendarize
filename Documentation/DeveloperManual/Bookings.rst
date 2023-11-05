..  include:: /Includes.txt

Bookings
========

The extension only provides interfaces for the booking system and does **not process or store** the submitted data.
This is up to custom extensions.


Needs to be implemented
-----------------------

*   :ref:`PSR-14 Event listener <t3coreapi:EventDispatcherImplementation>` for :php:`GenericActionAssignmentEvent` (`className=BookingController`)

    *   Contains the :php:`BookingRequest` with the submitted data

*   Processing the booking

    *    Database
    *    Email notification
    *    Configuration
    *    ...

*   For *custom data fields*

    *   Custom BookingRequest (like :php:`DefaultBookingRequest`)
    *   Custom HTML template (`Templates/Booking/Booking.html`)

*   For *custom events*

    *   Add the feature :php:`BookingInterface` to your event
