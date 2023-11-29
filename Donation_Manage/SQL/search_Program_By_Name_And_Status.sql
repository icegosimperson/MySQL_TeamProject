CREATE DEFINER=`root`@`localhost` PROCEDURE `search_Program_By_Name_And_Status`(
    IN programName VARCHAR(50),
    IN programStatus VARCHAR(15),
    IN programCategory VARCHAR(50),
    IN sortOrder VARCHAR(20)
)
BEGIN
    SET @query = CONCAT('SELECT p.*, c.name AS category_name, phc.company_name, phc.id AS company_id, COALESCE(SUM(d.money), 0) AS total_donations FROM program p LEFT JOIN category c ON p.category_id = c.id LEFT JOIN program_host_company phc ON p.program_host_company_id = phc.id LEFT JOIN donation d ON p.program_name = d.program_program_name WHERE 1=1');
    IF programName != '' THEN
        SET @query = CONCAT(@query, ' AND p.program_name LIKE CONCAT(''%', programName, '%'')');
    END IF;
    IF programStatus != '' THEN
        SET @query = CONCAT(@query, ' AND p.status = ''', programStatus, '''');
    END IF;
    IF programCategory != '' THEN
        SET @query = CONCAT(@query, ' AND c.name = ''', programCategory, '''');
    END IF;
    SET @query = CONCAT(@query, ' GROUP BY p.program_name, c.name, phc.company_name, phc.id ORDER BY ');
    CASE sortOrder
    WHEN 'start_date_asc' THEN SET @query = CONCAT(@query, 'p.start_date ASC');
    WHEN 'start_date_desc' THEN SET @query = CONCAT(@query, 'p.start_date DESC');
    WHEN 'end_date_asc' THEN SET @query = CONCAT(@query, 'p.end_date ASC');
    WHEN 'end_date_desc' THEN SET @query = CONCAT(@query, 'p.end_date DESC');
    WHEN 'total_donations_asc' THEN SET @query = CONCAT(@query, 'total_donations ASC');
    WHEN 'total_donations_desc' THEN SET @query = CONCAT(@query, 'total_donations DESC');
    ELSE SET @query = CONCAT(@query, 'p.end_date ASC'); -- 기본값
END CASE;
    PREPARE stmt FROM @query;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;
END