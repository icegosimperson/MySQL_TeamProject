<?php
session_start();


if (!isset($_SESSION['userID'])) {
    header('Location: http://localhost/login.php');
    exit();
}


if (isset($_GET['user_id'])) {
    $user_id = $_GET['user_id'];
}

$db_host = "localhost";
$db_user = "root";
$db_password = "1234";
$db_name = "후원관리시스템";
$con = mysqli_connect($db_host, $db_user, $db_password, $db_name);
if (mysqli_connect_error()) {
    echo "MySQL 연결 실패: " . mysqli_connect_error();
    exit();
}

$sort = isset($_GET['sort']) ? $_GET['sort'] : 'recent';



$itemsPerPage = 10;
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$offset = ($page - 1) * $itemsPerPage;

// 후원 내역 가져오기
$sql_donations = "SELECT d.*, g.name AS  goods_name,
                (SELECT gl.status FROM `gift_log` gl WHERE g.`id` = gl.`goods_id` LIMIT 1) AS gift_status,
                phc.company_phone_number AS company_phone
                FROM `donation` d
                LEFT JOIN `goods` g ON d.`program_program_name` = g.`program_program_name`
                LEFT JOIN `program` p ON d.`program_program_name` = p.`program_name`
                LEFT JOIN `program_host_company` phc ON p.`program_host_company_id` = phc.`id`
                WHERE d.`donor_id` = '$user_id'";


if ($sort == 'recent') {
    $sql_donations .= " ORDER BY d.date DESC";
} elseif ($sort == 'oldest') {
    $sql_donations .= " ORDER BY d.date ASC";
}

$sql_donations .= " LIMIT $itemsPerPage OFFSET $offset";

$result_donations = mysqli_query($con, $sql_donations);


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($user_id) ?>님의 후원 내역</title>
</head>
<body>
<header>
    <h1><?= htmlspecialchars($user_id) ?>님의 후원 내역</h1>
</header>

<section>

    <form method="GET">
        <select id="sort-order" name="sort" onchange="this.form.submit()">
            <option value="recent" <?php echo ($sort == 'recent') ? 'selected' : ''; ?>>최근 순</option>
            <option value="oldest" <?php echo ($sort == 'oldest') ? 'selected' : ''; ?>>오래된 순</option>
        </select>
    </form>

    <?php
    if ($result_donations) {
        if (mysqli_num_rows($result_donations) > 0) {
            ?>
            <table style : border="1">
                <tr>
                    <th>프로그램 이름</th>
                    <th>후원 날짜</th>
                    <th>금액</th>
                    <th>굿즈</th>
                    <th>주관 단체 연락처</th>
                </tr>
                <?php
                while ($row = mysqli_fetch_assoc($result_donations)) {
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($row['program_program_name']) ?></td>
                        <td><?= htmlspecialchars($row['date']) ?></td>
                        <td><?= htmlspecialchars($row['money']) ?></td>
                        <td>
                            <?php
                            if (!is_null($row['goods_name'])) {
                                echo htmlspecialchars($row['goods_name']);
                                if (!empty($row['gift_status'])) {
                                    if ($row['gift_status'] === '발송 전') {
                                        ?>
                                        <button onclick="showDeliveryInfo('<?= $user_id ?>', '<?= $row['program_program_name'] ?>')">발송 정보</button>

                                        <?php
                                    } elseif ($row['gift_status'] === '발송 완료') {
                                        echo ' (발송 완료)';
                                    }
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($row['company_phone']) ?></td>
                    </tr>
                    <?php
                }
                ?>
            </table>

         
            <?php
            $totalItems = mysqli_num_rows(mysqli_query($con, "SELECT * FROM `donation` WHERE `donor_id` = '$user_id'"));
            $totalPages = ceil($totalItems / $itemsPerPage);
            ?>
            
                <?php
                for ($i = 1; $i <= $totalPages; $i++) {
                    echo '<a href="?sort=' . $sort . '&page=' . $i . '" ' . ($page == $i ? '' : '') . '>' . $i . '</a>';
                }
                ?>
            

            <?php
        } else {
            echo "<p>후원 내역이 없습니다.</p>";
        }
        mysqli_free_result($result_donations);
    } else {
        echo "후원 내역을 불러오는 데 실패했습니다: " . mysqli_error($con);
    }
    ?>

    <br>
        <button onclick="goBack()">뒤로가기</button>
  
</section>

<script>
 function goBack() {
    window.location.href = 'donor.php'; 
}
  // 발송정보 페이지 이동
function showDeliveryInfo(userId, programName) {
    window.location.href = 'http://localhost/delivery_info.php?user_id=' + encodeURIComponent(userId) + '&program_name=' + encodeURIComponent(programName);
}



</script>

</body>
</html>

<?php
mysqli_close($con);
?>