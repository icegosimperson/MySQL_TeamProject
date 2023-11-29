CREATE DEFINER=`root`@`localhost` PROCEDURE `GenerateNewID`(IN tableName VARCHAR(64), IN prefix VARCHAR(10), OUT newID VARCHAR(20))
BEGIN
    DECLARE maxID INT;
    DECLARE newIDStr VARCHAR(20);

    SET @maxIDQuery = CONCAT('SELECT MAX(CAST(SUBSTRING(id, ', LENGTH(prefix) + 1, ') AS UNSIGNED)) AS max_id FROM ', tableName);
    PREPARE stmt FROM @maxIDQuery;
    EXECUTE stmt;
    DEALLOCATE PREPARE stmt;

    IF maxID IS NULL THEN
        SET maxID = 0;
    END IF;

    SET newIDStr = CONCAT(prefix, (maxID + 1));
    SET newID = newIDStr;
END