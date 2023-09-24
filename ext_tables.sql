CREATE TABLE `tx_calendarize_domain_model_configuration`
(
    `type`               text                          DEFAULT NULL,
    `handling`           text                          DEFAULT NULL,
    `state`              text                          DEFAULT NULL,
    `start_date`         date                          DEFAULT NULL,
    `end_date`           date                          DEFAULT NULL,
    `end_date_dynamic`   text                          DEFAULT NULL,
    `start_time`         int(11)              NOT NULL DEFAULT 0,
    `end_time`           int(11)              NOT NULL DEFAULT 0,
    `all_day`            smallint(5) unsigned NOT NULL DEFAULT 0,
    `open_end_time`      smallint(5) unsigned NOT NULL DEFAULT 0,
    `external_ics_url`   text                          DEFAULT NULL,
    `groups`             varchar(255)         NOT NULL DEFAULT '',
    `frequency`          text                          DEFAULT NULL,
    `till_date`          date                          DEFAULT NULL,
    `till_days`          int(11)                       DEFAULT NULL,
    `till_days_relative` smallint(5) unsigned NOT NULL DEFAULT 0,
    `till_days_past`     int(11)                       DEFAULT NULL,
    `counter_amount`     int(11)              NOT NULL DEFAULT 0,
    `counter_interval`   int(11)              NOT NULL DEFAULT 0,
    `recurrence`         text                          DEFAULT NULL,
    `day`                text                          DEFAULT NULL,
    `flex_form`          text                          DEFAULT NULL,
    `import_id`          varchar(150)                  DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE `tx_calendarize_domain_model_configurationgroup`
(
    `title`          text                          DEFAULT NULL,
    `configurations` tinytext                      DEFAULT NULL,
    `import_id`      varchar(150)                  DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE `tx_calendarize_domain_model_event`
(
    `title`            text                          DEFAULT NULL,
    `slug`             text                          DEFAULT NULL,
    `abstract`         text                          DEFAULT NULL,
    `description`      text                          DEFAULT NULL,
    `location`         text                          DEFAULT NULL,
    `location_link`    text                          DEFAULT NULL,
    `organizer`        text                          DEFAULT NULL,
    `organizer_link`   text                          DEFAULT NULL,
    `images`           int(11)              NOT NULL DEFAULT 0,
    `downloads`        int(11)              NOT NULL DEFAULT 0,
    `import_id`        varchar(150)                  DEFAULT NULL,
    `categories`       int(10) unsigned     NOT NULL DEFAULT 0,
    `calendarize`      tinytext                      DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE `tx_calendarize_domain_model_index`
(
    `unique_register_key` varchar(150)         NOT NULL DEFAULT '',
    `foreign_table`       varchar(150)         NOT NULL DEFAULT '',
    `foreign_uid`         int(11)              NOT NULL DEFAULT 0,
    `start_date`          date                          DEFAULT NULL,
    `end_date`            date                          DEFAULT NULL,
    `start_time`          int(11)              NOT NULL DEFAULT 0,
    `end_time`            int(11)              NOT NULL DEFAULT 0,
    `all_day`             smallint(5) unsigned NOT NULL DEFAULT 0,
    `open_end_time`       smallint(5) unsigned NOT NULL DEFAULT 0,
    `state`               text                          DEFAULT NULL,
    `slug`                text                          DEFAULT NULL
) ENGINE = InnoDB;

CREATE TABLE `tx_calendarize_domain_model_pluginconfiguration`
(
    `title`            text                          DEFAULT NULL,
    `model_name`       text                          DEFAULT NULL,
    `configuration`    text                          DEFAULT NULL,
    `storage_pid`      text                          DEFAULT NULL,
    `recursive`        int(11)              NOT NULL DEFAULT 0,
    `detail_pid`       int(11)              NOT NULL DEFAULT 0,
    `list_pid`         int(11)              NOT NULL DEFAULT 0,
    `year_pid`         int(11)              NOT NULL DEFAULT 0,
    `quarter_pid`      int(11)              NOT NULL DEFAULT 0,
    `month_pid`        int(11)              NOT NULL DEFAULT 0,
    `week_pid`         int(11)              NOT NULL DEFAULT 0,
    `day_pid`          int(11)              NOT NULL DEFAULT 0,
    `booking_pid`      int(11)              NOT NULL DEFAULT 0,
    `categories`       int(10) unsigned     NOT NULL DEFAULT 0
) ENGINE = InnoDB;
