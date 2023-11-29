-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema 후원관리시스템
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema 후원관리시스템
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `후원관리시스템` DEFAULT CHARACTER SET utf8 ;
USE `후원관리시스템` ;

-- -----------------------------------------------------
-- Table `후원관리시스템`.`category`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`category` (
  `id` VARCHAR(10) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `후원관리시스템`.`donor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`donor` (
  `id` VARCHAR(10) NOT NULL,
  `name` VARCHAR(10) NOT NULL,
  `phone` VARCHAR(15) NOT NULL,
  `pw` VARCHAR(45) NOT NULL,
  `category_id` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_donor_category1_idx` (`category_id` ASC) VISIBLE,
  CONSTRAINT `fk_donor_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `후원관리시스템`.`category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
    CHECK (LENGTH(id) >= 4 AND LENGTH(pw) >= 4) -- ID, PW 4자리 이상 제약
    )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `후원관리시스템`.`program_host_company`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`program_host_company` (
  `id` VARCHAR(10) NOT NULL,
  `company_name` VARCHAR(45) NOT NULL,
  `address` VARCHAR(45) NOT NULL,
  `company_phone_number` VARCHAR(15) NOT NULL,
  `business_number` BIGINT NULL,
  `pw` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`), CHECK (LENGTH(id) >= 4 AND LENGTH(pw) >= 4)
)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `후원관리시스템`.`program`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`program` (
  `program_name` VARCHAR(45) NOT NULL,
  `place` VARCHAR(45) NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `purpose` TEXT NOT NULL,
  `status` VARCHAR(5) NOT NULL,
  `category_id` VARCHAR(10) NOT NULL,
  `program_host_company_id` VARCHAR(10) NOT NULL,
  `account_number` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`program_name`),
  INDEX `fk_program_category1_idx` (`category_id` ASC) VISIBLE,
  INDEX `fk_program_program_host_company1_idx` (`program_host_company_id` ASC) VISIBLE,
  CONSTRAINT `fk_program_category1`
    FOREIGN KEY (`category_id`)
    REFERENCES `후원관리시스템`.`category` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_program_program_host_company1`
    FOREIGN KEY (`program_host_company_id`)
    REFERENCES `후원관리시스템`.`program_host_company` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `후원관리시스템`.`donation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`donation` (
  `id` VARCHAR(10) NOT NULL,
  `money` INT NOT NULL,
  `date` DATETIME NOT NULL,
  `program_program_name` VARCHAR(45) NOT NULL,
  `donor_id` VARCHAR(10) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_donation_program1_idx` (`program_program_name` ASC) VISIBLE,
  INDEX `fk_donation_donor1_idx` (`donor_id` ASC) VISIBLE,
  CONSTRAINT `fk_donation_program1`
    FOREIGN KEY (`program_program_name`)
    REFERENCES `후원관리시스템`.`program` (`program_name`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_donation_donor1`
    FOREIGN KEY (`donor_id`)
    REFERENCES `후원관리시스템`.`donor` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `후원관리시스템`.`goods`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`goods` (
  `id` VARCHAR(10) NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  `count` INT NOT NULL,
  `program_program_name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`, `program_program_name`),
  INDEX `fk_goods_program1_idx` (`program_program_name` ASC) VISIBLE,
  CONSTRAINT `fk_goods_program1`
    FOREIGN KEY (`program_program_name`)
    REFERENCES `후원관리시스템`.`program` (`program_name`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `후원관리시스템`.`gift_log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `후원관리시스템`.`gift_log` (
  `goods_id` VARCHAR(10) NOT NULL,
  `donation_id` VARCHAR(10) NOT NULL,
  `donor_id` VARCHAR(10) NOT NULL,
  `address` VARCHAR(45) NOT NULL,
  `status` VARCHAR(10) NOT NULL,
  INDEX `fk_order_goods1_idx` (`goods_id` ASC) VISIBLE,
  PRIMARY KEY (`goods_id`, `donation_id`),
  INDEX `fk_order_donation1_idx` (`donation_id` ASC) VISIBLE,
  INDEX `fk_order_donor1_idx` (`donor_id` ASC) VISIBLE,
  CONSTRAINT `fk_order_goods1`
    FOREIGN KEY (`goods_id`)
    REFERENCES `후원관리시스템`.`goods` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_order_donation1`
    FOREIGN KEY (`donation_id`)
    REFERENCES `후원관리시스템`.`donation` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_order_donor1`
    FOREIGN KEY (`donor_id`)
    REFERENCES `후원관리시스템`.`donor` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;