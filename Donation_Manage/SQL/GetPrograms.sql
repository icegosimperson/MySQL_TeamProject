CREATE DEFINER=`root`@`localhost` PROCEDURE `GetPrograms`(
    IN in_selectedCategory VARCHAR(255),
    IN in_searchValue VARCHAR(255),
    IN in_selectedSort VARCHAR(50)
)
BEGIN
    SELECT 
        p.program_name,
        p.start_date,
        p.end_date,
        p.company_name,
        p.category_name
    FROM `processing_programs_with_company` p
    WHERE
        (in_selectedCategory = 'all' OR p.category_name = in_selectedCategory)
        AND (in_searchValue = '' OR p.program_name LIKE CONCAT('%', in_searchValue, '%'))
    ORDER BY 
        CASE WHEN in_selectedSort = 'latest' THEN p.start_date END DESC,
        CASE WHEN in_selectedSort = 'deadline' THEN p.end_date END ASC;
END