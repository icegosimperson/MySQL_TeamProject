<?php
$con = mysqli_connect("localhost", "root", "1234", "후원관리시스템") or die("MySQL 접속 실패");

$program_name = isset($_GET['program_name']) ? $_GET['program_name'] : '';

$query_program = "SELECT * FROM program WHERE program_name = '$program_name'";
$result_program = mysqli_query($con, $query_program);

if (!$result_program) {
    echo "프로그램 조회 실패" . "<br>";
    echo "실패원인 : " . mysqli_error($con);
    exit();
}

$row_program = mysqli_fetch_array($result_program);
echo "<h1>프로그램 상세 정보</h1>";
echo "<p>프로그램 이름: " . $row_program['program_name'] . "</p>";
echo "<p>프로그램 진행 상태: " . $row_program['status'] . "</p>";

$query_sum = "SELECT IFNULL(SUM(donation.money), 0) AS total_money FROM donation WHERE program_program_name = '$program_name'";
$result_sum = mysqli_query($con, $query_sum);

if (!$result_sum) {
    echo "쿼리 실행 실패: " . mysqli_error($con);
    exit();
}

$row_sum = mysqli_fetch_assoc($result_sum);
$total_money = isset($row_sum['total_money']) ? $row_sum['total_money'] : 0;

echo "<p>총 후원금액: " . $total_money . " 원</p>";

$search_keyword = isset($_GET['search_keyword']) ? $_GET['search_keyword'] : '';
$sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'name_asc';

echo "<h2>후원자 목록</h2>";
echo "<form method='get'>";
echo "<input type='hidden' name='program_name' value='$program_name'>";
echo "<input type='text' name='search_keyword' placeholder='후원자 이름으로 검색' value='$search_keyword'>";
$sortOptions = [
    'name_asc' => '이름 오름차순',
    'name_desc' => '이름 내림차순',
    'money_asc' => '금액 적은순',
    'money_desc' => '금액 많은순',
    'date_asc' => '날짜 오름차순',
    'date_desc' => '날짜 내림차순'
];
echo "<select name='sort_order'>";
foreach ($sortOptions as $value => $label) {
    $selected = ($sort_order == $value) ? 'selected' : '';
    echo "<option value='$value' $selected>$label</option>";
}
echo "</select>";
echo "<input type='submit' value='검색 및 정렬'>";
echo "</form>";

$query_donation = "SELECT dn.id AS donation_id, dn.donor_id, d.name AS donor_name, dn.money, dn.date, IFNULL(gl.address, ' ') AS address, IFNULL(gl.status, ' ') AS status FROM donation dn JOIN donor d ON dn.donor_id = d.id LEFT JOIN gift_log gl ON dn.id = gl.donation_id WHERE dn.program_program_name = '$program_name'";
if (!empty($search_keyword)) {
    $query_donation .= " AND d.name LIKE '%" . mysqli_real_escape_string($con, $search_keyword) . "%'";
}
switch ($sort_order) {
    case 'name_asc':
        $query_donation .= " ORDER BY d.name ASC";
        break;
    case 'name_desc':
        $query_donation .= " ORDER BY d.name DESC";
        break;
    case 'money_asc':
        $query_donation .= " ORDER BY dn.money ASC";
        break;
    case 'money_desc':
        $query_donation .= " ORDER BY dn.money DESC";
        break;
    case 'date_asc':
        $query_donation .= " ORDER BY dn.date ASC";
        break;
    case 'date_desc':
        $query_donation .= " ORDER BY dn.date DESC";
        break;
}

$result_donation = mysqli_query($con, $query_donation);
if (!$result_donation) {
    echo "후원자 목록 조회 실패" . "<br>";
    echo "실패원인 : " . mysqli_error($con);
    exit();
}

