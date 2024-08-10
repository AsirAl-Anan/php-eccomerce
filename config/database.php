<?PHP
include 'config.php';
class Database{
    //properties
    public $server_name = SERVER;
    public $username = USERNAME;
    public $password = PASSWORD;
    public $db_name = DATABASE_NAME;
    public $error;
    public $conn;



//methods
//db connect
public function __construct(){
    $this->dbConnect();
}

   public function dbConnect(){
    $this->conn = mysqli_connect($this->server_name,$this->username,$this->password,$this->db_name);
    
    if($this->conn){
        echo 'database connection established';

    }else{
        $this->error = "Database connection failed";
        return false;
    }
}
public function insert($sql){
    $result = mysqli_query($this->conn,$sql) or die($this->conn->error.__LINE__);
    if($result){
        return true;
    }else{
            return false;
        }
    }
   
}


