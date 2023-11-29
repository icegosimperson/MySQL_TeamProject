<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>단체 회원 가입</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            text-align: center;
            margin: 50px;
        }

        h1 {
            color: #333;
        }

        form {
            max-width: 400px;
            margin: 0 auto;
        }

        label {
            display: block;
            margin: 10px 0 5px;
            font-weight: bold;
            color: #555;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 15px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #888;
            color: white;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #777;
        }
    </style>
</head>
<body>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $con = mysqli_connect("localhost", "root", "1234", "후원관리시스템") or die("MySQL 접속 실패 !!");

    $id = $_POST["id"];
    $pw = $_POST["pw"];
    $company_name = $_POST["company_name"];
    $address = $_POST["address"];
    $company_phone_number = $_POST["company_phone_number"];
    $business_number = $_POST["business_number"];

    $check_id_query = "SELECT id FROM program_host_company WHERE id = '$id'";
    $result = mysqli_query($con, $check_id_query);

    if (mysqli_num_rows($result) > 0) {
        echo "<h1> 회원 가입 실패 </h1>";
        echo "<p style='color:red;'>이미 존재하는 아이디입니다. </p>";
    } else {
        if ($_POST["pw_confirm"] != $pw) {
            echo "<h1> 회원 가입 실패 </h1>";
            echo "<p style='color:red;'>비밀번호가 일치하지 않습니다. </p>";
        } else {
            $sql = "INSERT INTO program_host_company (id, pw, company_name, address, company_phone_number, business_number) VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = mysqli_prepare($con, $sql);
            mysqli_stmt_bind_param($stmt, "ssssis", $id, $pw, $company_name, $address, $company_phone_number, $business_number);

            $ret = mysqli_stmt_execute($stmt);

            echo "<h1> 단체 회원 가입 </h1>";

            if ($ret) {
                echo "가입 완료.";
            } else {
                echo "데이터 입력 실패!!" . "<br>";
                echo "실패 원인 :" . mysqli_error($con);
            }

            mysqli_stmt_close($stmt);
        }
    }

    mysqli_close($con);

   
    echo "<br><br><a href='http://localhost/login.php'>뒤로가기</a>";
} else {
    
    echo "<br><br><a href='javascript:history.go(-1)'>뒤로가기</a>";
}
?>

<form method="post" action="">
    <label>ID:</label>
    <input type="text" name="id" required>

    <label>비밀번호:</label>
    <input type="text" name="pw" required>

    <label>비밀번호 확인:</label>
    <input type="password" name="pw_confirm" required>

    <label>단체 이름:</label>
    <input type="text" name="company_name" required호

    <label>주소:</label>
    <input type="text" name="address" required>

    <label>단체 전화번호:</label>
    <input type="text" name="company_phone_number" required>

    <label>사업자 등록 번호:</label>
    <input type="text" name="business_number">

    <br><br>
    <input type="submit" value="가입">
</form>

</body>
</html>


</form>