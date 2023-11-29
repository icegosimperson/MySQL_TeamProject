<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Login Form</title>
    <style>
        .login-container {
            max-width: 300px;
            margin: 0 auto;
            text-align: center; 
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 200px;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 3px;
        }
        .login-container input[type="submit"] {
            width: 200px;
            padding: 10px;
            background-color: #808080;
            color: #fff;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .signup-button-container {
            text-align: center;
            margin-top: 10px;
        }
        .signup-button {
            padding: 5px 10px;
            background-color: #808080;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
        }
        .title{
            text-align: center; 
        }
    </style>
</head>
<body>
    <div class="title"> 
        <h1>로그인</h1>
    </div>
    <div class="login-container">
        <form method="post" action="login.php">
            <input type="text" placeholder="아이디" name="userID" maxlength="20" required><br>
            <input type="text" placeholder="비밀번호" name="userPassword" maxlength="20" required><br>
            <input type="submit" value="로그인">
        </form>
        <div class="signup-button-container">
            <a href="http://localhost/apply_personal.php" class="signup-button">개인 회원가입</a>
            <a href="http://localhost/insert.php" class="signup-button">단체 회원가입</a>
        </div>
    </div>
    <?php
    session_start();

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $conn = new mysqli('localhost', 'root', '1234', '후원관리시스템');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $userID = mysqli_real_escape_string($conn, $_POST['userID']);
        $userPassword = mysqli_real_escape_string($conn, $_POST['userPassword']);

        $sql = "SELECT * FROM donor WHERE id = '$userID' AND pw = '$userPassword'";
        $result = $conn->query($sql);

        if (strncmp($userID, "Admin", 5) === 0 && $userPassword === 'admin1234') {
            header("Location: http://localhost/manage_login.php");
            exit();
        }

    // 사용자 유형을 확인하는 프로시저 호출
    $sql = "CALL CheckUserType('$userID', '$userPassword', @userType)";
    $result = $conn->query($sql);

    // 프로시저 호출 후 사용자 유형 가져오기
    $sql = "SELECT @userType AS userType";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $userType = $row['userType'];

    if ($result) {
        if ($userType === 'company') {
            $_SESSION['userID'] = $userID;
            header("Location: http://localhost/company_dd.php");
            exit();
        } elseif ($userType === 'donor') {
            $_SESSION['userID'] = $userID;
            header("Location: http://localhost/donation.php");
            exit();
        } else {
            echo "<script>
                    alert('일치하는 회원정보가 없거나 비밀번호가 틀렸습니다!');
                    window.location.href = 'login.php'; // 로그인 페이지로 리디렉션
                </script>";
            exit();
        }
    } else {
        echo "프로시저 호출 중 에러: " . $conn->error;
    }

    $conn->close();
    }
    ?>
</body>
</html>
