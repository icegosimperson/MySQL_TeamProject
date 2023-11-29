<!DOCTYPE html>
<html>
<head>
    <title>프로그램 관리</title>
</head>
<body>
    <?php
    // MySQL 데이터베이스 연결 설정
    $servername = "localhost";
    $username = "root";
    $password = "1234";
    $dbname = "후원관리시스템";

    
    $conn = new mysqli($servername, $username, $password, $dbname);

   
    if ($conn->connect_error) {
        die("데이터베이스 연결 실패: " . $conn->connect_error);
    }
    
// 프로그램 삭제 처리
if (isset($_GET['delete'])) {
    $programName = $_GET['delete'];
    $deleteSql = "DELETE FROM `program` WHERE program_name = '" . $conn->real_escape_string($programName) . "'";
    if ($conn->query($deleteSql) === TRUE) {
        echo "<script>
                alert('프로그램이 성공적으로 삭제되었습니다.');
                window.location.href = window.location.pathname; // 현재 페이지로 이동
              </script>";
        exit(); 
    } else {
        echo "삭제 오류: " . $conn->error;
    }
}

    // 카테고리 데이터 가져오기
    $categorySql = "SELECT id, name FROM category";
    $categoryResult = $conn->query($categorySql);

    // 상태 옵션 추가
    $statuses = ['모든 상태', '승인 전',  '승인 완료', '반려', '진행 중', '진행 완료'];

    // 상태 및 카테고리 선택 확인
    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : '모든 카테고리';
    $selectedStatus = isset($_GET['status']) ? $_GET['status'] : '모든 상태';
    $searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

    
    $sortOptions = [
        'end_date_asc' => '마감일 빠른 순',
        'end_date_desc' => '마감일 느린 순',
        'start_date_asc' => '시작일 빠른 순',
        'start_date_desc' => '시작일 느린 순',
        'total_donations_asc' => '총 후원금액 적은 순',
        'total_donations_desc' => '총 후원금액 많은 순'
    ];
    $selectedSort = isset($_GET['sort']) ? $_GET['sort'] : 'end_date';
    $sortOrder = array_key_exists($selectedSort, $sortOptions) ? $selectedSort : 'end_date';

    // 검색 실행
    $searchKeyword = $searchKeyword ?: ''; // 검색 키워드가 없으면 빈 문자열로 설정
    $selectedStatus = $selectedStatus !== '모든 상태' ? $selectedStatus : ''; 
    $selectedCategory = $selectedCategory == '모든 카테고리' ? '' : $selectedCategory; 
    // 프로시저 호출
    $stmt = $conn->prepare("CALL search_Program_By_Name_And_Status(?, ?, ?, ?)");
    $stmt->bind_param("ssss", $searchKeyword, $selectedStatus, $selectedCategory, $sortOrder);
    $stmt->execute();
    $programResult = $stmt->get_result();
    
    echo "<h1>프로그램</h1>";
    echo "<a href='manage_login.php'>이전으로 돌아가기</a>";
    // 검색
    echo "<form method='get'>";
    echo "<input type='text' name='search' placeholder='프로그램 검색...' value='$searchKeyword'>";
    echo "<input type='submit' value='검색'>";
    echo "<button type='button' onclick='window.location.href=window.location.pathname'>검색 초기화</button>";

    // 카테고리 선택 리스트 박스
    echo "<label for='category'>카테고리: </label>";
    echo "<select id='category' name='category' onchange='this.form.submit()'>";
    echo "<option value='모든 카테고리'" . ($selectedCategory == '모든 카테고리' ? ' selected' : '') . ">모든 카테고리</option>";
    while ($category = $categoryResult->fetch_assoc()) {
        $selected = $selectedCategory == $category['name'] ? 'selected' : '';
        echo "<option value='{$category['name']}' $selected>{$category['name']}</option>";
    }
    echo "</select>";

    // 진행 상태 선택 리스트 박스
    echo "<label for='status'>진행 상태: </label>";
    echo "<select id='status' name='status' onchange='this.form.submit()'>";
    foreach ($statuses as $status) {
        $selected = $selectedStatus == $status ? 'selected' : '';
        echo "<option value='$status' $selected>$status</option>";
    }
    echo "</select>";

    // 정렬 옵션 선택 목록 상자
    echo "<label for='sort'>정렬 순서: </label>";
    echo "<select id='sort' name='sort' onchange='this.form.submit()'>";
    foreach ($sortOptions as $value => $label) {
        $selected = ($value == $selectedSort) ? ' selected' : '';
        echo "<option value='$value'{$selected}>$label</option>";
    }
    echo "</select>";

    // 프로그램 테이블 출력
    if (isset($programResult) && $programResult->num_rows > 0) {
        echo "<table border='1'>";
        echo "<tr>
                <th>프로그램 이름</th>
                <th>카테고리</th>
                <th>시작 날짜</th>
                <th>종료 날짜</th>
                <th>상태</th>
                <th>회사 이름</th>
                <th>총 후원금액</th>
                <th>삭제</th>
              </tr>";
        while ($row = $programResult->fetch_assoc()) {
            echo "<tr>
                    <td><a href='program_manage_detail.php?program_name={$row['program_name']}'>{$row['program_name']}</a></td>
                    <td>{$row['category_name']}</td>
                    <td>{$row['start_date']}</td>
                    <td>{$row['end_date']}</td>
                    <td>{$row['status']}</td>
                    <td><a href='company_info.php?id={$row['company_id']}'>{$row['company_name']}</a></td>
                    <td>{$row['total_donations']}</td>
                    <td><a href='?delete={$row['program_name']}' onclick='return confirm(\"정말 이 프로그램을 삭제하시겠습니까?\");'>삭제</a></td>
                  </tr>";
        }
        echo "</table>";
    } else {
        echo "검색 결과가 없습니다.";
    }

    
    $conn->close();
    ?>
</body>
</html>
