<?php

session_start();

if (!isset($_SESSION['userID'])) {
    header('Location: http://localhost/donation.php');
    exit();
}

$servername = "localhost";
$username = "root";
$password = "1234";
$dbname = "후원관리시스템";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 사용자 아이디
$userID = $_SESSION['userID'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $password = $_POST["pw"];
    $name = $_POST["name"];
    $phone = $_POST["phone"];
    $interest = $_POST["interest"];

    $sql = "UPDATE donor SET pw='$password', name='$name', phone='$phone', 
            category_id = (SELECT id FROM category WHERE name='$interest') 
            WHERE id='$userID'";

    if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['confirmDelete'])) {
        if ($conn->query($sql) === TRUE) {
            echo "회원정보가 성공적으로 업데이트되었습니다.";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
    
    if (isset($_POST['confirmDelete'])) {
        // 사용자의 ID에 해당하는 donor 테이블 삭제
        $sql_delete_donor = "DELETE FROM `donor` WHERE `id` = '$userID'";
        $result_delete_donor = mysqli_query($conn, $sql_delete_donor);

        if ($result_delete_donor) {
            echo '<p>회원 탈퇴가 완료되었습니다. </p>';
            echo '<a href="http://localhost/login.php">로그인 페이지로 돌아가기</a>';
            exit();
        } else {
            echo "회원 탈퇴 중 오류가 발생했습니다: " . mysqli_error($conn);
        }
    }
}

// 사용자 정보
$sql_select = "SELECT donor.*, category.name AS category_name
               FROM donor
               JOIN category ON donor.category_id = category.id
               WHERE donor.id='$userID'";
$result = $conn->query($sql_select);

$userData = array();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userData['password'] = $row['pw'];
    $userData['name'] = $row['name'];
    $userData['phone'] = $row['phone'];
    $userData['interest_name'] = $row['category_name'];
} else {
    echo "사용자 정보를 가져오는 데 실패했습니다.";
}

// 모든 카테고리 가져오기
$allCategoriesQuery = "SELECT id, name FROM category";
$allCategoriesResult = $conn->query($allCategoriesQuery);

$allCategories = array();
if ($allCategoriesResult->num_rows > 0) {
    while ($categoryRow = $allCategoriesResult->fetch_assoc()) {
        $allCategories[] = $categoryRow;
    }
} else {
    echo "카테고리가 없습니다.";
}

$conn->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원정보 수정</title>
</head>
<body>

<form id="update-form" method="post" action="">
    <h2>회원정보 수정</h2>
    <button type="button" onclick="deleteAccount()">회원탈퇴</button>
    <br><br>
    <label for="id">
        <span>아이디:</span>
        <input type="text" id="id" name="id" value="<?php echo $userID; ?>" readonly>
    </label>
    <br>
    <label for="pw">
        <span>비밀번호:</span>
        <input type="text" id="pw" name="pw" value="<?php echo $userData['password']; ?>" required>
    </label>
    <br>
    <label for="name">
        <span>이름:</span>
        <input type="text" id="name" name="name" value="<?php echo $userData['name']; ?>" required>
    </label>
    <br>
    <label for="phone">
        <span>전화번호:</span>
        <input type="tel" id="phone" name="phone" value="<?php echo $userData['phone']; ?>" required>
    </label>
    <br>
    <label>
        <span>관심분야:</span>
        <?php
        foreach ($allCategories as $category) {
            echo '<label for="' . $category['name'] . '"><input type="radio" id="' . $category['name'] . '" name="interest" value="' . $category['name'] . '" ';
            echo $userData['interest_name'] == $category['name'] ? 'checked' : '';
            echo ' required>' . ucfirst($category['name']) . '</label>';
        }
        ?>
    </label>
    <br><br>
    <button type="submit" onclick="saveChanges()">저장</button>
    <br>
    <button type="button" onclick="goBack()">뒤로 가기</button>
    <button type="submit" name="confirmDelete" style="display: none;">회원탈퇴</button>
</form>

<script>
    function saveChanges() {
        var password = document.getElementById("pw").value;
        var name = document.getElementById("name").value;
        var phone = document.getElementById("phone").value;
        var interest = document.querySelector('input[name="interest"]:checked').value;

        var xhr = new XMLHttpRequest();
        xhr.open("POST", "", true);
        xhr.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                alert("정보가 업데이트 되었습니다");
            }
        };
        xhr.send("pw=" + password + "&name=" + name + "&phone=" + phone + "&interest=" + interest);
    }

    function goBack() {
        window.location.href = 'http://localhost/donation.php';
    }

    function deleteAccount() {
        var confirmDelete = confirm("정말로 회원을 탈퇴하시겠습니까?");
        if (confirmDelete) {
            document.getElementsByName('confirmDelete')[0].click();
        }
    }
</script>

</body>
</html>