echo "<table border='1'>";
echo "<tr><th>ID</th><th>이름</th><th>금액</th><th>후원 날짜</th><th>주소</th><th>배송 상태</th><th>발송</th><th>후원 취소</th></tr>";
while ($row_donation = mysqli_fetch_array($result_donation)) {
    echo "<tr>";
    echo "<td>" . $row_donation['donor_id'] . "</td>";
    echo "<td>" . $row_donation['donor_name'] . "</td>";
    echo "<td>" . $row_donation['money'] . "</td>";
    echo "<td>" . $row_donation['date'] . "</td>";
    echo "<td>" . $row_donation['address'] . "</td>";
    echo "<td>" . $row_donation['status'] . "</td>";
    echo "<td>";
    if ($row_donation['status'] == '발송 전') {
        echo "<form method='post' action=''>";
        echo "<input type='hidden' name='donation_id' value='" . $row_donation['donation_id'] . "'>";
        echo "<input type='submit' name='send_button' value='발송'>";
        echo "</form>";
    }
    echo "</td>";
    echo "<td>";
    echo "<form method='post' action=''>";
    echo "<input type='hidden' name='delete_donation_id' value='" . $row_donation['donation_id'] . "'>";
    echo "<input type='submit' name='delete_button' value='취소'>";
    echo "</form>";
    echo "</td>";
    echo "</tr>";
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['delete_button'])) {
        $delete_donation_id = $_POST['delete_donation_id'];

        // 트랜잭션 시작
        mysqli_begin_transaction($con);

        // gift_log에서 굿즈 ID 확인
        $check_gift_log_query = "SELECT goods_id FROM gift_log WHERE donation_id = '$delete_donation_id'";
        $result_check_gift_log = mysqli_query($con, $check_gift_log_query);

        if (mysqli_num_rows($result_check_gift_log) > 0) {
            $row = mysqli_fetch_assoc($result_check_gift_log);
            $goods_id = $row['goods_id'];

            // 후원 취소 쿼리
            $delete_donation_query = "DELETE FROM donation WHERE id = '$delete_donation_id'";
            $result_delete_donation = mysqli_query($con, $delete_donation_query);
    
            if ($result_delete_donation) {
                echo "<script>alert('후원이 삭제되었습니다.');</script>";
                // 페이지 새로고침
                echo "<script>window.location.href=window.location.href;</script>";
            } else {
                echo "후원 삭제 실패: " . mysqli_error($con);
            }
            
            if ($result_delete_donation) {
                // 굿즈 수량 증가 쿼리
                $update_goods_query = "UPDATE goods SET count = count + 1 WHERE id = '$goods_id'";
                $result_update_goods = mysqli_query($con, $update_goods_query);

                if ($result_update_goods) {
                    // 모든 쿼리가 성공하면 트랜잭션 커밋
                    mysqli_commit($con);
                    echo "<script>alert('후원 취소가 완료되었습니다.');</script>";
                } else {
                    // 굿즈 수량 업데이트 실패 시 롤백
                    mysqli_rollback($con);
                    echo "굿즈 수량 업데이트 실패: " . mysqli_error($con);
                }
            } else {
                // 후원 취소 쿼리 실패 시 롤백
                mysqli_rollback($con);
                echo "후원 취소 실패: " . mysqli_error($con);
            }
        } else {
            // gift_log에 해당 후원 기록이 없으면 롤백
            mysqli_rollback($con);
            echo "후원 취소 실패: gift_log에 해당 후원 기록 없음";
        }
    }


    if (isset($_POST['send_button'])) {
        $donation_id_to_send = $_POST['donation_id'];
        // gift_log 테이블의 status를 업데이트
        $update_status_query = "UPDATE gift_log SET status = '발송 완료' WHERE donation_id = '$donation_id_to_send'";
        $result_update_status = mysqli_query($con, $update_status_query);

        if ($result_update_status) {
            echo "<script>alert('발송이 완료되었습니다.'); window.location.href=window.location.href;</script>";
        } else {
            echo "발송 실패: " . mysqli_error($con);
        }
    }
}

mysqli_close($con);
?>
<form method="post" action="http://localhost/total.php">
    <input type="submit" value="뒤로가기">
</form>