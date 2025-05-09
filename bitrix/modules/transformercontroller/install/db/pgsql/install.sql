CREATE TABLE IF NOT EXISTS b_transformercontroller_time_statistic (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	COMMAND_NAME varchar(255) NOT NULL,
	PROCESSED_BY varchar(255),
	FILE_SIZE int8,
	DOMAIN varchar(255) NOT NULL,
	LICENSE_KEY varchar(255),
	ERROR int,
	ERROR_INFO text,
	TIME_ADD timestamp,
	TIME_START int,
	TIME_DOWNLOAD int,
	TIME_EXEC int,
	TIME_UPLOAD int,
	TIME_END int,
	TIME_END_ABSOLUTE timestamp,
	QUEUE_ID int,
	GUID varchar(32),
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_transformercontroller_time_statistic_time_add ON b_transformercontroller_time_statistic (time_add);
CREATE INDEX ix_b_transformercontroller_time_statistic_time_end ON b_transformercontroller_time_statistic (time_end);
CREATE INDEX ix_b_transformercontroller_time_statistic_time_end_absolute ON b_transformercontroller_time_statistic (time_end_absolute);
CREATE INDEX ix_b_transformercontroller_time_statistic_command_name ON b_transformercontroller_time_statistic (command_name);
CREATE INDEX ix_b_transformercontroller_time_statistic_queue_id ON b_transformercontroller_time_statistic (queue_id);
CREATE UNIQUE INDEX ux_b_transformercontroller_time_statistic_guid ON b_transformercontroller_time_statistic (guid);

CREATE TABLE IF NOT EXISTS b_transformercontroller_usage_statistic (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	COMMAND_NAME varchar(255) NOT NULL,
	FILE_SIZE int8,
	DOMAIN varchar(255) NOT NULL,
	LICENSE_KEY varchar(255),
	TARIF varchar(255),
	DATE timestamp NOT NULL,
	QUEUE_ID int,
	GUID varchar(32),
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_transformercontroller_usage_statistic_date ON b_transformercontroller_usage_statistic (date);
CREATE INDEX ix_b_transformercontroller_usage_statistic_command_name ON b_transformercontroller_usage_statistic (command_name);
CREATE INDEX ix_b_transformercontroller_usage_statistic_queue_id ON b_transformercontroller_usage_statistic (queue_id);
CREATE INDEX ix_b_transformercontroller_usage_statistic_domain ON b_transformercontroller_usage_statistic (domain);
CREATE INDEX ix_b_transformercontroller_usage_statistic_command_name_queue_i ON b_transformercontroller_usage_statistic (command_name, queue_id, license_key, date);
CREATE INDEX ix_b_transformercontroller_usage_statistic_command_name_queue_1 ON b_transformercontroller_usage_statistic (command_name, queue_id, domain, date);
CREATE UNIQUE INDEX ux_b_transformercontroller_usage_statistic_guid ON b_transformercontroller_usage_statistic (guid);

CREATE TABLE IF NOT EXISTS b_transformercontroller_ban_list (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	DOMAIN varchar(255) NOT NULL,
	LICENSE_KEY varchar(255),
	DATE_ADD timestamp NOT NULL,
	DATE_END timestamp,
	REASON text,
	QUEUE_ID int,
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_transformercontroller_ban_list_domain ON b_transformercontroller_ban_list (domain);
CREATE INDEX ix_b_transformercontroller_ban_list_queue_id ON b_transformercontroller_ban_list (queue_id);

CREATE TABLE IF NOT EXISTS b_transformercontroller_limits (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	TARIF varchar(255),
	TYPE varchar(255),
	COMMAND_NAME varchar(255),
	DOMAIN varchar(255),
	LICENSE_KEY varchar(255),
	COMMANDS_COUNT int,
	FILE_SIZE int8,
	PERIOD int,
	QUEUE_ID int,
	PRIMARY KEY (ID)
);
CREATE INDEX ix_b_transformercontroller_limits_tarif ON b_transformercontroller_limits (tarif);
CREATE INDEX ix_b_transformercontroller_limits_type ON b_transformercontroller_limits (type);
CREATE INDEX ix_b_transformercontroller_limits_queue_id ON b_transformercontroller_limits (queue_id);

CREATE TABLE IF NOT EXISTS b_transformercontroller_queue (
	ID int8 GENERATED BY DEFAULT AS IDENTITY NOT NULL,
	NAME varchar(255) NOT NULL,
	WORKERS int NOT NULL,
	SORT int NOT NULL DEFAULT 500,
	PRIMARY KEY (ID)
);
CREATE UNIQUE INDEX ux_b_transformercontroller_queue_name ON b_transformercontroller_queue (name);
