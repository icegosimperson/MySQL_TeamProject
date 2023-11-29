<?php

session_start();

// 로그인한 회사 ID 가져오기
$company_id = $_SESSION['userID'] ?? 'unknown_company_id';


$con = mysqli_connect("localhost", "root", "1234", "후원관리시스템");
if (!$con) {
    die("Connection failed: " . mysqli_connect_error());
}

function generateGoodsID($conn) {
    $query = "SELECT MAX(CAST(SUBSTRING(id, 2) AS UNSIGNED)) AS max_id FROM goods";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $maxId = $row['max_id'];
    } else {
        $maxId = 0;
    }

    $nextId = $maxId + 1;
    return 'G' . $nextId;
}


$message = "";
$program_name = "";
$program_exists = false;
$show_program_form = false;

// 프로그램 이름 중복 확인
if (isset($_POST['check_duplicate'])) {
    $program_name = mysqli_real_escape_string($con, $_POST['program_name']);

    $check_duplicate_query = "SELECT COUNT(*) as count FROM program WHERE program_name = ?";
    $check_stmt = mysqli_prepare($con, $check_duplicate_query);
    mysqli_stmt_bind_param($check_stmt, "s", $program_name);
    mysqli_stmt_execute($check_stmt);
    $check_result = mysqli_stmt_get_result($check_stmt);
    $check_row = mysqli_fetch_assoc($check_result);
    $program_count = $check_row['count'];

    if ($program_count > 0) {
        $message = "이미 존재하는 프로그램 이름입니다. 다른 이름을 선택해주세요.";
        $program_exists = true;
    } else {
        $message = "사용 가능한 프로그램 이름입니다. 아래의 정보를 입력해주세요.";
        $program_exists = false;
        $show_program_form = true;
    }
}



// 프로그램 정보 추가
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit']) && !$program_exists) {

    $program_name = mysqli_real_escape_string($con, $_POST['program_name']);
    $start_date = mysqli_real_escape_string($con, $_POST['start_date']);
    $end_date = mysqli_real_escape_string($con, $_POST['end_date']);
    $purpose = mysqli_real_escape_string($con, $_POST['purpose']);
    $category_id = mysqli_real_escape_string($con, $_POST['category_id']);
    $place = !empty($_POST['place']) ? mysqli_real_escape_string($con, $_POST['place']) : NULL;
    $account_number = mysqli_real_escape_string($con, $_POST['account_number']);

    
    $add_goods = !empty($_POST['goods_name']) && isset($_POST['count']);
     // 프로그램 정보 추가
     $insert_query = "INSERT INTO program (program_name, start_date, end_date, purpose, status, category_id, program_host_company_id, place, account_number) VALUES (?, ?, ?, ?, '승인 전', ?, ?, ?, ?)";
     $stmt = mysqli_prepare($con, $insert_query);
     mysqli_stmt_bind_param($stmt, "ssssssss", $program_name, $start_date, $end_date, $purpose, $category_id, $company_id, $place, $account_number);
 
     if (mysqli_stmt_execute($stmt)) {
         // 굿즈 정보 추가
         if ($add_goods) {
             $name = mysqli_real_escape_string($con, $_POST['goods_name']);
             $count = intval($_POST['count']);
             $goods_id = generateGoodsID($con);
 
             $goods_insert_query = "INSERT INTO goods (id, name, count, program_program_name) VALUES (?, ?, ?, ?)";
             $goods_stmt = mysqli_prepare($con, $goods_insert_query);
             mysqli_stmt_bind_param($goods_stmt, "ssis", $goods_id, $name, $count, $program_name);
             
             if (!mysqli_stmt_execute($goods_stmt)) {
                 $message = "굿즈 정보 추가에 실패했습니다. 오류: " . mysqli_error($con);
             }
         }
 
         $message = "프로그램 신청이 성공적으로 완료되었습니다.";
     } else {
         $message = "프로그램 신청에 실패했습니다. 오류: " . mysqli_error($con);
     }

}


$category_query = "SELECT id, name FROM category";
$category_result = mysqli_query($con, $category_query);
$categories = [];
while ($row = mysqli_fetch_assoc($category_result)) {
    $categories[] = $row;
}


mysqli_close($con);
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>프로그램 신청 폼</title>
</head>
<body>
    <div>
        <h2>프로그램 신청</h2>
        <?php echo "<p>$message</p>"; ?>
        <form method="post">
            <label for="program_name">프로그램 이름:</label>
            <input type="text" id="program_name" name="program_name" value="<?php echo $program_name; ?>" required>
            <button type="submit" name="check_duplicate">중복 확인</button>
            <br>

            <?php if ($show_program_form): ?>
                
                <label for="start_date">시작 날짜:</label>
                <input type="date" id="start_date" name="start_date" required><br>

                <label for="end_date">마감 날짜:</label>
                <input type="date" id="end_date" name="end_date" required><br>

                <label for="place">장소:</label>
                <input type="text" id="place" name="place"><br>

                <label for="account_number">계좌번호:</label>
                <input type="text" id="account_number" name="account_number" required><br>

                <label for="purpose">목적:</label>
                <textarea id="purpose" name="purpose" rows="4" required></textarea><br>

                <label for="category_id">카테고리:</label>
                <select id="category_id" name="category_id">
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                            <?php echo htmlspecialchars($category['name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select><br>

                <label for="goods_name">굿즈명:</label>
                <input type="text" id="goods_name" name="goods_name"><br>

                <label for="count">수량:</label>
                <input type="number" id="count" name="count" min="0"><br>

                <input type="submit" name="submit" value="신청">
            <?php endif; ?>
        </form>
    </div>
    <form method="post" action="http://localhost/company_dd.php">
        <input type="submit" value="뒤로가기">
    </form>
</body>
</html>
