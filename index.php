<?php







//turn on debugging messages
ini_set('display_errors', 'On');
error_reporting(E_ALL);


//instantiate the program object

//Class to load classes, it finds the file when the program fails to call a missing class
class Manage {
    public static function autoload($class) {
        //you can put any file name or directory here
        include $class . '.php';
    }
}

spl_autoload_register(array('Manage', 'autoload'));

//instantiate the program object
$obj = new main();


class main {

    public function __construct()
    {
        //print_r($_REQUEST);
        //set default page request when no parameters are in the URL
        $pageRequest = 'homepage';
        $filename='';
        //check if there are parameters
        if(isset($_REQUEST['page'])) {
            //load the type of page the request wants into page request
            $pageRequest = $_REQUEST['page'];

            //This will check additional file name parameter.
            if(isset($_REQUEST['file'])){
                $filename= $_REQUEST['file'];
            }

        }
        //instantiate the class that is being requested
        $page = new $pageRequest($filename);


        if($_SERVER['REQUEST_METHOD'] == 'GET') {
            $page->get();
        } else {
            //When form Submits the request, Object of the uploadform will be dynamically created
            //this object will call post method inside the uploadform class.
            $page->post();
        }

    }

}

abstract class page {
    protected $html;

    public function __construct()
    {
        $this->html .= '<html>';
        $this->html .= '<link rel="stylesheet" href="styles.css">';
        $this->html .= '<body>';
    }
    public function __destruct()
    {
        $this->html .= '</body></html>';
        stringFunctions::printThis($this->html);
    }

    public function get() {
        echo 'default get message';
    }


    public function post() {
        //print_r($_POST);
    }
}

class homepage extends page {

    private $f_name;

     public function __construct($filename)
    {
        parent::__construct();
        $this->f_name=$filename;
    }

    //get function will return form through which user can upload a file on server
    //Post method will call post method from 'uploadform' class
    public function get() {

        $form = '<form action="index.php?page=uploadform" method="post" enctype="multipart/form-data">';
        $form .= '<input type="file" name="fileToUpload" id="fileToUpload">';
        $form .= '<input type="submit" value="Upload File" name="submit">';
        $form .= '</form> ';
        $this->html .= '<h1>Upload Form</h1>';
        $this->html .= $form;

    }

}

class uploadform extends page
{

    public function get()
    {

    }

    public function post() {

        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;

        /*Check if the file is already present or not*/
        if (file_exists($target_file)) {
        echo "Sorry, file already exists.";
        $uploadOk = 0;
        }
        /*Check for the bigger file size,so that big file will not be loaded on webserver utilizing its
        full space*/
        if ($_FILES["fileToUpload"]["size"] > 500000) {
            echo "Sorry, your file is too large.";
            $uploadOk = 0;
        }

        if ($uploadOk == 0) {
            echo "Sorry, your file was not uploaded.";
            // if everything is ok, try to upload file
        } else {
            if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $target_file)) {

                //File upload successful, Now using header,re-directing call
                header('Location: index.php?page=htmlTable&file='.$target_file);


            } else {


                //If the file upload is not successful, return the error page.
                header('Location: index.php?page=errorhtmlPage');
            }
        }




    }
}


/*This class will be used to deliver csv file content
in table form*/
class htmlTable extends page {

    private $table;
    private $f_name;
    public function __construct($file)
    {
        //call the parent constructor,so that html page will be intialized
        parent::__construct();
        $this->f_name=$file;

    }

    public function get(){



        $this->html .='<table>';
        $this->createTable($this->f_name);
        $this->html .=$this->table;
        $this->html.='</table>';




    }



    /*This function will be use to read the CSV file from the server and
    print int as HTML table.*/

    public function createTable($filename)
    {

        $f = fopen($filename, "r");
        while (($line = fgetcsv($f)) !== false) {
            $this->table.="<tr>";
            foreach ($line as $cell) {
                $this->table.="<td>" . htmlspecialchars($cell) . "</td>";
            }
            //echo "</tr>\n";
            $this->table.="</tr>";
        }
        fclose($f);


    }




}

//All Error page will use this class
class errorhtmlPage extends page{

    public function get(){
        //If some error occurs while uploading the csv file, display the errorpage
        $this->html .'<h1>'.'500'.'</h1>';
        $this->html .='<h2>'."Sorry,there was an error uploading your file .Please try again Later".'</h2>';
       
    }
}

/*Class String Function
*/
class stringFunctions{

    //This fution will print HTML page
    public static function printThis($text){
        print($text);
    }

}


?>
