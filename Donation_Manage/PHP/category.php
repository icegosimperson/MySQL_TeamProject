<!-- 관리자가 후원 프로그램 카테고리를 관리하는 페이지 입니다 
* 기능 : 검색(카테고리 id, 카테고리 이름), 정렬(검색 기준과 동일), 초기화, 카테고리 추가, 카테고리 삭제
* 기능 목표 : 관리자가 입력한 검색어, 선택한 리스트 박스(정렬 기준, 검색 기준)를 
초기화 버튼 클릭 전까지 표시하여 관리자의 데이터 관리를 편리하게 한다.   
-->

<?php
  $conn = new mysqli('localhost', 'root', '1234', '후원관리시스템'); // DB connect
  if ($conn->connect_error) { // connect fail : print error message 
      die("Connection failed: " . $conn->connect_error);
  }

  $searchType = isset($_POST['searchType']) ? $_POST['searchType'] : 'all'; // 검색기준
  $searchValue = isset($_POST['searchValue']) ? $conn->real_escape_string($_POST['searchValue']) : ''; // 검색 데이터
  $orderBy = isset($_GET['orderBy']) ? $_GET['orderBy'] : (isset($_POST['orderBy']) ? $_POST['orderBy'] : 'id'); // 정렬
  $resetClicked = isset($_POST['resetClicked']) ? $_POST['resetClicked'] : '0'; // 초기화

  // 초기화 버튼 클릭시
  if ($resetClicked === '1') {
      $orderBy = 'id';
      $searchType = 'all';
      $searchValue = '';
      $_GET['orderBy'] = 'id';  // orderBy를 초기화
  } else {
    $orderBy = isset($_POST['orderBy']) ? $_POST['orderBy'] : (isset($_GET['orderBy']) ? $_GET['orderBy'] : 'id');
  }

  // 검색 기준 선택시 (한글자만 일치해도 검색되도록)
  $sql = "SELECT SQL_CALC_FOUND_ROWS id, name FROM category";
  if (isset($_POST['search']) && $searchValue != '') {
      switch ($searchType) {
          case 'id':
              $sql .= " WHERE id LIKE '%$searchValue%'";
              break;
          case 'name':
              $sql .= " WHERE name LIKE '%$searchValue%'";
              break;
          case 'all':
          default:
              $sql .= " WHERE id LIKE '%$searchValue%' OR name LIKE '%$searchValue%'";
              break;
      }
  }

  // 정렬 알고리즘 (c1, c2, c11, c12, c100... )
  if ($orderBy == 'id') {
    $sql .= " ORDER BY CONVERT(SUBSTRING(id, 2), UNSIGNED INTEGER) DESC";
  } else {
      $sql .= " ORDER BY $orderBy DESC";
  }

   // 페이지 로드 시 $_GET에서 orderBy 값을 가져옴
   if (isset($_GET['orderBy'])) {
    $orderBy = $_GET['orderBy'];
    } else {
        $orderBy = isset($_POST['orderBy']) ? $_POST['orderBy'] : 'id'; // 기본값
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

</style>
</head>
<body>
  <div class="title-container">
    <h1>관리자 모드</h1>  
    <a href="http://localhost/manage_login.php" class="home-link">홈 화면</a>
  </div>  
  <h2>카테고리 추가</h2>

   <!--초기화 기능-->
  <script>
    function resetForm() {
      document.getElementById("orderBy").value = "id";
      document.getElementById("resetIndicator").value = "1";
      document.getElementById("searchForm").elements['searchValue'].value = '';
      document.getElementById("searchForm").submit();
      }
  </script>

  <!--카테고리 추가, 삭제 기능-->
  <form action="" method="post">
    <label for="categoryId">카테고리 ID:</label>
    <input type="text" id="categoryId" name="categoryId"><br><br>
    <label for="categoryName">카테고리명:</label>
    <input type="text" id="categoryName" name="categoryName"><br><br>
    <input type="submit" name="action" value="카테고리 추가">
    <input type="submit" name="action" value="카테고리 삭제">
  </form>

  <!--검색 기능-->
  <form id="searchForm" action="" method="post">
     <!--선택한 리스트 박스 기준을 검색해도 계속 선택되어 있도록 설정-->
    <select name="searchType">
        <option value="all" <?php echo ($searchType == 'all') ? 'selected' : ''; ?>>전체</option>
        <option value="id" <?php echo ($searchType == 'id') ? 'selected' : ''; ?>>카테고리 ID</option>
        <option value="name" <?php echo ($searchType == 'name') ? 'selected' : ''; ?>>카테고리명</option>
    </select>

    <!--입력한 데이터를 검색해도 보이게 있도록 설정-->
    <input type="text" name="searchValue" placeholder="검색" value="<?php echo htmlspecialchars($searchValue); ?>">
    <button type="submit" name="search">검색</button>
    <button type="button" onclick="resetForm()">초기화</button>
    <input type="hidden" name="resetClicked" value="0" id="resetIndicator">
    
    <!--선택한 리스트 박스 기준을 정렬해도 볼 수 있도록 설정-->
    <select id="orderBy" name="orderBy">
      <option value="id" <?php echo ($orderBy == 'id') ? 'selected' : ''; ?>>카테고리 ID</option>
      <option value="name" <?php echo ($orderBy == 'name') ? 'selected' : ''; ?>>카테고리명</option>
  </select>
    <!-- 정렬 버튼 -->
    <button type="submit" name="search">정렬</button>
</form>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $categoryId = $conn->real_escape_string($_POST['categoryId']);
    $categoryName = $conn->real_escape_string($_POST['categoryName']);
    $action = $_POST['action'];

    // 카테고리 추가 버튼 클릭시 
    if ($action == "카테고리 추가") {
        if (!preg_match('/^C/', $categoryId)) { // c로 시작하지 않으면 카테고리 생성 불가 
            echo "에러: C를 포함해서 카테고리 아이디를 입력 하세요 'c'.<br>";
        } else { // DB data 확인
            $checkSql = "SELECT * FROM category WHERE id = '$categoryId' OR name = '$categoryName'";
            $checkResult = $conn->query($checkSql);
            // 중복 검사 
            if ($checkResult && $checkResult->num_rows > 0) {
                echo "에러: 이미 동일한 아이디나 카테고리 이름이 존재합니다.<br>";
            } else {
                $addSql = "INSERT INTO category (id, name) VALUES ('$categoryId', '$categoryName')";
                if ($conn->query($addSql) === TRUE) {
                    echo "새로운 카테고리를 성공적으로 추가 했습니다.<br>";
                } else {
                    echo "카테고리를 추가하지 못했습니다.: " . $conn->error;
                }
            }
        }
    } 
      // 카테고리 삭제 버튼 클릭 시 
      else if ($action == "카테고리 삭제") {
        $deleteSql = "DELETE FROM category WHERE id = '$categoryId' AND name = '$categoryName'";
        if ($conn->query($deleteSql) === TRUE) {
            echo "Category deleted successfully.<br>";
        } else {
            echo "Error deleting category: " . $conn->error;
        }
    }
}

// category table의 항목을 불러와 정렬
$sql = "SELECT SQL_CALC_FOUND_ROWS id, name FROM category 
        ORDER BY CAST(SUBSTRING(id, 2) AS UNSIGNED) DESC";
?>

<table>
  <tr>
    <th>카테고리 ID</th>
    <th>카테고리명</th>
  </tr>

  <!— 데이터베이스에서 검색한 결과를 HTML 테이블로 표시 —>
  <?php if ($result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['id']); ?></td>
        <td><?php echo htmlspecialchars($row['name']); ?></td>
      </tr>
    <?php endwhile; ?>
  <?php else: ?>
    <tr><td colspan='2'>카테고리가 없습니다</td></tr>
  <?php endif; ?>
</table>

<?php
$conn->close(); // php 연결 종료
?>
</body>
</html>
