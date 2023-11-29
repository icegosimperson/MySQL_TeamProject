<?php

$host = "localhost"; 
$dbUsername = "root";
$dbPassword = "1234"; 
$dbName = "후원관리시스템"; 


$conn = new mysqli($host, $dbUsername, $dbPassword, $dbName);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 아이디 중복 확인
    if (isset($_POST['checkDuplicate'])) {
        $id = $_POST['id'];

        $checkQuery = "SELECT COUNT(*) as count FROM donor WHERE id = '$id'";
        $checkResult = $conn->query($checkQuery);
        $checkQuery2 = "SELECT COUNT(*) as count FROM program_host_company WHERE id = '$id'";
        $checkResult2 = $conn->query($checkQuery2);

        if ($checkResult->num_rows > 0 || $checkResult2->num_rows > 0 ) {
            $checkRow = $checkResult->fetch_assoc();
            $count = $checkRow['count'];

           
            header('Content-Type: application/json');
            echo json_encode(['duplicate' => $count > 0]);
            exit;
        }
    } else {
        $id = $_POST['id'];
        $pw = $_POST['pw'];
        $name = $_POST['name'];
        $phone = $_POST['phone'];
        $interest = $_POST['interest'];

        // 선호하는 카테고리의 ID 가져오기
        $categoryId = $_POST['interest'];
       
        // donor 테이블에 삽입
        $sql = "INSERT INTO donor (id, name, phone, pw, category_id) VALUES ('$id', '$name', '$phone', '$pw', '$categoryId')";

        if ($conn->query($sql) === TRUE) {
            $message = "회원가입이 완료되었습니다!";
            header('Location: login.php');
            exit();
        } else {
            $error_message = "Error: " . $sql . "<br>" . $conn->error;
        }
        
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 페이지</title>
    
</head>
<body>
<script>
    function checkDuplicate() {
        var idInput = document.getElementById("id");
        if (idInput.value.trim() === "") {
            alert("아이디를 입력하세요.");
            return;
        }

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState === 4 && xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                if (response.duplicate) {
                    alert("이미 사용 중인 아이디입니다.");
                } else {
                    alert("사용 가능한 아이디입니다.");
                }
            }
        };

        var data = "checkDuplicate=1&id=" + idInput.value.trim();

        xhr.send(data);
    }
</script>

<form id="signup-form" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
    <h2>회원가입(개인)</h2>
    <?php if (isset($error_message)) { ?>
        <div style="color: red;"><?php echo $error_message; ?></div>
    <?php } ?>
    <?php if (isset($message)) { ?>
        <div style="color: green;"><?php echo $message; ?></div>
    <?php } ?>
    <label for="id">
        <span>아이디:</span>
        <input type="text" id="id" name="id" required>
        <button type="button" id="check-duplicate" onclick="checkDuplicate()">중복확인</button>
    </label>
    <br>
    <label for="pw">
        <span>비밀번호:</span>
        <input type="password" id="pw" name="pw" required>
    </label>
     <br>
    <label for="name">
        <span>이름:</span>
        <input type="text" id="name" name="name" required>
    </label>
    <br>    
    <label for="phone">
        <span>전화번호:</span>
        <input type="tel" id="phone" name="phone" required>
    </label>
    <br>
<label>
    <span>관심분야:</span>
    <?php
        $allCategoriesQuery = "SELECT id, name FROM category";
        $allCategoriesResult = $conn->query($allCategoriesQuery);

        if ($allCategoriesResult->num_rows > 0) {
            while ($categoryRow = $allCategoriesResult->fetch_assoc()) {
                $categoryId = $categoryRow['id'];
                $categoryName = $categoryRow['name'];

                echo "<label for='category$categoryId'><input type='radio' id='category$categoryId' name='interest' value='$categoryId' required> $categoryName</label>";
            }
        } else {
            echo "카테고리가 없습니다.";
        }
    ?>
</label>
<br>
    <button type="submit">완료</button>
</form>
</body>
</html>
