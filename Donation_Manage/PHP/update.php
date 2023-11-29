<?php
session_start();

// 로그인한 회사 ID 가져오기
$company_id = $_GET['company_id'] ?? 'unknown_company_id';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    

    if (isset($company_id) && $company_id !== 'unknown_company_id') {

        $conn = new mysqli('localhost', 'root', '1234', '후원관리시스템');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
    
        $pw = mysqli_real_escape_string($conn, $_POST['pw']);
        $company_name = mysqli_real_escape_string($conn, $_POST['company_name']);
        $address = mysqli_real_escape_string($conn, $_POST['address']);
        $company_phone_number = mysqli_real_escape_string($conn, $_POST['company_phone_number']);
        $business_number = mysqli_real_escape_string($conn, $_POST['business_number']);
    
       
        $sql = "UPDATE program_host_company SET pw='$pw', company_name='$company_name', address='$address', 
        company_phone_number='$company_phone_number', business_number='$business_number' WHERE id='$company_id'";
    
        $ret = $conn->query($sql);
    
        if (!$ret) {
            echo "실행에 문제가 있습니다: " . $conn->error;
        } else {
            echo "<h1>회사 정보 수정</h1>";
            echo "정보가 수정되었습니다.";
            header("Location: http://localhost/company_dd.php");
        }
    
        $conn->close();
    } else {
        header("Location: http://localhost/login.php");
        exit();
    }
} else {
    
    if (isset($company_id) && $company_id !== 'unknown_company_id') {
        $conn = new mysqli('localhost', 'root', '1234', '후원관리시스템');
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        $sql = "SELECT * FROM program_host_company WHERE id = '$company_id'";
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $id = $row['id'];
            $pw = $row['pw'];
            $company_name = $row['company_name'];
            $address = $row['address'];
            $company_phone_number = $row['company_phone_number'];
            $business_number = $row['business_number'];

            echo "<h1>회사 정보 수정</h1>";
            echo "<form method='post' action=''>";
            echo "<label>ID:</label><input type='text' name='id' value='$id' readonly><br>";
            echo "<label>비밀번호:</label><input type='password' name='pw' value='$pw' required><br>";
            echo "<label>단체 이름:</label><input type='text' name='company_name' value='$company_name' required><br>";
            echo "<label>주소:</label><input type='text' name='address' value='$address' required><br>";
            echo "<label>단체 전화번호:</label><input type='text' name='company_phone_number' value='$company_phone_number' required><br>";
            echo "<label>사업자 등록 번호:</label><input type='text' name='business_number' value='$business_number'><br>";
            echo "<br><input type='submit' value='수정'>";
            echo "</form>";
            
            
            echo "<br><br><a href='http://localhost/company_dd.php'>뒤로가기</a>";
        } else {
            echo "단체 정보를 찾을 수 없습니다.";
        }

        $conn->close();
    } else {
        //header("Location: http://localhost/login.php");
        exit();
    }
}
?>
