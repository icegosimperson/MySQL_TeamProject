<?php
session_start();
$host = "localhost"; 
$dbUsername = "root";
$dbPassword = "1234"; 
$dbName = "후원관리시스템"; 


$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_GET['user_id']) && isset($_GET['program_name'])) {
    $donorId = $_GET['user_id'];
    $programName = $_GET['program_name'];
} 
 



if (empty($programName)) {
    echo "프로그램 이름이 없습니다.";
    exit;
}


if (isset($_GET['user_id'])) {
    $donorId = $_GET['user_id'];
}


// Donation ID 
$donationIdQuery = "SELECT id FROM donation WHERE program_program_name = '$programName' AND donor_id = '$donorId'";
$donationIdResult = $conn->query($donationIdQuery);

if ($donationIdResult && $donationIdResult->num_rows > 0) {
    $donationIdRow = $donationIdResult->fetch_assoc();
    $donationId = $donationIdRow['id'];

    // Gift Log 가져오기
    $giftLogQuery = "SELECT goods.name AS goods_name, gift_log.status, gift_log.address
                    FROM gift_log
                    JOIN goods ON gift_log.goods_id = goods.id
                    WHERE gift_log.donation_id = '$donationId'";
    $giftLogResult = $conn->query($giftLogQuery);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>배송정보 확인</title>
    
</head>
<body>
    <button onclick="goBack()">◄</button><h2>배송정보 확인</h2>
    <table id="deliveryTable" style: border="1">
        <thead>
            <tr>
                <th>굿즈 이름</th>
                <th>배송현황</th>
                <th>주소</th>
            </tr>
        </thead>
        <tbody id="tableBody">
            <?php
            while ($row = $giftLogResult->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . $row['goods_name'] . '</td>';
                echo '<td>' . $row['status'] . '</td>';
                echo '<td>' . $row['address'] . '</td>';
                echo '</tr>';
            }
            ?>
        </tbody>
    </table>
    <script>
        function goBack() {
            window.history.back();
        }
    </script>
</body>
</html>

<?php
} else {
    echo "Donation ID를 찾을 수 없습니다.";
}

$conn->close();
?>