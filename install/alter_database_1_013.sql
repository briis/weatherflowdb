/* Upgrade Database to V1.0-013 */

ALTER TABLE `weatherflowdb`.`forecastDS_daily`
CHANGE COLUMN `description` `description` VARCHAR(100) NULL DEFAULT NULL ;

ALTER TABLE `weatherflowdb`.`forecastDS_hourly`
CHANGE COLUMN `description` `description` VARCHAR(100) NULL DEFAULT NULL ;

ALTER TABLE `weatherflowdb`.`forecastWU_daily`
CHANGE COLUMN `description` `description` VARCHAR(100) NULL DEFAULT NULL ;

ALTER TABLE `weatherflowdb`.`forecastWU_hourly`
CHANGE COLUMN `description` `description` VARCHAR(100) NULL DEFAULT NULL ;

ALTER TABLE `weatherflowdb`.`forecastDS_daily`
ADD COLUMN `updatetime` DATETIME NULL AFTER `copyrighturl`;

ALTER TABLE `weatherflowdb`.`forecastDS_hourly`
ADD COLUMN `updatetime` DATETIME NULL AFTER `copyrighturl`;

ALTER TABLE `weatherflowdb`.`forecastWU_daily`
ADD COLUMN `updatetime` DATETIME NULL AFTER `copyrighturl`;

ALTER TABLE `weatherflowdb`.`forecastWU_hourly`
ADD COLUMN `updatetime` DATETIME NULL AFTER `copyrighturl`;
