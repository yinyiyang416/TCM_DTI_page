<?php
//设置页面内容是html编码格式是utf-8
header("Content-Type: text/plain;charset=utf-8"); 
//判断如果是get请求，则执行getMethod();；如果是POST请求，则执行postMethod()。
//$_SERVER是一个超全局变量，在一个脚本的全部作用域中都可用，不用使用global关键字
if ($_SERVER["REQUEST_METHOD"] == "GET") {
    getMethod();
} elseif ($_SERVER["REQUEST_METHOD"] == "POST"){
    postMethod();
}
function searchShow(){    
}
function getMethod(){

}
function postMethod(){
    //使用超全局变量 $_GET 和 $_POST收集name对应的value，如下
    $gene_name = $_POST['name'];

    file_put_contents('input.txt', $gene_name."\n", FILE_APPEND | LOCK_EX);
    $content = file_get_contents('input.txt');
    
    // 数据库连接配置
    $servername = "192.168.199.109";
    $username = "tcm_user";
    $password = "123456";
    $dbname = "tcm_dti";
    // 创建数据库连接
    $conn = mysqli_connect($servername, $username, $password, $dbname);
    // 检测连接
    if (!$conn) {
        die("Connection failed: " . mysqli_connect_error());
    }
    // 构建查询语句
    $sql = "SELECT tcm1_1.TCMBank_ID, {$gene_name}, RANK() OVER(ORDER BY {$gene_name} desc) as 'Rank' 
    FROM tcm1_1 JOIN tcm1_2 ON tcm1_1.TCMBank_ID = tcm1_2.TCMBank_ID
    JOIN tcm2 ON tcm1_2.TCMBank_ID = tcm2.TCMBank_ID
    JOIN tcm3_1 ON tcm2.TCMBank_ID = tcm3_1.TCMBank_ID
    JOIN tcm3_2 ON tcm3_1.TCMBank_ID = tcm3_2.TCMBank_ID
    LIMIT 5";

    // 执行查询
    $result = $conn->query($sql);

    if(!$result){
        echo "<h4>find result error, please check the input</h4>";
        file_put_contents("output.txt", "get reslut for input ".$gene_name."fail\n", FILE_APPEND | LOCK_EX);
        die("get reslut for input ".$gene_name."fail\n".'无法读取数据,请联系管理员修复:'.mysqli_error($conn));
    }
    // 关闭数据库连接
    $conn->close();
    
    if ($result->num_rows > 0){
        echo "<h5>"."top 5 TCM herbs for ".$gene_name."</h5>";
        echo "<table><tr><th>TCM id</th><th>Score</th></tr>";
        while($row=mysqli_fetch_array($result)){
            $score = $row[$gene_name];
            $id = $row["TCMBank_ID"];
            echo "<tr>";
            echo "<td>".$id."</td>";
            echo "<td>".$score."</td>";
            echo "</tr>";
            file_put_contents("output.txt", $id, FILE_APPEND | LOCK_EX);
            file_put_contents("output.txt", $score."\n", FILE_APPEND | LOCK_EX);
            }
        echo "</table>";
    }
    else{
        echo "<h4>no result,please check the input</h4>";
        file_put_contents("output.txt", "no search result\n", FILE_APPEND | LOCK_EX);
    }
}
?>
