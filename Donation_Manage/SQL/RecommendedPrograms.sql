CREATE DEFINER=`root`@`localhost` PROCEDURE `RecommendedPrograms`(IN user_id_param VARCHAR(10))
BEGIN
    SELECT
        p.program_name,
        p.start_date,
        p.end_date,
        p.company_name,
        SUM(d.money) AS total_donation
    FROM
        `processing_programs_with_company` p
    LEFT JOIN
        `donation` d ON p.program_name = d.program_program_name
    WHERE 
        p.category_id = (SELECT category_id FROM donor WHERE id = user_id_param)
    GROUP BY
        p.program_name
    ORDER BY
        total_donation ASC,
        end_date ASC
    LIMIT 3;
END