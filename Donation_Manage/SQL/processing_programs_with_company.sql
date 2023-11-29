CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `processing_programs_with_company` AS
    SELECT 
        `p`.`program_name` AS `program_name`,
        `c`.`name` AS `category_name`,
        `p`.`purpose` AS `purpose`,
        `p`.`category_id` AS `category_id`,
        `p`.`start_date` AS `start_date`,
        `p`.`end_date` AS `end_date`,
        IFNULL(`p`.`place`, '-') AS `place`,
        `p`.`account_number` AS `account_number`,
        `phc`.`company_name` AS `company_name`,
        `phc`.`company_phone_number` AS `company_phone_number`
    FROM
        ((`program` `p`
        JOIN `category` `c` ON ((`p`.`category_id` = `c`.`id`)))
        JOIN `program_host_company` `phc` ON ((`p`.`program_host_company_id` = `phc`.`id`)))
    WHERE
        (`p`.`status` = '진행 중')