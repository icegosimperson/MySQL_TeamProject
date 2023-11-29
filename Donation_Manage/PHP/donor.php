<!-- 관리자가 후원자를 관리하는 페이지 입니다 
* 기능 : 검색(후원자 id, 이름, 휴대폰 번호, 선호 카테고리), 정렬(검색 기준과 동일), 초기화, 개인 선호 카테고리 조회, 개인 후원내역 조회
* 기능 목표 : 관리자가 입력한 검색어, 선택한 리스트 박스(정렬 기준, 검색 기준)를 
            초기화 버튼 클릭 전까지 표시하여 관리자의 후원자 정보 조회를 편리하게 한다.   
-->

<?php
    $conn = new mysqli('localhost', 'root', '1234', '후원관리시스템'); // DB connect
    if ($conn->connect_error) { // connect fail : print error message 
        die("Connection failed: " . $conn->connect_error);
    }


    // 검색 및 정렬 조건을 $_GET 및 $_POST에서 가져오기
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        $searchType = isset($_GET['searchType']) ? $_GET['searchType'] : 'all';
        $searchValue = isset($_GET['searchValue']) ? $conn->real_escape_string($_GET['searchValue']) : '';
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
    } else {
        $searchType = isset($_GET['searchType']) ? $_GET['searchType'] : 'all';
        $searchValue = isset($_GET['searchValue']) ? $conn->real_escape_string($_GET['searchValue']) : '';
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id';
    }
    
    $resetClicked = isset($_GET['resetClicked']) ? $_GET['resetClicked'] : '0'; // 초기화

    // 초기화 버튼 클릭시
    if ($resetClicked === '1') {
        $orderBy = 'id';
        $searchType = 'all';
        $searchValue = '';
        $_GET['orderBy'] = 'id';  // orderBy를 초기화
    } else {
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : (isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id');
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS id, name, phone, category_id FROM donor";

    // 검색 기준 선택시 (한글자만 일치해도 검색되도록, category는 정확히 일치해야 검색 가능)
    if (isset($_GET['search']) && $searchValue != '') {
        switch ($searchType) {
            case 'id':
                $sql .= " WHERE BINARY id LIKE '%$searchValue%'";
                break;
            case 'name':
                $sql .= " WHERE BINARY name LIKE '%$searchValue%'";
                break;
            case 'phone':
                $sql .= " WHERE BINARY phone LIKE '%$searchValue%'";
                break;
            case 'category': // 동일한 선호 카테고리를 선택한 후원자가 많을 경우 방지 
                $sql .= " WHERE category_id = '$searchValue'";
                break;
            case 'all':
            default:
                $sql .= " WHERE BINARY id LIKE '%$searchValue%' 
                            OR name LIKE '%$searchValue%' 
                            OR phone LIKE '%$searchValue%' 
                            OR category_id = '$searchValue'";
                break;
        }
    }

    // 정렬 알고리즘
    if ($orderBy == 'category_id') { // (category_id의 경우 c1, c2,..c10... 이렇게 보이게 문자열과 분리하여 정렬)
        $sql .= " ORDER BY CONVERT(SUBSTRING(category_id, 2), UNSIGNED INTEGER) ASC";
    } else {
        $sql .= " ORDER BY $orderBy ASC";
    }

    // 페이지 로드 시 $_GET에서 orderBy 값을 가져옴
    if (isset($_GET['orderBy'])) {
        $orderBy = $_GET['orderBy'];
    } else {
        $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id'; // 기본값
    }
    $result = $conn->query($sql); // 결과 출력

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>후원관리 시스템</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd; 
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even){background-color: #f2f2f2}
            .title-container {
                display: flex; 
                align-items: center;
            }   

        .home-link {
            display: inline-block; 
            margin-left: 20px; 
            padding: 5px 5px;
            font-size: 20px; 
            text-decoration: none;
            color: black; 
            border: solid 1px black;
            background-color: #f2f2f2; 
            text-align: center; 
            vertical-align: middle;
            cursor: pointer; 
            box-shadow: 1px 1px 1px #ccc; 
            border-radius: 2px; 
        }
        .page-link{
            display: inline-block;
            margin-left: 0px;
            padding: 5px 5px;
            font-size: 15px;
            text-decoration: none;
            color: black;
            border: solid 1px black;
            background-color: #f2f2f2;
            text-align: center;
            vertical-align: middle;
            cursor: pointer;
            box-shadow: 1px 1px 1px #ccc;
            border-radius: 2px;
            display: inline-block;
        }

        .page-container {
            text-align: center;
        }

        .line-button-container {
            display: flex;
            align-items: center;
            justify-content: flex-start; 
            width: 100%; 
        } 
        .button-container {
            width: 70%; 
            display: flex;
            justify-content: flex-start; 
        }
        
        button {
            margin-right: 10px; 
        }
    </style>
</head>
<body>
    <div class="title-container">
        <h1> 관리자 모드 </h1>  <a href="http://localhost/manage_login.php" class="home-link">홈 화면</a>
    </div>
<h2>후원자 관리</h2> 

<!-- 검색 폼 -->
<div class="line-button-container">
    <div class="short-line"></div>
    <div class="button-container">
        <!-- 초기화 기능 -->
        <!-- 초기화 스크립트 수정 -->
        <script>
            function resetForm() {
                // 폼의 모든 필드를 초기화
                document.getElementById("searchForm").reset();
                document.getElementById("resetIndicator").value = "1";
                document.getElementById("searchForm").submit();
            }
        </script>

        <!--검색 기능-->
        <form id="searchForm" action="" method="get">
        <!-- 입력 데이터를 검색하고도 리스트 박스를 계속 볼 수 있게 설정 -->
        <select name="searchType"> 
            <option value="all" <?php echo ($searchType === 'all' ? 'selected' : ''); ?>>전체</option>
            <option value="id" <?php echo ($searchType === 'id' ? 'selected' : ''); ?>>후원자 ID</option>
            <option value="name" <?php echo ($searchType === 'name' ? 'selected' : ''); ?>>이름</option>
            <option value="phone" <?php echo ($searchType === 'phone' ? 'selected' : ''); ?>>휴대폰 번호</option>
            <option value="category" <?php echo ($searchType === 'category' ? 'selected' : ''); ?>>선호 카테고리</option>
        </select>
        <!-- 입력 데이터를 검색하고도 입력 데이터를 계속 볼 수 있게 설정 -->
        <input type="text" name="searchValue" placeholder="검색" value="<?php echo htmlspecialchars($searchValue); ?>">
        <button type="submit" name="search">검색</button>
        <button type="button" onclick="resetForm()">초기화</button>
        <input type="hidden" name="resetClicked" value="0" id="resetIndicator">

        <!-- 정렬 -->
        <select id="orderBy" name="orderBy">
            <option value="id" <?php echo ($orderBy == 'id') ? 'selected' : ''; ?>>후원자 ID</option>
            <option value="name" <?php echo ($orderBy == 'name') ? 'selected' : ''; ?>>이름</option>
            <option value="phone" <?php echo ($orderBy == 'phone') ? 'selected' : ''; ?>>휴대폰 번호</option>
            <option value="category_id" <?php echo ($orderBy == 'category_id') ? 'selected' : ''; ?>>선호 카테고리</option>
        </select>
        <button type="submit" name="search">정렬</button>
        </form>
    </div>
</div>

<table>
    <tr>
        <th>후원자 ID</th>
        <th>이름</th> 
        <th>휴대폰 번호</th>
        <th>선호 카테고리</th>
        <th>개인 후원 내역</th>
    </tr>
    
 
    <?php
        if ($result && $result->num_rows > 0) {
            while($row = $result->fetch_assoc()) {
                    echo "<tr>";
                    echo "<td>" . htmlspecialchars($row['id']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['phone']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category_id']) . "</td>";
                    echo "<td style='text-align: center;'><a href='http://localhost/donation_personal.php?user_id=" . urlencode($row['id']) . "'>조회</a></td>";
                    echo "</tr>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No donors found</td></tr>";
            }
        ?>
    </table>



    <?php 
    $conn->close(); 
    ?> 
  </body>
  </html>