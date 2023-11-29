CREATE DEFINER=`root`@`localhost` PROCEDURE `donor_procedure`(
   IN _searchType VARCHAR(10),
   IN _searchValue VARCHAR(255),
   IN _orderBy VARCHAR(15)
)
BEGIN
    DECLARE _whereClause VARCHAR(1000) DEFAULT '';
    DECLARE _orderByClause VARCHAR(1000) DEFAULT '';
    DECLARE _sql_query TEXT;

IF _searchValue != '' THEN
    CASE _searchType
        WHEN 'id' THEN
            SET _whereClause = CONCAT('WHERE d.id LIKE ''%', _searchValue, '%'' ');
        WHEN 'name' THEN
            SET _whereClause = CONCAT('WHERE d.name LIKE ''%', _searchValue, '%'' ');
        WHEN 'phone' THEN
            SET _whereClause = CONCAT('WHERE d.phone LIKE ''%', _searchValue, '%'' ');
        WHEN 'category' THEN
            SET _whereClause = CONCAT('WHERE c.name = ''', _searchValue, ''' ');
        ELSE
            SET _whereClause = CONCAT('WHERE d.id LIKE ''%', _searchValue, '%'' OR d.name LIKE ''%', _searchValue, '%'' OR d.phone LIKE ''%', _searchValue, '%'' OR c.name = ''', _searchValue, ''' ');
    END CASE;
END IF;

CASE _orderBy
    WHEN 'id' THEN
        SET _orderByClause = 'ORDER BY d.id ASC';
    WHEN 'name' THEN
        SET _orderByClause = 'ORDER BY d.name ASC';
    WHEN 'phone' THEN
        SET _orderByClause = 'ORDER BY d.phone ASC';
    WHEN 'category_id' THEN
        SET _orderByClause = 'ORDER BY c.name ASC'; 
    ELSE
        SET _orderByClause = 'ORDER BY d.id ASC';
END CASE;

SET @sql_query = CONCAT('SELECT d.id, d.name, d.phone, c.name AS category_name, 
                                GROUP_CONCAT(DISTINCT dn.money ORDER BY dn.date DESC) AS donations
                         FROM 후원관리시스템.donor d
                         LEFT JOIN 후원관리시스템.donation dn ON d.id = dn.donor_id
                         JOIN 후원관리시스템.category c ON d.category_id = c.id
                         ', _whereClause, ' 
                         GROUP BY d.id, d.name, d.phone, c.name 
                         ', _orderByClause);

PREPARE stmt FROM @sql_query;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


END