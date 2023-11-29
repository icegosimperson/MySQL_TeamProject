<?php
session_start();

// 사용자 ID 설정
$company_id = $_SESSION['userID'] ?? 'unknown_company_id';


$db_host = "localhost";
$db_user = "root";
$db_password = "1234";
$db_name = "후원관리시스템";
$con = mysqli_connect($db_host, $db_user, $db_password, $db_name);
if (mysqli_connect_error()) {
    echo "MySQL 연결 실패: " . mysqli_connect_error();
    exit();
}

// 검색어 처리
$searchValue = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
$searchCondition = empty($searchValue) ? '' : " AND p.program_name LIKE '%$searchValue%' ";

// 프로그램 상태 필터
$statusFilter = isset($_GET['status']) ? mysqli_real_escape_string($con, $_GET['status']) : '';
$statusCondition = empty($statusFilter) ? '' : " AND p.status = '$statusFilter' ";


$sortOptions = ['recent' => '최근 등록순', 'urgent' => '마감 임박순'];


$selectedSort = isset($_GET['sort']) ? mysqli_real_escape_string($con, $_GET['sort']) : 'recent';


if ($selectedSort == 'recent') {
    $sortCondition = 'ORDER BY p.start_date DESC';
} elseif ($selectedSort == 'urgent') {
    $sortCondition = 'ORDER BY p.end_date ASC';
} else {
    $sortCondition = ''; 
}

$sql = "SELECT p.program_name,
               p.status,
               p.start_date,
               p.end_date,
               COALESCE(SUM(d.money), 0) AS total_amount
        FROM program p
        LEFT JOIN donation d ON p.program_name = d.program_program_name
        WHERE p.program_host_company_id = '$company_id'
        $searchCondition
        $statusCondition
        GROUP BY p.program_name, p.status, p.start_date, p.end_date
        $sortCondition";

$result = mysqli_query($con, $sql);


$statusOptions = ['승인 전', '승인 완료', '반려', '진행 중', '진행 완료'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>프로그램 내역</title>
    <style>
        .programButton {
            text-decoration: underline;
            cursor: pointer;
            border: none;
            background-color: inherit;
            font-size: 16px;
            color: #4285f4;
            margin: 0;
            padding: 0;
        }

        .programButton:hover {
            color: #0d47a1;
        }
    </style>
</head>
<body>

<?php

if ($result) {
    
    echo "<h2>프로그램 내역</h2>";

    // 검색 폼 및 상태 필터 출력
    echo '<div id="searchContainer">
            <select id="statusFilter" onchange="filterStatus()">
                <option value="">전체</option>';
    foreach ($statusOptions as $status) {
        $selected = ($status == $statusFilter) ? 'selected' : '';
        echo "<option value=\"$status\" $selected>$status</option>";
    }
    echo '</select>
            <select id="sortOptions" onchange="sortProgram()">
                <option value="recent" ' . ($selectedSort == 'recent' ? 'selected' : '') . '>최근 등록순</option>
                <option value="urgent" ' . ($selectedSort == 'urgent' ? 'selected' : '') . '>마감 임박순</option>
            </select>
            <input type="text" id="searchInput" placeholder="프로그램 검색" value="' . htmlspecialchars($searchValue) . '">
            <button id="searchButton" onclick="searchProgram()">검색</button>
            <button id="resetButton" onclick="resetSearch()">초기화</button>
          </div>';

    
    echo "<table border='1'>
            <br>
            <tr>
                <th>프로그램 이름</th>
                <th>시작 날짜</th>
                <th>끝나는 날짜</th>
                <th>진행 상태</th>
                <th>후원 금액</th>
            </tr>";

    
    while ($row = mysqli_fetch_assoc($result)) {
        echo "<tr>
                <td><button class='programButton' onclick=\"location.href='program_detail.php?program_name={$row['program_name']}'\">{$row['program_name']}</button></td>
                <td>{$row['start_date']}</td>
                <td>{$row['end_date']}</td>
                <td>{$row['status']}</td>
                <td>{$row['total_amount']} 원</td>
            </tr>";
    }

    echo "</table>";
} else {
    echo "쿼리 실행 에러: " . mysqli_error($con);
}


mysqli_free_result($result);


mysqli_close($con);
?>


<button onclick="goBack()">뒤로 가기</button>

<script>
    function searchProgram() {
        var searchValue = document.getElementById('searchInput').value;
        var statusFilter = document.getElementById('statusFilter').value;
        var sortOption = document.getElementById('sortOptions').value;

        var url = 'total.php?search=' + encodeURIComponent(searchValue);
        if (statusFilter !== '') {
            url += '&status=' + encodeURIComponent(statusFilter);
        }
        if (sortOption !== '') {
            url += '&sort=' + encodeURIComponent(sortOption);
        }
        window.location.href = url;
    }

    function resetSearch() {
        
        document.getElementById('searchInput').value = '';

        
        searchProgram();
    }

    function filterStatus() {
        searchProgram();
    }

    function sortProgram() {
        searchProgram();
    }
    
    function goBack() {
        window.location.href = 'http://localhost/company_dd.php';
    }
</script>


</body>
</html>