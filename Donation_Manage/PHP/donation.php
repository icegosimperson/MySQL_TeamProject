<!-- http://localhost/donation.php -->

<?php

session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: http://localhost/donation.php');
    exit();
}

    // 데이터베이스 연결
    $db_host = "localhost";
    $db_user = "root";
    $db_password = "1234";
    $db_name = "후원관리시스템";
    $con = mysqli_connect($db_host, $db_user, $db_password, $db_name);
    if (mysqli_connect_error()) {
        echo "MySQL 연결 실패: " . mysqli_connect_error();
        exit();
    }

    // 사용자 아이디
    $user_id = $_SESSION['userID'];

    // 추천 프로그램용 프로시저 호출
    $sqlCallProcedure = "CALL RecommendedPrograms('$user_id')";
    $result_program = mysqli_query($con, $sqlCallProcedure);

    if ($result_program) {
        $programs = array();
        while ($row = mysqli_fetch_assoc($result_program)) {
            $programs[] = $row;
        }
        mysqli_free_result($result_program);

        while (mysqli_next_result($con)) {
            if ($result = mysqli_store_result($con)) {
                mysqli_free_result($result);
            }
        }
    } else {
        echo "프로시저 호출 중 에러: " . mysqli_error($con);
    }
    // 카테고리
    $categories = array();
    $sql_category = "SELECT c.name FROM `category` c";
    $result_category = mysqli_query($con, $sql_category);

    if ($result_category) {
        while ($row = mysqli_fetch_assoc($result_category)) {
            $categories[] = $row['name'];
        }
        mysqli_free_result($result_category);
    } else {
        echo "카테고리 정보를 불러오는 데 실패했습니다: " . mysqli_error($con);
    }
    $selectedCategory = isset($_GET['category']) ? mysqli_real_escape_string($con, $_GET['category']) : 'all';
    
    // 검색
    $searchValue = isset($_GET['search']) ? mysqli_real_escape_string($con, $_GET['search']) : '';
    // 정렬
    $selectedSort = isset($_GET['sort']) ? $_GET['sort'] : 'latest';

    

    //프로시저 (선택한 카테고리, 정렬 순, 검색어에 해당하는 프로그램 들고 옴)
    $sqlCallProcedure = "CALL GetPrograms('$selectedCategory', '$searchValue', '$selectedSort')";
    $result = mysqli_query($con, $sqlCallProcedure);

    if ($result) {
        $categoryPrograms = array();
        while ($row = mysqli_fetch_assoc($result)) {
            $categoryPrograms[] = $row;
        }
        mysqli_free_result($result);
    } else {
        echo "프로시저 호출 중 에러: " . mysqli_error($con);
}
?>

<?php
    function displayPrograms($programs, $selectedCategory, $searchValue, $selectedSort) {

        $programsPerPage = 10; //한 페이지당 프로그램 개수
        $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1; //페이지 번호
        $totalPrograms = count($programs);
        $totalPages = ceil($totalPrograms / $programsPerPage); //전체 페이지 수
        $startIndex = ($currentPage - 1) * $programsPerPage;
        $currentPrograms = array_slice($programs, $startIndex, $programsPerPage);

        echo '<table id="programTable" style="margin-top: 20px" border="1;">';
        echo '<tr>';
        echo '<th>프로그램</th>';
        echo '<th>기간</th>';
        echo '<th>주관 단체</th>';
        echo '<th>상세정보</th>';
        echo '</tr>';

        foreach ($currentPrograms as $program) {
            echo '<tr>';
            echo '<td>' . htmlspecialchars($program['program_name']) . '</td>';
            echo '<td>' . date('Y-m-d', strtotime($program['start_date'])) . ' ~ ' . date('Y-m-d', strtotime($program['end_date'])) . '</td>';
            echo '<td>' . htmlspecialchars($program['company_name']) . '</td>';
            echo '<td><a href="http://localhost/donation_info.php?program_name=' . urlencode($program['program_name']) . '">상세정보</a></td>';
            echo '</tr>';
        }

        echo '</table>';

        // 페이지 링크
        for ($i = 1; $i <= $totalPages; $i++) {
            echo '<a href="donation.php?page=' . $i . '&category=' . urlencode($selectedCategory) . '&search=' . urlencode($searchValue) . '&sort=' . urlencode($selectedSort) . '" ' . ($currentPage == $i ? '' : '') . '>' . $i . '</a>';

        }
        echo '</div>';
    }
