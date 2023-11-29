<?php
session_start(); 



$company_id = $_SESSION['userID'] ?? 'unknown_company_id';
?>
<!DOCTYPE html>
<html>
<head>
    <title>프로그램 회사 페이지</title>
</head>
<body>
    <h1>목록 페이지</h1>
    <p>다음 페이지로 이동하세요:</p>
    <a href="program_insert.php?company_id=<?= urlencode($company_id) ?>">프로그램 신청 페이지</a><br>
    <a href="total.php?company_id=<?= urlencode($company_id) ?>">프로그램 내역 페이지</a><br>
    <a href="update.php?company_id=<?= urlencode($company_id) ?>">회사정보 수정 페이지</a>
</body>
</html>