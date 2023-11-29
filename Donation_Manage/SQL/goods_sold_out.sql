CREATE 
    ALGORITHM = UNDEFINED 
    DEFINER = `root`@`localhost` 
    SQL SECURITY DEFINER
VIEW `goods_sold_out` AS
    SELECT 
        `goods`.`id` AS `id`,
        `goods`.`name` AS `name`,
        `goods`.`count` AS `count`
    FROM
        `goods`
    WHERE
        (`goods`.`count` = 0)