<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>회사 정보 및 운영중인 프로그램 목록</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .container { display: flex; }
        .company { flex: 1; margin-right: 20px; }
        .company h2 { margin-top: 0; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 5px; font-size: 0.9em; white-space: nowrap; }
        .program-name { max-width: 200px; overflow: hidden; text-overflow: ellipsis; }
        .program-name:hover { max-width: none; overflow: visible; white-space: normal; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        form { margin-bottom: 20px; text-align: center; }
    </style>
</head>
<body>
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "1234";
    $dbname = "후원관리시스템";

    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);
    
        $searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';

        echo "<h1>회사 정보 및 운영중인 프로그램 목록</h1>";
        echo "<a href='manage_login.php'>이전으로 돌아가기</a>";
        echo "<form method='get'>";
        echo "<input type='text' name='search' placeholder='회사 이름 검색...' value='$searchKeyword'>";
        echo "<input type='submit' value='검색'>";

        // 검색 초기화 버튼
        echo "<button type='button' onclick='window.location.href=window.location.pathname'>검색 초기화</button>";

        echo "</form>";
        //프로시저 호출
        $sqlCompany = "CALL search_Company_By_Name(:searchKeyword)";
        $stmtCompany = $conn->prepare($sqlCompany);
        $stmtCompany->bindValue(':searchKeyword', '%' . $searchKeyword . '%');
        $stmtCompany->execute();
        $companies = $stmtCompany->fetchAll(PDO::FETCH_ASSOC);
        $stmtCompany->closeCursor(); 
        
        foreach ($companies as $company) {
            echo "<h2>".$company['company_name']."</h2>";
            echo "<p>주소: ".$company['address']."</p>";
            echo "<p>전화번호: ".$company['company_phone_number']."</p>";
            echo "<p>사업자 등록번호: ".$company['business_number']."</p>";

            $companyId = $company['id'];
            $sqlPrograms = "SELECT * FROM program WHERE program_host_company_id = :company_id";
            $stmtPrograms = $conn->prepare($sqlPrograms);
            $stmtPrograms->bindParam(':company_id', $companyId);
            $stmtPrograms->execute();
            $programs = $stmtPrograms->fetchAll(PDO::FETCH_ASSOC);
        
            echo "<h3>운영중인 프로그램</h3>";
            echo "<table>";
            echo "<tr><th>프로그램 이름</th><th>상태</th></tr>";
            foreach ($programs as $program) {
                $programName = $program['program_name'];
                $programStatus = $program['status'];
        
                $programLink = "program_manage_detail.php?program_name=" . urlencode($programName);
        
                echo "<tr>";
                echo "<td><a href='$programLink'>$programName</a></td>";
                echo "<td>".$programStatus."</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    } catch(PDOException $e) {
        echo "오류: " . $e->getMessage();
    }

    $conn = null;
    ?>
</body>
</html>