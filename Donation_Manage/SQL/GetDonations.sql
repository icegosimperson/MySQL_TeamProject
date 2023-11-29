CREATE DEFINER=`root`@`localhost` PROCEDURE `GetDonations`(IN prog_name VARCHAR(255), IN donor_search VARCHAR(255), IN sort_order VARCHAR(20))
BEGIN
    SET @query = CONCAT('SELECT d.*, donor.name AS donor_name FROM donation d LEFT JOIN donor ON d.donor_id = donor.id WHERE d.program_program_name = "', prog_name, '"');
    IF donor_search IS NOT NULL THEN
        SET @query = CONCAT(@query, ' AND donor.name LIKE "%', donor_search, '%"');
    END IF;
    CASE sort_order
        WHEN 'date_asc' THEN SET @query = CONCAT(@query, ' ORDER BY d.date ASC');
        WHEN 'date_desc' THEN SET @query = CONCAT(@query, 'ORDER BY d.date DESC');
        WHEN 'amount_desc' THEN SET @query = CONCAT(@query, ' ORDER BY d.money DESC');
        WHEN 'amount_asc' THEN SET @query = CONCAT(@query, ' ORDER BY d.money ASC');
       -- ELSE SET @query = CONCAT(@query, ' ORDER BY d.date DESC');
    END CASE;
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END