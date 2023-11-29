CREATE DEFINER=`root`@`localhost` PROCEDURE `CheckUserType`(IN p_userID VARCHAR(20), IN p_userPassword VARCHAR(20), OUT p_userType VARCHAR(20))
BEGIN
    DECLARE company_count INT;
    DECLARE donor_count INT;

    -- 회사 테이블에서 아이디 확인
    SELECT COUNT(*) INTO company_count FROM program_host_company WHERE id = p_userID AND pw = p_userPassword;

    -- 개인 테이블에서 아이디 확인
    SELECT COUNT(*) INTO donor_count FROM donor WHERE id = p_userID AND pw = p_userPassword;

    -- 결과에 따라 사용자 유형 설정
    IF company_count > 0 THEN
        SET p_userType = 'company';
    ELSEIF donor_count > 0 THEN
        SET p_userType = 'donor';
    ELSE
        SET p_userType = 'unknown';
    END IF;
END