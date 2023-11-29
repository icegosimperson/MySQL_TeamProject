<?php
session_start();

$donor_id = $_SESSION['userID'];

$host = "localhost";
$dbUsername = "root";
$dbPassword = "1234";
$dbName = "후원관리시스템";

$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$programName = isset($_GET['program_name']) ? $_GET['program_name'] : '';

if (empty($programName)) {
    echo "프로그램 이름이 없습니다.";
    exit;
}

function generateDonationId($conn) {
    $query = "SELECT MAX(CAST(SUBSTRING(id, 2) AS UNSIGNED)) AS max_id FROM donation";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $maxId = $row['max_id'];
    } else {
        $maxId = 0;
    }

    $nextId = $maxId + 1;
    return 'D' . $nextId;
}

$donationId = generateDonationId($conn);


$query = "SELECT * FROM processing_programs_with_company WHERE program_name = '$programName'";
$result = $conn->query($query);
$goodsList = '';
$goodsId = null;
$goodsCount = null;

$goodsQuery = "SELECT id, name, count FROM goods WHERE program_program_name = '$programName'";
$goodsResult = $conn->query($goodsQuery);

if ($goodsResult && $goodsResult->num_rows > 0) {
    while ($goodsRow = $goodsResult->fetch_assoc()) {
        $goodsName = $goodsRow['name'];
        $goodsCount = $goodsRow['count'];

        $goodsList .= $goodsCount > 0 ? $goodsName : $goodsName . ' (품절)';
        $goodsList .= ', ';

        $goodsId = $goodsRow['id'];
    }
    $goodsList = rtrim($goodsList, ', ');
} else {
    $goodsList = '없음';
}


if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $amount = isset($_POST['amount']) ? intval($_POST['amount']) : 0;
    $address = isset($_POST['address']) ? $_POST['address'] : '';

    // Donation ID 생성하기
    $donationId = generateDonationId($conn);

    // Donation 테이블에 값 삽입
    $donationQuery = "INSERT INTO donation (id, money, date, program_program_name, donor_id) 
    VALUES ('$donationId', '$amount', NOW(), '$programName', '$donor_id')";

    if ($conn->query($donationQuery) === TRUE) {
        if ($goodsId !== null) {
            // 굿즈가 있는 경우에 굿즈 수량 감소,Gift_Log에 값 삽입
            $updateGoodsQuery = "UPDATE goods SET count = '$goodsCount' - 1 WHERE id = '$goodsId'";


            if ($conn->query($updateGoodsQuery) === TRUE) {
                // Gift Log 테이블에 삽입
                $status = '발송 전';
                $giftLogQuery = "INSERT INTO gift_log (goods_id, donation_id, donor_id, address, status) 
                VALUES ('$goodsId', '$donationId', '$donor_id', '$address', '$status')";

               
        if ($conn->query($giftLogQuery) === TRUE) {
        echo "후원이 완료되었습니다! 감사합니다";
        } else {
        echo "Gift Log 정보를 처리하는 중 오류가 발생하였습니다: " . $conn->error;
        }
    } else {
    echo "굿즈 수량을 감소하는 중 오류가 발생하였습니다: " . $conn->error;
    }
    } else {
    
    echo "후원이 완료되었습니다! 감사합니다";
    }
    } else {
    echo "후원 정보를 처리하는 중 오류가 발생하였습니다: " . $conn->error;
    }
    }
?>


<button onclick="goBack()">◄</button><h2>프로그램 상세 정보</h2>

<div class="program-info">
    <?php
    if ($result && $result->num_rows > 0) {
        $programRow = $result->fetch_assoc();
        echo '<table border="1">';
        echo '<tr><th>프로그램 이름</th><td>' . $programRow['program_name'] . '</td></tr>';
        echo '<tr><th>목적</th><td>' . $programRow['purpose'] . '</td></tr>';
        echo '<tr><th>카테고리</th><td>' . $programRow['category_name'] . '</td></tr>';
        echo '<tr><th>기간</th><td>' . $programRow['start_date'] . ' ~ ' . $programRow['end_date'] . '</td></tr>';
        echo '<tr><th>장소</th><td>' . $programRow['place'] . '</td></tr>';
        echo '<tr><th>계좌번호</th><td>' . $programRow['account_number'] . '</td></tr>';
        echo '<tr><th>주최 기관</th><td>' . $programRow['company_name'] . '</td></tr>';

        if ($goodsList !== '없음') {
            echo '<tr><th>굿즈 목록</th><td>' . $goodsList . '</td></tr>';
        }

        echo '</table>';
    } else {
        echo "프로그램 정보가 없습니다.";
    }
    ?>
</div>
<div class="donation-form">
    <h2>후원하기</h2>
    <form method="post" action="">
        <label for="amount">후원금액:</label>
        <input type="number" id="amount" name="amount" required>

        <?php
        if ($goodsList !== '없음') {
            if ($goodsCount > 0) {
                echo '<label for="address">주소: </label>';
                echo '<input type="text" id="address" name="address" required>';
            }
        }
        ?>

        <button type="submit">후원하기</button>
    </form>
</div>

<div class="message">
    <?php
    if (isset($message)) {
        echo $message;
    }
    ?>
</div>

<script>
    function goBack() {
        window.location.href = 'donation.php';
    }
</script>

</body>
</html>