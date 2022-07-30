<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<?php
$dbhost = 'localhost';
$dbuser = 'root';
$dbpass = '';
$dbname = 'testv4';
$tables = '*'; // * = tot sau vrp_users / vrp_user_vehicles ... etc... pentru cazuri particulare.
 ;
if(isset($_POST['db'])) {backup_tables($dbhost, $dbuser, $dbpass, $dbname, $tables);}
function backup_tables($host, $user, $pass, $dbname, $tables = '*') {
    $link = mysqli_connect($host,$user,$pass, $dbname);
    if (mysqli_connect_errno()){
        echo "Failed to connect to MySQL: " . mysqli_connect_error();
        exit;
    }

    mysqli_query($link, "SET NAMES 'utf8'");

    if($tables == '*'){
        $tables = array();
        $result = mysqli_query($link, 'SHOW TABLES');
        while($row = mysqli_fetch_row($result))
        {
            $tables[] = $row[0];
        }
    }
    else{
        $tables = is_array($tables) ? $tables : explode(',',$tables);
    }

    $return = '';
    foreach($tables as $table){
        $result = mysqli_query($link, 'SELECT * FROM '.$table);
        $num_fields = mysqli_num_fields($result);
        $num_rows = mysqli_num_rows($result);

        $return.= 'DROP TABLE IF EXISTS '.$table.';';
        $row2 = mysqli_fetch_row(mysqli_query($link, 'SHOW CREATE TABLE '.$table));
        $return.= "\n\n".$row2[1].";\n\n";
        $counter = 1;
        for ($i = 0; $i < $num_fields; $i++){   
            while($row = mysqli_fetch_row($result)){   
                if($counter == 1){
                    $return.= 'INSERT INTO '.$table.' VALUES(';
                } else{
                    $return.= '(';
                }
                for($j=0; $j<$num_fields; $j++) 
                {
                    $row[$j] = addslashes($row[$j]);
                    $row[$j] = str_replace("\n","\\n",$row[$j]);
                    if (isset($row[$j])) { $return.= '"'.$row[$j].'"' ; } else { $return.= '""'; }
                    if ($j<($num_fields-1)) { $return.= ','; }
                }

                if($num_rows == $counter){
                    $return.= ");\n";
                } else{
                    $return.= "),\n";
                }
                ++$counter;
            }
        }
        $return.="\n\n\n";
    }
    $fileName = $dbname.'-'.date("Y-m-d H-i-s").'.sql';    
    $handle = fopen($fileName,'w+');
    fwrite($handle,$return);
    if(fclose($handle)){
        return $fileName;
        exit; 
    }
}

?>

<script>
var min_start = 1;
function startTimer(duration, display) {
    var timer = duration, min, sec;
    setInterval(function () {
        min = parseInt(timer / 60, 10);
        sec = parseInt(timer % 60, 10);

        min = min < 10 ? "0" + min : min;
        sec = sec < 10 ? "0" + sec : sec;

        display.textContent = min + ":" + sec;

        if (--timer < 0) {
            $.post('',{db : ''}).done(function(){
                timer = duration;
            });
        }
    }, 1000);
}

window.onload = function () {
    var cooldown = 60 * min_start,
        display = document.querySelector('#time');
    startTimer(cooldown, display);
};
</script>

<body>
    <div>se face backup in <span id="time"></span> minute <font color='red'>NU INCHIDE PAGINA</font></div>
</body>
