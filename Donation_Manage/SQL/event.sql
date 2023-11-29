DELIMITER //
DROP EVENT IF EXISTS UpdateAndCleanPrograms;
CREATE EVENT IF NOT EXISTS UpdateAndCleanPrograms
ON SCHEDULE EVERY 1 MINUTE
DO
BEGIN
    -- '승인 완료' 상태의 프로그램이 시작 날짜에 도달했을 때 '진행 중'으로 상태 변경
    UPDATE `후원관리시스템`.`program`
    SET `status` = '진행 중'
    WHERE `start_date` <= CURDATE() AND `status` = '승인 완료';
    -- 현재 날짜가 종료 날짜를 넘은 프로그램의 상태를 '진행 완료'로 변경
    UPDATE `후원관리시스템`.`program`
    SET `status` = '진행 완료'
    WHERE `end_date` < CURDATE() AND `status` = '진행 중';
    -- '진행 완료' 상태이며 종료 날짜가 3년 이전인 프로그램 삭제
    DELETE FROM `후원관리시스템`.`program`
    WHERE `status` = '진행 완료' AND `end_date` < DATE_SUB(CURDATE(), INTERVAL 3 YEAR);
END;
//
DELIMITER ;