CREATE DEFINER=`root`@`localhost` PROCEDURE `search_Company_By_Name`(IN companyName VARCHAR(45))
BEGIN
    IF companyName = '' THEN
        SELECT * FROM program_host_company;
    ELSE
        SELECT * FROM program_host_company WHERE company_name LIKE CONCAT('%', companyName, '%');
    END IF;
END