?> 

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>후원 관리 시스템</title>
</head>
<body>
<header>
    <h1>후원</h1>
        <button onclick="showDonationHistory()">후원 내역</button>
        <button onclick="showUserInfo()">내 정보 수정</button>
        <button onclick="showlogout()">로그아웃</button>
</header>

<section>
    <h2>*추천 프로그램*</h2>
        <table id="recommendationTable"  style="margin-bottom: 40px" border="1;">
            <?php foreach ($programs as $program): ?>
                <tr>
                <td><?= htmlspecialchars($program['program_name']) ?></td>
                <td><?= date('Y-m-d', strtotime($program['start_date'])) ?> ~ <?= date('Y-m-d', strtotime($program['end_date'])) ?></td>
                <td><?= htmlspecialchars($program['company_name']) ?></td>
                <td><a href="http://localhost/donation_info.php?program_name=<?= urlencode($program['program_name']) ?>">상세정보</a></td>
                </tr>
            <?php endforeach; ?>
        </table>
    </div>
</div>

        <select id="categorySelect" name="category" onchange="filterProgramsByCategory()">
            <option value="all" <?= ($selectedCategory === 'all') ? 'selected' : '' ?>>전체</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= htmlspecialchars($category) ?>" <?= ($selectedCategory === $category) ? 'selected' : '' ?>>
                    <?= htmlspecialchars($category) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <select id="sortSelect" name="sort" onchange="sortPrograms()">
            <option value="latest" <?php echo ($selectedSort == 'latest') ? 'selected' : ''; ?>>최신 등록 순</option>
            <option value="deadline" <?php echo ($selectedSort == 'deadline') ? 'selected' : ''; ?>>마감 임박 순</option>
        </select>

        <input type="text" id="programSearch" placeholder="프로그램 검색" value="<?= htmlspecialchars($searchValue) ?>">
        <button onclick="searchProgram()">검색</button>
        <button onclick="resetFilter()">초기화</button>
    </div>
    
    <?php
        if (empty($categoryPrograms)) {
            echo "<p>일치하는 프로그램이 없습니다.</p>";
        } else {
         displayPrograms($categoryPrograms, $selectedCategory, $searchValue, $selectedSort);
        }  
    ?>
</section>

<script>
     function filterProgramsByCategory() { //카테고리별로 필터
        var selectedCategory = document.getElementById('categorySelect').value;
        var searchValue = document.getElementById('programSearch').value;
        window.location.href = 'donation.php?category=' + encodeURIComponent(selectedCategory) + '&search=' + encodeURIComponent(searchValue);
    }

    function sortPrograms() {//정렬
        var selectedCategory = document.getElementById('categorySelect').value;
        var searchValue = document.getElementById('programSearch').value;
        var selectedSort = document.getElementById('sortSelect').value;
        window.location.href = 'donation.php?category=' + encodeURIComponent(selectedCategory) +
            '&search=' + encodeURIComponent(searchValue) + '&sort=' + encodeURIComponent(selectedSort);
    }

    function resetFilter() { //초기화
        var selectedCategory = document.getElementById('categorySelect').value;
        var selectedSort = document.getElementById('sortSelect').value;
        window.location.href = 'donation.php?category=' + encodeURIComponent(selectedCategory) + '&sort=' + encodeURIComponent(selectedSort);
    }

    function searchProgram() {//검색기능
        var selectedCategory = document.getElementById('categorySelect').value;
        var searchValue = document.getElementById('programSearch').value;
        var selectedSort = getParameterByName('sort'); // Retrieve the current sorting order from the URL
        window.location.href = 'donation.php?category=' + encodeURIComponent(selectedCategory) + '&search=' + encodeURIComponent(searchValue) + '&sort=' + encodeURIComponent(selectedSort);
    }

    function getParameterByName(name) {
        var urlParams = new URLSearchParams(window.location.search);
        return urlParams.get(name);
    }

    function showDetailPage(programName) { //상세정보 페이지
        window.location.href = 'http://localhost/donation_info.php?program_name=' + encodeURIComponent(programName);
    }

    function showDonationHistory() { //후원 내역 페이지
        var userId = "<?php echo $_SESSION['userID']; ?>";
        window.location.href = 'http://localhost/donation_user.php?user_id=' + userId;
    }

    function showUserInfo() { //내 정보 수정 페이지
        window.location.href ='http://localhost/change_info.php';
    }

    function showlogout() { //로그아웃 후 로그인 페이지
        window.location.href ='http://localhost/login.php';
    }
   
</script>

</body>
</html>