<?php

$servername = "localhost"; 
$username = "root"; 
$password = "1234";   
$dbname = "후원관리시스템"; 

// 회사 ID 가져오기
if (isset($_GET['id'])) {
    $company_id = $_GET['id'];
} else {
    echo "회사 ID가 누락되었습니다.";
    exit;
}


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("데이터베이스 연결 실패: " . $conn->connect_error);
}

$sql = "SELECT * FROM program_host_company WHERE id = '$company_id'";
$result = $conn->query($sql);
echo "<a href='program_list.php'>이전으로 돌아가기</a>";

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "<h1>회사 정보</h1>";
    echo "<p>회사 이름: {$row['company_name']}</p>";
    echo "<p>주소: {$row['address']}</p>";
    echo "<p>회사 전화번호: {$row['company_phone_number']}</p>";
    echo "<p>사업자 등록번호: {$row['business_number']}</p>";
} else {
    echo "해당 회사 정보가 없습니다.";
}

$conn->close();
?>