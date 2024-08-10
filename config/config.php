<?PHP
// define('SERVER','localhost');
// define('USERNAME','ANAN');
// define('PASSWORD','1234');
// define('DATABASE_NAME','eccomercesite');
if (!defined('SERVER')) define('SERVER', 'localhost');
if (!defined('USERNAME')) define('USERNAME', 'ANAN');
if (!defined('PASSWORD')) define('PASSWORD', '1234');
if (!defined('DATABASE_NAME')) define('DATABASE_NAME', 'eccomercesite');

$conn = new mysqli(SERVER, USERNAME, PASSWORD, DATABASE_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}