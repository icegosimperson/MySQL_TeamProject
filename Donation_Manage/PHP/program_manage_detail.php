<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>프로그램 상세 정보</title>
    
</head>
<body>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "1234";
    $dbname = "후원관리시스템";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("데이터베이스 연결 실패: " . $conn->connect_error);
    }

    if (isset($_GET['program_name'])) {
        $program_name = $conn->real_escape_string($_GET['program_name']);

        // 후원자 검색 및 정렬 설정
        $donor_name_search = isset($_GET['donor_name']) ? $conn->real_escape_string($_GET['donor_name']) : '';
        $sort_order = isset($_GET['sort_order']) ? $_GET['sort_order'] : 'date_desc';

        // 프로그램 상세 정보를 조회하는 부분
        $sql = "SELECT p.*, c.name AS category_name, phc.company_name, phc.company_phone_number FROM program p LEFT JOIN category c ON p.category_id = c.id LEFT JOIN program_host_company phc ON p.program_host_company_id = phc.id WHERE p.program_name = '$program_name'";
        $result = $conn->query($sql);
        echo "<a href='program_list.php'>프로그램으로 돌아가기</p>";
        echo "<a href='company_index.php'>회사로 돌아가기</a>";

        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo "<h1>{$row['program_name']} 상세 정보</h1>";
            echo "<p>시작 날짜: {$row['start_date']}</p>";
            echo "<p>종료 날짜: {$row['end_date']}</p>";
            echo "<p>장소: {$row['place']}</p>";
            echo "<p>상태: {$row['status']}</p>";
            echo "<p>목적: {$row['purpose']}</p>";
            echo "<p>카테고리: {$row['category_name']}</p>";
            echo "<p>회사 이름: {$row['company_name']}</p>";
            echo "<p>회사 번호: {$row['company_phone_number']}</p>";

            // 승인 및 반려 처리 부분
            if ($row['status'] == '승인 전') {
                echo "<form action='' method='post'>";
                echo "<input type='submit' name='approve' value='승인'>";
                echo "<input type='submit' name='reject' value='반려'>";
                echo "</form>";
            }

            if ($_SERVER["REQUEST_METHOD"] == "POST") {
                if (isset($_POST['approve'])) {
                    $update_sql = "UPDATE program SET status='승인 완료' WHERE program_name='$program_name'";
                    $conn->query($update_sql);
                } elseif (isset($_POST['reject'])) {
                    $update_sql = "UPDATE program SET status='반려' WHERE program_name='$program_name'";
                    $conn->query($update_sql);
                }
                header("Refresh:0");
            }

            // 검색 및 정렬 폼
            echo "<form id='searchForm' action='' method='get'>";
            echo "<input type='hidden' name='program_name' value='$program_name'>";
            echo "<input type='text' name='donor_name' placeholder='기부자 이름 검색' value='$donor_name_search'>";
            echo "<select name='sort_order'>";
            echo "<option value='date_asc'" . ($sort_order == 'date_asc' ? ' selected' : '') . ">기부 날짜 순</option>";
            echo "<option value='date_desc'" . ($sort_order == 'date_desc' ? ' selected' : '') . ">기부 날짜 역순</option>";
            echo "<option value='amount_desc'" . ($sort_order == 'amount_desc' ? ' selected' : '') . ">기부금액 많은순</option>";
            echo "<option value='amount_asc'" . ($sort_order == 'amount_asc' ? ' selected' : '') . ">기부금액 적은순</option>";
            echo "</select>";
            echo "<input type='submit' value='검색 및 정렬'>";
            echo "<button type='button' onclick='resetForm()'>검색 초기화</button>";
            echo "</form>";

            // 프로시저 호출
            $stmt = $conn->prepare("CALL GetDonations(?, ?, ?)");
            $stmt->bind_param("sss", $program_name, $donor_name_search, $sort_order);
            $stmt->execute();
            $donation_result = $stmt->get_result();

            if ($donation_result && $donation_result->num_rows > 0) {
                echo "<h2>기부 기록</h2>";
                echo "<table>";
                echo "<tr><th>기부자 ID</th><th>기부자 이름</th><th>기부액</th><th>기부 날짜</th></tr>";
                while ($donation_row = $donation_result->fetch_assoc()) {
                    $formatted_date = date('Y-m-d', strtotime($donation_row['date']));
                    echo "<tr>";
                    echo "<td>{$donation_row['donor_id']}</td>";
                    echo "<td>{$donation_row['donor_name']}</td>";
                    echo "<td>{$donation_row['money']}</td>";
                    echo "<td>{$formatted_date}</td>";
                    echo "</tr>";
                }
                echo "</table>";
            } else {
                echo "<p>이 프로그램에 대한 기부 기록이 없습니다.</p>";
            }

            $stmt->close();
        } else {
            echo "프로그램을 찾을 수 없습니다.";
        }
    } else {
        echo "프로그램 이름이 지정되지 않았습니다.";
    }

    $conn->close();
    ?>
</body>
</html>